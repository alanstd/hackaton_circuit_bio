<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    header('Location: ' . (is_centre_user() ? 'generator.php' : 'index.php'));
    exit;
}

$error = null;
$form = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['email'] = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $user = authenticate_user($form['email'], $password);
    if ($user === null) {
        $error = 'Les informations de connexion ne correspondent pas.';
    } else {
        login_user($user);
        header('Location: ' . ($user['role'] === 'CENTRE' ? 'generator.php' : 'index.php'));
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-xl-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-lg-5">
                    <span class="eyebrow">Connexion</span>
                    <h1 class="h2 mb-3">Accéder à Prélevia</h1>
                    <p class="text-secondary mb-4">Connectez-vous pour retrouver votre espace ou accéder à l’espace centre.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-4"><?= h($error) ?></div>
                    <?php endif; ?>

                    <form method="post" class="mt-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="email">Adresse e-mail</label>
                            <input class="form-control form-control-lg rounded-4" type="email" id="email" name="email" value="<?= h($form['email']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="password">Mot de passe</label>
                            <input class="form-control form-control-lg rounded-4" type="password" id="password" name="password" required>
                        </div>
                        <button class="btn btn-primary rounded-pill px-4" type="submit">Se connecter</button>
                    </form>

                    <hr class="my-4">

                    <div class="small text-muted">Comptes de démonstration :</div>
                    <div class="small mt-2">
                        <div><strong>Patient</strong> · claire@example.test / Bienvenue2026!</div>
                        <div><strong>Aidant</strong> · marc@example.test / Bonjour2026!</div>
                        <div><strong>Centre</strong> · centre@example.test / Centre2026!</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
