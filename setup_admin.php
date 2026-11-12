<?php
require_once 'config.php';

$username = 'admin';
$password = 'Admin123!';

$stmt = $pdo->prepare(
    "SELECT id FROM users WHERE username = ?"
);
$stmt->execute([$username]);

if ($stmt->fetch()) {
    echo "Admin account already exists.";
} else {
    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = $pdo->prepare(
        "INSERT INTO users
        (username, password, role, status)
        VALUES (?, ?, 'admin', 'approved')"
    );

    $stmt->execute([$username, $hashedPassword]);

    echo "Admin account created successfully!";
}
?>