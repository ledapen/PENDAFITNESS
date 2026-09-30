<?php

class HomeController
{
    public function index()
    {
        require_once __DIR__ . '/../models/Activity.php';
        require_once __DIR__ . '/../models/Review.php';

        $activities = array_slice(
            Activity::all(),
            0,
            6
        );

        $categories = Activity::categories();

        $reviews = Review::approved();

        view(
            'home/index',
            compact(
                'activities',
                'categories',
                'reviews'
            )
        );
    }


    public function contact()
    {
        /*
         * La variable est initialisée avant tout traitement.
         * Elle existe donc aussi lors d'un simple GET /contact.
         */
        $error = null;


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            check_csrf();

            require_once __DIR__ . '/../models/Contact.php';


            $email = trim(
                $_POST['email'] ?? ''
            );

            $message = trim(
                $_POST['message'] ?? ''
            );


            /*
             * Validation côté serveur.
             */
            if (
                !filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
                ||
                $message === ''
            ) {

                $error =
                    'Merci de vérifier les informations saisies.';

            } else {

                Contact::create($_POST);

                flash(
                    'success',
                    'Votre message a bien été envoyé.'
                );

                redirect('/contact');
            }
        }


        view(
            'home/contact',
            compact('error')
        );
    }
}