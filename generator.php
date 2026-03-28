<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_centre_user();

$templates = protocol_templates();
$payloadPreview = null;
$generatedToken = null;
$error = null;
$generatedSchedule = [];
$user = get_app_user();

$defaults = [
    'protocol' => 'ANC-001',
    'reference_date' => date('Y-m-d'),
    'arm' => 'A',
];

$form = array_merge($defaults, $_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $payloadPreview = [
            'protocol' => (string) ($form['protocol'] ?? ''),
            'reference_date' => (string) ($form['reference_date'] ?? ''),
            'arm' => trim((string) ($form['arm'] ?? '')) ?: null,
            'generated_at' => date('c'),
        ];

        $generatedSchedule = generate_schedule($payloadPreview['protocol'], $payloadPreview['reference_date'], $payloadPreview['arm']);
        $generatedToken = create_qr_token($payloadPreview);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-lg-5">
                    <span class="eyebrow"><?= h($user['space_label']) ?></span>
                    <h1 class="h2 mb-3">Générer un calendrier à partager</h1>
                    <p class="text-secondary">Le centre prépare ici le calendrier puis affiche un QR code que le patient peut flasher dans l’application.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-4"><?= h($error) ?></div>
                    <?php endif; ?>

                    <form method="post" class="row g-3 mt-1">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="protocol">Protocole</label>
                            <select class="form-select form-select-lg rounded-4" id="protocol" name="protocol">
                                <?php foreach ($templates as $code => $template): ?>
                                    <option value="<?= h($code) ?>" <?= $form['protocol'] === $code ? 'selected' : '' ?>><?= h($code) ?> · <?= h($template['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="reference_date">Date de référence</label>
                            <input class="form-control form-control-lg rounded-4" type="date" id="reference_date" name="reference_date" value="<?= h((string) $form['reference_date']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="arm">Variante / bras</label>
                            <input class="form-control form-control-lg rounded-4" type="text" id="arm" name="arm" value="<?= h((string) $form['arm']) ?>" placeholder="A">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary rounded-pill px-4" type="submit">Générer le QR code</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="h4 mb-3">Résultat</h2>
                    <?php if ($generatedToken): ?>
                        <div class="text-center mb-4">
                            <div class="qr-box qr-box-large d-inline-flex align-items-center justify-content-center">
                                <img src="https://quickchart.io/qr?size=520&text=<?= rawurlencode($generatedToken) ?>" alt="QR code du calendrier" class="img-fluid" style="max-width: 360px; width: 100%; height: auto;">
                            </div>
                            <p class="text-secondary mt-3 mb-0">Le patient ou l’aidant peut scanner ce QR code pour retrouver son calendrier dans l’application.</p>
                        </div>

                        <hr class="my-4">

                        <h3 class="h5 mb-3">Calendrier généré</h3>
                        <div class="timeline-list">
                            <?php foreach ($generatedSchedule as $event): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-card">
                                        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start mb-1">
                                            <h4 class="h6 mb-0"><?= h($event['title']) ?></h4>
                                            <span class="badge rounded-pill <?= h(status_badge_class($event['status'])) ?>"><?= h(status_label($event['status'])) ?></span>
                                        </div>
                                        <div class="text-secondary small mb-2"><?= h(format_fr_date($event['scheduled_at'])) ?> · <?= h($event['type']) ?> · <?= h($event['window']) ?></div>
                                        <div class="small"><?= h($event['instructions']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-qr-code"></i>
                            <p class="mb-0">Le QR code apparaîtra ici après génération.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
