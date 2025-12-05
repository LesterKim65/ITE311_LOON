<?= $this->extend('template/header') ?>

<?= $this->section('title') ?>
Manage Users
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1">Manage Users</h1>
        <p class="text-muted mb-0">View and manage all system users.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-plus me-2"></i>Add User
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email/Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <?php 
                            $isProtected = ($user['email'] === $protectedAdminEmail);
                            // Handle NULL status - default to 'active' for existing users
                            $status = isset($user['status']) && !empty($user['status']) ? $user['status'] : 'active';
                            $isInactive = ($status === 'inactive');
                            ?>
                            <tr id="user-row-<?= esc($user['id']) ?>" class="<?= $isInactive ? 'table-secondary opacity-75' : '' ?>">
                                <td><?= esc($user['id']) ?></td>
                                <td><?= esc($user['name']) ?></td>
                                <td><?= esc($user['email']) ?></td>
                                <td>
                                    <?php if ($isProtected): ?>
                                        <select class="form-select form-select-sm" id="role-select-<?= esc($user['id']) ?>" disabled style="max-width: 150px;">
                                            <option value="admin" selected>Admin</option>
                                        </select>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-shield-alt me-1"></i>Protected
                                        </small>
                                    <?php else: ?>
                                        <select class="form-select form-select-sm role-select" data-user-id="<?= esc($user['id']) ?>" style="max-width: 150px;" <?= $isInactive ? 'disabled' : '' ?>>
                                            <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                            <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status === 'active'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times-circle me-1"></i>Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if ($isProtected): ?>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal" 
                                                    data-user-id="<?= esc($user['id']) ?>" 
                                                    data-user-name="<?= esc($user['name']) ?>"
                                                    data-user-email="<?= esc($user['email']) ?>">
                                                <i class="fas fa-key me-1"></i>Change Password
                                            </button>
                                        <?php elseif ($isInactive): ?>
                                            <button type="button" class="btn btn-outline-success restore-user-btn" 
                                                    data-user-id="<?= esc($user['id']) ?>" 
                                                    data-user-name="<?= esc($user['name']) ?>">
                                                <i class="fas fa-check-circle me-1"></i>Activate
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                                    data-user-id="<?= esc($user['id']) ?>" 
                                                    data-user-name="<?= esc($user['name']) ?>"
                                                    data-user-email="<?= esc($user['email']) ?>">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <button type="button" class="btn btn-outline-danger delete-user-btn" 
                                                    data-user-id="<?= esc($user['id']) ?>" 
                                                    data-user-name="<?= esc($user['name']) ?>">
                                                <i class="fas fa-ban me-1"></i>Deactivate
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addUserName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addUserName" name="name" required minlength="3" maxlength="100">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="addUserEmail" class="form-label">Email/Username <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="addUserEmail" name="email" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Default Password:</strong> password123 will be automatically assigned to this user.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="addUserRole" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="addUserRole" name="role" required>
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="fas fa-user-edit me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
                <input type="hidden" id="editUserId" name="user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editUserName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editUserName" name="name" required minlength="3" maxlength="100">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editUserEmail" class="form-label">Email/Username <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="editUserEmail" name="email" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="fas fa-key me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePasswordForm">
                <input type="hidden" id="changePasswordUserId" name="user_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong id="changePasswordUserName"></strong> (<span id="changePasswordUserEmail"></span>)
                    </div>
                    <div class="mb-3">
                        <label for="changePasswordNew" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="changePasswordNew" name="password" required minlength="8">
                        <small class="form-text text-muted">
                            Must be at least 8 characters with uppercase, lowercase, number, and special character.
                        </small>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle role change
    $('.role-select').on('change', function() {
        var $select = $(this);
        var userId = $select.data('user-id');
        var newRole = $select.val();
        var originalRole = $select.data('original-role') || $select.find('option:selected').text();

        // Disable select while processing
        $select.prop('disabled', true);

        $.ajax({
            url: '<?= base_url('manage-users/change-role') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                user_id: userId,
                role: newRole,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $select.data('original-role', newRole);
                } else {
                    showAlert(response.message, 'error');
                    // Revert to original value
                    $select.val(originalRole);
                }
            },
            error: function(xhr, status, error) {
                showAlert('An error occurred while updating the role. Please try again.', 'error');
                // Revert to original value
                $select.val(originalRole);
            },
            complete: function() {
                $select.prop('disabled', false);
            }
        });
    });

    // Handle add user form
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        $.ajax({
            url: '<?= base_url('manage-users/add') ?>',
            type: 'POST',
            dataType: 'json',
            data: form.serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#addUserModal').modal('hide');
                    form[0].reset();
                    // Reload page after 1 second
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert(response.message, 'error');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                showAlert('An error occurred while creating the user. Please try again.', 'error');
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Handle edit user modal
    $('#editUserModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var userId = button.data('user-id');
        var userName = button.data('user-name');
        var userEmail = button.data('user-email');

        $('#editUserId').val(userId);
        $('#editUserName').val(userName);
        $('#editUserEmail').val(userEmail);
    });

    // Handle edit user form
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: '<?= base_url('manage-users/update') ?>',
            type: 'POST',
            dataType: 'json',
            data: form.serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#editUserModal').modal('hide');
                    // Reload page after 1 second
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert(response.message, 'error');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                showAlert('An error occurred while updating the user. Please try again.', 'error');
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Handle change password modal
    $('#changePasswordModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var userId = button.data('user-id');
        var userName = button.data('user-name');
        var userEmail = button.data('user-email');

        $('#changePasswordUserId').val(userId);
        $('#changePasswordUserName').text(userName);
        $('#changePasswordUserEmail').text(userEmail);
    });

    // Handle change password form
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        // Validate password strength
        var password = $('#changePasswordNew').val();
        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(password)) {
            showAlert('Password must contain uppercase, lowercase, number, and special character.', 'error');
            return;
        }

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        $.ajax({
            url: '<?= base_url('manage-users/change-password') ?>',
            type: 'POST',
            dataType: 'json',
            data: form.serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#changePasswordModal').modal('hide');
                    form[0].reset();
                } else {
                    showAlert(response.message, 'error');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                showAlert('An error occurred while updating the password. Please try again.', 'error');
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Handle deactivate user
    $('.delete-user-btn').on('click', function() {
        var button = $(this);
        var userId = button.data('user-id');
        var userName = button.data('user-name');

        if (!confirm('Are you sure you want to deactivate user "' + escapeHtml(userName) + '"? The user will remain in the database but will not be able to log in.')) {
            return;
        }

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '<?= base_url('manage-users/delete') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                user_id: userId,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    // Reload page after 1 second to show updated status
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert(response.message, 'error');
                    button.prop('disabled', false).html('<i class="fas fa-ban me-1"></i>Deactivate');
                }
            },
            error: function(xhr, status, error) {
                showAlert('An error occurred while deactivating the user. Please try again.', 'error');
                button.prop('disabled', false).html('<i class="fas fa-ban me-1"></i>Deactivate');
            }
        });
    });

    // Handle activate user
    $('.restore-user-btn').on('click', function() {
        var button = $(this);
        var userId = button.data('user-id');
        var userName = button.data('user-name');

        if (!confirm('Are you sure you want to activate user "' + escapeHtml(userName) + '"?')) {
            return;
        }

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '<?= base_url('manage-users/restore') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                user_id: userId,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    // Reload page after 1 second to show updated status
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert(response.message, 'error');
                    button.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i>Activate');
                }
            },
            error: function(xhr, status, error) {
                showAlert('An error occurred while activating the user. Please try again.', 'error');
                button.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i>Activate');
            }
        });
    });

    // Reset modals when closed
    $('#addUserModal, #editUserModal, #changePasswordModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('.invalid-feedback').text('');
        $(this).find('.is-invalid').removeClass('is-invalid');
    });
});
</script>
<?= $this->endSection() ?>

