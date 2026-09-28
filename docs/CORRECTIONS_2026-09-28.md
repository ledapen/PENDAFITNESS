# Corrections PENDAFITNESS — 28/09/2026

- Harmonisation de la marque PENDAFITNESS dans les sources et la documentation texte.
- Vérification TLS du certificat TiDB activée dans la configuration PDO.
- Correction du retour d'annulation : le message de succès n'est plus affiché si l'annulation échoue.
- Ajout d'un échange asynchrone réel : `GET /api/activite/{id}/seances` + Fetch API pour actualiser les places sans recharger la page.
- Amélioration des contrôles navigateur et de l'accessibilité des boutons d'affichage du mot de passe.
- Renommage de la variable Chart.js en `pendafitnessChart`.
- Ajout d'un composant NoSQL `NoSqlStats` utilisant un Render Key Value Redis-compatible via `REDIS_URL`. Le stockage est non bloquant et comptabilise les consultations d'activités par jour.
- Dockerfile complété avec l'extension PHP Redis.
- MCD détaillé, diagramme de cas d'utilisation et diagramme de séquence de réservation ajoutés dans `docs/figures_final/`.

## Vérifications locales
Tous les fichiers PHP passent `php -l` sans erreur de syntaxe.

## Déploiement
Le service Render PENDAFITNESS est relié à la branche `main` et utilise l'auto-déploiement. Le connecteur GitHub de cette session est toutefois en lecture seule côté installation GitHub (403 sur écriture). Le ZIP corrigé est donc prêt à être poussé manuellement si cette autorisation n'est pas rétablie.
