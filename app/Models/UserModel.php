<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'email', 'password', 'role', 'status'];

    // Enable automatic timestamps if your table has created_at and updated_at columns
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Optionally, you can add validation rules here as well
    protected $validationRules = [
        'name'     => 'required|min_length[3]|max_length[50]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]|max_length[255]',
    ];

    // Get only active users by default
    public function getActiveUsers()
    {
        return $this->where('status', 'active')->findAll();
    }

    // Get all users including inactive
    public function getAllUsers()
    {
        return $this->findAll();
    }
}