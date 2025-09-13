<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Homepage
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h1>Welcome to Our Website</h1>
    <p>This is the homepage of our web application.</p>
    <?php $errors = session()->get('errors') ?? []; ?>
    <?php if (! empty($errors)): ?>
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success mt-3"><?= esc(session()->getFlashdata('message')) ?></div>
    <?php endif; ?>

    <div class="card mt-4">
        <div class="card-body">
            <h2 class="card-title">Register</h2>
            <form method="get" action="/">
                <input type="hidden" name="register" value="1" />
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= old('name') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>
