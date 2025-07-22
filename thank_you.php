<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'buyer') {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thank You</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin: 0;
    padding: 0;
    background: url("../img/kickslab-banner.png") no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: Arial, sans-serif;
    height: 100vh;
}
.overlay {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 50px;
    text-align: center;
    color: #fff;
    max-width: 600px;
}
</style>
</head>
<body>

<div class="overlay">
    <h2>Thank You for Your Order!</h2>
    <p>You will get your product in 3 to 4 days.</p>
    <p>A rider will contact you on your given phone number.</p>
    <a href="buyer_dashboard.php" class="btn btn-success mt-3">Back to Dashboard</a>
</div>

</body>
</html>
