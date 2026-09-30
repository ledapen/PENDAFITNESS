<?php

class NoSqlStats
{
    /**
     * Crée et retourne le client Redis.
     * Retourne null si Redis n'est pas disponible.
     */
    private static function client(): ?Redis
    {
        // Diagnostic temporaire pour Render.
        // Aucune URL, aucun identifiant et aucun mot de passe ne sont affichés.
        error_log(
            'Redis diagnostic - extension: ' .
            (class_exists('Redis') ? 'OK' : 'ABSENTE')
        );

        $redisUrl = envv('REDIS_URL', '');

        error_log(
            'Redis diagnostic - REDIS_URL: ' .
            ($redisUrl !== '' ? 'PRESENTE' : 'ABSENTE')
        );

        // Vérifie que l'extension PHP Redis est disponible.
        if (!class_exists('Redis')) {
            return null;
        }

        // Vérifie que REDIS_URL existe.
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

            // Render peut fournir redis:// ou rediss://.
            $host = $scheme === 'rediss'
                ? 'tls://' . $parts['host']
                : $parts['host'];

            // Connexion avec timeout de 2 secondes.
            $redis->connect(
                $host,
                $port,
                2.0
            );

            // Authentification si nécessaire.
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

            error_log('Redis diagnostic - connexion: OK');

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

            $key = "pendafitness:activity:{$activityId}:views:{$day}";

            // Incrémente le compteur de consultations.
            $redis->incr($key);

            // Conservation du compteur pendant 31 jours.
            $redis->expire(
                $key,
                60 * 60 * 24 * 31
            );

            // Conserve également la dernière consultation
            // de chaque activité.
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

            error_log(
                'Redis diagnostic - écriture activité ' .
                $activityId .
                ': OK'
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