<?= $this->extend('template/header') ?>

<?= $this->section('title') ?>
Upload Material
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Upload Material for Course</h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?= esc($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?= esc($error) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Debug info -->
                    <div class="alert alert-info">
                        <strong>Debug Info:</strong><br>
                        <?= esc($debug) ?>
                    </div>

                    <?php
                    $userRole = session()->get('role');
                    $userId = session()->get('id');

                    // Check if user has courses assigned (like in Auth controller)
                    $db = \Config\Database::connect();
                    $hasCourses = $db->query("SELECT COUNT(*) as count FROM courses WHERE instructor_id = ?", [$userId])->getRow();
                    $isInstructor = $hasCourses && $hasCourses->count > 0;

                    // Only teachers, instructors, and users with assigned courses can upload materials
                    if ($userRole !== 'teacher' && $userRole !== 'instructor' && !$isInstructor) { ?>
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> Access Restricted</h5>
                            <p>Only teachers, instructors, and course instructors can upload materials.</p>
                            <a href="<?= site_url('dashboard') ?>" class="btn btn-primary">Back to Dashboard</a>
                        </div>
                    <?php } else {
                        // Check if this teacher is the instructor of the course
                        $db = \Config\Database::connect();
                        $course = $db->query("SELECT instructor_id FROM courses WHERE id = ?", [$course_id])->getRow();

                        if (!$course || $course->instructor_id != $userId) { ?>
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle"></i> Access Restricted</h5>
                                <p>You can only upload materials to courses you teach.</p>
                                <a href="<?= site_url('dashboard') ?>" class="btn btn-primary">Back to Dashboard</a>
                            </div>
                        <?php } else { ?>
                            <form action="<?= site_url('admin/course/' . $course_id . '/upload') ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="material_file" class="form-label">Select File</label>
                                    <input type="file" class="form-control" id="material_file" name="material_file" required>
                                    <div class="form-text">Allowed file types: PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, PNG, ZIP. Maximum size: 50MB.</div>
                                </div>
                                <button type="submit" class="btn btn-primary">Upload Material</button>
                                <a href="<?= site_url('dashboard') ?>" class="btn btn-secondary">Back to Dashboard</a>
                            </form>

                            <?php if (!empty($materials)): ?>
                                <h5 class="mt-4">Uploaded Materials</h5>
                                <div class="list-group">
                                    <?php foreach ($materials as $material): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-file"></i> <strong><?= esc($material['file_name']) ?></strong>
                                                <br>
                                                <small class="text-muted">Uploaded: <?= date('M j, Y g:i A', strtotime($material['created_at'])) ?></small>
                                            </div>
                                            <div>
                                                <a href="<?= site_url('materials/download/' . $material['id']) ?>" class="btn btn-sm btn-outline-primary">Download</a>
                                                <button onclick="deleteMaterial(<?= $material['id'] ?>)" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <script>
                                document.getElementById('material_file').addEventListener('change', function() {
                                    console.log('File selected:', this.files[0]);
                                });

                                document.querySelector('form').addEventListener('submit', function(e) {
                                    console.log('Form submit event triggered');
                                    console.log('Form data:', new FormData(this));
                                });

                                function deleteMaterial(materialId) {
                                    if (confirm('Are you sure you want to delete this material? This action cannot be undone.')) {
                                        const form = document.createElement('form');
                                        form.method = 'POST';
                                        form.action = '<?= site_url('materials/delete') ?>/' + materialId;

                                        // Add CSRF token from the main form
                                        const mainForm = document.querySelector('form');
                                        const csrfInputMain = mainForm.querySelector('input[name="csrf_test_name"]');
                                        if (csrfInputMain) {
                                            const csrfInput = document.createElement('input');
                                            csrfInput.type = 'hidden';
                                            csrfInput.name = 'csrf_test_name';
                                            csrfInput.value = csrfInputMain.value;
                                            form.appendChild(csrfInput);
                                        }

                                        document.body.appendChild(form);
                                        form.submit();
                                    }
                                }
                            </script>
                        <?php }
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
