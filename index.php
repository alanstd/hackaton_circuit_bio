<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_patient_or_aidant();

$state = get_patient_state();
$user = get_app_user();
$nextEvent = next_patient_event($state);
require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="hero-card p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="eyebrow"><?= h($user['space_label']) ?></span>
                <h1 class="display-6 fw-bold mt-2 mb-3">Bonjour <?= h($user['first_name']) ?></h1>
                <p class="lead text-secondary mb-4">Retrouvez les prochaines étapes du suivi, ajoutez un nouveau calendrier reçu lors de votre prise en charge et gardez vos repères en un coup d’œil.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="scan.php" class="btn btn-primary btn-lg rounded-pill px-4"><i class="bi bi-qr-code-scan me-2"></i>Ajouter un calendrier</a>
                    <?php if ($state): ?>
                        <a href="timeline.php" class="btn btn-light btn-lg rounded-pill px-4">Consulter le calendrier</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="soft-panel h-100 d-flex flex-column justify-content-center p-4">
                    <?php if ($nextEvent): ?>
                        <div class="small text-uppercase text-muted mb-2">Prochaine étape</div>
                        <h2 class="h4 mb-1"><?= h($nextEvent['title']) ?></h2>
                        <div class="text-secondary mb-2"><?= h(format_fr_date($nextEvent['scheduled_at'])) ?></div>
                        <div class="small text-muted"><?= h($nextEvent['instructions']) ?></div>
                    <?php else: ?>
                        <div class="small text-uppercase text-muted mb-2">Votre espace</div>
                        <h2 class="h4 mb-1">Aucun calendrier ajouté pour le moment</h2>
                        <div class="text-secondary">Ajoutez le document remis par votre équipe pour afficher vos prochaines dates.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="info-card h-100">
                <div class="icon-badge"><i class="bi bi-stars"></i></div>
                <h3>Simple à consulter</h3>
                <p>Les étapes proches sont mises en avant pour aider à garder l’essentiel en tête.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card h-100">
                <div class="icon-badge"><i class="bi bi-bell"></i></div>
                <h3>Repères visuels</h3>
                <p>Des badges et des cartes indiquent rapidement ce qui arrive bientôt et ce qui a déjà eu lieu.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card h-100">
                <div class="icon-badge"><i class="bi bi-telephone"></i></div>
                <h3>Besoin d’aide ?</h3>
                <p>En cas de doute sur une date ou une consigne, contactez votre équipe habituelle.</p>
            </div>
        </div>
    </div>

    <?php if ($state): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <div class="text-muted small">Calendrier actuel</div>
                        <h2 class="h4 mb-0">Départ du suivi le <?= h(format_fr_date($state['reference_date'])) ?></h2>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="timeline.php" class="btn btn-primary rounded-pill px-4">Ouvrir</a>
                        <a href="scan.php?reset=1" class="btn btn-outline-secondary rounded-pill px-4">Remplacer</a>
                    </div>
                </div>
                <div class="row g-3">
                    <?php foreach (array_slice($state['schedule'], 0, 3) as $event): ?>
                        <div class="col-md-4">
                            <div class="schedule-mini-card h-100">
                                <span class="badge rounded-pill <?= h(status_badge_class($event['status'])) ?> mb-3"><?= h(status_label($event['status'])) ?></span>
                                <h3 class="h5 mb-1"><?= h($event['title']) ?></h3>
                                <div class="text-secondary mb-2"><?= h(format_fr_date($event['scheduled_at'])) ?></div>
                                <div class="small text-muted"><?= h($event['window']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
