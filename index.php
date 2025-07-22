<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kickslab";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if (isset($_POST['signup'])) {
    $role = $_POST['role'];

    if ($role == "buyer") {
        $uname = $_POST['username'];
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $email = $_POST['email'];
        $pass = $_POST['password'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        $sql = "INSERT INTO buyers (username, first_name, last_name, email, password, phone, address)
                VALUES ('$uname', '$fname', '$lname', '$email', '$pass', '$phone', '$address')";

        if ($conn->query($sql) === TRUE) {
            $message = "Buyer registered successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    } elseif ($role == "seller") {
        $uname = $_POST['username'];
        $pass = $_POST['password'];
        $shopname = $_POST['shopname'];
        $shoploc = $_POST['shoploc'];
        $phone = $_POST['phone'];
        $license = $_POST['license'];
        $nid = $_POST['nid'];

        $sql = "INSERT INTO sellers (username, password, shop_name, shop_location, phone, license_number, owner_nid)
                VALUES ('$uname', '$pass', '$shopname', '$shoploc', '$phone', '$license', '$nid')";

        if ($conn->query($sql) === TRUE) {
            $message = "Seller registered successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

if (isset($_POST['login'])) {
    $uname = $_POST['login_username'];
    $pass = $_POST['login_password'];

    $sql = "SELECT * FROM sellers WHERE username='$uname' AND password='$pass'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $_SESSION['role'] = 'seller';
        $_SESSION['username'] = $uname;
        header("Location: seller_dashboard.php");
        exit;
    }

    $sql = "SELECT * FROM buyers WHERE username='$uname' AND password='$pass'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $_SESSION['role'] = 'buyer';
        $_SESSION['username'] = $uname;
        header("Location: buyer_dashboard.php");
        exit;
    }

    $message = "Invalid login credentials.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KicksLab - Login & Signup</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin: 0;
    padding: 0;
    height: 100vh;
    background: url("../img/kickslab-banner.png") no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: Arial, sans-serif;
}
.overlay {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 30px;
    max-width: 500px;
    width: 100%;
    color: #fff;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    border: 1px solid rgba(255, 255, 255, 0.18);
}
.btn-custom {
    background-color: #3498db;
    color: white;
}
.btn-custom:hover {
    background-color: #2980b9;
}
.form-control, .form-select {
    background-color: rgba(255,255,255,0.8);
    color: #2c3e50;
}
.form-control::placeholder {
    color: #7f8c8d;
}
label {
    color: #fff;
}
</style>
</head>
<body>

<div class="overlay">
    <h2 class="text-center mb-3"><strong>KicksLab</strong></h2>
    <p class="text-center">Welcome! Please Login or Sign Up below</p>

    <?php if ($message) echo "<div class='alert alert-info'>$message</div>"; ?>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">Login</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="signup-tab" data-bs-toggle="tab" data-bs-target="#signup" type="button" role="tab">Sign Up</button>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="login" role="tabpanel">
            <form method="post">
                <input type="hidden" name="login" value="1">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" class="form-control" name="login_username" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" class="form-control" name="login_password" required>
                </div>
                <button class="btn btn-custom w-100" type="submit">Login</button>
            </form>
        </div>

        <div class="tab-pane fade" id="signup" role="tabpanel">
            <div>
                <select class="form-select mb-3" id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="buyer">Buyer</option>
                    <option value="seller">Seller</option>
                </select>
            </div>

            <form method="post" id="buyer-form" style="display:none;">
                <input type="hidden" name="signup" value="1">
                <input type="hidden" name="role" value="buyer">
                <div class="mb-3"><input type="text" class="form-control" name="username" placeholder="Username" required></div>
                <div class="mb-3"><input type="text" class="form-control" name="fname" placeholder="First Name" required></div>
                <div class="mb-3"><input type="text" class="form-control" name="lname" placeholder="Last Name" required></div>
                <div class="mb-3"><input type="email" class="form-control" name="email" placeholder="Email" required></div>
                <div class="mb-3"><input type="password" class="form-control" name="password" placeholder="Password" required></div>
                <div class="mb-3"><input type="text" class="form-control" name="phone" placeholder="Phone" required></div>
                <div class="mb-3"><textarea class="form-control" name="address" placeholder="Address" required></textarea></div>
                <button class="btn btn-custom w-100" type="submit">Sign Up as Buyer</button>
            </form>

            <form method="post" id="seller-form" style="display:none;">
                <input type="hidden" name="signup" value="1">
                <input type="hidden" name="role" value="seller">
                <div class="mb-3"><input type="text" class="form-control" name="username" placeholder="Username" required></div>
                <div class="mb-3"><input type="password" class="form-control" name="password" placeholder="Password" required></div>
                <div class="mb-3"><input type="text" class="form-control" name="shopname" placeholder="Shop Name" required></div>
                <div class="mb-3"><input type="text" class="form-control" name="shoploc" placeholder="Shop Location" required></div>
                <div class="mb-3"><input type="text" class="form-control" name="phone" placeholder="Phone" required></div>
                <div class="mb-3"><input type="text" class="form-control" name="license" placeholder="Shop License Number" required></div>
                <div class="mb-3"><input type="text" class="form-control" name="nid" placeholder="Owner NID Number" required></div>
                <button class="btn btn-custom w-100" type="submit">Sign Up as Seller</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('role').addEventListener('change', function() {
    document.getElementById('buyer-form').style.display = 'none';
    document.getElementById('seller-form').style.display = 'none';
    if (this.value === 'buyer') {
        document.getElementById('buyer-form').style.display = 'block';
    } else if (this.value === 'seller') {
        document.getElementById('seller-form').style.display = 'block';
    }
});
</script>
</body>
</html>
