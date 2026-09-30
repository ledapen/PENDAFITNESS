<main class="container py-5">

    <div class="dashboard-head">
        <div>
            <span class="eyebrow">ESPACE MEMBRE</span>
            <h1 class="fw-bold">Mes réservations</h1>
        </div>

        <a href="/activites" class="btn btn-accent">
            Trouver une séance
        </a>
    </div>

    <?php if (!$items): ?>

        <div class="empty-state">
            <i class="bi bi-calendar2-x"></i>
            <h3>Aucune réservation</h3>
            <p>Votre prochaine séance vous attend.</p>
        </div>

    <?php endif; ?>

    <div class="row g-4">

        <?php foreach ($items as $x): ?>

            <?php
            $reservationStatus = $x['status'];
            $sessionStatus = $x['session_status'] ?? 'scheduled';
            $isFuture = strtotime($x['starts_at']) > time();

            $reservationBadge = match ($reservationStatus) {
                'confirmed' => 'success',
                'completed' => 'primary',
                default => 'secondary'
            };
            ?>

            <div class="col-lg-6">

                <div class="reservation-card">

                    <img
                        src="<?= e($x['image_url']) ?>"
                        alt="<?= e($x['title']) ?>"
                    >

                    <div class="p-3 flex-grow-1">

                        <div class="d-flex justify-content-between gap-2">

                            <h3 class="h5 fw-bold">
                                <?= e($x['title']) ?>
                            </h3>

                            <span class="badge text-bg-<?= $reservationBadge ?>">
                                <?= e($reservationStatus) ?>
                            </span>

                        </div>

                        <p class="mb-2">
                            <i class="bi bi-calendar-event"></i>

                            <?= date(
                                'd/m/Y H:i',
                                strtotime($x['starts_at'])
                            ) ?>

                            · <?= e($x['coach_name']) ?>
                        </p>

                        <!-- Statut opérationnel de la séance -->

                        <?php if ($sessionStatus === 'blocked'): ?>

                            <div
                                class="alert alert-warning py-2"
                                role="alert"
                            >
                                <strong>Séance temporairement indisponible.</strong>

                                <?php if (!empty($x['cancellation_reason'])): ?>
                                    <div class="small mt-1">
                                        Motif :
                                        <?= e($x['cancellation_reason']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($sessionStatus === 'cancelled'): ?>

                            <div
                                class="alert alert-danger py-2"
                                role="alert"
                            >
                                <strong>Cette séance a été annulée.</strong>

                                <?php if (!empty($x['cancellation_reason'])): ?>
                                    <div class="small mt-1">
                                        Motif :
                                        <?= e($x['cancellation_reason']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <?php endif; ?>

                        <!-- Annulation par le membre -->

                        <?php if (
                            $reservationStatus === 'confirmed'
                            && $sessionStatus === 'scheduled'
                            && $isFuture
                        ): ?>

                            <form
                                method="post"
                                action="/annuler"
                                onsubmit="return confirm(
                                    'Annuler cette réservation ?'
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
                                    value="<?= (int) $x['id'] ?>"
                                >

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                >
                                    Annuler
                                </button>
                            </form>

                        <?php endif; ?>

                        <!-- Avis uniquement pour une séance réellement passée -->

                        <?php if (
                            $sessionStatus !== 'cancelled'
                            && $sessionStatus !== 'blocked'
                            && (
                                !$isFuture
                                || $reservationStatus === 'completed'
                            )
                        ): ?>

                            <button
                                class="btn btn-sm btn-outline-dark mt-2"
                                data-bs-toggle="collapse"
                                data-bs-target="#review<?= (int) $x['id'] ?>"
                            >
                                Donner un avis
                            </button>

                            <form
                                id="review<?= (int) $x['id'] ?>"
                                class="collapse mt-3"
                                method="post"
                                action="/avis"
                            >
                                <input
                                    type="hidden"
                                    name="csrf"
                                    value="<?= csrf() ?>"
                                >

                                <input
                                    type="hidden"
                                    name="activity_id"
                                    value="<?= (int) $x['activity_id'] ?>"
                                >

                                <select
                                    name="rating"
                                    class="form-select form-select-sm mb-2"
                                    required
                                >
                                    <option value="5">
                                        5 - Excellent
                                    </option>

                                    <option value="4">
                                        4 - Très bien
                                    </option>

                                    <option value="3">
                                        3 - Bien
                                    </option>

                                    <option value="2">
                                        2 - Moyen
                                    </option>

                                    <option value="1">
                                        1 - Décevant
                                    </option>
                                </select>

                                <textarea
                                    name="comment"
                                    class="form-control form-control-sm mb-2"
                                    required
                                    placeholder="Votre expérience"
                                ></textarea>

                                <button class="btn btn-accent btn-sm">
                                    Envoyer
                                </button>
                            </form>

                        <?php endif; ?>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>

    </div>

</main>