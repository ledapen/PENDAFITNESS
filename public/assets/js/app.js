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

        const activity = JSON.parse(btn.dataset.json);

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


        if (form.elements.category_id) {

            form.elements.category_id.value =
                activity.category_id ?? '';

        }


        if (form.elements.level) {

            form.elements.level.value =
                activity.level ?? '';

        }


        if (form.elements.featured) {

            form.elements.featured.checked =
                Number(activity.featured) === 1;

        }


        if (form.elements.active) {

            form.elements.active.checked =
                Number(activity.active) === 1;

        }


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
    Array.isArray(window.pendafitnessChart) &&
    document.getElementById('adminChart')
) {

    const chartCanvas =
        document.getElementById('adminChart');

    const isMobile =
        window.innerWidth < 768;


    const labels =
        window.pendafitnessChart.map(
            item => item.title
        );


    const values =
        window.pendafitnessChart.map(
            item => Number(item.bookings)
        );


    /*
     * MOBILE
     * --------------------------------------------------------
     * Le graphique devient horizontal.
     *
     * Chaque activité dispose de sa propre ligne :
     * aucun nom n'est supprimé et les libellés ne sont plus
     * empilés les uns sur les autres.
     */
    if (isMobile) {

        const height =
            Math.max(
                320,
                labels.length * 55
            );

        chartCanvas.parentElement.style.height =
            `${height}px`;

        chartCanvas.style.height =
            '100%';

        chartCanvas.style.width =
            '100%';

    }


    new Chart(
        chartCanvas,
        {

            type: 'bar',


            data: {

                labels: labels,

                datasets: [
                    {

                        label: 'Réservations',

                        data: values

                    }
                ]

            },


            options: {

                /*
                 * indexAxis = y :
                 * les activités sont placées verticalement
                 * sur téléphone.
                 *
                 * Sur ordinateur le graphique conserve
                 * son affichage classique.
                 */
                indexAxis:
                    isMobile
                        ? 'y'
                        : 'x',


                responsive: true,


                /*
                 * Sur mobile, Chart.js utilise la hauteur
                 * que nous avons définie ci-dessus.
                 */
                maintainAspectRatio:
                    !isMobile,


                animation: {

                    duration: 400

                },


                plugins: {

                    legend: {

                        display: false

                    },


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

                                const value =
                                    isMobile
                                        ? context.parsed.x
                                        : context.parsed.y;

                                return (
                                    'Réservations : ' +
                                    value
                                );

                            }

                        }

                    }

                },


                scales: isMobile

                    ? {

                        /*
                         * MOBILE :
                         * axe horizontal = nombre
                         * de réservations.
                         */
                        x: {

                            beginAtZero: true,

                            ticks: {

                                precision: 0,

                                stepSize: 1

                            },

                            title: {

                                display: true,

                                text: 'Réservations'

                            }

                        },


                        /*
                         * MOBILE :
                         * axe vertical = activités.
                         */
                        y: {

                            ticks: {

                                autoSkip: false,

                                font: {

                                    size: 10

                                }

                            },

                            grid: {

                                display: false

                            }

                        }

                    }

                    : {

                        /*
                         * ORDINATEUR :
                         * conservation du graphique vertical.
                         */
                        x: {

                            ticks: {

                                autoSkip: false,

                                maxRotation: 25,

                                minRotation: 0,

                                font: {

                                    size: 12

                                }

                            },

                            grid: {

                                display: false

                            }

                        },


                        y: {

                            beginAtZero: true,

                            ticks: {

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


            refreshBtn.disabled = true;


            if (status) {

                status.textContent =
                    'Actualisation en cours…';

            }


            try {

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

                refreshBtn.disabled = false;

            }

        }
    );

}