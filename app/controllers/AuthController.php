<?php

class AuthController
{
    public function login()
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();

            require_once __DIR__ . '/../models/User.php';

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $u = User::byEmail($email);

            if ($u && password_verify($password, $u['password'])) {
                unset($u['password']);

                session_regenerate_id(true);
                $_SESSION['user'] = $u;

                flash('success', 'Bienvenue ' . $u['firstname'] . ' !');

                redirect(
                    $u['role'] === 'admin'
                        ? '/admin'
                        : ($u['role'] === 'coach' ? '/coach' : '/')
                );
            }

            $error = 'Identifiants incorrects ou compte désactivé.';
        }

        view('auth/login', compact('error'));
    }

    public function register()
    {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();

            $firstname = trim($_POST['firstname'] ?? '');
            $lastname  = trim($_POST['lastname'] ?? '');
            $email     = trim($_POST['email'] ?? '');
            $phone     = trim($_POST['phone'] ?? '');
            $city      = trim($_POST['city'] ?? '');
            $password  = $_POST['password'] ?? '';

            // Validation précise de chaque champ
            if ($firstname === '') {
                $errors['firstname'] = 'Le prénom est obligatoire.';
            } elseif (mb_strlen($firstname) > 80) {
                $errors['firstname'] = 'Le prénom ne doit pas dépasser 80 caractères.';
            }

            if ($lastname === '') {
                $errors['lastname'] = 'Le nom est obligatoire.';
            } elseif (mb_strlen($lastname) > 80) {
                $errors['lastname'] = 'Le nom ne doit pas dépasser 80 caractères.';
            }

            if ($email === '') {
                $errors['email'] = 'L’adresse e-mail est obligatoire.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Veuillez saisir une adresse e-mail valide.';
            }

            if ($phone === '') {
    $errors['phone'] = 'Le numéro de téléphone est obligatoire.';
} elseif (!preg_match('/^(?:\+33|0)[1-9](?:[\s.-]?\d{2}){4}$/', $phone)) {
    $errors['phone'] = 'Veuillez saisir un numéro de téléphone français valide.';
}

if ($city === '') {
    $errors['city'] = 'La ville est obligatoire.';
} elseif (mb_strlen($city) > 120) {
    $errors['city'] = 'La ville ne doit pas dépasser 120 caractères.';
} else {
    require_once __DIR__ . '/../models/CityValidator.php';

    $cityExists = CityValidator::existsInFrance($city);

    if ($cityExists === false) {
        $errors['city'] = 'Cette ville française n’a pas été reconnue.';
    } elseif ($cityExists === null) {
        $errors['city'] = 'La vérification de la ville est temporairement indisponible. Veuillez réessayer.';
    }
}
            if ($password === '') {
                $errors['password'] = 'Le mot de passe est obligatoire.';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }

            // Création uniquement si tous les champs sont valides
if (empty($errors)) {
    require_once __DIR__ . '/../models/User.php';

    // Vérification explicite : un e-mail = un seul compte
    if (User::emailExists($email)) {
        $errors['email'] = 'Cette adresse e-mail est déjà utilisée.';
    } else {
        try {
            User::create($_POST);

            flash(
                'success',
                'Compte créé. Vous pouvez maintenant vous connecter.'
            );

            redirect('/login');
        } catch (Throwable $e) {
            // La contrainte UNIQUE de la base reste une seconde sécurité.
            $errors['email'] = 'Cette adresse e-mail est déjà utilisée.';
        }
    }
}
        }

        view('auth/register', compact('errors'));
    }

    public function logout()
    {
        session_destroy();
        session_start();

        flash('success', 'Vous êtes déconnecté.');

        redirect('/');
    }
}