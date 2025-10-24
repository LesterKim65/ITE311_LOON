<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\EnrollmentModel;

class Materials extends BaseController
{
    public function upload($course_id)
    {
        $success = null;
        $error = null;
        $debug = 'Controller called - Method: ' . $this->request->getMethod() . ', Course: ' . $course_id;

        log_message('info', '=== UPLOAD METHOD CALLED ===');
        log_message('info', 'Course ID: ' . $course_id);
        log_message('info', 'Request Method: ' . $this->request->getMethod());

        // Get materials for display
        $materialModel = new MaterialModel();
        $materials = $materialModel->getMaterialsByCourse($course_id);

        if ($this->request->getMethod() === 'POST') {
            $debug .= ' | POST request received';

            $file = $this->request->getFile('material_file');
            $debug .= ' | File object: ' . ($file ? 'EXISTS' : 'NULL');

            if ($file) {
                $debug .= ' | File valid: ' . ($file->isValid() ? 'YES' : 'NO') . ', Has moved: ' . ($file->hasMoved() ? 'YES' : 'NO');
            }

            if ($file && $file->isValid() && !$file->hasMoved()) {
                // Validate file type and size
                $allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'zip'];
                $maxSize = 50 * 1024 * 1024; // 50MB in bytes
                $extension = strtolower($file->getClientExtension());
                $size = $file->getSize();

                if (!in_array($extension, $allowedTypes)) {
                    $error = 'Invalid file type. Allowed types: PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, PNG, ZIP.';
                } elseif ($size > $maxSize) {
                    $error = 'File size exceeds 50MB.';
                } else {
                    // Create upload directory
                    $uploadPath = WRITEPATH . 'uploads/materials/';
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    $newName = uniqid() . '_' . $file->getClientName();
                    $fullPath = $uploadPath . $newName;

                    if ($file->move($uploadPath, $newName)) {
                        // Set file permissions
                        chmod($fullPath, 0644);

                        // Save to database
                        $materialModel = new MaterialModel();
                        $data = [
                            'course_id' => $course_id,
                            'file_name' => $file->getClientName(),
                            'file_path' => 'uploads/materials/' . $newName,
                            'instructor_id' => session()->get('id') ?: 1,
                            'created_at' => date('Y-m-d H:i:s')
                        ];

                        log_message('info', 'Attempting to insert material with data: ' . json_encode($data));

                        $insertResult = $materialModel->insert($data);
                        log_message('info', 'Insert result: ' . ($insertResult ? 'SUCCESS' : 'FAILED'));

                        if ($insertResult) {
                            log_message('info', 'Material inserted successfully with ID: ' . $insertResult);
                            $success = 'Material uploaded successfully!';

                            // Refetch materials after insert
                            $materials = $materialModel->getMaterialsByCourse($course_id);

                            return view('materials/upload', [
                                'course_id' => $course_id,
                                'materials' => $materials,
                                'success' => $success,
                                'error' => $error,
                                'debug' => $debug
                            ]);
                        } else {
                            log_message('error', 'Failed to insert material. Model errors: ' . json_encode($materialModel->errors()));
                            // Remove file if database insert fails
                            if (file_exists($fullPath)) {
                                unlink($fullPath);
                            }
                            $error = 'Failed to save material to database.';
                        }
                    } else {
                        $error = 'Failed to move uploaded file.';
                    }
                }
            } else {
                $error = 'File upload error: ' . ($file ? $file->getErrorString() : 'No file received');
            }
        }

        // Debug session info
        log_message('info', 'Session ID: ' . session()->get('id'));
        log_message('info', 'Session Role: ' . session()->get('role'));
        log_message('info', 'Session Name: ' . session()->get('name'));

        return view('materials/upload', [
            'course_id' => $course_id,
            'materials' => $materials,
            'success' => $success,
            'error' => $error,
            'debug' => $debug
        ]);
    }

    public function view($course_id)
    {
        // SIMPLE VIEW - SHOWS ALL MATERIALS FOR A COURSE
        $materialModel = new MaterialModel();
        $materials = $materialModel->getMaterialsByCourse($course_id);
        
        return view('materials/view', [
            'course_id' => $course_id,
            'materials' => $materials
        ]);
    }

    public function delete($material_id)
    {
        // SIMPLE DELETE - NO BULLSHIT, JUST WORKS LIKE THE SIMPLE VERSION
        log_message('info', '=== SIMPLE DELETE METHOD CALLED ===');
        log_message('info', 'Material ID: ' . $material_id);
        
        $materialModel = new MaterialModel();
        $material = $materialModel->find($material_id);

        if ($material) {
            // Handle relative or absolute path
            if (strpos($material['file_path'], 'uploads') === 0) {
                $filePath = WRITEPATH . $material['file_path'];
            } else {
                $filePath = $material['file_path'];
            }
            
            // Delete from database (this always works)
            $dbResult = $materialModel->delete($material_id);
            
            // Try to delete file (if it fails, who cares)
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            
            if ($dbResult) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => true, 'message' => 'Material deleted successfully']);
                } else {
                    session()->setFlashdata('success', 'Material deleted successfully.');
                    return redirect()->back();
                }
            } else {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Database delete failed']);
                } else {
                    session()->setFlashdata('error', 'Database delete failed.');
                    return redirect()->back();
                }
            }
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Material not found']);
            } else {
                session()->setFlashdata('error', 'Material not found.');
                return redirect()->back();
            }
        }
    }

    public function download($material_id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $materialModel = new MaterialModel();
        $material = $materialModel->find($material_id);

        if (!$material) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Check if user is enrolled in the course
        $enrollmentModel = new EnrollmentModel();
        $user_id = session()->get('id');
        $isEnrolled = $enrollmentModel->isAlreadyEnrolled($user_id, $material['course_id']);

        if (!$isEnrolled) {
            session()->setFlashdata('error', 'You are not enrolled in this course.');
            return redirect()->to('/dashboard');
        }

        // Handle relative or absolute path
        if (strpos($material['file_path'], 'uploads') === 0) {
            $filePath = WRITEPATH . $material['file_path'];
        } else {
            $filePath = $material['file_path'];
        }

        // Force download
        return $this->response->download($filePath, null)->setFileName($material['file_name']);
    }

    public function getMaterialsByCourse($course_id)
    {
        log_message('info', 'AJAX request for materials - Course ID: ' . $course_id);

        $materialModel = new MaterialModel();
        $materials = $materialModel->getMaterialsByCourse($course_id);

        log_message('info', 'Materials found: ' . count($materials));

        $response = [
            'success' => true,
            'materials' => $materials,
            'course_id' => $course_id,
            'count' => count($materials)
        ];

        return $this->response->setJSON($response);
    }

    public function debugUpload()
    {
        log_message('info', '=== DEBUG UPLOAD METHOD CALLED ===');
        
        if ($this->request->getMethod() === 'POST') {
            log_message('info', 'POST request received in debug upload');
            
            $file = $this->request->getFile('material_file');
            log_message('info', 'File received: ' . ($file ? $file->getName() : 'NO FILE'));
            
            if ($file && $file->isValid() && !$file->hasMoved()) {
                log_message('info', 'File is valid, attempting upload');
                
                $uploadPath = WRITEPATH . 'uploads/materials/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                $newName = $file->getRandomName();
                if ($file->move($uploadPath, $newName)) {
                    log_message('info', 'File moved successfully');
                    
                    // Test database insert
                    $materialModel = new MaterialModel();
                    $data = [
                        'course_id' => 1,
                        'file_name' => $file->getClientName(),
                        'file_path' => $uploadPath . $newName,
                        'instructor_id' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $result = $materialModel->insertMaterial($data);
                    if ($result) {
                        log_message('info', 'Database insert successful, ID: ' . $result);
                        return $this->response->setJSON(['success' => true, 'message' => 'Upload successful', 'id' => $result]);
                    } else {
                        log_message('error', 'Database insert failed: ' . json_encode($materialModel->errors()));
                        return $this->response->setJSON(['success' => false, 'message' => 'Database insert failed', 'errors' => $materialModel->errors()]);
                    }
                } else {
                    log_message('error', 'File move failed');
                    return $this->response->setJSON(['success' => false, 'message' => 'File move failed']);
                }
            } else {
                $error = $file ? $file->getError() : 'No file received';
                log_message('error', 'File validation failed: ' . $error);
                return $this->response->setJSON(['success' => false, 'message' => 'File validation failed: ' . $error]);
            }
        }
        
        return view('materials/debug_upload');
    }

    public function simpleDebug()
    {
        if ($this->request->getMethod() === 'POST') {
            log_message('info', '=== SIMPLE DEBUG UPLOAD METHOD CALLED ===');
            
            $file = $this->request->getFile('material_file');
            log_message('info', 'File received: ' . ($file ? $file->getName() : 'NO FILE'));
            
            if ($file && $file->isValid() && !$file->hasMoved()) {
                log_message('info', 'File is valid, attempting upload');
                
                $uploadPath = WRITEPATH . 'uploads/materials/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                $newName = $file->getRandomName();
                if ($file->move($uploadPath, $newName)) {
                    log_message('info', 'File moved successfully');
                    
                    // Test database insert
                    $materialModel = new MaterialModel();
                    $data = [
                        'course_id' => 1,
                        'file_name' => $file->getClientName(),
                        'file_path' => $uploadPath . $newName,
                        'instructor_id' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $result = $materialModel->insertMaterial($data);
                    if ($result) {
                        log_message('info', 'Database insert successful, ID: ' . $result);
                        return $this->response->setJSON(['success' => true, 'message' => 'Upload successful', 'id' => $result, 'data' => $data]);
                    } else {
                        log_message('error', 'Database insert failed: ' . json_encode($materialModel->errors()));
                        return $this->response->setJSON(['success' => false, 'message' => 'Database insert failed', 'errors' => $materialModel->errors()]);
                    }
                } else {
                    log_message('error', 'File move failed');
                    return $this->response->setJSON(['success' => false, 'message' => 'File move failed']);
                }
            } else {
                $error = $file ? $file->getError() : 'No file received';
                log_message('error', 'File validation failed: ' . $error);
                return $this->response->setJSON(['success' => false, 'message' => 'File validation failed: ' . $error]);
            }
        }
        
        return view('materials/simple_debug');
    }
}
