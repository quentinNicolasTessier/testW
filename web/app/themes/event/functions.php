<?php

// Ajouter la prise en charge des images mises en avant
add_theme_support('post-thumbnails');

// Ajouter automatiquement le titre du site dans l'en-tête du site
add_theme_support('title-tag');

wp_enqueue_style('style', get_stylesheet_uri());

register_nav_menus([
    'main' => 'Menu Principal',
    'footer' => 'Bas de page',
]);
add_action('init', function () {
    register_extended_post_type('event');
});

add_action('init', function () {
    register_extended_post_type('inscription');
});

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

        // Vérifier le nombre de places disponibles ou si c'est illimité
        $nombre_places = get_field('number_place', $evenement_id);
        $inscriptions_count = compter_inscriptions($evenement_id);
        $unlimited_place = get_field('unlimited_place', $evenement_id);
        if ($unlimited_place === true  || ($unlimited_place === false && $inscriptions_count < $nombre_places)) {
            // Enregistrer l'inscription dans la base de données
            $inscription_id = wp_insert_post([
                'post_title' => $nom,
                'post_type' => 'inscription',
                'post_status' => 'publish',
                'meta_input' => [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'date_naissance' => $dateNaissance,
                    'status' => $status,
                    'email' => $email,
                    'evenement_id' => $evenement_id,
                ],
            ]);
            $billet_entree = get_field('billet');
            $url_billet = $billet_entree ? wp_get_attachment_url($billet_entree) : '';
            //Envoie du mail de comfirmation avec l'url du billet de la place si presént
            if ($billet_entree) {
                $to = $email;
                $subject = 'Confirmation d\'inscription à l\'événement';
                $body = 'Merci pour votre inscription à l\'événement. Vous pouvez télécharger votre billet d\'entrée en suivant ce lien : ' . esc_url($url_billet);
                $headers = ['Content-Type: text/html; charset=UTF-8'];
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

// Fonction pour ajouter une colonne personnalisée sur la liste des Event
function custom_add_event_column($columns)
{
    $columns['nb_place'] = 'Nombre de Place';
    $columns['nb_inscription'] = 'Nombre d\'inscription';
    return $columns;
}

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
add_filter('manage_event_posts_columns', 'custom_add_event_column');
add_filter('manage_inscription_posts_columns', 'custom_add_inscription_column');

// Fonction pour afficher le contenu de la colonne personnalisée pour les event
function custom_display_event_column($column)
{
    // Vérifier si c'est notre colonne personnalisée
    switch ($column) {
        case 'nb_place':
            // Afficher le nombre de place disponible
            $unlimited_place = get_field('unlimited_place', get_the_ID());
            if ($unlimited_place === false) {
                the_field('number_place');
            } else {
                echo 'Illimité';
            }
            break;
        case 'nb_inscription':
            // Afficher le nombre d'inscription
            echo compter_inscriptions(get_the_ID());
            break;
    }
}

// Fonction pour afficher le contenu de la colonne personnalisée pour les inscriptions
function custom_display_inscription_column($column)
{
    //Afficher tout les infos sur la personne et le nom de l'evenement
    switch ($column) {
        case 'nom':
            the_field('nom');
            break;
        case 'prenom':
            the_field('prenom');
            break;
        case 'mail':
            the_field('email');
            break;
        case 'date_naissance':
            the_field('date_naissance');
            break;
        case 'statut':
            the_field('status');
            break;
        case 'titre':
            $evenement_id = get_field('evenement_id', get_the_ID());
            $title = get_field('titre', get_post($evenement_id)->ID);
            echo $title;
            break;
    }
}


add_action('manage_inscription_posts_custom_column', 'custom_display_inscription_column', 10);
add_action('manage_event_posts_custom_column', 'custom_display_event_column', 10);
