<section
    class="detail-hero"
    style="background-image:
        linear-gradient(90deg, rgba(10,18,38,.9), rgba(10,18,38,.25)),
        url('<?= e($activity['image_url']) ?>')"
>
    <div class="container py-5 text-white">
        <div class="py-5 col-lg-7">

            <span class="badge text-bg-warning">
                <?= e($activity['category']) ?>
            </span>

            <h1 class="display-3 fw-bold mt-3">
                <?= e($activity['title']) ?>
            </h1>

            <p class="lead">
                <?= e($activity['short_description']) ?>
            </p>

            <div class="d-flex gap-3 flex-wrap">
                <span>
                    <i class="bi bi-clock"></i>
                    <?= (int) $activity['duration_minutes'] ?> min
                </span>

                <span>
                    <i class="bi bi-bar-chart"></i>
                    <?= e($activity['level']) ?>
                </span>

                <span>
                    <i class="bi bi-geo-alt"></i>
                    <?= e($activity['location']) ?>
                </span>

                <strong class="text-warning">
                    <?= number_format($activity['price'], 2, ',', ' ') ?> €
                </strong>
            </div>

        </div>
    </div>
</section>

<main class="container py-5">

    <div class="row g-5">

        <div class="col-lg-7">

            <h2 class="fw-bold">
                À propos de cette activité
            </h2>

            <p class="lead text-secondary">
                <?= nl2br(e($activity['description'])) ?>
            </p>

            <?php if (auth()): ?>

                <form
                    method="post"
                    action="/favori"
                    class="mt-3"
                >
                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= csrf() ?>"
                    >

                    <input
                        type="hidden"
                        name="activity_id"
                        value="<?= (int) $activity['id'] ?>"
                    >

                    <button class="btn btn-outline-danger">
                        <i class="bi bi-heart"></i>
                        Ajouter/retirer des favoris
                    </button>
                </form>

            <?php endif; ?>

            <hr class="my-5">

            <h3 class="fw-bold">
                Avis des membres
            </h3>

            <?php if (!$reviews): ?>

                <p class="text-secondary">
                    Aucun avis validé pour le moment.
                </p>

            <?php endif; ?>

            <?php foreach ($reviews as $r): ?>

                <div class="border rounded-4 p-3 mb-3">

                    <span class="stars">
                        <?= str_repeat('★', (int) $r['rating']) ?>
                    </span>

                    <p class="mb-1">
                        <?= e($r['comment']) ?>
                    </p>

                    <small>
                        <?= e($r['firstname'] . ' ' . $r['lastname']) ?>
                    </small>

                </div>

            <?php endforeach; ?>

        </div>

        <aside class="col-lg-5">

            <div
                class="booking-box sticky-lg-top"
                data-activity-id="<?= (int) $activity['id'] ?>"
            >

                <!--
                    Sur mobile : titre et bouton sont l'un sous l'autre.
                    À partir de md : ils repassent sur la même ligne.
                -->
                <div
                    class="d-flex flex-column flex-md-row
                           justify-content-between
                           align-items-stretch align-items-md-center
                           gap-3"
                >

                    <h3 class="fw-bold mb-0">
                        Prochaines séances
                    </h3>

                    <button
                        type="button"
                        class="btn btn-outline-dark btn-sm
                               align-self-start align-self-md-center"
                        id="refresh-sessions"
                    >
                        <i class="bi bi-arrow-clockwise"></i>
                        Actualiser les places
                    </button>

                </div>

                <p
                    id="sessions-status"
                    class="small text-secondary mt-3"
                    aria-live="polite"
                >
                    Disponibilités chargées depuis le serveur.
                </p>

                <?php foreach ($sessions as $s): ?>

                    <!--
                        Une séance passe en colonne sur mobile
                        et revient en ligne à partir de md.
                    -->
                    <div
                        class="session-row d-flex
                               flex-column flex-md-row
                               justify-content-between
                               align-items-stretch align-items-md-center
                               gap-3"
                    >

                        <div class="flex-grow-1">

                            <strong class="d-block">
                                <?= date(
                                    'd/m/Y H:i',
                                    strtotime($s['starts_at'])
                                ) ?>
                            </strong>

                            <small class="d-block text-secondary">
                                <?= e($s['coach_name']) ?>
                                ·
                                <?= e($s['room']) ?>
                            </small>

                        </div>

                        <div class="text-start text-md-end">

                            <span
                                class="small d-block
                                <?= $s['remaining'] <= 3
                                    ? 'text-danger'
                                    : 'text-success' ?>"
                                data-session-remaining="<?= (int) $s['id'] ?>"
                            >
                                <?= (int) $s['remaining'] ?> places
                            </span>

                            <?php if (auth() && $s['remaining'] > 0): ?>

                                <form
                                    method="post"
                                    action="/reserver"
                                    class="mt-1"
                                >
                                    <input
                                        type="hidden"
                                        name="csrf"
                                        value="<?= csrf() ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="session_id"
                                        value="<?= (int) $s['id'] ?>"
                                    >

                                    <button
                                        class="btn btn-accent btn-sm"
                                    >
                                        Réserver
                                    </button>
                                </form>

                            <?php elseif (!auth()): ?>

                                <a
                                    class="btn btn-accent btn-sm mt-1"
                                    href="/login"
                                >
                                    Connexion
                                </a>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

                <?php if (!$sessions): ?>

                    <p class="text-secondary">
                        Aucune séance programmée.
                    </p>

                <?php endif; ?>

            </div>

        </aside>

    </div>

</main>