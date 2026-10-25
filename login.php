<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_is_valid()) {
        $errorMessage = 'Your session expired. Please try again.';
    } else {
        $admin = null;
        if ($username !== '') {
            $admin = fetch_row($conn, 'SELECT id, username, password FROM admins WHERE username = ? LIMIT 1', 's', [$username]);
        }

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect('dashboard.php');
        }

        $errorMessage = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Student Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
<div class="login-card">
    <div class="brand-icon">SM</div>
    <h1 class="login-title">Admin Login</h1>
    <p class="login-subtitle">Student Management System</p>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger"><?= e($errorMessage) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" autocomplete="off">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required value="<?= e($_POST['username'] ?? '') ?>">
        </div>
        <div class="form-group" style="margin-top: 14px;">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>

    <p class="login-hint">Sample login: <strong>admin</strong> / <strong>admin123</strong></p>
</div>
</body>
</html>
