<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Leitor de Código de Barras</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
  <style>
    .scanner-container {
      position: relative;
      width: 100%;
      max-width: 500px;
      margin: 0 auto;
      overflow: hidden;
      border-radius: 0.5rem;
    }
    .scanner-overlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0));
      pointer-events: none;
    }
    .scanner-line {
      position: absolute;
      top: 50%;
      width: 100%;
      height: 3px;
      background: red;
      animation: scan 2s infinite linear;
    }
    @keyframes scan {
      0% { transform: translateY(-100px); }
      100% { transform: translateY(100px); }
    }
  </style>
</head>
<body class="bg-gray-100">

  <div class="container mx-auto py-8">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg overflow-hidden">
      <div class="bg-blue-600 text-white px-6 py-4 text-center">
        <h1 class="text-xl font-bold">Leitor de Código de Barras</h1>
        <p class="text-sm">Aponte a câmera para o código</p>
      </div>

      <div class="p-6">
        <div class="scanner-container mb-4">
          <div id="interactive" class="viewport"></div>
          <div class="scanner-overlay">
            <div class="scanner-line"></div>
          </div>
        </div>

        <div class="flex justify-between mb-4">
          <button id="startBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Iniciar</button>
          <button id="stopBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded" disabled>Parar</button>
        </div>

        <div id="resultArea" class="hidden bg-gray-100 p-4 rounded text-center">
          <p class="text-gray-700">Código Detectado:</p>
          <h2 id="barcodeResult" class="text-xl font-mono font-semibold mt-2">-</h2>
          <svg id="barcode" class="mx-auto mt-4"></svg>
        </div>
      </div>
    </div>
  </div>

  <script>
    const startBtn = document.getElementById("startBtn");
    const stopBtn = document.getElementById("stopBtn");
    const resultArea = document.getElementById("resultArea");
    const barcodeResult = document.getElementById("barcode");

    let lastResult = null;

    function startScanner() {
      Quagga.init({
        inputStream: {
          type: "LiveStream",
          target: document.querySelector("#interactive"),
          constraints: {
            facingMode: "environment" // Use câmera traseira
          }
        },
        decoder: {
          readers: [
            "code_128_reader", 
            "ean_reader", 
            "ean_8_reader", 
            "upc_reader", 
            "code_39_reader"
          ]
        },
        locate: true
      }, function (err) {
        if (err) {
          console.error("Erro ao iniciar Quagga:", err);
          alert("Erro ao iniciar scanner.");
          return;
        }
        Quagga.start();
      });

      Quagga.onDetected(data => {
        const code = data.codeResult.code;

        if (code !== lastResult) {
          lastResult = code;
          showResult(code);
        }
      });
    }

    function stopScanner() {
      Quagga.stop();
      lastResult = null;
    }

    function showResult(code) {
      document.getElementById("resultArea").classList.remove("hidden");
      document.getElementById("barcodeResult").textContent = code;

      JsBarcode("#barcode", code, {
        format: "CODE128",
        lineColor: "#000",
        width: 2,
        height: 40,
        displayValue: false
      });

      const audio = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-arcade-game-jump-coin-216.mp3");
      audio.play();
    }

    startBtn.addEventListener("click", () => {
      startBtn.disabled = true;
      stopBtn.disabled = false;
      startScanner();
    });

    stopBtn.addEventListener("click", () => {
      startBtn.disabled = false;
      stopBtn.disabled = true;
      stopScanner();
    });
  </script>
</body>
</html>
