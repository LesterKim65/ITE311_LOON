<?php

namespace App\Controllers;

use App\Models\UserModel;

class Home extends BaseController
{
    public function index()
    {
        if ($this->request->getGet('register') !== null) {
            $rules = [
                'name'     => 'required|min_length[3]|max_length[100]',
                'email'    => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]',
            ];

            if (! $this->validate($rules)) {
                return redirect()->to('/')->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $userModel->insert([
                'name'     => (string) $this->request->getGet('name'),
                'email'    => (string) $this->request->getGet('email'),
                'password' => password_hash((string) $this->request->getGet('password'), PASSWORD_DEFAULT),
                'role'     => 'student',
            ]);

            return redirect()->to('/')->with('message', 'Registration successful. You can now log in.');
        }

        return view('index'); // loads app/Views/template.php
    }
    public function about()
    {
        return view('about'); // loads app/Views/template.php
    }   public function contact()
    {
        return view('contact'); // loads app/Views/contact.php
    }

}
