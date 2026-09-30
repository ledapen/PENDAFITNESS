<main class="container py-5">

    <div class="dashboard-head mb-4">
        <div>
            <span class="eyebrow">COACH</span>
            <h1 class="fw-bold">Gestion des séances</h1>
            <p class="text-muted mb-0">
                Programmez les séances ponctuelles ou récurrentes
                et gérez les indisponibilités.
            </p>
        </div>

        <a href="/coach" class="btn btn-outline-dark">
            Dashboard
        </a>
    </div>

    <!-- =====================================================
         1. PROGRAMMATION RÉCURRENTE
    ====================================================== -->

    <section class="mb-5">
        <h2 class="h4 fw-bold mb-3">
            Programmation récurrente
        </h2>

        <div class="form-card">
            <p class="text-muted">
                Créez par exemple une séance de Yoga
                chaque lundi à 18h. PENDAFITNESS générera
                automatiquement les séances futures.
            </p>

            <form
                class="row g-3"
                method="post"
                action="/coach/seances/recurrentes/add"
            >
                <input
                    type="hidden"
                    name="csrf"
                    value="<?= csrf() ?>"
                >

                <div class="col-md-4">
                    <label class="form-label">
                        Activité
                    </label>

                    <select
                        name="activity_id"
                        class="form-select"
                        required
                    >
                        <?php foreach ($activities as $a): ?>
                            <option value="<?= (int) $a['id'] ?>">
                                <?= e($a['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Jour
                    </label>

                    <select
                        name="day_of_week"
                        class="form-select"
                        required
                    >
                        <option value="1">Lundi</option>
                        <option value="2">Mardi</option>
                        <option value="3">Mercredi</option>
                        <option value="4">Jeudi</option>
                        <option value="5">Vendredi</option>
                        <option value="6">Samedi</option>
                        <option value="7">Dimanche</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Heure
                    </label>

                    <input
                        type="time"
                        name="start_time"
                        class="form-control"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Durée (minutes)
                    </label>

                    <input
                        type="number"
                        name="duration_minutes"
                        value="60"
                        min="15"
                        max="300"
                        class="form-control"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Nombre de places
                    </label>

                    <input
                        type="number"
                        name="capacity"
                        value="12"
                        min="1"
                        max="500"
                        class="form-control"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Coach
                    </label>

                    <input
                        type="text"
                        name="coach_name"
                        class="form-control"
                        placeholder="Nom du coach"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Salle
                    </label>

                    <input
                        type="text"
                        name="room"
                        class="form-control"
                        placeholder="Ex : Studio A"
                    >
                </div>

                <div class="col-12">
                    <button class="btn btn-accent">
                        Créer la programmation
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- =====================================================
         2. PROGRAMMATIONS EXISTANTES
    ====================================================== -->

    <section class="mb-5">
        <h2 class="h4 fw-bold mb-3">
            Programmations récurrentes
        </h2>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Activité</th>
                        <th>Jour</th>
                        <th>Heure</th>
                        <th>Coach</th>
                        <th>Places</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $days = [
                    1 => 'Lundi',
                    2 => 'Mardi',
                    3 => 'Mercredi',
                    4 => 'Jeudi',
                    5 => 'Vendredi',
                    6 => 'Samedi',
                    7 => 'Dimanche'
                ];
                ?>

                <?php if (empty($schedules)): ?>

                    <tr>
                        <td colspan="7" class="text-muted">
                            Aucune programmation récurrente.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($schedules as $schedule): ?>

                        <tr>
                            <td>
                                <?= e($schedule['title']) ?>
                            </td>

                            <td>
                                <?= e(
                                    $days[(int) $schedule['day_of_week']]
                                    ?? ''
                                ) ?>
                            </td>

                            <td>
                                <?= e(
                                    substr(
                                        $schedule['start_time'],
                                        0,
                                        5
                                    )
                                ) ?>
                            </td>

                            <td>
                                <?= e($schedule['coach_name']) ?>
                            </td>

                            <td>
                                <?= (int) $schedule['capacity'] ?>
                            </td>

                            <td>
                                <?php if ((int) $schedule['active'] === 1): ?>
                                    <span class="badge text-bg-success">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">
                                        Suspendue
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ((int) $schedule['active'] === 1): ?>

                                    <form
                                        method="post"
                                        action="/coach/seances/recurrentes/pause"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= csrf() ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $schedule['id'] ?>"
                                        >

                                        <button
                                            class="btn btn-sm btn-outline-warning"
                                        >
                                            Suspendre
                                        </button>
                                    </form>

                                <?php else: ?>

                                    <form
                                        method="post"
                                        action="/coach/seances/recurrentes/resume"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= csrf() ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $schedule['id'] ?>"
                                        >

                                        <button
                                            class="btn btn-sm btn-outline-success"
                                        >
                                            Réactiver
                                        </button>
                                    </form>

                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </section>

    <!-- =====================================================
         3. SÉANCE PONCTUELLE
    ====================================================== -->

    <section class="mb-5">
        <h2 class="h4 fw-bold mb-3">
            Ajouter une séance ponctuelle
        </h2>

        <form
            class="form-card row g-3"
            method="post"
            action="/coach/seances/add"
        >
            <input
                type="hidden"
                name="csrf"
                value="<?= csrf() ?>"
            >

            <div class="col-md-4">
                <label class="form-label">
                    Activité
                </label>

                <select
                    name="activity_id"
                    class="form-select"
                    required
                >
                    <?php foreach ($activities as $a): ?>
                        <option value="<?= (int) $a['id'] ?>">
                            <?= e($a['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">
                    Date et heure
                </label>

                <input
                    type="datetime-local"
                    name="starts_at"
                    class="form-control"
                    required
                >
            </div>

            <div class="col-md-2">
                <label class="form-label">
                    Durée
                </label>

                <input
                    type="number"
                    name="duration_minutes"
                    value="60"
                    min="15"
                    class="form-control"
                    required
                >
            </div>

            <div class="col-md-2">
                <label class="form-label">
                    Places
                </label>

                <input
                    type="number"
                    name="capacity"
                    value="12"
                    min="1"
                    class="form-control"
                    required
                >
            </div>

            <div class="col-md-5">
                <label class="form-label">
                    Coach
                </label>

                <input
                    name="coach_name"
                    class="form-control"
                    placeholder="Nom du coach"
                    required
                >
            </div>

            <div class="col-md-5">
                <label class="form-label">
                    Salle
                </label>

                <input
                    name="room"
                    class="form-control"
                    placeholder="Salle"
                >
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-accent w-100">
                    Ajouter
                </button>
            </div>
        </form>
    </section>

    <!-- =====================================================
         4. PROCHAINES SÉANCES
    ====================================================== -->

    <section>
        <h2 class="h4 fw-bold mb-3">
            Prochaines séances
        </h2>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Activité</th>
                        <th>Coach</th>
                        <th>Places</th>
                        <th>Statut</th>
                        <th>Gestion</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($sessions)): ?>

                    <tr>
                        <td colspan="6" class="text-muted">
                            Aucune séance future.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($sessions as $s): ?>

                        <tr>
                            <td>
                                <?= date(
                                    'd/m/Y H:i',
                                    strtotime($s['starts_at'])
                                ) ?>
                            </td>

                            <td>
                                <?= e($s['title']) ?>
                            </td>

                            <td>
                                <?= e($s['coach_name']) ?>
                            </td>

                            <td>
                                <?= (int) $s['remaining'] ?>
                                /
                                <?= (int) $s['capacity'] ?>
                            </td>

                            <td>
                                <?php if ($s['status'] === 'scheduled'): ?>

                                    <span class="badge text-bg-success">
                                        Programmée
                                    </span>

                                <?php elseif ($s['status'] === 'blocked'): ?>

                                    <span class="badge text-bg-warning">
                                        Bloquée
                                    </span>

                                <?php else: ?>

                                    <span class="badge text-bg-danger">
                                        Annulée
                                    </span>

                                <?php endif; ?>

                                <?php if (!empty($s['cancellation_reason'])): ?>
                                    <div class="small text-muted mt-1">
                                        <?= e($s['cancellation_reason']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>

                                <?php if ($s['status'] === 'scheduled'): ?>

                                    <form
                                        method="post"
                                        action="/coach/seances/block"
                                        class="mb-2"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= csrf() ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $s['id'] ?>"
                                        >

                                        <input
                                            type="text"
                                            name="reason"
                                            class="form-control form-control-sm mb-1"
                                            placeholder="Motif du blocage"
                                            required
                                        >

                                        <button
                                            class="btn btn-sm btn-outline-warning"
                                        >
                                            Bloquer
                                        </button>
                                    </form>

                                    <form
                                        method="post"
                                        action="/coach/seances/delete"
                                        onsubmit="return confirm(
                                            'Annuler cette séance ? Les réservations seront conservées dans l’historique.'
                                        )"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= csrf() ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $s['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="reason"
                                            value="Séance annulée par le coach."
                                        >

                                        <button
                                            class="btn btn-sm btn-outline-danger"
                                        >
                                            Annuler
                                        </button>
                                    </form>

                                <?php elseif ($s['status'] === 'blocked'): ?>

                                    <form
                                        method="post"
                                        action="/coach/seances/reopen"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= csrf() ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $s['id'] ?>"
                                        >

                                        <button
                                            class="btn btn-sm btn-outline-success"
                                        >
                                            Réouvrir
                                        </button>
                                    </form>

                                <?php else: ?>

                                    <span class="text-muted small">
                                        Historique conservé
                                    </span>

                                <?php endif; ?>

                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </section>

</main>