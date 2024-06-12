<?php

require_once __DIR__ . '/../Model/Event.php';
require_once __DIR__ . '/../Model/Inscription.php';

use Model\Event;
use Model\Inscription;

add_action('init', function () {
    $args = [
        'label' => 'inscription',
        'public' => true,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => false,
        'rewrite' => false,
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => ['title', 'editor', 'thumbnail'],
    ];
    register_extended_post_type('inscription', $args);
});

// Fonction pour ajouter une colonne personnalisée sur la liste des inscriptions
function custom_add_inscription_column($columns)
{
    // Ajouter une nouvelle colonne avec le titre "Custom Column"
    $columns['nom'] = 'Nom';
    $columns['prenom'] = 'Prenom';
    $columns['mail'] = 'Email';
    $columns['date_naissance'] = 'Date de naissance';
    $columns['statut'] = 'Statut';
    $columns['titre'] = 'Titre de l\'evenement';
    return $columns;
}

add_filter('manage_inscription_posts_columns', 'custom_add_inscription_column');

// Compter les inscriptions pour un événement donné
function compter_inscriptions($evenement_id)
{
    $args = [
        'post_type' => 'inscription',
        'meta_query' => [
            [
                'key' => 'evenement_id',
                'value' => $evenement_id,
                'compare' => '='
            ]
        ]
    ];

    $inscriptions = new WP_Query($args);
    return $inscriptions->found_posts;
}

// Gérer la soumission du formulaire d'inscription à l'événement

function gerer_inscription_evenement()
{
    // Vérifier la présence et la validité des champs
    if (isset($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['date_naissance'], $_POST['status'], $_POST['evenement_id']) && is_email($_POST['email'])) {
        $nom = sanitize_text_field($_POST['nom']);
        $prenom = sanitize_text_field($_POST['prenom']);
        $dateNaissance = $_POST['date_naissance'];
        $status = $_POST['status'];
        $email = sanitize_email($_POST['email']);
        $evenement_id = intval($_POST['evenement_id']);
        $inscription = new Inscription($nom, $prenom, $email, $dateNaissance, $status, $evenement_id);

        // Vérifier le nombre de places disponibles ou si c'est illimité
        $event = new Event("", "", "", "", "", get_field('billet', $evenement_id), get_field('unlimited_place', $evenement_id));
        if ($event->isPlaceIllimite() === false) {
            $event->setNombrePlace(get_field('number_place', $evenement_id));
        }
        $inscriptions_count = compter_inscriptions($evenement_id);
        if ($event->isPlaceIllimite() === true  || ($event->isPlaceIllimite() === false && $inscriptions_count < $event->getNombrePlace())) {
            // Enregistrer l'inscription dans la base de données
            $inscription_id = wp_insert_post([
                'post_title' => $nom,
                'post_type' => 'inscription',
                'post_status' => 'publish',
                'meta_input' => [
                    'nom' => $inscription->getNom(),
                    'prenom' => $inscription->getPrenom(),
                    'date_naissance' => $inscription->getDateNaissance(),
                    'status' => $inscription->getStatut(),
                    'email' => $inscription->getEmail(),
                    'evenement_id' => $inscription->getEventId(),
                ],
            ]);
            $url_billet = $event->getBillet() ? wp_get_attachment_url($event->getBillet()) : '';
            //Envoie du mail de comfirmation avec l'url du billet de la place si presént
            $to = $email;
            $subject = 'Confirmation d\'inscription à l\'événement';
            $body = 'Merci pour votre inscription à l\'événement.';
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            if ($url_billet) {
                $body .= ' Veuillez trouver ci-joint votre billet d\'entrée';
                wp_mail($to, $subject, $body, $headers, [get_attached_file($event->getBillet())]);
            } else {
                $body .= 'Les billets ne sont pas encore disponible';
                wp_mail($to, $subject, $body, $headers);
            }
            // Rediriger vers une page de confirmation
            wp_redirect(add_query_arg('inscription_event', 'success', get_permalink($evenement_id)));
            exit;
        } else {
            // Rediriger si l'événement est complet
            wp_redirect(add_query_arg('inscription_event', 'complet', get_permalink($evenement_id)));
            exit;
        }
    }
}

add_action('admin_post_inscription_evenement', 'gerer_inscription_evenement');
add_action('admin_post_nopriv_inscription_evenement', 'gerer_inscription_evenement');

// Fonction pour afficher le contenu de la colonne personnalisée pour les inscriptions
function custom_display_inscription_column($column): void
{
    $inscription = new Inscription(get_field("nom", get_the_ID()), get_field("prenom", get_the_ID()), get_field("email", get_the_ID()), get_field("date_naissance", get_the_ID()), get_field("status", get_the_ID()), get_field('evenement_id', get_the_ID()));
    //Afficher tout les infos sur la personne et le nom de l'evenement
    switch ($column) {
        case 'nom':
            echo $inscription->getNom();
            break;
        case 'prenom':
            echo $inscription->getPrenom();
            break;
        case 'mail':
            echo $inscription->getEmail();
            break;
        case 'date_naissance':
            echo $inscription->getDateNaissance();
            break;
        case 'statut':
            echo $inscription->getStatut();
            break;
        case 'titre':
            $event = new Event(get_field('titre', get_post($inscription->getEventId())->ID), "", "", "", "", "", false);
            echo $event->getTitre();
            break;
    }
}
add_action('manage_inscription_posts_custom_column', 'custom_display_inscription_column', 10);
