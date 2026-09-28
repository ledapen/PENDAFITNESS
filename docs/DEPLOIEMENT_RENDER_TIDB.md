# Déploiement Render + TiDB Cloud
1. Créer une base TiDB Cloud et importer `database/schema.sql`.
2. Créer les comptes de démonstration avec `database/create_demo_users.php` depuis un environnement ayant les variables TiDB.
3. Créer le dépôt GitHub `PENDAFITNESS`, puis `git add .`, `git commit -m "feat: complete PENDAFITNESS ECF"`, `git push`.
4. Sur Render : New > Web Service > dépôt GitHub. Le Dockerfile lance Apache/PHP avec `public/` comme DocumentRoot.
5. Variables : `DB_HOST`, `DB_PORT=4000` (ou valeur TiDB), `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_SSL=true`.
6. Déployer. Contrôler les logs puis tester accueil, filtres, inscription, réservation, coach et admin.
7. Ne jamais versionner les secrets. `.env` reste ignoré par Git.
