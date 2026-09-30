<main class="container py-5">

    <div class="dashboard-head">
        <div>
            <span class="eyebrow">ADMINISTRATION</span>
            <h1 class="fw-bold">Pilotage PENDAFITNESS</h1>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="/coach/seances" class="btn btn-outline-dark">
                <i class="bi bi-calendar-week"></i>
                Programmer les séances
            </a>

            <a href="/admin/equipe" class="btn btn-accent">
                <i class="bi bi-person-plus"></i>
                Gérer les coachs
            </a>
        </div>
    </div>

    <div class="row g-3 mb-5">
        <?php
        foreach ([
            ['Utilisateurs', $stats['users'], 'bi-people'],
            ['Activités', $stats['activities'], 'bi-lightning'],
            ['Réservations', $stats['reservations'], 'bi-calendar-check'],
            [
                'Chiffre d’affaires',
                number_format($stats['revenue'], 2, ',', ' ') . ' €',
                'bi-cash-stack'
            ]
        ] as $s):
        ?>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <i class="bi <?= $s[2] ?>"></i>

                    <div>
                        <span><?= $s[0] ?></span>
                        <strong><?= $s[1] ?></strong>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form-card">
        <h3 class="fw-bold">Réservations par activité</h3>
        <canvas id="adminChart" height="100"></canvas>
    </div>

    <script>
        window.pendafitnessChart =
            <?= json_encode($chart, JSON_UNESCAPED_UNICODE) ?>;
    </script>

</main>