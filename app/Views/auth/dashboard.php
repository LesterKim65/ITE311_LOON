<?= $this->extend('template/header') ?>

<?= $this->section('title') ?>
Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10">
        <h2 class="mb-4 text-center">Dashboard</h2>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Welcome, <?= esc($name) ?>!</h5>
                <p class="card-text">Your role: <?= esc($role) ?></p>
                <a href="<?= site_url('logout') ?>" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <?php if ($role == 'student'): ?>
            <!-- Enrolled Courses -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Your Enrolled Courses</h5>
                </div>
                <div class="card-body" id="enrolled-courses">
                    <div class="row" id="enrolled-courses-container">
                        <?php if (isset($enrolledCourses) && !empty($enrolledCourses)): ?>
                            <?php foreach ($enrolledCourses as $course): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= esc($course['title']) ?></h6>
                                            <p class="card-text"><?= esc($course['description']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <p class="text-center text-muted mb-0">No enrolled courses found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Course Materials -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Course Materials</h5>
                </div>
                <div class="card-body">
                    <?php
                    $materialModel = new \App\Models\MaterialModel();
                    $hasMaterials = false;
                    if (isset($enrolledCourses) && !empty($enrolledCourses)):
                        foreach ($enrolledCourses as $course):
                            $materials = $materialModel->getMaterialsByCourse($course['id']);
                            if (!empty($materials)):
                                $hasMaterials = true;
                    ?>
                            <h6 class="mb-3"><?= esc($course['title']) ?> Materials</h6>
                            <ul class="list-group mb-3">
                                <?php foreach ($materials as $material): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= esc($material['file_name']) ?>
                                        <a href="<?= site_url('materials/download/' . $material['id']) ?>" class="btn btn-sm btn-primary">Download</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                    <?php
                            endif;
                        endforeach;
                    endif;
                    if (!$hasMaterials):
                    ?>
                        <p class="text-center text-muted mb-0">No materials available for your enrolled courses.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Available Courses -->
            <div class="card">
                <div class="card-header">
                    <h5>Available Courses</h5>
                </div>
                <div class="card-body" id="available-courses">
                    <?php if (isset($availableCourses) && !empty($availableCourses)): ?>
                        <div class="row">
                            <?php foreach ($availableCourses as $course): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= esc($course['title']) ?></h6>
                                            <p class="card-text"><?= esc($course['description']) ?></p>
                                            <button type="button" class="btn btn-primary enroll-btn" data-course-id="<?= esc($course['id']) ?>">Enroll</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No available courses found.</p>
                    <?php endif; ?>
                </div>
            </div>



        <?php elseif ($role == 'teacher' || (isset($courses) && !empty($courses))): ?>
            <!-- Course Management -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Your Taught Courses</h5>
                    <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                        <i class="fas fa-plus"></i> Create Course
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($courses) && !empty($courses)): ?>
                        <div class="row">
                            <?php foreach ($courses as $course): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <a href="#" class="text-decoration-none course-title" data-course-id="<?= esc($course['id']) ?>">
                                                    <?= esc($course['title']) ?>
                                                </a>
                                            </h6>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php if ($role == 'teacher'): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= site_url('admin/course/' . $course['id'] . '/upload') ?>">
                                                            <i class="fas fa-upload"></i> Upload Material
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= site_url('admin/course/' . $course['id'] . '/materials') ?>">
                                                            <i class="fas fa-file-alt"></i> View Materials
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="deleteCourse(<?= esc($course['id']) ?>)">
                                                            <i class="fas fa-trash"></i> Delete Course
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text text-muted small">
                                                <?= esc(substr($course['description'], 0, 100)) ?>
                                                <?php if (strlen($course['description']) > 100): ?>...<?php endif; ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Course ID: <?= esc($course['id']) ?></small>
                                                <span class="badge bg-primary">Active</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No courses found</h5>
                            <p class="text-muted">Start by creating your first course</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                                <i class="fas fa-plus"></i> Create Your First Course
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>


            <script>
                function deleteCourse(courseId) {
                    if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                        // Implement course deletion
                        alert('Course deletion feature coming soon!');
                    }
                }
            </script>

        <?php elseif ($role == 'admin'): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>All Users</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($users) && !empty($users)): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?= esc($user['id']) ?></td>
                                                    <td><?= esc($user['name']) ?></td>
                                                    <td><?= esc($user['email']) ?></td>
                                                    <td><?= esc($user['role']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p>No users found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>All Courses</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($courses) && !empty($courses)): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Instructor ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($courses as $course): ?>
                                                <tr>
                                                    <td><?= esc($course['id']) ?></td>
                                                    <td><?= esc($course['title']) ?></td>
                                                    <td><?= esc($course['description']) ?></td>
                                                    <td><?= esc($course['instructor_id']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p>No courses found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
