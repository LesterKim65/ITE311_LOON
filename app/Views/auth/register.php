<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Register
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4 text-center">Register</h2>

        <?php if (isset($validation)): ?>
            <div class="alert alert-danger">
                <?= $validation->listErrors() ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('register') ?>" novalidate>
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Full Name" 
                    class="form-control <?= isset($validation) && $validation->hasError('name') ? 'is-invalid' : '' ?>" 
                    value="<?= esc(old('name')) ?>" 
                    required>
                <div class="invalid-feedback">
                    <?= isset($validation) ? $validation->getError('name') : '' ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Email" 
                    class="form-control <?= isset($validation) && $validation->hasError('email') ? 'is-invalid' : '' ?>" 
                    value="<?= esc(old('email')) ?>" 
                    required>
                <div class="invalid-feedback">
                    <?= isset($validation) ? $validation->getError('email') : '' ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select 
                    id="role"
                    name="role"
                    class="form-select <?= isset($validation) && $validation->hasError('role') ? 'is-invalid' : '' ?>"
                    required>
            
                    <option value="student" <?= old('role') === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="teacher" <?= old('role') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                    <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <div class="invalid-feedback">
                    <?= isset($validation) ? $validation->getError('role') : '' ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Password" 
                    class="form-control <?= isset($validation) && $validation->hasError('password') ? 'is-invalid' : '' ?>" 
                    required>
                <div class="invalid-feedback">
                    <?= isset($validation) ? $validation->getError('password') : '' ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    placeholder="Confirm Password" 
                    class="form-control <?= isset($validation) && $validation->hasError('password_confirm') ? 'is-invalid' : '' ?>" 
                    required>
                <div class="invalid-feedback">
                    <?= isset($validation) ? $validation->getError('password_confirm') : '' ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <div class="mt-3 text-center">
            <a href="<?= site_url('login') ?>">Already have an account? Login</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>