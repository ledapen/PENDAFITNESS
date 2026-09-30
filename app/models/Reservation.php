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
             * Verrouillage de la séance pendant le contrôle.
             * Une séance doit :
             * - exister ;
             * - être future ;
             * - être au statut scheduled ;
             * - disposer encore d'une place.
             */
            $q = $pdo->prepare("
                SELECT
                    s.capacity,
                    COUNT(r.id) used
                FROM sessions s
                LEFT JOIN reservations r
                    ON r.session_id = s.id
                    AND r.status <> 'cancelled'
                WHERE s.id = ?
                  AND s.starts_at > NOW()
                  AND s.status = 'scheduled'
                GROUP BY s.id
                FOR UPDATE
            ");

            $q->execute([$sid]);

            $x = $q->fetch();

            if (!$x || (int) $x['used'] >= (int) $x['capacity']) {
                $pdo->rollBack();
                return false;
            }

            /*
             * La contrainte UNIQUE existante sur user_id/session_id
             * empêche la création de plusieurs lignes identiques.
             *
             * Si l'utilisateur avait précédemment annulé sa réservation,
             * celle-ci peut être réactivée.
             */
            $s = $pdo->prepare("
                INSERT INTO reservations (
                    user_id,
                    session_id,
                    status
                )
                VALUES (?, ?, 'confirmed')
                ON DUPLICATE KEY UPDATE status = 'confirmed'
            ");

            $s->execute([
                $uid,
                $sid
            ]);

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