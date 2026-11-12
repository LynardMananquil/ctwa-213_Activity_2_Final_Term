<?php
require_once 'config.php';
?>

<nav class="navbar">

    <a href="dashboard.php" class="navbar-brand">
        Nard's Inventory System
    </a>

    <div class="navbar-links">

        <a href="dashboard.php">Dashboard</a>

        <a href="products.php">Products</a>

        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <a href="users.php">Users</a>
        <?php endif; ?>

        <a href="logout.php">Logout</a>

    </div>

</nav>