<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'course_id', 'enrolled_at', 'status'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function enrollUser($data)
    {
        $data['enrolled_at'] = date('Y-m-d H:i:s');
        $data['status'] = 'active';
        return $this->insert($data);
    }

    public function getUserEnrollments($user_id)
    {
        return $this->select('enrollments.*, courses.title, courses.description')
                    ->join('courses', 'enrollments.course_id = courses.id')
                    ->where('enrollments.user_id', $user_id)
                    ->findAll();
    }

    public function isAlreadyEnrolled($user_id, $course_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('course_id', $course_id)
                    ->first() !== null;
    }
}
