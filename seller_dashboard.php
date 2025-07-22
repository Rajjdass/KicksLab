<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kickslab";
$conn = new mysqli($servername, $username, $password, $dbname);

$seller_username = $_SESSION['username'];

if (!is_dir('uploads')) {
    mkdir('uploads');
}

// Handle shoe upload
if (isset($_POST['upload'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $condition = $_POST['condition'];
    $color = $_POST['color'];
    $price = $_POST['price'];
    $brand = $_POST['brand'];

    $image_path = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . "." . $ext;
        $destination = "uploads/" . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $image_path = $destination;
        }
    }

    if ($image_path) {
        $conn->query("INSERT INTO products (name, category, condition_type, color, price, brand, shop_name, image) 
        VALUES ('$name', '$category', '$condition', '$color', '$price', '$brand', '$seller_username', '$image_path')");

        $product_id = $conn->insert_id;

        for ($i = 0; $i < count($_POST['sizes']); $i++) {
            $size = $_POST['sizes'][$i];
            $qty = $_POST['quantities'][$i];
            $conn->query("INSERT INTO product_sizes (product_id, size, quantity) VALUES ('$product_id', '$size', '$qty')");
        }
    }
}

// Handle restock
if (isset($_POST['restock'])) {
    $product_id = $_POST['product_id'];
    $size = $_POST['size'];
    $quantity = $_POST['quantity'];

    $check = $conn->query("SELECT * FROM product_sizes WHERE product_id='$product_id' AND size='$size'");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE product_sizes SET quantity = quantity + $quantity WHERE product_id='$product_id' AND size='$size'");
    } else {
        $conn->query("INSERT INTO product_sizes (product_id, size, quantity) VALUES ('$product_id', '$size', '$quantity')");
    }
}

$products = $conn->query("SELECT * FROM products WHERE shop_name='$seller_username'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Dashboard</title>
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
    max-width: 1000px;
    width: 100%;
    color: #fff;
}
.btn-custom {
    background-color: #3498db;
    color: white;
}
.btn-custom:hover {
    background-color: #2980b9;
}
.form-control {
    background-color: rgba(255,255,255,0.8);
    color: #2c3e50;
}
label {
    color: #fff;
}
</style>
</head>
<body>

<div class="overlay">
    <div class="d-flex justify-content-between">
        <h2>Welcome, <?= htmlspecialchars($seller_username) ?> (Seller)</h2>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <h4 class="mt-4">Your Listed Shoes</h4>
    <div class="row">
    <?php while($row = $products->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
            <div class="card">
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
                        Price: ৳<?= htmlspecialchars($row['price']) ?>
                    </p>
                    <h6>Sizes & Stock:</h6>
                    <ul>
                    <?php
                        $pid = $row['id'];
                        $sizes = $conn->query("SELECT * FROM product_sizes WHERE product_id='$pid'");
                        while($s = $sizes->fetch_assoc()) {
                            echo "<li>Size {$s['size']} — ";
                            if ($s['quantity'] > 0) {
                                echo "{$s['quantity']} in stock";
                            } else {
                                echo "<span class='text-danger'>Stock Out</span>";
                            }
                            echo "</li>";
                        }
                    ?>
                    </ul>
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#restockModal<?= $row['id'] ?>">Restock</button>
                </div>
            </div>
        </div>

        <!-- Restock Modal -->
        <div class="modal fade" id="restockModal<?= $row['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content text-dark">
              <div class="modal-header">
                <h5 class="modal-title">Restock: <?= htmlspecialchars($row['name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <form method="post">
                  <input type="hidden" name="restock" value="1">
                  <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                  <div class="mb-3">
                    <label>Size</label>
                    <input name="size" class="form-control" required placeholder="e.g. 42">
                  </div>
                  <div class="mb-3">
                    <label>Quantity</label>
                    <input name="quantity" class="form-control" type="number" min="1" required placeholder="e.g. 10">
                  </div>
                  <button type="submit" class="btn btn-success">Add Stock</button>
                </form>
              </div>
            </div>
          </div>
        </div>
    <?php endwhile; ?>
    </div>

    <hr>

    <h4>Upload New Shoe</h4>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="upload" value="1">
        <div class="col-md-6">
            <input name="name" class="form-control" placeholder="Shoe Name" required>
        </div>
        <div class="col-md-6">
            <input name="category" class="form-control" placeholder="Category" required>
        </div>
        <div class="col-md-6">
            <select name="condition" class="form-control" required>
                <option value="">Select Condition</option>
                <option value="New">New</option>
                <option value="Used">Used</option>
            </select>
        </div>
        <div class="col-md-6">
            <input name="color" class="form-control" placeholder="Color" required>
        </div>
        <div class="col-md-6">
            <input name="price" class="form-control" placeholder="Price" required>
        </div>
        <div class="col-md-6">
            <input name="brand" class="form-control" placeholder="Brand" required>
        </div>
        <div class="col-md-6">
            <input type="file" name="image" class="form-control" required>
        </div>

        <div class="col-12">
            <h6>Enter Sizes & Quantities</h6>
            <div id="sizes-container">
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <input name="sizes[]" class="form-control" placeholder="Size" required>
                    </div>
                    <div class="col-md-3">
                        <input name="quantities[]" class="form-control" placeholder="Quantity" required>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-light" onclick="addSize()">+ Add Another Size</button>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-custom w-100">Upload Shoe</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function addSize() {
    const container = document.getElementById('sizes-container');
    const row = document.createElement('div');
    row.classList.add('row', 'g-2', 'mb-2');
    row.innerHTML = `
        <div class="col-md-3">
            <input name="sizes[]" class="form-control" placeholder="Size" required>
        </div>
        <div class="col-md-3">
            <input name="quantities[]" class="form-control" placeholder="Quantity" required>
        </div>
    `;
    container.appendChild(row);
}
</script>

</body>
</html>
