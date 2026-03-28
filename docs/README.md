# Prélevia · Prototype V1

Prototype d'application web PHP/Bootstrap pour le suivi patient de prélèvements d'études ancillaires.

## Idée fonctionnelle

- Le centre investigateur génère un QR code.
- Le QR contient uniquement une logique minimale : protocole, date de référence, variante éventuelle.
- Le patient scanne ou importe ce QR dans l'application.
- L'application reconstruit un calendrier daté et l'affiche dans une interface douce de style grand public.

## Ce que contient ce prototype

- `index.php` : page d'accueil
- `generator.php` : génération de QR côté centre
- `scan.php` : import côté patient
- `timeline.php` : affichage du calendrier patient
- `includes/` : fonctions PHP utilitaires
- `assets/` : style et JS
- `sql/schema.sql` : schéma minimal pour une future version MySQL

## Lancer localement

### Option 1 — serveur PHP intégré

```bash
php -S localhost:8000
```

Puis ouvrir :

```text
http://localhost:8000
```

### Option 2 — Apache / XAMPP / WAMP

Copier le dossier dans le répertoire web et ouvrir `index.php`.

## Notes importantes

- La V1 fonctionne sans base de données.
- Les données importées sont stockées en session PHP pour la démonstration.
- Le QR est signé avec une clé applicative statique définie dans `includes/functions.php`.
- Changer `APP_SECRET` avant tout usage réel.
- Le scan caméra natif n'est pas intégré dans cette V1 ; le flux est simulé par collage du contenu du QR.

## Pistes de V2

- lecture caméra réelle dans le navigateur
- persistance MySQL
- gestion des centres et des protocoles en base
- audit trail
- notifications patient
- export PDF / fiche récapitulative
