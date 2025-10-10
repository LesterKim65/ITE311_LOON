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
                                            <button class="btn btn-primary enroll-btn" data-course-id="<?= esc($course['id']) ?>">Enroll</button>
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

            <script>
                $(document).ready(function() {
                    console.log('Dashboard JavaScript loaded');

                    $('.enroll-btn').click(function(e) {
                        e.preventDefault();
                        console.log('Enroll button clicked');

                        var button = $(this);
                        var courseId = button.data('course-id');
                        console.log('Course ID:', courseId);

                        if (!courseId) {
                            console.error('No course ID found');
                            return false;
                        }

                        button.prop('disabled', true).text('Enrolling...');

                        $.ajax({
                            url: '<?= site_url('course/enroll') ?>',
                            type: 'POST',
                            data: { course_id: courseId },
                            success: function(response) {
                                console.log('AJAX success:', response);

                                if (response.success) {
                                    // Update button to enrolled state
                                    button.removeClass('btn-primary').addClass('btn-success')
                                          .html('✓ Enrolled').prop('disabled', true);

                                    // Get course info and move to enrolled section
                                    var courseCard = button.closest('.col-md-4');
                                    var courseTitle = courseCard.find('.card-title').text();
                                    var courseDescription = courseCard.find('.card-text').text();

                                    console.log('Moving course:', courseTitle);

                                    // Create enrolled course card
                                    var enrolledCourseHtml = `
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border-success">
                                                <div class="card-body">
                                                    <h6 class="card-title">${courseTitle}</h6>
                                                    <p class="card-text">${courseDescription}</p>
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>Enrolled
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    `;

                                    // Add to enrolled courses section
                                    $('#enrolled-courses-container').append(enrolledCourseHtml);

                                    // Remove from available courses with fade effect
                                    courseCard.fadeOut(300, function() {
                                        courseCard.remove();
                                        console.log('Course card removed');
                                    });

                                    // Hide "No enrolled courses" message if visible
                                    $('#enrolled-courses-container .text-muted').hide();

                                    console.log('Course moved successfully');
                                } else {
                                    button.prop('disabled', false).text('Enroll');
                                    console.error('Enrollment failed:', response.message);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX error:', error, 'Status:', status);
                                button.prop('disabled', false).text('Enroll');
                            }
                        });

                        return false;
                    });
                });
            </script>

        <?php elseif ($role == 'teacher'): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Your Taught Courses</h5>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td><?= esc($course['id']) ?></td>
                                            <td><?= esc($course['title']) ?></td>
                                            <td><?= esc($course['description']) ?></td>
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
