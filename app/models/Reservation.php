<?php

class Reservation
{
    public static function mine($uid)
    {
        $s = db()->prepare("
            SELECT
                r.*,
                a.id activity_id,
                a.title,
                a.image_url,
                s.starts_at,
                s.duration_minutes,
                s.coach_name,
                s.status session_status,
                s.cancellation_reason
            FROM reservations r
            JOIN sessions s ON s.id = r.session_id
            JOIN activities a ON a.id = s.activity_id
            WHERE r.user_id = ?
            ORDER BY s.starts_at DESC
        ");

        $s->execute([$uid]);

        return $s->fetchAll();
    }

    public static function create($uid, $sid)
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        /*
         * On verrouille d'abord la séance elle-même.
         * Cela permettra aussi de sécuriser correctement
         * le contrôle de capacité lors des réservations simultanées.
         */
        $q = $pdo->prepare("
            SELECT capacity
            FROM sessions
            WHERE id = ?
              AND starts_at > NOW()
              AND status = 'scheduled'
            FOR UPDATE
        ");

        $q->execute([$sid]);
        $session = $q->fetch();

        if (!$session) {
            $pdo->rollBack();
            return false;
        }

        /*
         * Vérifie si cet utilisateur possède déjà
         * une réservation pour cette séance.
         */
        $q = $pdo->prepare("
            SELECT status
            FROM reservations
            WHERE user_id = ?
              AND session_id = ?
            LIMIT 1
        ");

        $q->execute([$uid, $sid]);
        $existing = $q->fetch();

        /*
         * Une réservation déjà confirmée ne doit pas être
         * considérée comme une nouvelle réservation.
         */
        if ($existing && $existing['status'] === 'confirmed') {
            $pdo->rollBack();
            return false;
        }

        /*
         * Compte les places réellement occupées pendant
         * que la séance est verrouillée.
         */
        $q = $pdo->prepare("
            SELECT COUNT(*)
            FROM reservations
            WHERE session_id = ?
              AND status <> 'cancelled'
        ");

        $q->execute([$sid]);
        $used = (int) $q->fetchColumn();

        if ($used >= (int) $session['capacity']) {
            $pdo->rollBack();
            return false;
        }

        /*
         * Nouvelle réservation ou réactivation
         * d'une réservation précédemment annulée.
         */
        if ($existing) {
            $q = $pdo->prepare("
                UPDATE reservations
                SET status = 'confirmed'
                WHERE user_id = ?
                  AND session_id = ?
            ");

            $q->execute([$uid, $sid]);
        } else {
            $q = $pdo->prepare("
                INSERT INTO reservations (
                    user_id,
                    session_id,
                    status
                )
                VALUES (?, ?, 'confirmed')
            ");

            $q->execute([$uid, $sid]);
        }

        $pdo->commit();

        return true;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log(
            'Reservation creation failed: ' . $e->getMessage()
        );

        return false;
    }
}
    public static function cancel($id, $uid)
    {
        $s = db()->prepare("
            UPDATE reservations r
            JOIN sessions s ON s.id = r.session_id
            SET r.status = 'cancelled'
            WHERE r.id = ?
              AND r.user_id = ?
              AND r.status = 'confirmed'
              AND s.starts_at > NOW()
        ");

        $s->execute([
            $id,
            $uid
        ]);

        return $s->rowCount() > 0;
    }

    public static function all()
    {
        return db()->query("
            SELECT
                r.id,
                r.status,
                r.created_at,
                u.firstname,
                u.lastname,
                u.email,
                a.title,
                s.starts_at,
                s.status session_status,
                s.cancellation_reason
            FROM reservations r
            JOIN users u ON u.id = r.user_id
            JOIN sessions s ON s.id = r.session_id
            JOIN activities a ON a.id = s.activity_id
            ORDER BY s.starts_at DESC
            LIMIT 100
        ")->fetchAll();
    }

    public static function setStatus($id, $status)
    {
        if (
            !in_array(
                $status,
                ['confirmed', 'cancelled', 'completed'],
                true
            )
        ) {
            return false;
        }

        $s = db()->prepare("
            UPDATE reservations
            SET status = ?
            WHERE id = ?
        ");

        $s->execute([
            $status,
            $id
        ]);

        return $s->rowCount() > 0;
    }
}