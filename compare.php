<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'buyer' || count($_SESSION['compare']) < 2) {
    header("Location: buyer_dashboard.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "kickslab");

$product_ids = implode(",", array_map('intval', $_SESSION['compare']));
$result = $conn->query("SELECT * FROM products WHERE id IN ($product_ids)");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Compare Shoes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Shoe Comparison</h2>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Attribute</th>
                    <?php foreach ($result as $shoe): ?>
                        <th><?= htmlspecialchars($shoe['name']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Image</th>
                    <?php foreach ($result as $shoe): ?>
                        <td><img src="<?= htmlspecialchars($shoe['image']) ?>" style="width:100px;height:100px;object-fit:cover;"></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Brand</th>
                    <?php foreach ($result as $shoe): ?>
                        <td><?= htmlspecialchars($shoe['brand']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Condition</th>
                    <?php foreach ($result as $shoe): ?>
                        <td><?= htmlspecialchars($shoe['condition_type']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Color</th>
                    <?php foreach ($result as $shoe): ?>
                        <td><?= htmlspecialchars($shoe['color']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Price</th>
                    <?php foreach ($result as $shoe): ?>
                        <td>৳<?= htmlspecialchars($shoe['price']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Shop</th>
                    <?php foreach ($result as $shoe): ?>
                        <td><?= htmlspecialchars($shoe['shop_name']) ?></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
    <a href="buyer_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
</div>
</body>
</html>
