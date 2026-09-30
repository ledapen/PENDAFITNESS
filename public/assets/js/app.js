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
// 2. MODIFICATION D'UNE ACTIVITÉ
// ------------------------------------------------------------

document.addEventListener('click', function (event) {

    const btn = event.target.closest('.edit-activity');

    if (!btn) return;

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
// 3. GRAPHIQUE ADMIN
// Même structure ordinateur / téléphone
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


    // Taille stable sur téléphone
    if (isMobile) {

        canvas.style.width = '100%';
        canvas.style.height = '280px';
        canvas.style.maxHeight = '280px';

    }


    new Chart(canvas, {

        type: 'bar',

        data: {

            labels: labels,

            datasets: [{
                label: 'Réservations',
                data: values
            }]
        },

        options: {

            responsive: true,

            maintainAspectRatio: !isMobile,

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

                // ---------------------------------------------
                // AXE X : ACTIVITÉS + TRAITS VERTICAUX
                // ---------------------------------------------

                x: {

                    offset: true,

                    grid: {

                        display: true,

                        drawOnChartArea: true,

                        drawTicks: true,

                        offset: false

                    },

                    ticks: {

                        display: true,

                        autoSkip: false,

                        minRotation:
                            isMobile ? 55 : 0,

                        maxRotation:
                            isMobile ? 55 : 25,

                        font: {

                            size:
                                isMobile ? 7 : 12
                        },

                        padding: 3
                    }
                },


                // ---------------------------------------------
                // AXE Y : 0 → 4 PAR PAS DE 0,5
                // ---------------------------------------------

                y: {

                    type: 'linear',

                    beginAtZero: true,

                    min: 0,

                    max: 4,

                    bounds: 'ticks',

                    grid: {

                        display: true,

                        drawOnChartArea: true,

                        drawTicks: true,

                        offset: false

                    },

                    ticks: {

                        display: true,

                        autoSkip: false,

                        min: 0,

                        max: 4,

                        stepSize: 0.5,

                        precision: 1,

                        font: {

                            size:
                                isMobile ? 9 : 12
                        },

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
// 4. ACTUALISATION ASYNCHRONE DES PLACES
// ------------------------------------------------------------

const refreshBtn =
    document.getElementById('refresh-sessions');


if (refreshBtn) {

    refreshBtn.addEventListener('click', async () => {

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

        if (!id) return;

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

            payload.sessions.forEach(session => {

                const el =
                    document.querySelector(
                        `[data-session-remaining="${session.id}"]`
                    );

                if (!el) return;

                const remaining =
                    Number(session.remaining);

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
            });


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
    });
}