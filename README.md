# PENDAFITNESS - ECF Développeur Web et Web Mobile

Projet de **PENDA ESSAMA LEOPOLD EMMANUEL**. Application web de réservation d'activités sportives réalisée pour démontrer les compétences front-end et back-end du titre DWWM.

## Fonctionnalités
- Catalogue illustré, recherche et filtres (sport, niveau, prix, lieu)
- Inscription, connexion, profil, sessions PHP et rôles
- Réservation/annulation de séances avec contrôle de capacité
- Favoris et avis après participation, avec modération
- Formulaire de contact et traitement par le coach
- Dashboard coach : CRUD activités, séances, réservations, avis, contacts
- Dashboard admin : statistiques Chart.js et gestion des comptes coach
- Responsive Bootstrap 5, JavaScript, œil mot de passe
- Sécurité : password_hash, PDO préparé, CSRF, contrôle d'accès, échappement HTML

## Stack
PHP 8 / MVC / PDO / TiDB Cloud (SQL) / Render Key Value Redis-compatible (NoSQL) / HTML5 / CSS3 / Bootstrap 5 / JavaScript / Fetch API / Chart.js / Git / GitHub / Render.

## Installation locale
1. Importer `database/schema.sql` dans MySQL.
2. Copier `.env.example` selon votre environnement (ou définir les variables système).
3. Depuis la racine : `php -S localhost:8000 -t public`
4. Créer les comptes de démonstration : `php database/create_demo_users.php`
5. Ouvrir `http://localhost:8000`.

## Comptes de démonstration
- membre : `membre@urbanfit.fr` / `URBAN2026`
- coach : `coach@urbanfit.fr` / `URBAN2026`
- administrateur : `admin@urbanfit.fr` / `STUDI2026`

## Déploiement Render + TiDB Cloud
1. Utiliser le dépôt GitHub `ledapen/PENDAFITNESS` et pousser ce dossier.
2. Importer `database/schema.sql` dans TiDB Cloud puis exécuter `database/create_demo_users.php` avec les variables TiDB.
3. Créer un Web Service Render depuis GitHub. Le `Dockerfile` est fourni.
4. Ajouter : `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_SSL=true`.
5. Déployer et tester les trois rôles.

## Dossier ECF
Le dossier `docs/` contient la conception, les user stories, MCD/MLD, UML (Mermaid), plan de tests, sécurité, déploiement et checklist de soutenance.
