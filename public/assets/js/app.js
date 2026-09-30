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

        // Récupération des données JSON de l'activité
        const activity = JSON.parse(btn.dataset.json);


        // Champs texte et numériques
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


        // Remonte vers le formulaire
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

    const chartCanvas =
        document.getElementById('adminChart');

    const isMobile =
        window.innerWidth < 768;


    /*
     * Sur mobile, découpe les noms longs sur plusieurs lignes
     * pour éviter que Chart.js masque certaines activités.
     */
    const formatMobileLabel = (label) => {

        if (!isMobile) {
            return label;
        }

        const words =
            String(label).split(' ');

        const lines = [];

        let currentLine = '';


        words.forEach(word => {

            const testLine =
                currentLine
                    ? currentLine + ' ' + word
                    : word;


            if (
                testLine.length > 14 &&
                currentLine
            ) {

                lines.push(currentLine);

                currentLine = word;

            } else {

                currentLine = testLine;

            }

        });


        if (currentLine) {
            lines.push(currentLine);
        }


        return lines;

    };


    // Tous les noms d'activités
    const chartLabels =
        window.pendafitnessChart.map(
            item =>
                formatMobileLabel(
                    item.title
                )
        );


    // Nombre de réservations
    const chartValues =
        window.pendafitnessChart.map(
            item =>
                Number(item.bookings)
        );


    /*
     * Sur mobile, le graphique reçoit davantage
     * de hauteur pour conserver tous les libellés.
     */
    if (isMobile) {

        chartCanvas.style.height =
            '380px';

    }


    new Chart(
        chartCanvas,
        {

            type: 'bar',

            data: {

                labels: chartLabels,

                datasets: [
                    {

                        label: 'Réservations',

                        data: chartValues

                    }
                ]

            },


            options: {

                responsive: true,

                maintainAspectRatio:
                    !isMobile,


                plugins: {

                    legend: {

                        display: false

                    },


                    /*
                     * Affiche le nom complet de l'activité
                     * lorsque l'utilisateur touche une barre.
                     */
                    tooltip: {

                        callbacks: {

                            title: function (items) {

                                if (!items.length) {
                                    return '';
                                }

                                const index =
                                    items[0].dataIndex;

                                return window
                                    .pendafitnessChart[index]
                                    .title;

                            },


                            label: function (context) {

                                return (
                                    'Réservations : ' +
                                    context.parsed.y
                                );

                            }

                        }

                    }

                },


                scales: {

                    x: {

                        ticks: {

                            /*
                             * IMPORTANT :
                             * aucune activité ne doit être
                             * automatiquement masquée.
                             */
                            autoSkip: false,


                            maxRotation:
                                isMobile
                                    ? 0
                                    : 25,


                            minRotation: 0,


                            font: {

                                size:
                                    isMobile
                                        ? 10
                                        : 12

                            }

                        },


                        grid: {

                            display: false

                        }

                    },


                    y: {

                        beginAtZero: true,


                        ticks: {

                            /*
                             * Les réservations sont des
                             * nombres entiers.
                             */
                            precision: 0,

                            stepSize: 1

                        }

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
    document.getElementById(
        'refresh-sessions'
    );


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


            refreshBtn.disabled =
                true;


            if (status) {

                status.textContent =
                    'Actualisation en cours…';

            }


            try {

                /*
                 * Requête asynchrone vers l'API
                 * des séances de l'activité.
                 */
                const response =
                    await fetch(
                        `/api/activite/${encodeURIComponent(id)}/seances`,
                        {

                            headers: {

                                Accept:
                                    'application/json'

                            },

                            cache:
                                'no-store'

                        }
                    );


                if (!response.ok) {

                    throw new Error(
                        `HTTP ${response.status}`
                    );

                }


                const payload =
                    await response.json();


                /*
                 * Mise à jour du nombre de places
                 * sans recharger toute la page.
                 */
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
                            new Date()
                                .toLocaleTimeString(
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

                refreshBtn.disabled =
                    false;

            }

        }
    );

}