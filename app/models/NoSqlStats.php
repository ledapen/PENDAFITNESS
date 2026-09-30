<?php

class NoSqlStats
{
    /**
     * Crée et retourne le client Redis.
     * Retourne null si Redis n'est pas disponible.
     */
    private static function client(): ?Redis
    {
        // Vérifie que l'extension PHP Redis est disponible.
        if (!class_exists('Redis')) {
            return null;
        }

        // Récupère l'URL Redis depuis les variables d'environnement.
        $redisUrl = envv('REDIS_URL', '');

        if ($redisUrl === '') {
            return null;
        }

        // Analyse l'URL de connexion.
        $parts = parse_url($redisUrl);

        if (!$parts || empty($parts['host'])) {
            error_log('Redis unavailable: URL de connexion invalide.');
            return null;
        }

        try {
            $redis = new Redis();

            $scheme = $parts['scheme'] ?? 'redis';
            $port = (int) ($parts['port'] ?? 6379);

            // Prise en charge des connexions Redis sécurisées TLS.
            $host = $scheme === 'rediss'
                ? 'tls://' . $parts['host']
                : $parts['host'];

            // Connexion avec un timeout de 2 secondes.
            $redis->connect(
                $host,
                $port,
                2.0
            );

            // Authentification si elle est requise.
            if (isset($parts['pass'])) {
                $user = $parts['user'] ?? null;

                if ($user) {
                    $redis->auth([
                        $user,
                        $parts['pass']
                    ]);
                } else {
                    $redis->auth($parts['pass']);
                }
            }

            return $redis;

        } catch (Throwable $e) {
            error_log(
                'Redis unavailable: ' . $e->getMessage()
            );

            return null;
        }
    }

    /**
     * Enregistre la consultation d'une activité dans Redis.
     */
    public static function recordActivityView(int $activityId): bool
    {
        $redis = self::client();

        if (!$redis) {
            return false;
        }

        try {
            $day = date('Y-m-d');

            /*
             * Compteur journalier de consultations.
             * Exemple :
             * pendafitness:activity:4:views:2026-09-30
             */
            $key = "pendafitness:activity:{$activityId}:views:{$day}";

            $redis->incr($key);

            // Conservation du compteur pendant 31 jours.
            $redis->expire(
                $key,
                60 * 60 * 24 * 31
            );

            /*
             * Stocke également la dernière consultation
             * enregistrée pour chaque activité.
             */
            $redis->hSet(
                'pendafitness:last_views',
                (string) $activityId,
                json_encode(
                    [
                        'activity_id' => $activityId,
                        'viewed_at'   => date(DATE_ATOM)
                    ],
                    JSON_UNESCAPED_SLASHES
                )
            );

            return true;

        } catch (Throwable $e) {
            error_log(
                'Redis write failed: ' . $e->getMessage()
            );

            return false;
        }
    }
}