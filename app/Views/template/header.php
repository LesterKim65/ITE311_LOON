<?php
if (session()->get('isLoggedIn')) {
    $unreadCount = (new \App\Models\NotificationModel())->getUnreadCount(session()->get('id'));
} else {
    $unreadCount = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $this->renderSection('title') ?> - My WebSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php $current_path = service('uri')->getPath(); ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('/') ?>">My WebSystem</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (uri_string() !== 'dashboard'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('/') ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('about') ?>">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('contact') ?>">Contact</a>
                        </li>
                    <?php endif; ?>
                    <?php if (session()->get('isLoggedIn')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard') ?>">Dashboard</a>
                        </li>
                        <?php if (session()->get('role') == 'student'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('grades') ?>">Grades</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('courses') ?>">Courses</a>
                            </li>
                        <?php endif; ?>
                        <?php if (session()->get('role') == 'teacher'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('create-course') ?>">Create Course</a>
                            </li>
                        <?php endif; ?>
                        <?php if (in_array(session()->get('role'), ['teacher', 'admin'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('student-courses') ?>">Student Courses</a>
                            </li>
                        <?php endif; ?>
                        <?php if (session()->get('role') == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('manage-users') ?>">Manage Users</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav">
                    <?php if (session()->get('isLoggedIn')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                🔔
                                <span class="badge bg-danger" id="notificationBadge" style="display: <?= $unreadCount > 0 ? 'inline' : 'none' ?>;"><?=$unreadCount?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" id="notificationList" aria-labelledby="notificationDropdown">
                                <!-- Notifications will be populated here -->
                            </ul>
                        </li>
                        <li class="nav-item">
                            <span class="navbar-text text-white me-3">
                                Hello, <?= esc(session()->get('name')) ?> (<?= esc(session()->get('role')) ?>)
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-danger btn-sm" href="<?= base_url('/logout') ?>">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('/login') ?>">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('/register') ?>">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?= $this->renderSection('content') ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            fetchNotifications();
            // Fetch notifications every 60 seconds
            setInterval(fetchNotifications, 60000);

            // Function to fetch notifications
            function fetchNotifications() {
                $.get('/notifications', function(data) {
                    if (data.error) return;

                    var count = data.unreadCount;
                    var badge = $('#notificationBadge');
                    if (count > 0) {
                        badge.text(count).show();
                    } else {
                        badge.hide();
                    }

                    var list = $('#notificationList');
                    list.empty();
                    data.notifications.forEach(function(notif) {
                        var item = $('<li><a class="dropdown-item" href="#">' + notif.message + '</a></li>');
                        item.find('a').append(' <button class="btn btn-sm btn-primary mark-read" data-id="' + notif.id + '">Mark as Read</button>');
                        list.append(item);
                    });
                });
            }

            // Mark as read
            $(document).on('click', '.mark-read', function() {
                var id = $(this).data('id');
                $.post('/notifications/mark_read/' + id, {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                }, function(data) {
                    if (data.success) {
                        fetchNotifications();
                    }
                });
            });

            // Enroll button handler
            $(document).on('click', '.enroll-btn', function(e) {
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
                    data: {
                        course_id: courseId,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
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
</body>
</html>
