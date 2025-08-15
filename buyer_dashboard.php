<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'buyer') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kickslab";

$conn = new mysqli($servername, $username, $password, $dbname);
$buyer_username = $_SESSION['username'];

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (!isset($_SESSION['compare'])) $_SESSION['compare'] = [];

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $size = $_POST['size'];
    $_SESSION['cart'][] = ['product_id' => $product_id, 'size' => $size];
}

if (isset($_POST['remove_from_cart'])) {
    $index = $_POST['index'];
    unset($_SESSION['cart'][$index]);
}

if (isset($_POST['add_to_compare'])) {
    $compare_id = $_POST['product_id'];
    if (!in_array($compare_id, $_SESSION['compare'])) {
        $_SESSION['compare'][] = $compare_id;
    }
}

if (isset($_POST['clear_compare'])) {
    $_SESSION['compare'] = [];
}

$filters = [];
if (!empty($_GET['color'])) $filters[] = "color='" . $_GET['color'] . "'";
if (!empty($_GET['brand'])) $filters[] = "brand='" . $_GET['brand'] . "'";
if (!empty($_GET['shop_name'])) $filters[] = "shop_name='" . $_GET['shop_name'] . "'";
if (!empty($_GET['condition'])) $filters[] = "condition_type='" . $_GET['condition'] . "'";
if (!empty($_GET['max_price'])) $filters[] = "price <= " . intval($_GET['max_price']);

$where = $filters ? "WHERE " . implode(" AND ", $filters) : "";
$products = $conn->query("SELECT * FROM products $where");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buyer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url("../img/kickslab-banner.png") no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .overlay {
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            max-width: 1200px;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="overlay">
    <div class="d-flex justify-content-between mb-4">
        <h2>Welcome, <?= htmlspecialchars($buyer_username) ?> (Buyer)</h2>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <h4>Filter Products</h4>
    <form method="get" class="row g-2 mb-4 text-dark">
        <div class="col-md-2"><input type="text" name="color" class="form-control" placeholder="Color" value="<?= $_GET['color'] ?? '' ?>"></div>
        <div class="col-md-2"><input type="text" name="brand" class="form-control" placeholder="Brand" value="<?= $_GET['brand'] ?? '' ?>"></div>
        <div class="col-md-2"><input type="text" name="shop_name" class="form-control" placeholder="Shop Name" value="<?= $_GET['shop_name'] ?? '' ?>"></div>
        <div class="col-md-2">
            <select name="condition" class="form-select">
                <option value="">Condition</option>
                <option value="New" <?= (@$_GET['condition']=='New')?'selected':'' ?>>New</option>
                <option value="Used" <?= (@$_GET['condition']=='Used')?'selected':'' ?>>Used</option>
            </select>
        </div>
        <div class="col-md-2"><input type="number" name="max_price" class="form-control" placeholder="Max Price" value="<?= $_GET['max_price'] ?? '' ?>"></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">Apply</button></div>
        <div class="col-md-1"><a href="buyer_dashboard.php" class="btn btn-secondary w-100">Reset</a></div>
    </form>

    <h4>Available Shoes</h4>
    <div class="row">
    <?php while($row = $products->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <?php if ($row['image']): ?>
                    <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                    <p class="card-text">
                        Category: <?= htmlspecialchars($row['category']) ?><br>
                        Condition: <?= htmlspecialchars($row['condition_type']) ?><br>
                        Color: <?= htmlspecialchars($row['color']) ?><br>
                        Brand: <?= htmlspecialchars($row['brand']) ?><br>
                        Price: ৳<?= htmlspecialchars($row['price']) ?><br>
                        Seller: <?= htmlspecialchars($row['shop_name']) ?>
                    </p>
                    <form method="post">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <div class="mb-2">
                            <select name="size" class="form-select" required>
                                <option value="">Select Size</option>
                                <?php
                                $pid = $row['id'];
                                $sizes = $conn->query("SELECT * FROM product_sizes WHERE product_id='$pid'");
                                while($s = $sizes->fetch_assoc()) {
                                    $disabled = ($s['quantity'] <= 0) ? 'disabled' : '';
                                    $text = ($s['quantity'] <= 0) ? "{$s['size']} (Out of stock)" : "{$s['size']} ({$s['quantity']} left)";
                                    echo "<option value=\"{$s['size']}\" $disabled>$text</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary w-100">Add to Cart</button>
                    </form>

                    <!-- Compare Button -->
                    <form method="post" class="mt-2">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="add_to_compare" class="btn btn-warning w-100">Add to Compare</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>

    <hr>

    <h4>Your Cart</h4>
    <?php if (!empty($_SESSION['cart'])): ?>
        <ul class="list-group mb-3 text-dark">
        <?php
        $total = 0;
        foreach ($_SESSION['cart'] as $index => $item) {
            $pid = $item['product_id'];
            $size = $item['size'];
            $p = $conn->query("SELECT * FROM products WHERE id='$pid'")->fetch_assoc();
            $price = $p['price'];
            $total += $price;

            echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
            echo "{$p['name']} - Size {$size} — ৳{$price}";
            echo "<form method='post' class='mb-0'>";
            echo "<input type='hidden' name='index' value='$index'>";
            echo "<button name='remove_from_cart' class='btn btn-sm btn-danger ms-2'>Remove</button>";
            echo "</form>";
            echo "</li>";
        }
        ?>
        </ul>
        <p><strong>Total: ৳<?= $total ?></strong></p>
        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>

    <!-- Comparison Panel -->
    <?php if (count($_SESSION['compare']) >= 2): ?>
        <div class="mt-4">
            <a href="compare.php" class="btn btn-info me-2">View Comparison (<?= count($_SESSION['compare']) ?>)</a>
            <form method="post" class="d-inline">
                <button name="clear_compare" class="btn btn-sm btn-danger">Clear Comparison</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Style with AI Feature -->
    <div class="mt-4">
        <a href="style_ai.php" class="btn btn-dark">Style with AI</a>
    </div>

</div>
</body>
</html>
