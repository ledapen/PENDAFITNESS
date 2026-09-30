<?php

class Session
{
    /**
     * Génère les séances futures à partir des programmations récurrentes actives.
     * On conserve en permanence jusqu'à 90 jours de séances futures.
     */
    public static function generateFutureSessions(int $daysAhead = 90): int
    {
        $pdo = db();

        $schedules = $pdo->query("
            SELECT *
            FROM session_schedules
            WHERE active = 1
            ORDER BY id ASC
        ")->fetchAll();

        $created = 0;

        $today = new DateTimeImmutable('today');
        $endDate = $today->modify("+{$daysAhead} days");

        foreach ($schedules as $schedule) {
            $date = $today;

            while ($date <= $endDate) {
                // PHP : 1 = lundi ... 7 = dimanche
                $dayOfWeek = (int) $date->format('N');

                if ($dayOfWeek === (int) $schedule['day_of_week']) {
                    $startsAt = $date->format('Y-m-d')
                        . ' '
                        . $schedule['start_time'];

                    // Ne jamais générer une séance dans le passé.
                    if (strtotime($startsAt) > time()) {
                        $check = $pdo->prepare("
                            SELECT id
                            FROM sessions
                            WHERE schedule_id = ?
                              AND starts_at = ?
                            LIMIT 1
                        ");

                        $check->execute([
                            $schedule['id'],
                            $startsAt
                        ]);

                        // Évite toute création en double.
                        if (!$check->fetchColumn()) {
                            $insert = $pdo->prepare("
                                INSERT INTO sessions (
                                    activity_id,
                                    schedule_id,
                                    starts_at,
                                    duration_minutes,
                                    capacity,
                                    coach_name,
                                    room,
                                    status
                                )
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled')
                            ");

                            $insert->execute([
                                $schedule['activity_id'],
                                $schedule['id'],
                                $startsAt,
                                $schedule['duration_minutes'],
                                $schedule['capacity'],
                                $schedule['coach_name'],
                                $schedule['room']
                            ]);

                            $created++;
                        }
                    }
                }

                $date = $date->modify('+1 day');
            }
        }

        return $created;
    }

    /**
     * Bloque une séance sans la supprimer.
     * Les réservations et l'historique restent conservés.
     */
    public static function block(
        int $sessionId,
        string $reason = ''
    ): bool {
        $stmt = db()->prepare("
            UPDATE sessions
            SET status = 'blocked',
                cancellation_reason = ?
            WHERE id = ?
              AND status = 'scheduled'
        ");

        $stmt->execute([
            trim($reason),
            $sessionId
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Annule une séance sans supprimer son historique.
     */
    public static function cancel(
        int $sessionId,
        string $reason = ''
    ): bool {
        $stmt = db()->prepare("
            UPDATE sessions
            SET status = 'cancelled',
                cancellation_reason = ?
            WHERE id = ?
              AND status <> 'cancelled'
        ");

        $stmt->execute([
            trim($reason),
            $sessionId
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Réactive une séance bloquée.
     */
    public static function reopen(int $sessionId): bool
    {
        $stmt = db()->prepare("
            UPDATE sessions
            SET status = 'scheduled',
                cancellation_reason = NULL
            WHERE id = ?
              AND status = 'blocked'
        ");

        $stmt->execute([$sessionId]);

        return $stmt->rowCount() > 0;
    }
    /**
     * Crée une programmation récurrente.
     * day_of_week : 1 = lundi ... 7 = dimanche.
     */
    public static function createSchedule(array $data): int
    {
        $dayOfWeek = (int) ($data['day_of_week'] ?? 0);
        $activityId = (int) ($data['activity_id'] ?? 0);
        $duration = (int) ($data['duration_minutes'] ?? 0);
        $capacity = (int) ($data['capacity'] ?? 0);
        $startTime = trim($data['start_time'] ?? '');
        $coachName = trim($data['coach_name'] ?? '');
        $room = trim($data['room'] ?? '');

        if (
            $activityId <= 0 ||
            $dayOfWeek < 1 ||
            $dayOfWeek > 7 ||
            !preg_match('/^\d{2}:\d{2}$/', $startTime) ||
            $duration <= 0 ||
            $capacity <= 0 ||
            $coachName === ''
        ) {
            return 0;
        }

        /*
         * Empêche de créer deux fois exactement
         * la même programmation active.
         */
        $check = db()->prepare("
            SELECT id
            FROM session_schedules
            WHERE activity_id = ?
              AND day_of_week = ?
              AND start_time = ?
              AND coach_name = ?
              AND active = 1
            LIMIT 1
        ");

        $check->execute([
            $activityId,
            $dayOfWeek,
            $startTime,
            $coachName
        ]);

        $existing = $check->fetchColumn();

        if ($existing) {
            return (int) $existing;
        }

        $stmt = db()->prepare("
            INSERT INTO session_schedules (
                activity_id,
                day_of_week,
                start_time,
                duration_minutes,
                capacity,
                coach_name,
                room,
                active
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ");

        $stmt->execute([
            $activityId,
            $dayOfWeek,
            $startTime,
            $duration,
            $capacity,
            $coachName,
            $room !== '' ? $room : null
        ]);

        $id = (int) db()->lastInsertId();

        /*
         * Dès qu'une nouvelle règle est créée,
         * ses occurrences futures sont générées.
         */
        self::generateFutureSessions();

        return $id;
    }

    /**
     * Retourne toutes les programmations récurrentes
     * pour l'espace coach.
     */
    public static function schedules(): array
    {
        return db()->query("
            SELECT
                ss.*,
                a.title
            FROM session_schedules ss
            JOIN activities a ON a.id = ss.activity_id
            ORDER BY
                ss.active DESC,
                ss.day_of_week ASC,
                ss.start_time ASC
        ")->fetchAll();
    }

    /**
     * Suspend une programmation récurrente.
     *
     * Les séances déjà créées restent dans la base.
     * Elles pourront être administrées individuellement.
     */
    public static function pauseSchedule(int $scheduleId): bool
    {
        $stmt = db()->prepare("
            UPDATE session_schedules
            SET active = 0
            WHERE id = ?
              AND active = 1
        ");

        $stmt->execute([$scheduleId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Réactive une programmation et complète immédiatement
     * les 90 prochains jours.
     */
    public static function resumeSchedule(int $scheduleId): bool
    {
        $stmt = db()->prepare("
            UPDATE session_schedules
            SET active = 1
            WHERE id = ?
              AND active = 0
        ");

        $stmt->execute([$scheduleId]);

        if ($stmt->rowCount() > 0) {
            self::generateFutureSessions();
            return true;
        }

        return false;
    }
}