Prélevia - Prototype V1.1 rôles
================================

Application web PHP / Bootstrap de démonstration.

Cette version gère 3 profils :
- PATIENT : peut ajouter et consulter un calendrier
- AIDANT : peut ajouter et consulter un calendrier
- CENTRE : peut générer un calendrier et afficher un QR code flashable

Lancement rapide :
1. Ouvrir un terminal dans le dossier de l'application
2. Exécuter : php -S localhost:8000
3. Aller sur : http://localhost:8000/login.php

Comptes de démonstration :
- Patient : claire@example.test / Bienvenue2026!
- Aidant : marc@example.test / Bonjour2026!
- Centre : centre@example.test / Centre2026!

Parcours de test :
- se connecter en centre et générer un QR code
- copier le contenu généré
- se connecter en patient ou aidant
- ouvrir l’ajout de calendrier
- coller le document reçu
- consulter le calendrier

Important :
- cette V1 stocke les données en session, sans base de données
- le fichier sql/schema.sql propose une base pour une future version MySQL
- les écrans patient et aidant évitent les intitulés techniques


Note QR code: cette version affiche le QR code via un service HTTPS externe pour éviter les erreurs locales liées à Python/shell_exec sous Windows.
