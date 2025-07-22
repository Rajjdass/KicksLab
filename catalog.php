<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kickslab";

// DB Connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $_SESSION['cart'][] = $product_id;
}

// Get Filters
$category = $_GET['category'] ?? '';
$brand = $_GET['brand'] ?? '';
$condition = $_GET['condition'] ?? '';

// Fetch Products
$sql = "SELECT * FROM products WHERE 1";
if ($category) $sql .= " AND category='$category'";
if ($brand) $sql .= " AND brand='$brand'";
if ($condition) $sql .= " AND condition_type='$condition'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Shoe Catalog</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Shoe Catalog</h2>

    <form class="row mb-4">
        <div class="col"><input type="text" name="category" class="form-control" placeholder="Category" value="<?= htmlspecialchars($category) ?>"></div>
        <div class="col"><input type="text" name="brand" class="form-control" placeholder="Brand" value="<?= htmlspecialchars($brand) ?>"></div>
        <div class="col">
            <select name="condition" class="form-select">
                <option value="">Any Condition</option>
                <option <?= $condition=='New'?'selected':'' ?>>New</option>
                <option <?= $condition=='Used'?'selected':'' ?>>Used</option>
            </select>
        </div>
        <div class="col"><button class="btn btn-primary">Filter</button></div>
    </form>

    <div class="row">
        <?php while($row = $result->fetch_assoc()): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <?php if ($row['image']): ?>
                    <img src="<?= $row['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                    <p>Category: <?= htmlspecialchars($row['category']) ?></p>
                    <p>Condition: <?= htmlspecialchars($row['condition_type']) ?></p>
                    <p>Brand: <?= htmlspecialchars($row['brand']) ?></p>
                    <p>Shop: <?= htmlspecialchars($row['shop_name']) ?></p>
                    <p>Price: $<?= htmlspecialchars($row['price']) ?></p>
                    <form method="post">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <button name="add_to_cart" class="btn btn-success w-100">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <hr>
    <h4>Your Cart (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?> items)</h4>
    <ul>
        <?php
        if (!empty($_SESSION['cart'])) {
            $ids = implode(",", $_SESSION['cart']);
            $cart_items = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
            while($item = $cart_items->fetch_assoc()) {
                echo "<li>{$item['name']} - \${$item['price']}</li>";
            }
        }
        ?>
    </ul>
</div>
</body>
</html>
