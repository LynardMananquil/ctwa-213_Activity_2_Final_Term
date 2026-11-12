
<?php
require_once 'config.php';
require_login();

$is_admin = ($_SESSION['role'] ?? '') === 'admin';

$stmt = $pdo->query(
    "SELECT COUNT(*) FROM products"
);
$total_products = $stmt->fetchColumn();

$stmt = $pdo->query(
    "SELECT COALESCE(SUM(quantity), 0) FROM products"
);
$total_stock = $stmt->fetchColumn();

if ($is_admin) {
    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM users"
    );
    $total_users = $stmt->fetchColumn();

    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM users WHERE status = 'approved'"
    );
    $approved_users = $stmt->fetchColumn();

    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM users WHERE status = 'pending'"
    );
    $pending_users = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nard's Inventory System - Dashboard</title>

    <link rel="stylesheet" href="style.css?v=5">
</head>
<body>

<?php include 'nav.php'; ?>

<main class="dashboard-container">

    <section class="welcome-section">

        <div>
            <span class="eyebrow">NARD'S INVENTORY SYSTEM</span>

            <h1>Dashboard</h1>

            <p>
                Welcome back,
                <?= h($_SESSION['username']) ?>!
            </p>
        </div>

        <div class="welcome-icon">📦</div>

    </section>

    <section class="dashboard-cards">

        <div class="card blue-card">

            <div class="card-icon">📦</div>

            <span>Total Products</span>

            <h2><?= (int)$total_products ?></h2>

            <small>Products in inventory</small>

        </div>

        <div class="card green-card">

            <div class="card-icon">📊</div>

            <span>Total Stock</span>

            <h2><?= (int)$total_stock ?></h2>

            <small>Total available quantity</small>

        </div>

        <?php if ($is_admin): ?>

            <div class="card purple-card">

                <div class="card-icon">👥</div>

                <span>Total Users</span>

                <h2><?= (int)$total_users ?></h2>

                <small>Registered accounts</small>

            </div>

            <div class="card orange-card">

                <div class="card-icon">✅</div>

                <span>Approved Users</span>

                <h2><?= (int)$approved_users ?></h2>

                <small>Approved accounts</small>

            </div>

            <div class="card red-card">

                <div class="card-icon">⏳</div>

                <span>Pending Users</span>

                <h2><?= (int)$pending_users ?></h2>

                <small>Waiting for approval</small>

            </div>

        <?php endif; ?>

    </section>

    <section class="dashboard-section">

        <div>
            <span class="eyebrow">SYSTEM NAVIGATION</span>

            <h2>Quick Access</h2>

            <p>Access your inventory and account management features.</p>
        </div>

        <div class="quick-actions">

            <a href="products.php" class="action-card">

                <span class="action-icon">📦</span>

                <div>
                    <h3>Manage Products</h3>

                    <p>
                        View, add, and edit inventory products.
                        <?php if ($is_admin): ?>
                            Delete products as well.
                        <?php endif; ?>
                    </p>
                </div>

                <span class="action-arrow">›</span>

            </a>

            <?php if ($is_admin): ?>

                <a href="users.php" class="action-card">

                    <span class="action-icon">👥</span>

                    <div>
                        <h3>Manage Users</h3>

                        <p>
                            View users and approve or reject registrations.
                        </p>
                    </div>

                    <span class="action-arrow">›</span>

                </a>

            <?php endif; ?>

        </div>

    </section>

    <footer class="dashboard-footer">

        <span>Nard's Inventory System</span>

        <span>Inventory Management</span>

    </footer>

</main>

</body>
</html>
