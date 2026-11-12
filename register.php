<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($username === '' || $password === '' || $confirm === '') {
        $error = 'Please fill in all fields.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE username = ?"
        );
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = 'Username already exists.';
        } else {
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (username, password, role, status)
                VALUES (?, ?, 'user', 'pending')"
            );

            $stmt->execute([
                $username,
                $hashedPassword
            ]);

            $success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nard's Inventory System - Register</title>
    <link rel="stylesheet" href="style.css?v=4">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-header">
            <h1>Nard's Inventory System</h1>
            <p>Create your account</p>
        </div>

        <?php if ($success): ?>

            <div class="success">
                Registration successful! Your account is waiting
                for admin approval.
            </div>

            <a href="index.php" class="auth-button">
                Back to Sign In
            </a>

        <?php else: ?>

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
                        minlength="3"
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
                        minlength="6"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Re-enter Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        required
                    >
                </div>

                <button type="submit" class="auth-button">
                    Register
                </button>

            </form>

            <p class="auth-switch">
                Already have an account?
                <a href="index.php">Sign In</a>
            </p>

        <?php endif; ?>

    </div>
</div>

</body>
</html>