<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'buyer') {
    header("Location: index.php");
    exit;
}

$match_score = null;
$suggestion = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pant = strtolower(trim($_POST['pant_color']));
    $shirt = strtolower(trim($_POST['shirt_color']));

    // 🧠 Fake AI logic — can later integrate with OpenAI Vision API
    $match_score = rand(4, 10); // Random rating between 4 and 10 for demo

    // Generate suggestion based on contrast
    $suggestion = "Try combining ";
    $suggestion .= ($pant == "black") ? "light or pastel shirts" : "darker shirt shades";
    $suggestion .= " with " . htmlspecialchars($pant) . " pants for balance. ";
    $suggestion .= "You may also consider shoes with neutral tones or matching accents.";

    // Simulated image check
    if (isset($_FILES['shoe_image']) && $_FILES['shoe_image']['error'] === UPLOAD_ERR_OK) {
        $image_path = 'uploads/' . uniqid() . '_' . basename($_FILES['shoe_image']['name']);
        move_uploaded_file($_FILES['shoe_image']['tmp_name'], $image_path);
    } else {
        $image_path = null;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Style with AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 p-4 bg-white rounded shadow">
    <h2 class="mb-4">👟 Style with AI</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Pant Color</label>
            <input type="text" name="pant_color" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Shirt Color</label>
            <input type="text" name="shirt_color" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Shoe Image</label>
            <input type="file" name="shoe_image" accept="image/*" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Get AI Style Score</button>
        <a href="buyer_dashboard.php" class="btn btn-secondary ms-2">Back</a>
    </form>

    <?php if ($match_score !== null): ?>
        <hr>
        <h4 class="mt-4">🧠 AI Results:</h4>
        <p><strong>Match Score:</strong> <?= $match_score ?>/10</p>
        <p><strong>Suggestion:</strong> <?= $suggestion ?></p>
        <?php if (!empty($image_path)): ?>
            <p><strong>Uploaded Shoe:</strong></p>
            <img src="<?= $image_path ?>" style="max-width:200px;height:auto;border:1px solid #ccc;">
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
