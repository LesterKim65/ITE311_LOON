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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .alert-sm {
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }
        #notificationList {
            max-width: 350px;
            max-height: 400px;
            overflow-y: auto;
        }
        #notificationList .alert {
            word-wrap: break-word;
        }
        #notificationList li {
            list-style: none;
        }
        #notificationList .mark-read {
            cursor: pointer !important;
            pointer-events: auto !important;
            z-index: 1000 !important;
            position: relative !important;
        }
        #notificationList .alert {
            pointer-events: auto;
        }
    </style>
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
                    <?php if (!session()->get('isLoggedIn')): ?>
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
                            <ul class="dropdown-menu dropdown-menu-end" id="notificationList" aria-labelledby="notificationDropdown" style="min-width: 300px;">
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
        <!-- Success/Error Alert Container -->
        <div id="alert-container" style="position: fixed; top: 80px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 400px; max-width: 90%;"></div>
        <?= $this->renderSection('content') ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Helper function to escape HTML to prevent XSS
        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
        }

        // Function to show alert messages
        function showAlert(message, type) {
            type = type || 'info'; // default to 'info' if not specified
            var alertClass = 'alert-' + type;
            var icon = '';
            
            if (type === 'success') {
                icon = '<i class="fas fa-check-circle me-2"></i>';
            } else if (type === 'danger' || type === 'error') {
                icon = '<i class="fas fa-exclamation-circle me-2"></i>';
            } else if (type === 'warning') {
                icon = '<i class="fas fa-exclamation-triangle me-2"></i>';
            } else {
                icon = '<i class="fas fa-info-circle me-2"></i>';
            }
            
            var alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show shadow" role="alert">
                    ${icon}${escapeHtml(message)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            $('#alert-container').html(alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('#alert-container .alert').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }

        $(document).ready(function() {
            // Fetch notifications immediately on page load
            fetchNotifications();
            // Fetch notifications every 60 seconds
            setInterval(fetchNotifications, 60000);
            
            // Also fetch when dropdown is opened
            $('#notificationDropdown').on('show.bs.dropdown', function() {
                fetchNotifications();
            });

            // Function to fetch notifications
            function fetchNotifications() {
                var list = $('#notificationList');
                
                // Show loading state if list is empty
                if (list.children().length === 0) {
                    list.html('<li class="px-3 py-2 text-center text-muted small"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading notifications...</li>');
                }
                
                $.ajax({
                    url: '<?= base_url('notifications') ?>',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log('Notifications response:', data);
                        
                        if (data.error) {
                            console.error('Notification error:', data.error);
                            list.empty();
                            list.append('<li class="px-3 py-2 text-center text-muted small">Error: ' + escapeHtml(data.error) + '</li>');
                            return;
                        }

                        var count = data.unreadCount || 0;
                        var badge = $('#notificationBadge');
                        if (count > 0) {
                            badge.text(count).show();
                        } else {
                            badge.hide();
                        }

                        // Always clear the list before adding new items
                        list.empty();

                        // Check if notifications exist and is an array
                        if (data.notifications && Array.isArray(data.notifications) && data.notifications.length > 0) {
                            data.notifications.forEach(function(notif) {
                                // Determine alert class based on read status
                                var alertClass = notif.is_read == 0 ? 'alert-info' : 'alert-secondary';
                                
                                // Create notification item with Bootstrap alert styling
                                var item = $('<li class="px-2 py-1"></li>');
                                var alertDiv = $('<div class="alert ' + alertClass + ' alert-sm mb-1 py-2 px-2" role="alert"></div>');
                                
                                // Add message and timestamp
                                var messageDiv = $('<div class="d-flex flex-column"></div>');
                                var messageText = $('<span class="small mb-1">' + escapeHtml(notif.message) + '</span>');
                                messageDiv.append(messageText);
                                
                                // Add timestamp if available
                                if (notif.created_at) {
                                    var timestamp = new Date(notif.created_at);
                                    var timeStr = timestamp.toLocaleString();
                                    var timeSpan = $('<small class="text-muted" style="font-size: 0.7rem;">' + escapeHtml(timeStr) + '</small>');
                                    messageDiv.append(timeSpan);
                                }
                                
                                // Add mark as read button if unread
                                if (notif.is_read == 0) {
                                    var buttonDiv = $('<div class="mt-1"></div>');
                                    var markReadBtn = $('<button>')
                                        .attr('type', 'button')
                                        .addClass('btn btn-sm btn-primary mark-read')
                                        .attr('data-id', notif.id)
                                        .css({
                                            'font-size': '0.7rem',
                                            'padding': '0.15rem 0.4rem',
                                            'cursor': 'pointer',
                                            'z-index': '1000',
                                            'position': 'relative',
                                            'pointer-events': 'auto'
                                        })
                                        .text('Mark as Read');
                                    buttonDiv.append(markReadBtn);
                                    messageDiv.append(buttonDiv);
                                }
                                
                                alertDiv.append(messageDiv);
                                item.append(alertDiv);
                                list.append(item);
                            });
                        } else {
                            // Show message when no notifications
                            var noNotifications = $('<li class="px-3 py-2 text-center text-muted small">No notifications</li>');
                            list.append(noNotifications);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to fetch notifications:', error, 'Status:', status, 'Response:', xhr.responseText);
                        list.empty();
                        
                        // Try to parse error response
                        try {
                            var errorData = JSON.parse(xhr.responseText);
                            if (errorData.error) {
                                list.append('<li class="px-3 py-2 text-center text-muted small">Error: ' + escapeHtml(errorData.error) + '</li>');
                            } else {
                                list.append('<li class="px-3 py-2 text-center text-muted small">Unable to load notifications</li>');
                            }
                        } catch (e) {
                            list.append('<li class="px-3 py-2 text-center text-muted small">Unable to load notifications</li>');
                        }
                    }
                });
            }

            // Mark as read - using event delegation on the notification list container
            $('#notificationList').on('click', '.mark-read', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('Mark as read button clicked');
                
                var button = $(this);
                var id = button.attr('data-id') || button.data('id');
                
                console.log('Notification ID:', id);
                
                if (!id) {
                    console.error('No notification ID found');
                    alert('Error: No notification ID found');
                    return false;
                }
                
                // Disable button to prevent double-clicks
                var originalText = button.text().trim();
                var originalHtml = button.html();
                button.prop('disabled', true)
                      .html('<span class="spinner-border spinner-border-sm me-1"></span>Marking...')
                      .css('pointer-events', 'none');
                
                console.log('Sending AJAX request to mark notification as read:', id);
                
                $.ajax({
                    url: '<?= base_url('notifications/mark_read') ?>/' + id,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(data) {
                        console.log('Mark as read response:', data);
                        
                        if (data.success) {
                            // Show success feedback briefly
                            button.removeClass('btn-primary').addClass('btn-success')
                                  .html('<i class="fas fa-check me-1"></i>Marked as Read')
                                  .prop('disabled', true);
                            
                            // Refresh notifications list after a short delay for visual feedback
                            setTimeout(function() {
                                fetchNotifications();
                            }, 300);
                        } else {
                            button.prop('disabled', false)
                                  .css('pointer-events', 'auto')
                                  .html(originalHtml);
                            console.error('Failed to mark as read:', data.error);
                            alert('Error: ' + (data.error || 'Failed to mark notification as read'));
                        }
                    },
                    error: function(xhr, status, error) {
                        button.prop('disabled', false)
                              .css('pointer-events', 'auto')
                              .html(originalHtml);
                        console.error('Error marking as read:', error, 'Status:', status, 'Response:', xhr.responseText);
                        
                        // Try to parse error response
                        try {
                            var errorData = JSON.parse(xhr.responseText);
                            if (errorData.error) {
                                alert('Error: ' + errorData.error);
                            } else {
                                alert('An error occurred while marking the notification as read. Please try again.');
                            }
                        } catch (e) {
                            alert('An error occurred while marking the notification as read. Please try again.');
                        }
                    }
                });
                
                return false;
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
                    dataType: 'json',
                    data: {
                        course_id: courseId,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        console.log('AJAX success:', response);

                        if (response.success && response.course) {
                            // Show success message
                            showAlert('You have successfully enrolled in ' + escapeHtml(response.course.title) + '!', 'success');
                            
                            // Get course card to remove from available courses
                            var courseCard = button.closest('.col-md-4');
                            
                            // Hide "No enrolled courses" message if it exists
                            var noCoursesMsg = $('#enrolled-courses-container .col-12');
                            if (noCoursesMsg.length && noCoursesMsg.find('.text-muted').length) {
                                noCoursesMsg.remove();
                            }

                            // Create enrolled course card matching the exact structure from dashboard.php
                            var enrolledCourseHtml = `
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">${escapeHtml(response.course.title)}</h6>
                                            <p class="card-text">${escapeHtml(response.course.description)}</p>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // Add to enrolled courses section with fade-in effect
                            var $newCard = $(enrolledCourseHtml).hide();
                            $('#enrolled-courses-container').append($newCard);
                            $newCard.fadeIn(300);

                            // Remove from available courses with fade effect
                            courseCard.fadeOut(300, function() {
                                courseCard.remove();
                                
                                // If no more available courses, show message
                                if ($('#available-courses .row .col-md-4').length === 0) {
                                    $('#available-courses .row').html('<p>No available courses found.</p>');
                                }
                                
                                console.log('Course card removed from available courses');
                            });

                            console.log('Course added to enrolled courses successfully');
                        } else {
                            button.prop('disabled', false).text('Enroll');
                            console.error('Enrollment failed:', response.message);
                            alert(response.message || 'Enrollment failed. Please try again.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error, 'Status:', status, 'Response:', xhr.responseText);
                        button.prop('disabled', false).text('Enroll');
                        
                        // Try to parse error response if it's JSON
                        try {
                            var errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse.message) {
                                alert('Error: ' + errorResponse.message);
                            } else {
                                alert('An error occurred. Please try again.');
                            }
                        } catch (e) {
                            alert('An error occurred. Please try again.');
                        }
                    }
                });

                return false;
            });
        });
    </script>
</body>
</html>
