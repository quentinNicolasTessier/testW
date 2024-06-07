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
    register_extended_post_type('inscription event');
});

// Compter les inscriptions pour un événement donné
function compter_inscriptions($evenement_id)
{
    $args = [
        'post_type' => 'inscription event',
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

        // Vérifier le nombre de places disponibles
        $nombre_places = get_field('number_place', $evenement_id);
        $inscriptions_count = compter_inscriptions($evenement_id);
        $places_restantes = get_field('number_available_place', $evenement_id);
        $unlimited_place = get_field('unlimited_place', $evenement_id);
        if ($unlimited_place === true  || ($unlimited_place === false && $inscriptions_count < $nombre_places)) {
            // Enregistrer l'inscription dans la base de données
            $inscription_id = wp_insert_post([
                'post_title' => $nom,
                'post_type' => 'inscription event',
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
            if ($unlimited_place === false) {
                update_field('number_available_place', $places_restantes - 1, $evenement_id);
            }
            $billet_entree = get_field('billet');
            $url_billet = $billet_entree ? wp_get_attachment_url($billet_entree) : '';
            if ($billet_entree) {
                $to = $email;
                $subject = 'Confirmation d\'inscription à l\'événement';
                $body = 'Merci pour votre inscription à l\'événement. Vous pouvez télécharger votre billet d\'entrée en suivant ce lien : ' . esc_url($url_billet);
                $headers = ['Content-Type: text/html; charset=UTF-8'];
                wp_mail($to, $subject, $body, $headers);
            }
            // Rediriger vers une page de confirmation
            wp_redirect(add_query_arg('inscription', 'success', get_permalink($evenement_id)));
            exit;
        } else {
            // Rediriger si l'événement est complet
            wp_redirect(add_query_arg('inscription', 'complet', get_permalink($evenement_id)));
            exit;
        }
    }
}

add_action('admin_post_inscription_evenement', 'gerer_inscription_evenement');
add_action('admin_post_nopriv_inscription_evenement', 'gerer_inscription_evenement');
