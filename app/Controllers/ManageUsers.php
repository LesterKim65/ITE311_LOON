<?php

namespace App\Controllers;

use App\Models\UserModel;

class ManageUsers extends BaseController
{
    // Protected admin email - the main admin that cannot be deleted or have role changed
    private const PROTECTED_ADMIN_EMAIL = 'admin@example.com';

    public function index()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access denied. Admin privileges required.');
        }

        $userModel = new UserModel();
        $users = $userModel->orderBy('id', 'ASC')->findAll();

        // Ensure all users have a status (set NULL to 'active' for existing users)
        foreach ($users as &$user) {
            if (!isset($user['status']) || empty($user['status'])) {
                // Update user in database to set status
                $userModel->update($user['id'], ['status' => 'active']);
                $user['status'] = 'active';
            }
        }

        return view('manage-users/index', [
            'users' => $users,
            'protectedAdminEmail' => self::PROTECTED_ADMIN_EMAIL
        ]);
    }

    public function add()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ]);
        }

        helper(['form']);

        // Always use default password "password123"
        $password = 'password123';

        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'role'     => 'required|in_list[student,teacher,admin]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(' ', $this->validator->getErrors())
            ]);
        }

        $userModel = new UserModel();

        $data = [
            'name'     => esc($this->request->getPost('name')),
            'email'    => esc($this->request->getPost('email')),
            'password' => password_hash($password, PASSWORD_DEFAULT), // Use default password 'password123'
            'role'     => esc($this->request->getPost('role')),
            'status'   => 'active', // New users are always active
        ];

        try {
            if ($userModel->save($data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'User created successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create user. ' . implode(', ', $userModel->errors())
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    public function update()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ]);
        }

        $userId = $this->request->getPost('user_id');
        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User ID is required.'
            ]);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Check if this is the protected admin
        if ($user['email'] === self::PROTECTED_ADMIN_EMAIL) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot edit protected admin account.'
            ]);
        }

        helper(['form']);

        $rules = [
            'name'  => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $userId . ']',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(' ', $this->validator->getErrors())
            ]);
        }

        $data = [
            'id'    => $userId,
            'name'  => esc($this->request->getPost('name')),
            'email' => esc($this->request->getPost('email')),
        ];

        try {
            if ($userModel->save($data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'User updated successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update user. ' . implode(', ', $userModel->errors())
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    public function delete()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ]);
        }

        $userId = $this->request->getPost('user_id');
        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User ID is required.'
            ]);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Check if this is the protected admin
        if ($user['email'] === self::PROTECTED_ADMIN_EMAIL) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot deactivate protected admin account.'
            ]);
        }

        // Prevent deactivating yourself
        if ($userId == session()->get('id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You cannot deactivate your own account.'
            ]);
        }

        try {
            // Deactivate user: Mark user as inactive instead of actually deleting
            $data = [
                'id'     => $userId,
                'status' => 'inactive'
            ];

            if ($userModel->save($data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'User deactivated successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to deactivate user. ' . implode(', ', $userModel->errors())
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    public function changeRole()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ]);
        }

        $userId = $this->request->getPost('user_id');
        $newRole = $this->request->getPost('role');

        if (!$userId || !$newRole) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User ID and role are required.'
            ]);
        }

        if (!in_array($newRole, ['student', 'teacher', 'admin'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid role specified.'
            ]);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Check if this is the protected admin - prevent demoting
        if ($user['email'] === self::PROTECTED_ADMIN_EMAIL && $newRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot change role of protected admin account.'
            ]);
        }

        try {
            $data = [
                'id'   => $userId,
                'role' => esc($newRole)
            ];

            if ($userModel->save($data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'User role updated successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update role. ' . implode(', ', $userModel->errors())
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    public function changePassword()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ]);
        }

        $userId = $this->request->getPost('user_id');
        $newPassword = $this->request->getPost('password');

        if (!$userId || !$newPassword) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User ID and password are required.'
            ]);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Validate password strength
        if (strlen($newPassword) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $newPassword)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Password must be at least 8 characters and contain uppercase, lowercase, number, and special character.'
            ]);
        }

        try {
            $data = [
                'id'       => $userId,
                'password' => password_hash($newPassword, PASSWORD_DEFAULT)
            ];

            if ($userModel->save($data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Password updated successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update password. ' . implode(', ', $userModel->errors())
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    public function restore()
    {
        // Check if user is logged in and is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ]);
        }

        $userId = $this->request->getPost('user_id');
        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User ID is required.'
            ]);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        try {
            // Activate user: Mark user as active
            $data = [
                'id'     => $userId,
                'status' => 'active'
            ];

            if ($userModel->save($data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'User activated successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to activate user. ' . implode(', ', $userModel->errors())
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
}

