<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_patient_or_aidant();

$message = null;
$messageType = 'success';

if (isset($_GET['reset'])) {
    clear_patient_state();
    header('Location: scan.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $token = trim($_POST['qr_payload'] ?? '');
        if ($token === '') {
            throw new RuntimeException('Scannez le QR code remis par le centre ou ajoutez le code reçu pour afficher votre calendrier.');
        }

        $payload = decode_qr_token($token);
        $protocolCode = (string) ($payload['protocol'] ?? '');
        $referenceDate = (string) ($payload['reference_date'] ?? '');
        $arm = isset($payload['arm']) ? (string) $payload['arm'] : null;
        $templates = protocol_templates();

        $schedule = generate_schedule($protocolCode, $referenceDate, $arm);

        save_patient_state([
            'protocol_code' => $protocolCode,
            'protocol_label' => $templates[$protocolCode]['label'] ?? $protocolCode,
            'reference_date' => $referenceDate,
            'arm' => $arm,
            'schedule' => $schedule,
            'source_payload' => $token,
        ]);

        header('Location: timeline.php?added=1');
        exit;
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

$state = get_patient_state();
$user = get_app_user();
require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-lg-5">
                    <span class="eyebrow"><?= h($user['space_label']) ?></span>
                    <h1 class="h2 mb-3">Ajouter mon calendrier</h1>
                    <p class="text-secondary">Vous pouvez scanner le QR code remis par le centre pour retrouver automatiquement vos prochaines étapes.</p>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= h($messageType) ?> rounded-4"><?= h($message) ?></div>
                    <?php endif; ?>

                    <div class="scanner-card mb-4">
                        <div id="reader" class="reader-box"></div>
                        <div id="scanner-status" class="small text-secondary mt-3">Ouvrez la caméra puis placez le QR code devant l’objectif.</div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="button" id="start-scan" class="btn btn-primary rounded-pill px-4">Ouvrir la caméra</button>
                            <button type="button" id="stop-scan" class="btn btn-outline-secondary rounded-pill px-4 d-none">Arrêter</button>
                        </div>
                    </div>

                    <form method="post" id="scan-form">
                        <input type="hidden" id="qr_payload" name="qr_payload" value="<?= h($state['source_payload'] ?? '') ?>">
                        <div class="soft-panel mb-3">
                            <div class="fw-semibold mb-1">Besoin d’une autre solution ?</div>
                            <div class="text-secondary small mb-3">Vous pouvez aussi coller le code reçu si le QR code n’est pas disponible.</div>
                            <textarea class="form-control rounded-4" id="qr_payload_fallback" rows="5" placeholder="Collez ici le code reçu par votre équipe"><?= h($state['source_payload'] ?? '') ?></textarea>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Afficher mon calendrier</button>
                            <?php if ($state): ?>
                                <a href="timeline.php" class="btn btn-outline-secondary rounded-pill px-4">Consulter mon calendrier</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">En quelques étapes</h2>
                    <div class="vstack gap-3">
                        <div class="soft-panel">
                            <div class="fw-semibold mb-1">1. Recevez votre QR code</div>
                            <div class="text-secondary small">Votre centre vous montre un QR code correspondant à votre calendrier.</div>
                        </div>
                        <div class="soft-panel">
                            <div class="fw-semibold mb-1">2. Scannez-le</div>
                            <div class="text-secondary small">L’application lit le QR code et prépare votre calendrier automatiquement.</div>
                        </div>
                        <div class="soft-panel">
                            <div class="fw-semibold mb-1">3. Retrouvez vos repères</div>
                            <div class="text-secondary small">Vos prochaines étapes s’affichent ensuite dans un format clair et facile à relire.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hiddenInput = document.getElementById('qr_payload');
    const fallbackInput = document.getElementById('qr_payload_fallback');
    const form = document.getElementById('scan-form');
    const status = document.getElementById('scanner-status');
    const startBtn = document.getElementById('start-scan');
    const stopBtn = document.getElementById('stop-scan');
    let scanner = null;
    let scannerRunning = false;

    fallbackInput.addEventListener('input', function () {
        hiddenInput.value = fallbackInput.value.trim();
    });

    function setStatus(text) {
        status.textContent = text;
    }

    async function stopScanner() {
        if (scanner && scannerRunning) {
            try {
                await scanner.stop();
                await scanner.clear();
            } catch (e) {}
        }
        scannerRunning = false;
        stopBtn.classList.add('d-none');
        startBtn.classList.remove('d-none');
    }

    startBtn.addEventListener('click', async function () {
        if (typeof Html5Qrcode === 'undefined') {
            setStatus('La lecture par caméra n’est pas disponible sur cet appareil.');
            return;
        }

        try {
            if (!scanner) {
                scanner = new Html5Qrcode('reader');
            }

            const cameras = await Html5Qrcode.getCameras();
            if (!cameras || !cameras.length) {
                setStatus('Aucune caméra n’a été trouvée.');
                return;
            }

            let cameraConfig = { facingMode: { exact: 'environment' } };
            const rearCamera = cameras.find(function (camera) {
                const label = (camera.label || '').toLowerCase();
                return label.includes('back') || label.includes('rear') || label.includes('environment') || label.includes('arrière');
            });

            if (rearCamera) {
                cameraConfig = { deviceId: { exact: rearCamera.id } };
            }

            try {
                await scanner.start(
                    cameraConfig,
                    { fps: 10, qrbox: { width: 240, height: 240 } },
                    function (decodedText) {
                        hiddenInput.value = decodedText;
                        fallbackInput.value = decodedText;
                        setStatus('QR code reconnu. Votre calendrier est en cours d’ouverture...');
                        stopScanner().then(function () {
                            form.submit();
                        });
                    },
                    function () {}
                );
            } catch (startError) {
                const fallbackCameraId = rearCamera ? rearCamera.id : cameras[0].id;
                await scanner.start(
                    fallbackCameraId,
                    { fps: 10, qrbox: { width: 240, height: 240 } },
                    function (decodedText) {
                        hiddenInput.value = decodedText;
                        fallbackInput.value = decodedText;
                        setStatus('QR code reconnu. Votre calendrier est en cours d’ouverture...');
                        stopScanner().then(function () {
                            form.submit();
                        });
                    },
                    function () {}
                );
            }
            scannerRunning = true;
            setStatus('Caméra active. Placez le QR code bien en face.');
            startBtn.classList.add('d-none');
            stopBtn.classList.remove('d-none');
        } catch (error) {
            setStatus('Impossible d’ouvrir la caméra. Vérifiez l’autorisation accordée au navigateur.');
        }
    });

    stopBtn.addEventListener('click', function () {
        stopScanner();
        setStatus('La caméra est arrêtée. Vous pouvez la relancer quand vous voulez.');
    });
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
