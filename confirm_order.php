<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'buyer') {
    header("Location: index.php");
    exit;
}

if (!isset($_POST['delivery_address']) || !isset($_POST['phone'])) {
    header("Location: checkout.php");
    exit;
}

$delivery_address = $_POST['delivery_address'];
$phone = $_POST['phone'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kickslab";

$conn = new mysqli($servername, $username, $password, $dbname);

$buyer_username = $_SESSION['username'];

$cart_items = $_SESSION['cart'];
$delivery_charge = 160;
$subtotal = 0;

foreach ($cart_items as $item) {
    $pid = $item['product_id'];
    $size = $item['size'];
    $p = $conn->query("SELECT * FROM products WHERE id='$pid'")->fetch_assoc();
    $price = $p['price'];
    $subtotal += $price;

    // 🔷 Decrement quantity in product_sizes table
    $conn->query("UPDATE product_sizes SET quantity = quantity - 1 WHERE product_id='$pid' AND size='$size'");
}

$total = $subtotal + $delivery_charge;

// Insert order record
$conn->query("INSERT INTO orders (buyer_username, total_amount, delivery_address, phone) 
VALUES ('$buyer_username', '$total', '$delivery_address', '$phone')");

// Clear cart
unset($_SESSION['cart']);

// Redirect to thank you page
header("Location: thank_you.php");
exit;
?>
