<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_patient_or_aidant();
$state = get_patient_state();
if (!$state) {
    header('Location: scan.php');
    exit;
}
$user = get_app_user();
$nextEvent = next_patient_event($state);
require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <span class="eyebrow"><?= h($user['space_label']) ?></span>
                            <h1 class="h2 mb-1">Bonjour <?= h($user['first_name']) ?></h1>
                            <p class="text-secondary mb-0">Voici les prochaines étapes du suivi à partir du <?= h(format_fr_date($state['reference_date'])) ?>.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="scan.php" class="btn btn-outline-secondary rounded-pill px-4">Ajouter ou remplacer</a>
                            <a href="scan.php?reset=1" class="btn btn-light rounded-pill px-4">Effacer</a>
                        </div>
                    </div>
                    <div class="timeline-list">
                        <?php foreach ($state['schedule'] as $event): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-card">
                                    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start mb-2">
                                        <div>
                                            <div class="small text-uppercase text-muted"><?= h($event['type']) ?></div>
                                            <h2 class="h5 mb-0"><?= h($event['title']) ?></h2>
                                        </div>
                                        <span class="badge rounded-pill <?= h(status_badge_class($event['status'])) ?>"><?= h(status_label($event['status'])) ?></span>
                                    </div>
                                    <div class="event-date mb-2"><i class="bi bi-calendar-event me-2"></i><?= h(format_fr_date($event['scheduled_at'])) ?></div>
                                    <div class="small text-muted mb-2">Période recommandée : <?= h($event['window']) ?></div>
                                    <div class="small"><?= h($event['instructions']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">En bref</h2>
                    <div class="vstack gap-3">
                        <div class="soft-panel">
                            <div class="small text-uppercase text-muted">Prochaine étape</div>
                            <div class="fw-semibold"><?= h($nextEvent['title'] ?? '—') ?></div>
                        </div>
                        <div class="soft-panel">
                            <div class="small text-uppercase text-muted">Date</div>
                            <div class="fw-semibold"><?= h(isset($nextEvent['scheduled_at']) ? format_fr_date($nextEvent['scheduled_at']) : '—') ?></div>
                        </div>
                        <div class="soft-panel">
                            <div class="small text-uppercase text-muted">Nombre d’étapes</div>
                            <div class="fw-semibold"><?= count($state['schedule']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">À retenir</h2>
                    <p class="mb-0 text-secondary">Ce calendrier aide à garder des repères. Si une date ne convient pas, contactez l’équipe avant toute modification.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
