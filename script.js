const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const resultSpan = document.getElementById('result');
const startScanBtn = document.getElementById('startScan');
const stopScanBtn = document.getElementById('stopScan');
const ctx = canvas.getContext('2d');

let scanning = false;
let videoStream = null;

// Função para iniciar a leitura
async function startScanning() {
    try {
        // Solicita acesso à câmera traseira, se disponível
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: { exact: "environment" } // Tenta usar a câmera traseira
            }
        });
        video.srcObject = videoStream;
        video.setAttribute('playsinline', true); // Necessário para iOS
        await video.play();
        scanning = true;
        requestAnimationFrame(tick); // Inicia o loop de detecção
        startScanBtn.style.display = 'none';
        stopScanBtn.style.display = 'inline-block';
        resultSpan.textContent = 'Procurando...';
    } catch (err) {
        console.error('Erro ao acessar a câmera: ', err);
        resultSpan.textContent = 'Erro ao acessar a câmera. Verifique as permissões.';
        alert('Não foi possível acessar a câmera. Verifique se o navegador tem permissão.');
    }
}

// Função para parar a leitura
function stopScanning() {
    scanning = false;
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
    }
    video.srcObject = null;
    ctx.clearRect(0, 0, canvas.width, canvas.height); // Limpa o canvas
    resultSpan.textContent = 'Nenhum';
    startScanBtn.style.display = 'inline-block';
    stopScanBtn.style.display = 'none';
}

// Loop principal de detecção
function tick() {
    if (!scanning) return;

    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.height = video.videoHeight;
        canvas.width = video.videoWidth;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

        // Tenta decodificar o código de barras/QR
        // O jsQR é mais otimizado para QR Codes, mas pode decodificar alguns códigos 1D (barras)
        const code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: "dontInvert",
        });

        if (code) {
            resultSpan.textContent = code.data;
            // Opcional: desenhar um quadrado em volta do código detectado
            drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
            drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
            drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
            drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
            // Se você quiser parar a leitura após o primeiro código
            // stopScanning();
        } else {
            resultSpan.textContent = 'Procurando...';
        }
    }
    requestAnimationFrame(tick);
}

// Função auxiliar para desenhar linhas (visualização do código detectado)
function drawLine(begin, end, color) {
    ctx.beginPath();
    ctx.moveTo(begin.x, begin.y);
    ctx.lineTo(end.x, end.y);
    ctx.lineWidth = 4;
    ctx.strokeStyle = color;
    ctx.stroke();
}

// Event Listeners para os botões
startScanBtn.addEventListener('click', startScanning);
stopScanBtn.addEventListener('click', stopScanning);

// Opcional: Iniciar automaticamente ao carregar a página (remova ou comente se quiser iniciar via botão)
// window.onload = startScanning;