<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'ACT_CTWA';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function require_login() {
    global $pdo;

    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT id, username, role, status
         FROM users
         WHERE id = ?"
    );

    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'approved') {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header("Location: index.php");
        exit;
    }

    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
}

function require_admin() {
    require_login();

    if (($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: dashboard.php");
        exit;
    }
}
?>
