<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
	public function register()
	{
		return view('register');
	}

	public function store(): RedirectResponse
	{
		$rules = [
			'name'     => 'required|min_length[3]|max_length[100]',
			'email'    => 'required|valid_email|is_unique[users.email]',
			'password' => 'required|min_length[6]',
		];

		if (! $this->validate($rules)) {
			return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
		}

		$userModel = new UserModel();

		$data = [
			'name'     => $this->request->getPost('name'),
			'email'    => $this->request->getPost('email'),
			'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
			'role'     => 'student',
		];

		$userModel->insert($data);

		return redirect()->to('/')->with('message', 'Registration successful. You can now log in.');
	}
}

