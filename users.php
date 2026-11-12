
<?php
require_once 'config.php';
require_admin();

$message = '';
$error = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Invalid request. Please refresh the page.';
    } else {
        $user_id = filter_input(
            INPUT_POST,
            'user_id',
            FILTER_VALIDATE_INT
        );

        $action = $_POST['action'] ?? '';

        if (
            $user_id &&
            in_array($action, ['approve', 'reject'], true)
        ) {
            $stmt = $pdo->prepare(
                "SELECT id, role FROM users WHERE id = ?"
            );

            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'User not found.';
            } elseif ($user['role'] === 'admin') {
                $error = 'Admin account cannot be changed.';
            } else {
                $status = $action === 'approve'
                    ? 'approved'
                    : 'rejected';

                $stmt = $pdo->prepare(
                    "UPDATE users
                     SET status = ?
                     WHERE id = ? AND role = 'user'"
                );

                $stmt->execute([$status, $user_id]);

                $message = $action === 'approve'
                    ? 'User approved successfully.'
                    : 'User rejected successfully.';
            }
        } else {
            $error = 'Invalid request.';
        }
    }
}

$stmt = $pdo->query(
    "SELECT id, username, role, status, created_at
     FROM users
     ORDER BY created_at DESC"
);

$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nard's Inventory System - Users</title>

    <link rel="stylesheet" href="style.css?v=5">
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container">

    <h2>Manage Users</h2>

    <p>
        <a href="dashboard.php">Back to Dashboard</a>
    </p>

    <?php if ($message): ?>
        <div class="success">
            <?= h($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <div class="table-container">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($users) > 0): ?>

                    <?php foreach ($users as $user): ?>

                        <tr>

                            <td><?= (int)$user['id'] ?></td>

                            <td><?= h($user['username']) ?></td>

                            <td><?= h($user['role']) ?></td>

                            <td><?= h($user['status']) ?></td>

                            <td><?= h($user['created_at']) ?></td>

                            <td>

                                <?php if ($user['role'] === 'admin'): ?>

                                    Admin Account

                                <?php else: ?>

                                    <?php if ($user['status'] !== 'approved'): ?>

                                        <form method="POST">

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= h($_SESSION['csrf_token']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$user['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="action"
                                                value="approve"
                                            >
                                                Approve
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                    <?php if ($user['status'] !== 'rejected'): ?>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to reject this user?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= h($_SESSION['csrf_token']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$user['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="action"
                                                value="reject"
                                            >
                                                Reject
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6">No users found.</td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
