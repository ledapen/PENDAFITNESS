// ============================================================
// PENDAFITNESS - JavaScript principal
// ============================================================


// ------------------------------------------------------------
// 1. AFFICHER / MASQUER LES MOTS DE PASSE
// ------------------------------------------------------------

document.querySelectorAll('.password-toggle').forEach(btn => {

    btn.setAttribute('aria-label', 'Afficher le mot de passe');

    btn.addEventListener('click', () => {

        const input = document.getElementById(btn.dataset.target);

        if (!input) {
            return;
        }

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
// 2. MODIFICATION D'UNE ACTIVITÉ
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
// 3. GRAPHIQUE DU TABLEAU DE BORD ADMINISTRATEUR
// ------------------------------------------------------------

if (
    Array.isArray(window.pendafitnessChart) &&
    document.getElementById('adminChart')
) {

    const canvas =
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


    // --------------------------------------------------------
    // Taille du graphique sur téléphone
    // --------------------------------------------------------

    if (isMobile) {

        canvas.style.width = '100%';

        canvas.style.height = '280px';

        canvas.style.maxHeight = '280px';

    }


    // --------------------------------------------------------
    // Création du graphique
    // --------------------------------------------------------

    new Chart(canvas, {

        type: 'bar',


        data: {

            labels: labels,


            datasets: [{

                label: 'Réservations',

                data: values,

                /*
                 * La barre ne remplit pas toute la case.
                 * Elle reste bien au centre.
                 */
                categoryPercentage: 0.72,

                barPercentage: 0.72

            }]

        },


        options: {

            responsive: true,


            /*
             * Sur mobile, la hauteur définie plus haut
             * est utilisée.
             */
            maintainAspectRatio: !isMobile,


            /*
             * Évite les mouvements du graphique
             * pendant son affichage.
             */
            animation: false,


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

                            return labels[
                                items[0].dataIndex
                            ];

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


                // =================================================
                // AXE X
                //
                // Une activité = une case.
                // Les traits verticaux délimitent les cases.
                // La barre et le nom sont centrés dans la case.
                // =================================================

                x: {

                    /*
                     * Centre les catégories entre
                     * deux lignes de grille.
                     */
                    offset: true,


                    grid: {

                        /*
                         * Affiche les traits verticaux.
                         */
                        display: true,

                        drawOnChartArea: true,

                        drawTicks: true,


                        /*
                         * TRÈS IMPORTANT :
                         * les lignes sont placées entre
                         * les catégories et non au milieu
                         * des barres.
                         */
                        offset: true

                    },


                    ticks: {

                        display: true,


                        /*
                         * Les 7 activités doivent rester
                         * affichées.
                         */
                        autoSkip: false,


                        /*
                         * Chaque nom est centré sous
                         * la case correspondante.
                         */
                        align: 'center',


                        /*
                         * Sur téléphone les textes sont
                         * inclinés pour tenir dans la largeur.
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


                        padding: 4

                    }

                },


                // =================================================
                // AXE Y
                //
                // 0,0
                // 0,5
                // 1,0
                // 1,5
                // 2,0
                // 2,5
                // 3,0
                // 3,5
                // 4,0
                // =================================================

                y: {

                    type: 'linear',


                    beginAtZero: true,


                    /*
                     * Même échelle que le graphique
                     * de référence.
                     */
                    min: 0,

                    max: 4,


                    bounds: 'ticks',


                    grid: {

                        /*
                         * Affiche toutes les lignes
                         * horizontales.
                         */
                        display: true,

                        drawOnChartArea: true,

                        drawTicks: true,

                        offset: false

                    },


                    ticks: {

                        display: true,


                        /*
                         * Empêche Chart.js de supprimer
                         * certaines graduations sur mobile.
                         */
                        autoSkip: false,


                        /*
                         * Une graduation tous les 0,5.
                         */
                        stepSize: 0.5,


                        precision: 1,


                        font: {

                            size:
                                isMobile
                                    ? 9
                                    : 12

                        },


                        /*
                         * Format :
                         *
                         * 0,0
                         * 0,5
                         * 1,0
                         * 1,5
                         * ...
                         * 4,0
                         */
                        callback: function (value) {

                            return Number(value)
                                .toFixed(1)
                                .replace('.', ',');

                        }

                    }

                }

            }

        }

    });

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