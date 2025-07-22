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

$delivery_charge = 160;
$subtotal = 0;

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header("Location: buyer_dashboard.php");
    exit;
}

$cart_items = $_SESSION['cart'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin: 0;
    padding: 0;
    background: url("../img/kickslab-banner.png") no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    font-family: Arial, sans-serif;
}
.overlay {
    margin-top: 20px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 30px;
    max-width: 800px;
    width: 100%;
    color: #fff;
}
</style>
</head>
<body>

<div class="overlay">
    <div class="d-flex justify-content-between mb-4">
        <h2>Checkout</h2>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <h4>Your Order</h4>
    <ul class="list-group mb-3 text-dark">
    <?php
    foreach ($cart_items as $item) {
        $pid = $item['product_id'];
        $size = $item['size'];
        $p = $conn->query("SELECT * FROM products WHERE id='$pid'")->fetch_assoc();
        $price = $p['price'];
        $subtotal += $price;

        echo "<li class='list-group-item'>{$p['name']} - Size {$size} — ৳{$price}</li>";
    }
    ?>
    </ul>

    <p><strong>Subtotal:</strong> ৳<?= $subtotal ?></p>
    <p><strong>Delivery Charge:</strong> ৳<?= $delivery_charge ?></p>
    <p><strong>Total:</strong> ৳<?= $subtotal + $delivery_charge ?></p>

    <hr>

    <h4>Delivery Details</h4>
    <form method="post" action="confirm_order.php">
        <textarea name="delivery_address" class="form-control mb-3" placeholder="Enter your exact delivery address" rows="3" required></textarea>
        <input type="text" name="phone" class="form-control mb-3" placeholder="Enter your contact phone number" required>
        <button type="submit" class="btn btn-success w-100">Confirm Order</button>
    </form>
</div>

</body>
</html>
