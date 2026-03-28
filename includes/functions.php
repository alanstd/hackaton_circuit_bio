<?php

declare(strict_types=1);

const APP_SECRET = 'change-me-in-production';

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    return $path;
}

function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string|false
{
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }

    return base64_decode(strtr($data, '-_', '+/'));
}

function sign_payload(string $payload): string
{
    return hash_hmac('sha256', $payload, APP_SECRET);
}

function create_qr_token(array $payload): string
{
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $encoded = base64url_encode($json ?: '{}');
    $signature = sign_payload($encoded);

    return $encoded . '.' . $signature;
}

function decode_qr_token(string $token): array
{
    $parts = explode('.', trim($token), 2);
    if (count($parts) !== 2) {
        throw new RuntimeException('Le document reçu ne semble pas valide.');
    }

    [$encoded, $signature] = $parts;

    if (!hash_equals(sign_payload($encoded), $signature)) {
        throw new RuntimeException('Le document reçu ne semble pas valide.');
    }

    $decoded = base64url_decode($encoded);
    if ($decoded === false) {
        throw new RuntimeException('Nous n’avons pas pu lire ce document.');
    }

    $payload = json_decode($decoded, true);
    if (!is_array($payload)) {
        throw new RuntimeException('Nous n’avons pas pu lire ce document.');
    }

    return $payload;
}

function demo_users(): array
{
    return [
        'claire@example.test' => [
            'first_name' => 'Claire',
            'email' => 'claire@example.test',
            'password' => 'Bienvenue2026!',
            'role' => 'PATIENT',
            'space_label' => 'Mon espace patient',
        ],
        'marc@example.test' => [
            'first_name' => 'Marc',
            'email' => 'marc@example.test',
            'password' => 'Bonjour2026!',
            'role' => 'AIDANT',
            'space_label' => 'Mon espace aidant',
        ],
        'centre@example.test' => [
            'first_name' => 'Équipe centre',
            'email' => 'centre@example.test',
            'password' => 'Centre2026!',
            'role' => 'CENTRE',
            'space_label' => 'Espace centre',
        ],
    ];
}

function authenticate_user(string $email, string $password): ?array
{
    $email = mb_strtolower(trim($email));
    $users = demo_users();

    if (!isset($users[$email])) {
        return null;
    }

    $user = $users[$email];
    if (!hash_equals($user['password'], $password)) {
        return null;
    }

    return [
        'first_name' => $user['first_name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'space_label' => $user['space_label'],
    ];
}

function login_user(array $user): void
{
    $_SESSION['app_user'] = $user;
}

function logout_user(): void
{
    unset($_SESSION['app_user']);
}

function get_app_user(): ?array
{
    return $_SESSION['app_user'] ?? null;
}

function is_logged_in(): bool
{
    return get_app_user() !== null;
}

function current_user_role(): ?string
{
    return get_app_user()['role'] ?? null;
}

function is_patient_or_aidant(): bool
{
    return in_array((string) current_user_role(), ['PATIENT', 'AIDANT'], true);
}

function is_centre_user(): bool
{
    return current_user_role() === 'CENTRE';
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_patient_or_aidant(): void
{
    require_login();
    if (!is_patient_or_aidant()) {
        header('Location: generator.php');
        exit;
    }
}

function require_centre_user(): void
{
    require_login();
    if (!is_centre_user()) {
        header('Location: index.php');
        exit;
    }
}

function protocol_templates(): array
{
    return [
        'ANC-001' => [
            'label' => 'Suivi avec prises de sang et recueil à domicile',
            'events' => [
                ['offset_days' => 0, 'title' => 'Premier prélèvement', 'type' => 'Prise de sang', 'window' => 'Le jour de départ', 'instructions' => 'Présentez-vous au lieu habituel avec les documents remis par votre équipe soignante.'],
                ['offset_days' => 14, 'title' => 'Contrôle de suivi', 'type' => 'Prise de sang', 'window' => 'Autour de cette date', 'instructions' => 'Pensez à bien vous hydrater avant votre venue.'],
                ['offset_days' => 28, 'title' => 'Recueil à domicile', 'type' => 'Recueil', 'window' => 'Dans les jours autour de cette date', 'instructions' => 'Utilisez le kit remis par votre centre et rapportez-le selon les consignes reçues.'],
                ['offset_days' => 56, 'title' => 'Bilan intermédiaire', 'type' => 'Prise de sang', 'window' => 'Autour de cette date', 'instructions' => 'En cas d’empêchement, contactez votre équipe avant la date prévue.'],
                ['offset_days' => 84, 'title' => 'Point à 3 mois', 'type' => 'Prise de sang', 'window' => 'Autour de cette date', 'instructions' => 'Si besoin, vous pouvez demander un rendez-vous à votre centre.'],
            ],
        ],
        'ANC-002' => [
            'label' => 'Suivi avec prélèvements complémentaires',
            'events' => [
                ['offset_days' => 0, 'title' => 'Premier temps de suivi', 'type' => 'Prélèvement', 'window' => 'Le jour de départ', 'instructions' => 'Votre équipe s’occupe de l’organisation de cette première étape.'],
                ['offset_days' => 21, 'title' => 'Prise de sang associée', 'type' => 'Prise de sang', 'window' => 'Autour de cette date', 'instructions' => 'Aucune préparation particulière n’est nécessaire.'],
                ['offset_days' => 63, 'title' => 'Contrôle de suivi', 'type' => 'Prise de sang', 'window' => 'Autour de cette date', 'instructions' => 'Pensez à confirmer votre disponibilité auprès du centre.'],
            ],
        ],
    ];
}

function generate_schedule(string $protocolCode, string $referenceDate, ?string $arm = null): array
{
    $templates = protocol_templates();

    if (!isset($templates[$protocolCode])) {
        throw new RuntimeException('Le calendrier reçu n’est pas reconnu.');
    }

    $baseDate = DateTimeImmutable::createFromFormat('Y-m-d', $referenceDate);
    if (!$baseDate) {
        throw new RuntimeException('La date de départ du calendrier est invalide.');
    }

    $schedule = [];
    foreach ($templates[$protocolCode]['events'] as $index => $event) {
        $scheduledAt = $baseDate->modify('+' . (int) $event['offset_days'] . ' day');
        $schedule[] = [
            'id' => 'EVT-' . ($index + 1),
            'title' => $event['title'],
            'type' => $event['type'],
            'window' => $event['window'],
            'instructions' => $event['instructions'],
            'scheduled_at' => $scheduledAt->format('Y-m-d'),
            'status' => event_status($scheduledAt),
        ];
    }

    return $schedule;
}

function event_status(DateTimeImmutable $scheduledAt): string
{
    $today = new DateTimeImmutable('today');
    $diff = (int) $today->diff($scheduledAt)->format('%r%a');

    if ($diff < 0) {
        return 'passed';
    }
    if ($diff <= 7) {
        return 'soon';
    }

    return 'upcoming';
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'passed' => 'bg-soft-success text-success-emphasis',
        'soon' => 'bg-soft-warning text-warning-emphasis',
        default => 'bg-soft-primary text-primary-emphasis',
    };
}

function status_label(string $status): string
{
    return match ($status) {
        'passed' => 'À vérifier avec mon équipe',
        'soon' => 'Bientôt',
        default => 'À venir',
    };
}

function format_fr_date(string $date): string
{
    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    return $dt ? $dt->format('d/m/Y') : $date;
}

function save_patient_state(array $data): void
{
    $_SESSION['patient_app'] = $data;
}

function get_patient_state(): ?array
{
    return $_SESSION['patient_app'] ?? null;
}

function clear_patient_state(): void
{
    unset($_SESSION['patient_app']);
}

function next_patient_event(?array $state): ?array
{
    if (!$state || empty($state['schedule'])) {
        return null;
    }

    foreach ($state['schedule'] as $event) {
        if (($event['status'] ?? '') !== 'passed') {
            return $event;
        }
    }

    return $state['schedule'][0] ?? null;
}
