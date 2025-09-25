<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Login
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4 text-center">Login</h2>

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

        <form method="post" action="<?= site_url('login') ?>" novalidate>
            <?= csrf_field() ?>

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

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <div class="mt-3 text-center">
            <a href="<?= site_url('register') ?>">Don't have an account? Register</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>