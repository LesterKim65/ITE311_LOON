<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;

class Course extends BaseController
{
    public function index()
    {
        $courseModel = new CourseModel();
        $courses = $courseModel->orderBy('title', 'ASC')->findAll();

        return view('courses/index', [
            'courses' => $courses,
            'searchTerm' => ''
        ]);
    }

    public function search()
    {
        $searchTerm = $this->request->getGet('search_term');
        if ($searchTerm === null) {
            $searchTerm = $this->request->getPost('search_term');
        }

        $courseModel = new CourseModel();
        $courseModel->applySearchFilter($searchTerm)->orderBy('title', 'ASC');
        $courses = $courseModel->findAll();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($courses);
        }

        return view('courses/index', [
            'courses' => $courses,
            'searchTerm' => $searchTerm
        ]);
    }

    public function enroll()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not logged in']);
        }

        // Check if user is active
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find(session()->get('id'));
        if (!$user || (isset($user['status']) && $user['status'] === 'inactive')) {
            session()->destroy();
            return $this->response->setJSON(['success' => false, 'message' => 'Your account has been deactivated.']);
        }

        $user_id = session()->get('id');
        $course_id = $this->request->getPost('course_id');

        if (!$course_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Course ID required']);
        }

        $enrollmentModel = new EnrollmentModel();

        if ($enrollmentModel->isAlreadyEnrolled($user_id, $course_id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Already enrolled in this course']);
        }

        $data = [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrolled_at' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ];

        try {
            // Use the model's enrollUser method instead of direct insert
            if ($enrollmentModel->enrollUser($data)) {
                // Create notification
                $notificationModel = new \App\Models\NotificationModel();
                $db = \Config\Database::connect();
                $course = $db->table('courses')->where('id', $course_id)->get()->getRow();
                $courseName = $course ? $course->title : 'Unknown Course';
                $message = "You have been enrolled in " . $courseName;
                $notificationModel->insert([
                    'user_id' => $user_id,
                    'message' => $message,
                    'is_read' => 0
                ]);

                // Return course data for AJAX to update the UI
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'Enrolled successfully',
                    'course' => [
                        'id' => $course_id,
                        'title' => $course ? $course->title : 'Unknown Course',
                        'description' => $course ? $course->description : ''
                    ]
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Enrollment failed: ' . implode(', ', $enrollmentModel->errors())]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
