<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, username, password, role, status
             FROM users
             WHERE username = ?"
        );

        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Incorrect username or password.';
        } elseif ($user['status'] === 'pending') {
            $error = 'Your account is waiting for admin approval.';
        } elseif ($user['status'] !== 'approved') {
            $error = 'Your account is not approved.';
        } else {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nard's Inventory System - Sign In</title>
    <link rel="stylesheet" href="style.css?v=5">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-header">
            <h1>Nard's Inventory System</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >
            </div>

            <button type="submit" class="auth-button">
                Sign In
            </button>

        </form>

        <p class="auth-switch">
            Don't have an account?
            <a href="register.php">Register</a>
        </p>

    </div>
</div>

</body>
</html>