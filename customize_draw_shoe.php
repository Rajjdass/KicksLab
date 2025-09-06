<!DOCTYPE html>
<html>
<head>
  <title>Draw Your Shoe Design</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #111;
      color: #fff;
      text-align: center;
    }
    .canvas-wrapper {
      position: relative;
      display: inline-block;
    }
    canvas {
      border: 2px solid #ccc;
      border-radius: 10px;
      position: absolute;
      top: 0;
      left: 0;
    }
    #shoeCanvas {
      z-index: 1;
    }
    #drawCanvas {
      z-index: 2;
    }
    .toolbar {
      margin: 20px;
    }
    label {
      margin-right: 10px;
    }
  </style>
</head>
<body>

  <h1>🎨 Design Your Own Shoe</h1>

  <div class="toolbar">
    <label for="shoeSelect">👟 Shoe Type:</label>
    <select id="shoeSelect">
      <option value="lowtop">Low Top</option>
      <option value="hightop">High Top</option>
      <option value="running">Running</option>
    </select>

    <label for="colorPicker">🎨 Color:</label>
    <input type="color" id="colorPicker" value="#ff0000">

    <label for="brushSize">🖌️ Brush Size:</label>
    <input type="range" id="brushSize" min="1" max="30" value="5">

    <label>
      <input type="checkbox" id="eraserToggle"> 🧽 Eraser Mode
    </label>

    <button onclick="clearCanvas()">♻️ Reset</button>
    <button onclick="saveImage()">💾 Save</button>
  </div>

  <div class="canvas-wrapper" style="width:768px; height:768px;">
    <canvas id="shoeCanvas" width="768" height="768"></canvas>
    <canvas id="drawCanvas" width="768" height="768"></canvas>
  </div>

  <script>
    const shoeCanvas = document.getElementById('shoeCanvas');
    const drawCanvas = document.getElementById('drawCanvas');
    const shoeCtx = shoeCanvas.getContext('2d');
    const drawCtx = drawCanvas.getContext('2d');

    const shoeSelect = document.getElementById('shoeSelect');
    const colorPicker = document.getElementById('colorPicker');
    const brushSize = document.getElementById('brushSize');
    const eraserToggle = document.getElementById('eraserToggle');

    let drawing = false;
    let shoeImage = new Image();

    function loadShoe(type) {
      shoeImage.src = `shapes/${type}.png`;
      shoeImage.onload = () => {
        shoeCtx.clearRect(0, 0, shoeCanvas.width, shoeCanvas.height);
        drawCtx.clearRect(0, 0, drawCanvas.width, drawCanvas.height);
        shoeCtx.drawImage(shoeImage, 0, 0, shoeCanvas.width, shoeCanvas.height);
      };
    }

    shoeSelect.addEventListener('change', (e) => loadShoe(e.target.value));
    loadShoe('lowtop'); // Default

    drawCanvas.addEventListener('mousedown', () => {
      drawing = true;
      drawCtx.beginPath();
    });

    drawCanvas.addEventListener('mouseup', () => {
      drawing = false;
      drawCtx.beginPath();
    });

    drawCanvas.addEventListener('mousemove', (e) => {
      if (!drawing) return;

      const rect = drawCanvas.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;

      drawCtx.lineWidth = brushSize.value;
      drawCtx.lineCap = 'round';

      if (eraserToggle.checked) {
        drawCtx.globalCompositeOperation = 'destination-out';
        drawCtx.strokeStyle = 'rgba(0,0,0,1)';
      } else {
        drawCtx.globalCompositeOperation = 'source-over';
        drawCtx.strokeStyle = colorPicker.value;
      }

      drawCtx.lineTo(x, y);
      drawCtx.stroke();
      drawCtx.beginPath();
      drawCtx.moveTo(x, y);
    });

    function clearCanvas() {
      drawCtx.clearRect(0, 0, drawCanvas.width, drawCanvas.height);
    }

    function saveImage() {
      const finalCanvas = document.createElement('canvas');
      finalCanvas.width = 768;
      finalCanvas.height = 768;
      const finalCtx = finalCanvas.getContext('2d');

      finalCtx.drawImage(shoeCanvas, 0, 0);
      finalCtx.drawImage(drawCanvas, 0, 0);

      const link = document.createElement('a');
      link.download = 'custom_shoe.png';
      link.href = finalCanvas.toDataURL();
      link.click();
    }
  </script>

</body>
</html>
