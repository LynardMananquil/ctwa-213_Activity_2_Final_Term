
<?php
require_once 'config.php';
require_login();

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

$error = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$is_admin = ($_SESSION['role'] ?? '') === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Invalid request. Please refresh the page.';
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'add') {
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $quantity = filter_var(
                    $_POST['quantity'] ?? null,
                    FILTER_VALIDATE_INT
                );
                $price = $_POST['price'] ?? '';

                if (
                    $name === '' ||
                    $quantity === false ||
                    $quantity === null ||
                    $quantity < 0 ||
                    !is_numeric($price) ||
                    !is_finite((float)$price) ||
                    (float)$price < 0
                ) {
                    $error = 'Please enter valid product information.';
                } else {
                    $stmt = $pdo->prepare(
                        "INSERT INTO products
                        (name, description, quantity, price, created_by)
                        VALUES (?, ?, ?, ?, ?)"
                    );

                    $stmt->execute([
                        $name,
                        $description,
                        $quantity,
                        $price,
                        $_SESSION['user_id']
                    ]);

                    $_SESSION['success_message'] = 'Product added successfully!';
                    header("Location: products.php");
                    exit;
                }
            }

            elseif ($action === 'edit') {
                $id = filter_var(
                    $_POST['id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $quantity = filter_var(
                    $_POST['quantity'] ?? null,
                    FILTER_VALIDATE_INT
                );
                $price = $_POST['price'] ?? '';

                if (
                    !$id ||
                    $name === '' ||
                    $quantity === false ||
                    $quantity === null ||
                    $quantity < 0 ||
                    !is_numeric($price) ||
                    !is_finite((float)$price) ||
                    (float)$price < 0
                ) {
                    $error = 'Please enter valid product information.';
                } else {
                    $stmt = $pdo->prepare(
                        "UPDATE products
                         SET name = ?, description = ?, quantity = ?, price = ?
                         WHERE id = ?"
                    );

                    $stmt->execute([
                        $name,
                        $description,
                        $quantity,
                        $price,
                        $id
                    ]);

                    $_SESSION['success_message'] = 'Product updated successfully!';
                    header("Location: products.php");
                    exit;
                }
            }

            elseif ($action === 'delete') {
                if (!$is_admin) {
                    $error = 'Only administrators can delete products.';
                } else {
                    $id = filter_var(
                        $_POST['id'] ?? null,
                        FILTER_VALIDATE_INT
                    );

                    if (!$id) {
                        $error = 'Invalid product ID.';
                    } else {
                        $stmt = $pdo->prepare(
                            "DELETE FROM products WHERE id = ?"
                        );

                        $stmt->execute([$id]);

                        if ($stmt->rowCount() > 0) {
                            $_SESSION['success_message'] = 'Product deleted successfully!';
                        } else {
                            $_SESSION['success_message'] = 'Product was not found.';
                        }

                        header("Location: products.php");
                        exit;
                    }
                }
            }

            else {
                $error = 'Invalid action.';
            }
        } catch (PDOException $e) {
            $error = 'Something went wrong while processing the product. Please try again.';
        }
    }
}

$edit_product = null;

if (isset($_GET['edit'])) {
    $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);

    if ($id) {
        $stmt = $pdo->prepare(
            "SELECT * FROM products WHERE id = ?"
        );

        $stmt->execute([$id]);
        $edit_product = $stmt->fetch();
    }
}

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare(
        "SELECT id, name, description, quantity, price
         FROM products
         WHERE name LIKE ?
         ORDER BY id DESC"
    );

    $stmt->execute(['%' . $search . '%']);
} else {
    $stmt = $pdo->query(
        "SELECT id, name, description, quantity, price
         FROM products
         ORDER BY id DESC"
    );
}

$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Products - Nard's Inventory System</title>

    <link rel="stylesheet" href="./style.css?v=6">
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container">

    <h2>Product Management</h2>

    <p>Welcome, <?= h($_SESSION['username']) ?>!</p>

    <h3>
        <?= $edit_product ? 'Edit Product' : 'Add New Product' ?>
    </h3>

    <form method="POST" action="products.php">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= h($_SESSION['csrf_token']) ?>"
        >

        <input
            type="hidden"
            name="action"
            value="<?= $edit_product ? 'edit' : 'add' ?>"
        >

        <?php if ($edit_product): ?>
            <input
                type="hidden"
                name="id"
                value="<?= (int)$edit_product['id'] ?>"
            >
        <?php endif; ?>

        <label for="name">Product Name</label>

        <input
            type="text"
            id="name"
            name="name"
            placeholder="Enter product name"
            value="<?= h($edit_product['name'] ?? '') ?>"
            required
        >

        <label for="description">Description</label>

        <textarea
            id="description"
            name="description"
            placeholder="Enter product description"
        ><?= h($edit_product['description'] ?? '') ?></textarea>

        <label for="quantity">Quantity</label>

        <input
            type="number"
            id="quantity"
            name="quantity"
            placeholder="Enter quantity"
            min="0"
            value="<?= h($edit_product['quantity'] ?? '') ?>"
            required
        >

        <label for="price">Price</label>

        <input
            type="number"
            id="price"
            name="price"
            placeholder="Enter price"
            min="0"
            step="0.01"
            value="<?= h($edit_product['price'] ?? '') ?>"
            required
        >

        <button type="submit">
            <?= $edit_product ? 'Update Product' : 'Add Product' ?>
        </button>

        <?php if ($edit_product): ?>
            <a href="products.php" class="btn">Cancel</a>
        <?php endif; ?>

    </form>

    <hr>

    <h3>Product List</h3>

    <form method="GET" action="products.php" class="search-form">

        <input
            type="text"
            name="search"
            placeholder="Search product name..."
            value="<?= h($search) ?>"
        >

        <button type="submit">Search</button>

        <?php if ($search !== ''): ?>
            <a href="products.php" class="btn">Clear Search</a>
        <?php endif; ?>

    </form>

    <div class="table-container">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($products) > 0): ?>

                    <?php foreach ($products as $product): ?>

                        <tr>

                            <td><?= (int)$product['id'] ?></td>

                            <td><?= h($product['name']) ?></td>

                            <td><?= h($product['description']) ?></td>

                            <td><?= (int)$product['quantity'] ?></td>

                            <td>
                                ₱<?= number_format(
                                    (float)$product['price'],
                                    2
                                ) ?>
                            </td>

                            <td>

                                <a
                                    href="products.php?edit=<?= (int)$product['id'] ?>"
                                    class="btn"
                                >
                                    Edit
                                </a>

                                <?php if ($is_admin): ?>

                                    <form
                                        method="POST"
                                        action="products.php"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= h($_SESSION['csrf_token']) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$product['id'] ?>"
                                        >

                                        <button type="submit">
                                            Delete
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6">
                            No products found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <p>
        Total Products Displayed: <?= count($products) ?>
    </p>

</div>

<script>
<?php if ($success_message): ?>
    alert(<?= json_encode($success_message) ?>);
<?php endif; ?>

<?php if ($error): ?>
    alert(<?= json_encode($error) ?>);
<?php endif; ?>
</script>

</body>
</html>
