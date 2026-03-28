<?php
$state = get_patient_state();
$user = get_app_user();
$roleClass = '';
if ($user) {
    $roleClass = match ($user['role'] ?? '') {
        'PATIENT' => 'role-patient',
        'AIDANT' => 'role-aidant',
        'CENTRE' => 'role-centre',
        default => '',
    };
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prélevia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="<?= h($roleClass) ?>">
<nav class="navbar navbar-expand-lg navbar-light sticky-top app-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <span class="brand-bubble" aria-hidden="true">
                <svg class="brand-mark" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 10C18 8.89543 18.8954 8 20 8H27.5C27.7761 8 28 8.22386 28 8.5V55.5C28 55.7761 27.7761 56 27.5 56H20C18.8954 56 18 55.1046 18 54V10Z" fill="var(--app-primary)"/>
                    <path d="M28 12.5C28 10.0147 30.0147 8 32.5 8H39.5C48.0604 8 55 14.9396 55 23.5C55 32.0604 48.0604 39 39.5 39H32.5C30.0147 39 28 36.9853 28 34.5V12.5Z" fill="var(--app-primary-deep)" fill-opacity="0.78"/>
                    <path d="M28 30.5C28 28.0147 30.0147 26 32.5 26H41C45.9706 26 50 30.0294 50 35C50 39.9706 45.9706 44 41 44H32.5C30.0147 44 28 41.9853 28 39.5V30.5Z" fill="var(--app-primary-soft)"/>
                </svg>
            </span>
            <span>
                <strong>Prélevia</strong><br>
                <small class="text-muted">Mon suivi de prélèvements</small>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-lg-2 align-items-lg-center">
                <?php if ($user && is_patient_or_aidant()): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php">Mon espace</a></li>
                    <li class="nav-item"><a class="nav-link" href="scan.php">Ajouter un calendrier</a></li>
                    <?php if ($state): ?>
                        <li class="nav-item"><a class="nav-link" href="timeline.php">Consulter le calendrier</a></li>
                    <?php endif; ?>
                <?php elseif ($user && is_centre_user()): ?>
                    <li class="nav-item"><a class="nav-link" href="generator.php">Générer un calendrier</a></li>
                <?php endif; ?>

                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Se déconnecter</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Se connecter</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="py-4">
