CREATE DATABASE IF NOT EXISTS urbanfit
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE urbanfit;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS
    favorites,
    reviews,
    reservations,
    sessions,
    session_schedules,
    activities,
    sport_categories,
    contacts,
    opening_hours,
    users,
    roles;

SET FOREIGN_KEY_CHECKS = 1;


-- =========================================================
-- RÔLES ET UTILISATEURS
-- =========================================================

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) UNIQUE NOT NULL
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    firstname VARCHAR(80) NOT NULL,
    lastname VARCHAR(80) NOT NULL,
    email VARCHAR(190) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    city VARCHAR(100),
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (role_id)
        REFERENCES roles(id)
);


-- =========================================================
-- CATÉGORIES ET ACTIVITÉS
-- =========================================================

CREATE TABLE sport_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(80) UNIQUE NOT NULL,
    icon VARCHAR(50) DEFAULT 'bi-activity'
);

CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    short_description VARCHAR(255),
    description TEXT,
    level VARCHAR(40),
    duration_minutes INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(500),
    location VARCHAR(150) DEFAULT 'Paris',
    featured TINYINT(1) DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id)
        REFERENCES sport_categories(id)
);


-- =========================================================
-- PROGRAMMATIONS RÉCURRENTES
-- 1 = lundi ... 7 = dimanche
-- =========================================================

CREATE TABLE session_schedules (
    id INT NOT NULL AUTO_INCREMENT,
    activity_id INT NOT NULL,
    day_of_week TINYINT NOT NULL,
    start_time TIME NOT NULL,
    duration_minutes INT NOT NULL DEFAULT 60,
    capacity INT NOT NULL DEFAULT 12,
    coach_name VARCHAR(120) NOT NULL,
    room VARCHAR(80) DEFAULT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_schedule_activity (activity_id),

    FOREIGN KEY (activity_id)
        REFERENCES activities(id)
        ON DELETE CASCADE
);


-- =========================================================
-- SÉANCES RÉELLES RÉSERVABLES
-- =========================================================

CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,

    -- NULL = séance ponctuelle
    -- renseigné = séance générée par une récurrence
    schedule_id INT NULL,

    starts_at DATETIME NOT NULL,
    duration_minutes INT NOT NULL,
    capacity INT NOT NULL,
    coach_name VARCHAR(120),
    room VARCHAR(80),

    status VARCHAR(20)
        NOT NULL
        DEFAULT 'scheduled',

    cancellation_reason VARCHAR(255) NULL,

    KEY idx_sessions_schedule (schedule_id),
    KEY idx_sessions_status (status),
    KEY idx_sessions_activity_date (activity_id, starts_at),

    FOREIGN KEY (activity_id)
        REFERENCES activities(id)
        ON DELETE CASCADE,

    FOREIGN KEY (schedule_id)
        REFERENCES session_schedules(id)
        ON DELETE SET NULL
);


-- =========================================================
-- RÉSERVATIONS
-- =========================================================

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id INT NOT NULL,

    status ENUM(
        'confirmed',
        'cancelled',
        'completed'
    ) DEFAULT 'confirmed',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (user_id, session_id),

    FOREIGN KEY (user_id)
        REFERENCES users(id),

    FOREIGN KEY (session_id)
        REFERENCES sessions(id)
        ON DELETE CASCADE
);


-- =========================================================
-- AVIS
-- =========================================================

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,

    status ENUM(
        'pending',
        'approved',
        'rejected'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id),

    FOREIGN KEY (activity_id)
        REFERENCES activities(id)
);


-- =========================================================
-- FAVORIS
-- =========================================================

CREATE TABLE favorites (
    user_id INT NOT NULL,
    activity_id INT NOT NULL,

    PRIMARY KEY (user_id, activity_id),

    FOREIGN KEY (user_id)
        REFERENCES users(id),

    FOREIGN KEY (activity_id)
        REFERENCES activities(id)
        ON DELETE CASCADE
);


-- =========================================================
-- CONTACT
-- =========================================================

CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    subject VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,

    status ENUM(
        'new',
        'processed'
    ) DEFAULT 'new',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- HORAIRES D'OUVERTURE
-- =========================================================

CREATE TABLE opening_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_name VARCHAR(20) UNIQUE NOT NULL,
    open_time TIME NULL,
    close_time TIME NULL,
    closed TINYINT(1) DEFAULT 0
);


-- =========================================================
-- DONNÉES INITIALES
-- =========================================================

INSERT INTO roles(name)
VALUES
    ('member'),
    ('coach'),
    ('admin');


INSERT INTO sport_categories(name, slug, icon)
VALUES
    ('Fitness', 'fitness', 'bi-lightning-charge'),
    ('Boxe', 'boxe', 'bi-shield'),
    ('Yoga', 'yoga', 'bi-flower1'),
    ('Cross-training', 'cross-training', 'bi-fire'),
    ('Natation', 'natation', 'bi-water'),
    ('Running', 'running', 'bi-person-walking');


INSERT INTO activities (
    category_id,
    title,
    short_description,
    description,
    level,
    duration_minutes,
    price,
    image_url,
    location,
    featured
)
VALUES

(
    1,
    'HIIT Energy',
    'Une séance explosive pour travailler cardio et puissance.',
    'Circuit complet alternant effort intense et récupération. Le coach adapte chaque mouvement au niveau du groupe.',
    'Intermédiaire',
    45,
    18.00,
    'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=1200&q=80',
    'Paris 12e',
    1
),

(
    2,
    'Boxe anglaise - Foundations',
    'Apprenez les bases : garde, déplacements et enchaînements.',
    'Une séance technique accessible avec échauffement, travail aux pattes d’ours et retour au calme.',
    'Débutant',
    60,
    22.00,
    'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?auto=format&fit=crop&w=1200&q=80',
    'Paris 11e',
    1
),

(
    3,
    'Yoga Sunset Flow',
    'Mobilité, respiration et équilibre dans une ambiance apaisante.',
    'Un flow progressif pour délier le corps et améliorer la mobilité. Tapis disponible sur place.',
    'Débutant',
    60,
    16.00,
    'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=1200&q=80',
    'Vigneux-sur-Seine',
    1
),

(
    4,
    'Cross Training Power',
    'Force, cardio et esprit d’équipe.',
    'Atelier fonctionnel en petits groupes : rameur, kettlebells, squats et exercices au poids du corps.',
    'Avancé',
    50,
    24.00,
    'https://images.unsplash.com/photo-1534367610401-9f5ed68180aa?auto=format&fit=crop&w=1200&q=80',
    'Créteil',
    1
),

(
    5,
    'Aqua Performance',
    'Travail complet en bassin pour progresser sans impact.',
    'Séance encadrée axée endurance, technique et renforcement aquatique.',
    'Intermédiaire',
    45,
    20.00,
    'https://images.unsplash.com/photo-1530549387789-4c1017266635?auto=format&fit=crop&w=1200&q=80',
    'Athis-Mons',
    0
),

(
    6,
    'Urban Running Club',
    'Courez en groupe et progressez à votre rythme.',
    'Sortie urbaine encadrée avec échauffement, blocs d’allure et récupération.',
    'Débutant',
    60,
    10.00,
    'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1200&q=80',
    'Paris 13e',
    0
);


-- =========================================================
-- PROGRAMMATIONS PAR DÉFAUT
--
-- Plus de séances figées à NOW() + 2 / + 5 jours.
-- Ces règles sont permanentes et Session.php maintient
-- automatiquement les occurrences des 90 prochains jours.
-- =========================================================

INSERT INTO session_schedules (
    activity_id,
    day_of_week,
    start_time,
    duration_minutes,
    capacity,
    coach_name,
    room
)
VALUES
    (1, 1, '18:00:00', 45, 14, 'Sarah M.', 'Studio A'),
    (1, 4, '19:00:00', 45, 12, 'Sarah M.', 'Studio A'),

    (2, 2, '18:30:00', 60, 12, 'David K.', 'Studio B'),
    (2, 6, '11:00:00', 60, 12, 'David K.', 'Studio B'),

    (3, 3, '18:00:00', 60, 14, 'Emma L.', 'Studio Zen'),
    (3, 7, '10:00:00', 60, 14, 'Emma L.', 'Studio Zen'),

    (4, 2, '19:30:00', 50, 12, 'Thomas R.', 'Training Zone'),
    (4, 5, '18:30:00', 50, 12, 'Thomas R.', 'Training Zone'),

    (5, 3, '19:00:00', 45, 12, 'Julie P.', 'Bassin'),
    (5, 6, '09:30:00', 45, 12, 'Julie P.', 'Bassin'),

    (6, 4, '18:30:00', 60, 20, 'Marc D.', 'Extérieur'),
    (6, 7, '09:00:00', 60, 20, 'Marc D.', 'Extérieur');


INSERT INTO opening_hours (
    day_name,
    open_time,
    close_time,
    closed
)
VALUES
    ('Lundi', '07:00', '22:00', 0),
    ('Mardi', '07:00', '22:00', 0),
    ('Mercredi', '07:00', '22:00', 0),
    ('Jeudi', '07:00', '22:00', 0),
    ('Vendredi', '07:00', '21:00', 0),
    ('Samedi', '08:00', '20:00', 0),
    ('Dimanche', '09:00', '18:00', 0);