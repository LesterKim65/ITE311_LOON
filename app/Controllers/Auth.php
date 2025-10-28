<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\NotificationModel;

class Auth extends BaseController
{
	public function register()
	{
		helper(['form']);

		log_message('info', 'Auth::register method: {method}', ['method' => $this->request->getMethod()]);

		// Treat as submission whenever there is POST data (handles environments where method check is unreliable)
		if (! empty($this->request->getPost())) {
			log_message('info', 'Auth::register POST received');
			$rules = [
				'name'             => 'required|min_length[3]|max_length[50]',
				'email'            => 'required|valid_email|is_unique[users.email]',
				'password'         => 'required|min_length[6]|max_length[255]',
				'password_confirm' => 'required|matches[password]',
				'role'             => 'required|in_list[student,teacher,admin]'
			];

			if (! $this->validate($rules)) {
				$errorString = (string) implode(' ', $this->validator->getErrors());
				log_message('warning', 'Auth::register validation failed: {errors}', [
					'errors' => $errorString,
				]);
				// Return the view directly so errors show without relying on session/flashdata
				$unreadCount = session()->get('isLoggedIn') ? (new NotificationModel())->getUnreadCount(session()->get('id')) : 0;
				return view('auth/register', [
					'validation' => $this->validator,
					'unreadCount' => $unreadCount
				]);
			}

			$userModel = new UserModel();

			$data = [
				'name'     => $this->request->getPost('name'),
				'email'    => $this->request->getPost('email'),
				'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
				'role'     => $this->request->getPost('role'),
			];

			try {
				if (! $userModel->save($data)) {
					$errors = $userModel->errors();
					$message = $errors ? implode(' ', $errors) : 'Unknown error.';
					log_message('error', 'Auth::register model save errors: {msg}', ['msg' => $message]);
					return redirect()->back()->withInput()->with('error', 'Registration failed: ' . $message);
				}
			} catch (\Throwable $e) {
				log_message('critical', 'Auth::register exception: {msg}', ['msg' => $e->getMessage()]);
				return redirect()->back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
			}

			// Render login view directly to avoid redirect issues
			$unreadCount = session()->get('isLoggedIn') ? (new NotificationModel())->getUnreadCount(session()->get('id')) : 0;
			return view('auth/login', [
				'success' => 'Registration successful! Please login.',
				'unreadCount' => $unreadCount
			]);
		}

		$unreadCount = session()->get('isLoggedIn') ? (new NotificationModel())->getUnreadCount(session()->get('id')) : 0;
		return view('auth/register', ['unreadCount' => $unreadCount]);
	}

	public function login()
	{
		helper(['form']);

		log_message('info', 'Auth::login method: {method}', ['method' => $this->request->getMethod()]);

		if (! empty($this->request->getPost())) {
			$rules = [
				'email'    => 'required|valid_email',
				'password' => 'required|min_length[6]|max_length[255]'
			];

			if (! $this->validate($rules)) {
				log_message('warning', 'Auth::login validation failed: {errors}', [
					'errors' => implode(' ', $this->validator->getErrors()),
				]);
				$unreadCount = session()->get('isLoggedIn') ? (new NotificationModel())->getUnreadCount(session()->get('id')) : 0;
				return view('auth/login', [
					'validation' => $this->validator,
					'unreadCount' => $unreadCount
				]);
			}

			$userModel = new UserModel();
			$user = $userModel->where('email', $this->request->getPost('email'))->first();

			if ($user && password_verify($this->request->getPost('password'), $user['password'])) {
				$sessionData = [
					'id'         => $user['id'],
					'name'       => $user['name'],
					'email'      => $user['email'],
					'role'       => $user['role'],
					'isLoggedIn' => true
				];

				// Set session data
				foreach ($sessionData as $key => $value) {
					session()->set($key, $value);
				}

				log_message('info', 'Login successful for user: {email}', ['email' => $user['email']]);
				log_message('info', 'Session data set: {data}', ['data' => json_encode($sessionData)]);

				// Test if session is working
				$testSession = session()->get('isLoggedIn');
				log_message('info', 'Session test - isLoggedIn: {value}', ['value' => $testSession]);

				return redirect()->to(site_url('dashboard'))
					->with('success', 'Welcome back, ' . $user['name'] . '!');
			}

			log_message('warning', 'Login failed for email: {email}', ['email' => $this->request->getPost('email')]);
			$unreadCount = session()->get('isLoggedIn') ? (new NotificationModel())->getUnreadCount(session()->get('id')) : 0;
			return view('auth/login', ['error' => 'Invalid login credentials.', 'unreadCount' => $unreadCount]);
		}

		$unreadCount = session()->get('isLoggedIn') ? (new NotificationModel())->getUnreadCount(session()->get('id')) : 0;
		return view('auth/login', ['unreadCount' => $unreadCount]);
	}

	public function dashboard()
	{
		if (! session()->get('isLoggedIn')) {
			return redirect()->to('/login');
		}

		$user_id = session()->get('id');
		$role = session()->get('role');

		$db = \Config\Database::connect();

		$data = [
			'name' => session()->get('name'),
			'role' => $role
		];

		if ($role == 'student') {
			// Get enrolled courses
			$enrolledCourses = $db->query("SELECT c.id, c.title, c.description FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = ?", [$user_id])->getResultArray();
			$data['enrolledCourses'] = $enrolledCourses;

			// Get available courses (not enrolled)
			$enrolledCourseIds = array_column($enrolledCourses, 'id');
			if (count($enrolledCourseIds) > 0) {
				$placeholders = implode(',', array_fill(0, count($enrolledCourseIds), '?'));
				$sql = "SELECT id, title, description FROM courses WHERE id NOT IN ($placeholders)";
				$availableCourses = $db->query($sql, $enrolledCourseIds)->getResultArray();
			} else {
				$availableCourses = $db->query("SELECT id, title, description FROM courses")->getResultArray();
			}
			$data['availableCourses'] = $availableCourses;
		} elseif ($role == 'teacher' || $role == '') {
			// Handle case where role might be empty but user is a teacher based on courses
			$teacherCourses = $db->query("SELECT id, title, description FROM courses WHERE instructor_id = ?", [$user_id])->getResultArray();
			if (!empty($teacherCourses)) {
				// User has courses assigned as instructor, treat as teacher
				$data['courses'] = $teacherCourses;
				$data['role'] = 'teacher'; // Override role for display
			}
		} elseif ($role == 'admin') {
			$users = $db->query("SELECT id, name, email, role FROM users")->getResultArray();
			$courses = $db->query("SELECT id, title, description, instructor_id FROM courses")->getResultArray();
			$data['users'] = $users;
			$data['courses'] = $courses;
		}

		$unreadCount = (new NotificationModel())->getUnreadCount($user_id);
		$data['unreadCount'] = $unreadCount;
		return view('auth/dashboard', $data);
	}

	public function logout()
	{
		session()->destroy();
		return redirect()->to(site_url('login'))
			->with('success', 'You have been logged out.');
	}

	// TEMP: Debug helper to verify DB insert works without the form/CSRF

}
