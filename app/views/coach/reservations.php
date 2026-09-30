<main class="container py-5">

    <div class="dashboard-head mb-4">
        <div>
            <span class="eyebrow">COACH</span>
            <h1 class="fw-bold">Réservations</h1>
        </div>

        <a href="/coach" class="btn btn-outline-dark">
            Dashboard
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">

            <thead>
                <tr>
                    <th>Membre</th>
                    <th>Activité</th>
                    <th>Séance</th>
                    <th>État séance</th>
                    <th>Réservation</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

            <?php if (empty($items)): ?>

                <tr>
                    <td colspan="6" class="text-muted">
                        Aucune réservation.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($items as $x): ?>

                    <?php
                    $sessionStatus =
                        $x['session_status'] ?? 'scheduled';

                    $reservationStatus =
                        $x['status'] ?? 'confirmed';
                    ?>

                    <tr>

                        <td>
                            <?= e(
                                $x['firstname']
                                . ' '
                                . $x['lastname']
                            ) ?>

                            <br>

                            <small class="text-muted">
                                <?= e($x['email']) ?>
                            </small>
                        </td>

                        <td>
                            <?= e($x['title']) ?>
                        </td>

                        <td>
                            <?= date(
                                'd/m/Y H:i',
                                strtotime($x['starts_at'])
                            ) ?>
                        </td>

                        <td>

                            <?php if (
                                $sessionStatus === 'scheduled'
                            ): ?>

                                <span class="badge text-bg-success">
                                    Programmée
                                </span>

                            <?php elseif (
                                $sessionStatus === 'blocked'
                            ): ?>

                                <span class="badge text-bg-warning">
                                    Bloquée
                                </span>

                            <?php elseif (
                                $sessionStatus === 'cancelled'
                            ): ?>

                                <span class="badge text-bg-danger">
                                    Annulée
                                </span>

                            <?php else: ?>

                                <span class="badge text-bg-secondary">
                                    <?= e($sessionStatus) ?>
                                </span>

                            <?php endif; ?>

                            <?php if (
                                !empty($x['cancellation_reason'])
                            ): ?>

                                <div class="small text-muted mt-1">
                                    <?= e(
                                        $x['cancellation_reason']
                                    ) ?>
                                </div>

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php if (
                                $reservationStatus === 'confirmed'
                            ): ?>

                                <span class="badge text-bg-success">
                                    Confirmée
                                </span>

                            <?php elseif (
                                $reservationStatus === 'completed'
                            ): ?>

                                <span class="badge text-bg-primary">
                                    Terminée
                                </span>

                            <?php else: ?>

                                <span class="badge text-bg-secondary">
                                    Annulée
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <form
                                class="d-flex gap-2"
                                method="post"
                                action="/coach/reservations/status"
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

                                <select
                                    name="status"
                                    class="form-select form-select-sm"
                                >

                                    <option
                                        value="confirmed"
                                        <?= $reservationStatus === 'confirmed'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Confirmée
                                    </option>

                                    <option
                                        value="completed"
                                        <?= $reservationStatus === 'completed'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Terminée
                                    </option>

                                    <option
                                        value="cancelled"
                                        <?= $reservationStatus === 'cancelled'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Annulée
                                    </option>

                                </select>

                                <button class="btn btn-sm btn-dark">
                                    OK
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>
    </div>

</main>