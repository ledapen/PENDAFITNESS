<?php

class CoachController
{
    public function dashboard()
    {
        requireRole('coach');

        $stats = [
            'activities' => db()->query(
                "SELECT COUNT(*) FROM activities WHERE active = 1"
            )->fetchColumn(),

            'sessions' => db()->query(
                "SELECT COUNT(*)
                 FROM sessions
                 WHERE starts_at > NOW()
                   AND status = 'scheduled'"
            )->fetchColumn(),

            'reservations' => db()->query(
                "SELECT COUNT(*)
                 FROM reservations
                 WHERE status = 'confirmed'"
            )->fetchColumn()
        ];

        view('coach/dashboard', compact('stats'));
    }

    public function activities()
    {
        requireRole('coach');

        require_once __DIR__ . '/../models/Activity.php';

        $activities = Activity::allAdmin();
        $categories = Activity::categories();

        view(
            'coach/activities',
            compact('activities', 'categories')
        );
    }

    public function saveActivity()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Activity.php';

        Activity::save(
            $_POST,
            !empty($_POST['id'])
                ? (int) $_POST['id']
                : null
        );

        flash('success', 'Activité enregistrée.');

        redirect('/coach/activites');
    }

    public function toggleActivity()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Activity.php';

        Activity::toggle((int) $_POST['id']);

        redirect('/coach/activites');
    }

    /**
     * Gestion des séances et des programmations récurrentes.
     */
    public function sessions()
    {
        requireRole('coach');

        require_once __DIR__ . '/../models/Activity.php';
        require_once __DIR__ . '/../models/Session.php';

        // Maintient automatiquement 90 jours de séances futures.
        Session::generateFutureSessions();

        $activities = Activity::allAdmin();
        $sessions = Activity::upcomingSessions();
        $schedules = Session::schedules();

        view(
            'coach/sessions',
            compact(
                'activities',
                'sessions',
                'schedules'
            )
        );
    }

    /**
     * Ajout d'une séance ponctuelle.
     */
    public function addSession()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Activity.php';

        Activity::addSession($_POST);

        flash(
            'success',
            'Séance ponctuelle programmée.'
        );

        redirect('/coach/seances');
    }

    /**
     * Création d'une programmation récurrente.
     */
    public function addSchedule()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Session.php';

        $id = Session::createSchedule($_POST);

        if ($id > 0) {
            flash(
                'success',
                'Programmation récurrente enregistrée.'
            );
        } else {
            flash(
                'error',
                'Impossible de créer cette programmation.'
            );
        }

        redirect('/coach/seances');
    }

    /**
     * Suspend une programmation récurrente.
     */
    public function pauseSchedule()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Session.php';

        Session::pauseSchedule(
            (int) ($_POST['id'] ?? 0)
        );

        flash(
            'success',
            'Programmation suspendue.'
        );

        redirect('/coach/seances');
    }

    /**
     * Réactive une programmation récurrente.
     */
    public function resumeSchedule()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Session.php';

        Session::resumeSchedule(
            (int) ($_POST['id'] ?? 0)
        );

        flash(
            'success',
            'Programmation réactivée.'
        );

        redirect('/coach/seances');
    }

    /**
     * Bloque temporairement une séance précise.
     */
    public function blockSession()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Session.php';

        $reason = trim(
            $_POST['reason'] ?? ''
        );

        $ok = Session::block(
            (int) ($_POST['id'] ?? 0),
            $reason
        );

        flash(
            $ok ? 'success' : 'error',
            $ok
                ? 'Séance bloquée.'
                : 'Impossible de bloquer cette séance.'
        );

        redirect('/coach/seances');
    }

    /**
     * Réouvre une séance précédemment bloquée.
     */
    public function reopenSession()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Session.php';

        $ok = Session::reopen(
            (int) ($_POST['id'] ?? 0)
        );

        flash(
            $ok ? 'success' : 'error',
            $ok
                ? 'Séance réouverte.'
                : 'Impossible de réouvrir cette séance.'
        );

        redirect('/coach/seances');
    }

    /**
     * L'ancienne action "Supprimer" devient une annulation.
     * La ligne et les réservations restent conservées.
     */
    public function deleteSession()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Session.php';

        $reason = trim(
            $_POST['reason']
                ?? 'Séance annulée par le coach.'
        );

        $ok = Session::cancel(
            (int) ($_POST['id'] ?? 0),
            $reason
        );

        flash(
            $ok ? 'success' : 'error',
            $ok
                ? 'Séance annulée.'
                : 'Impossible d’annuler cette séance.'
        );

        redirect('/coach/seances');
    }

    public function reservations()
    {
        requireRole('coach');

        require_once __DIR__ . '/../models/Reservation.php';

        $items = Reservation::all();

        view(
            'coach/reservations',
            compact('items')
        );
    }

    public function reservationStatus()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Reservation.php';

        Reservation::setStatus(
            (int) $_POST['id'],
            $_POST['status']
        );

        redirect('/coach/reservations');
    }

    public function reviews()
    {
        requireRole('coach');

        require_once __DIR__ . '/../models/Review.php';

        $items = Review::all();

        view(
            'coach/reviews',
            compact('items')
        );
    }

    public function reviewStatus()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Review.php';

        Review::moderate(
            (int) $_POST['id'],
            $_POST['status']
        );

        redirect('/coach/avis');
    }

    public function contacts()
    {
        requireRole('coach');

        require_once __DIR__ . '/../models/Contact.php';

        $items = Contact::all();

        view(
            'coach/contacts',
            compact('items')
        );
    }

    public function contactProcessed()
    {
        requireRole('coach');
        check_csrf();

        require_once __DIR__ . '/../models/Contact.php';

        Contact::processed(
            (int) $_POST['id']
        );

        redirect('/coach/contacts');
    }
}