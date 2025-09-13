<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Register
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h1>Create an Account</h1>

    <?php $errors = session()->get('errors') ?? []; ?>
    <?php if (! empty($errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('message')): ?>
        <div style="color: green;"><?= esc(session()->getFlashdata('message')) ?></div>
    <?php endif; ?>

    <form method="post" action="/register">
        <?= csrf_field() ?>
        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= old('name') ?>" required>
        </div>
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Register</button>
    </form>
<?= $this->endSection() ?>

