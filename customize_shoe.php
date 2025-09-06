<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customize Your Shoe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url("img/kickslab-banner.png") no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .overlay {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            max-width: 1000px;
            margin: 30px auto;
            border-radius: 20px;
            color: #fff;
        }
        canvas {
            border: 2px solid #fff;
            border-radius: 10px;
        }
        #toolbar {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="overlay text-center">
    <h2>Design Your Own Shoe</h2>

    <!-- Shoe Type Selector -->
    <div class="mb-3">
        <label class="form-label">Select Shoe Type:</label>
        <select id="shoeSelect" class="form-select w-50 mx-auto">
            <option value="shapes/running_base.png">Running Shoe</option>
            <option value="shapes/lowtop1.png">Low-Top Style 1</option>
            <option value="shapes/lowtop2.png">Low-Top Style 2</option>
        </select>
    </div>

    <!-- Toolbar -->
    <div id="toolbar" class="d-flex justify-content-center align-items-center gap-3 flex-wrap mb-3">
        <label>Color:</label>
        <input type="color" id="colorPicker" value="#ff0000">

        <label>Brush Size:</label>
        <input type="range" id="brushSize" min="1" max="30" value="5">

        <label><input type="checkbox" id="eraser"> Eraser Mode</label>

        <button id="clearBtn" class="btn btn-sm btn-warning">Clear All</button>
    </div>

    <!-- Canvas -->
    <canvas id="shoeCanvas" width="600" height="400"></canvas>

    <!-- Save Image -->
    <div class="mt-3">
        <input type="text" id="filename" class="form-control w-50 mx-auto mb-2" placeholder="Enter file name (e.g., mydesign)">
        <button onclick="saveImage()" class="btn btn-success">Save Design</button>
        <a href="buyer_dashboard.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
    </div>
</div>

<script>
    const canvas = document.getElementById('shoeCanvas');
    const ctx = canvas.getContext('2d');

    let isDrawing = false;
    let eraserMode = false;
    let layers = [];
    let currentImage = new Image();

    function loadShoe(src) {
        currentImage.src = src;
        currentImage.onload = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(currentImage, 0, 0, canvas.width, canvas.height);
            layers = []; // Reset layers
        };
    }

    loadShoe(document.getElementById("shoeSelect").value);

    document.getElementById("shoeSelect").addEventListener("change", (e) => {
        loadShoe(e.target.value);
    });

    canvas.addEventListener("mousedown", (e) => {
        isDrawing = true;
        draw(e);
    });

    canvas.addEventListener("mouseup", () => {
        isDrawing = false;
        ctx.beginPath();
        layers.push(ctx.getImageData(0, 0, canvas.width, canvas.height)); // Save layer
    });

    canvas.addEventListener("mousemove", draw);

    function draw(e) {
        if (!isDrawing) return;

        const brushSize = document.getElementById("brushSize").value;
        const color = document.getElementById("colorPicker").value;

        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        ctx.lineWidth = brushSize;
        ctx.lineCap = "round";
        ctx.strokeStyle = eraserMode ? "#ffffff" : color;

        ctx.lineTo(x, y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x, y);
    }

    document.getElementById("eraser").addEventListener("change", function () {
        eraserMode = this.checked;
    });

    document.getElementById("clearBtn").addEventListener("click", () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(currentImage, 0, 0, canvas.width, canvas.height);
        layers = [];
    });

    function saveImage() {
        const name = document.getElementById("filename").value.trim() || "mydesign";
        const link = document.createElement('a');
        link.download = name + ".png";
        link.href = canvas.toDataURL();
        link.click();
    }
</script>

</body>
</html>
