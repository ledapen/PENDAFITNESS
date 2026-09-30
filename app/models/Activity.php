<?php

class Activity
{
    public static function all($filters = [])
    {
        $sql = "
            SELECT a.*, c.name category, c.slug category_slug,
            (
                SELECT MIN(s.starts_at)
                FROM sessions s
                WHERE s.activity_id = a.id
                  AND s.starts_at > NOW()
                  AND s.status = 'scheduled'
            ) next_session
            FROM activities a
            JOIN sport_categories c ON c.id = a.category_id
            WHERE a.active = 1
        ";

        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND c.slug = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['level'])) {
            $sql .= " AND a.level = ?";
            $params[] = $filters['level'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND a.price <= ?";
            $params[] = (float) $filters['max_price'];
        }

        if (!empty($filters['q'])) {
            $sql .= " AND (
                LOWER(a.title) LIKE LOWER(?)
                OR LOWER(a.description) LIKE LOWER(?)
                OR LOWER(a.location) LIKE LOWER(?)
            )";

            $q = '%' . trim($filters['q']) . '%';
            array_push($params, $q, $q, $q);
        }

        $sql .= " ORDER BY a.featured DESC, a.id DESC";

        $st = db()->prepare($sql);
        $st->execute($params);

        return $st->fetchAll();
    }

    public static function allAdmin()
    {
        return db()->query("
            SELECT a.*, c.name category
            FROM activities a
            JOIN sport_categories c ON c.id = a.category_id
            ORDER BY a.id DESC
        ")->fetchAll();
    }

    public static function categories()
    {
        return db()->query("
            SELECT *
            FROM sport_categories
            ORDER BY name
        ")->fetchAll();
    }

    public static function find($id)
    {
        $st = db()->prepare("
            SELECT a.*, c.name category
            FROM activities a
            JOIN sport_categories c ON c.id = a.category_id
            WHERE a.id = ?
        ");

        $st->execute([$id]);

        return $st->fetch();
    }

    /**
     * Séances réservables d'une activité.
     * Les séances bloquées ou annulées sont exclues.
     */
    public static function sessions($id)
    {
        require_once __DIR__ . '/Session.php';

        // Entretient automatiquement l'horizon des séances futures.
        Session::generateFutureSessions();

        $st = db()->prepare("
            SELECT
                s.*,
                (s.capacity - COUNT(r.id)) remaining
            FROM sessions s
            LEFT JOIN reservations r
                ON r.session_id = s.id
                AND r.status <> 'cancelled'
            WHERE s.activity_id = ?
              AND s.starts_at > NOW()
              AND s.status = 'scheduled'
            GROUP BY s.id
            ORDER BY s.starts_at
        ");

        $st->execute([$id]);

        return $st->fetchAll();
    }

    public static function save($d, $id = null)
    {
        $vals = [
            (int) $d['category_id'],
            trim($d['title']),
            trim($d['short_description']),
            trim($d['description']),
            trim($d['level']),
            (int) $d['duration_minutes'],
            (float) $d['price'],
            trim($d['image_url']),
            trim($d['location']),
            !empty($d['featured']) ? 1 : 0,
            !empty($d['active']) ? 1 : 0
        ];

        if ($id) {
            $vals[] = $id;

            $s = db()->prepare("
                UPDATE activities
                SET category_id = ?,
                    title = ?,
                    short_description = ?,
                    description = ?,
                    level = ?,
                    duration_minutes = ?,
                    price = ?,
                    image_url = ?,
                    location = ?,
                    featured = ?,
                    active = ?
                WHERE id = ?
            ");
        } else {
            $s = db()->prepare("
                INSERT INTO activities (
                    category_id,
                    title,
                    short_description,
                    description,
                    level,
                    duration_minutes,
                    price,
                    image_url,
                    location,
                    featured,
                    active
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
        }

        $s->execute($vals);

        return $id ?: db()->lastInsertId();
    }

    public static function toggle($id)
    {
        $s = db()->prepare("
            UPDATE activities
            SET active = IF(active = 1, 0, 1)
            WHERE id = ?
        ");

        $s->execute([$id]);
    }

    /**
     * Conserve la possibilité de créer une séance ponctuelle.
     */
    public static function addSession($d)
    {
        $s = db()->prepare("
            INSERT INTO sessions (
                activity_id,
                starts_at,
                duration_minutes,
                capacity,
                coach_name,
                room,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, 'scheduled')
        ");

        $s->execute([
            (int) $d['activity_id'],
            $d['starts_at'],
            (int) $d['duration_minutes'],
            (int) $d['capacity'],
            trim($d['coach_name']),
            trim($d['room'] ?? '')
        ]);
    }

    /**
     * Toutes les prochaines séances destinées au tableau coach.
     * Contrairement au catalogue public, les séances bloquées/annulées
     * restent visibles afin de permettre leur administration.
     */
    public static function upcomingSessions()
{
    require_once __DIR__ . '/Session.php';

    Session::generateFutureSessions();

    return db()->query("
        SELECT
            s.id,
            s.activity_id,
            s.schedule_id,
            s.starts_at,
            s.duration_minutes,
            s.capacity,
            s.coach_name,
            s.room,
            s.status,
            s.cancellation_reason,
            a.title,
            (
                s.capacity - (
                    SELECT COUNT(*)
                    FROM reservations r
                    WHERE r.session_id = s.id
                      AND r.status <> 'cancelled'
                )
            ) AS remaining
        FROM sessions s
        JOIN activities a
            ON a.id = s.activity_id
        WHERE s.starts_at > NOW()
        ORDER BY s.starts_at ASC
        LIMIT 200
    ")->fetchAll();
}
}