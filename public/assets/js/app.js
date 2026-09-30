// ============================================================
// PENDAFITNESS - JavaScript principal
// ============================================================


// ------------------------------------------------------------
// 1. AFFICHER / MASQUER LES MOTS DE PASSE
// ------------------------------------------------------------

document.querySelectorAll('.password-toggle').forEach(btn => {

    btn.setAttribute(
        'aria-label',
        'Afficher le mot de passe'
    );

    btn.addEventListener('click', () => {

        const input =
            document.getElementById(
                btn.dataset.target
            );

        if (!input) {
            return;
        }

        const show =
            input.type === 'password';

        input.type =
            show ? 'text' : 'password';

        const icon =
            btn.querySelector('i');

        if (icon) {

            icon.className =
                show
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
// 2. MODIFICATION D'UNE ACTIVITÉ
// ------------------------------------------------------------

document.addEventListener(
    'click',
    function (event) {

        const btn =
            event.target.closest(
                '.edit-activity'
            );

        if (!btn) {
            return;
        }

        event.preventDefault();

        const form =
            document.querySelector(
                'form[action="/coach/activites/save"]'
            );

        if (!form) {

            console.error(
                'PENDAFITNESS : formulaire activité introuvable.'
            );

            return;
        }

        try {

            const activity =
                JSON.parse(
                    btn.dataset.json
                );


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
                    Number(
                        activity.featured
                    ) === 1;

            }


            if (form.elements.active) {

                form.elements.active.checked =
                    Number(
                        activity.active
                    ) === 1;

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

    }
);


// ------------------------------------------------------------
// 3. GRAPHIQUE DU TABLEAU DE BORD ADMINISTRATEUR
// ------------------------------------------------------------

if (
    Array.isArray(
        window.pendafitnessChart
    ) &&
    document.getElementById(
        'adminChart'
    )
) {

    const chartCanvas =
        document.getElementById(
            'adminChart'
        );


    const isMobile =
        window.innerWidth < 768;


    const labels =
        window.pendafitnessChart.map(
            item => item.title
        );


    const values =
        window.pendafitnessChart.map(
            item => Number(
                item.bookings
            )
        );


    /*
     * Même graphique vertical
     * sur ordinateur et téléphone.
     *
     * On ne change PAS indexAxis.
     */
    if (isMobile) {

        /*
         * Hauteur raisonnable.
         * On évite le graphique très long
         * et les redimensionnements excessifs.
         */
        chartCanvas.style.height =
            '260px';

        chartCanvas.style.maxHeight =
            '260px';

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

                        label:
                            'Réservations',

                        data:
                            values

                    }
                ]

            },


            options: {

                responsive: true,


                /*
                 * Sur téléphone nous utilisons
                 * la hauteur définie plus haut.
                 */
                maintainAspectRatio:
                    !isMobile,


                animation: {

                    duration: 300

                },


                plugins: {

                    /*
                     * Pas besoin de légende :
                     * le titre indique déjà
                     * qu'il s'agit des réservations.
                     */
                    legend: {

                        display: false

                    },


                    /*
                     * Au toucher d'une barre,
                     * affichage du nom complet
                     * de l'activité.
                     */
                    tooltip: {

                        callbacks: {

                            title:
                                function (
                                    items
                                ) {

                                    if (
                                        !items.length
                                    ) {

                                        return '';

                                    }

                                    return labels[
                                        items[0]
                                            .dataIndex
                                    ];

                                },


                            label:
                                function (
                                    context
                                ) {

                                    return (
                                        'Réservations : ' +
                                        context
                                            .parsed
                                            .y
                                    );

                                }

                        }

                    }

                },


                scales: {

                    /*
                     * AXE DES ACTIVITÉS
                     */
                    x: {

                        grid: {

                            display: false

                        },


                        ticks: {

                            /*
                             * IMPORTANT :
                             * aucune activité
                             * n'est supprimée.
                             */
                            autoSkip: false,


                            /*
                             * Sur téléphone,
                             * les intitulés sont
                             * inclinés pour pouvoir
                             * conserver les 7 activités.
                             */
                            minRotation:
                                isMobile
                                    ? 55
                                    : 0,


                            maxRotation:
                                isMobile
                                    ? 55
                                    : 25,


                            font: {

                                size:
                                    isMobile
                                        ? 7
                                        : 12

                            },


                            padding:
                                isMobile
                                    ? 2
                                    : 4

                        }

                    },


                    /*
                     * AXE DU NOMBRE
                     * DE RÉSERVATIONS
                     */
                    y: {

                        beginAtZero: true,


                        ticks: {

                            /*
                             * Pas de 1,5 ou 2,5 :
                             * une réservation est
                             * toujours un entier.
                             */
                            precision: 0,

                            stepSize: 1,


                            font: {

                                size:
                                    isMobile
                                        ? 9
                                        : 12

                            }

                        }

                    }

                }

            }

        }
    );

}


// ------------------------------------------------------------
// 4. ACTUALISATION ASYNCHRONE DES PLACES DISPONIBLES
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


                        if (!el) {
                            return;
                        }


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
                );


                if (status) {

                    status.textContent =
                        `Disponibilités actualisées à ${
                            new Date()
                                .toLocaleTimeString(
                                    'fr-FR',
                                    {

                                        hour:
                                            '2-digit',

                                        minute:
                                            '2-digit',

                                        second:
                                            '2-digit'

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