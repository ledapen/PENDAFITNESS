// ============================================================
// PENDAFITNESS - JavaScript principal
// ============================================================


// ------------------------------------------------------------
// 1. Afficher / masquer les mots de passe
// ------------------------------------------------------------

document.querySelectorAll('.password-toggle').forEach(btn => {

    btn.setAttribute('aria-label', 'Afficher le mot de passe');

    btn.addEventListener('click', () => {

        const input = document.getElementById(btn.dataset.target);

        if (!input) return;

        const show = input.type === 'password';

        input.type = show ? 'text' : 'password';

        const icon = btn.querySelector('i');

        if (icon) {
            icon.className = show
                ? 'bi bi-eye-slash'
                : 'bi bi-eye';
        }

        btn.setAttribute(
            'aria-label',
            show
                ? 'Masquer le mot de passe'
                : 'Afficher le mot de passe'
        );
    });
});


// ------------------------------------------------------------
// 2. Modification d'une activité depuis l'espace coach
// ------------------------------------------------------------

document.addEventListener('click', function (event) {

    const btn = event.target.closest('.edit-activity');

    if (!btn) {
        return;
    }

    event.preventDefault();

    const form = document.querySelector(
        'form[action="/coach/activites/save"]'
    );

    if (!form) {
        console.error(
            'PENDAFITNESS : formulaire activité introuvable.'
        );
        return;
    }

    try {

        // Récupération des données JSON stockées dans le bouton
        const activity = JSON.parse(btn.dataset.json);

        // Remplissage des champs principaux
        const fields = [
            'id',
            'title',
            'short_description',
            'description',
            'price',
            'duration_minutes',
            'location',
            'image_url'
        ];

        fields.forEach(name => {

            if (form.elements[name]) {
                form.elements[name].value =
                    activity[name] ?? '';
            }

        });


        // Catégorie
        if (form.elements.category_id) {
            form.elements.category_id.value =
                activity.category_id ?? '';
        }


        // Niveau
        if (form.elements.level) {
            form.elements.level.value =
                activity.level ?? '';
        }


        // Activité mise à la une
        if (form.elements.featured) {

            form.elements.featured.checked =
                Number(activity.featured) === 1;

        }


        // Activité active / inactive
        if (form.elements.active) {

            form.elements.active.checked =
                Number(activity.active) === 1;

        }


        // Remonter automatiquement vers le formulaire
        form.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });


    } catch (error) {

        console.error(
            'PENDAFITNESS - erreur modification activité :',
            error
        );

    }

});


// ------------------------------------------------------------
// 3. Graphique du tableau de bord administrateur
// ------------------------------------------------------------

if (
    window.pendafitnessChart &&
    document.getElementById('adminChart')
) {

    new Chart(
        document.getElementById('adminChart'),
        {
            type: 'bar',

            data: {

                labels: window.pendafitnessChart.map(
                    x => x.title
                ),

                datasets: [
                    {
                        label: 'Réservations',

                        data: window.pendafitnessChart.map(
                            x => x.bookings
                        )
                    }
                ]

            },

            options: {

                responsive: true,

                plugins: {

                    legend: {
                        display: false
                    }

                },

                scales: {

                    y: {
                        beginAtZero: true
                    }

                }

            }

        }
    );

}


// ------------------------------------------------------------
// 4. Actualisation asynchrone des places disponibles
// ------------------------------------------------------------

const refreshBtn =
    document.getElementById('refresh-sessions');


if (refreshBtn) {

    refreshBtn.addEventListener(
        'click',
        async () => {

            const box =
                refreshBtn.closest(
                    '[data-activity-id]'
                );

            const status =
                document.getElementById(
                    'sessions-status'
                );

            const id =
                box?.dataset.activityId;


            if (!id) {
                return;
            }


            refreshBtn.disabled = true;


            if (status) {
                status.textContent =
                    'Actualisation en cours…';
            }


            try {

                const response = await fetch(
                    `/api/activite/${encodeURIComponent(id)}/seances`,
                    {
                        headers: {
                            Accept: 'application/json'
                        },

                        cache: 'no-store'
                    }
                );


                if (!response.ok) {

                    throw new Error(
                        `HTTP ${response.status}`
                    );

                }


                const payload =
                    await response.json();


                payload.sessions.forEach(
                    session => {

                        const el =
                            document.querySelector(
                                `[data-session-remaining="${session.id}"]`
                            );


                        if (el) {

                            const remaining =
                                Number(
                                    session.remaining
                                );


                            el.textContent =
                                `${remaining} places`;


                            el.classList.toggle(
                                'text-danger',
                                remaining <= 3
                            );


                            el.classList.toggle(
                                'text-success',
                                remaining > 3
                            );

                        }

                    }
                );


                if (status) {

                    status.textContent =
                        `Disponibilités actualisées à ${
                            new Date().toLocaleTimeString(
                                'fr-FR',
                                {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit'
                                }
                            )
                        }.`;

                }


            } catch (error) {

                if (status) {

                    status.textContent =
                        'Impossible d’actualiser les places. Réessayez dans un instant.';

                }


                console.error(
                    'PENDAFITNESS sessions fetch',
                    error
                );


            } finally {

                refreshBtn.disabled = false;

            }

        }
    );

}