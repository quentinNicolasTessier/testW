<?php

require_once __DIR__ . '/../Model/Event.php';
require_once __DIR__ . '/../Model/Inscription.php';

use Model\Event;
use Model\Inscription;

add_action('init', function () {
    register_extended_post_type('event');
});

// Fonction pour ajouter une colonne personnalisée sur la liste des Event
function custom_add_event_column($columns)
{
    $columns['nb_place'] = 'Nombre de Place';
    $columns['nb_inscription'] = 'Nombre d\'inscription';
    $columns['export'] = 'Exporter';
    return $columns;
}

add_filter('manage_event_posts_columns', 'custom_add_event_column');

// Fonction pour afficher le contenu de la colonne personnalisée pour les event
function custom_display_event_column($column): void
{
    // Vérifier si c'est notre colonne personnalisée
    switch ($column) {
        case 'nb_place':
            // Afficher le nombre de place disponible
            $event = new Event("", "", "", "", "", "", get_field('unlimited_place', get_the_ID()));
            if ($event->isPlaceIllimite() === false) {
                echo $event->getNombrePlace();
            } else {
                echo 'Illimité';
            }
            break;
        case 'nb_inscription':
            // Afficher le nombre d'inscription
            echo compter_inscriptions(get_the_ID());
            break;
        case 'export':
            $export_url = wp_nonce_url(
                add_query_arg(
                    [
                        'action' => 'export_cpt',
                        'post_id' => get_the_ID(),
                    ],
                    admin_url('admin-post.php')
                ),
                'export_cpt_' . get_the_ID()
            );

            echo '<a href="' . esc_url($export_url) . '" class="button">Exporter</a>';
            break;
    }
}
add_action('manage_event_posts_custom_column', 'custom_display_event_column', 10);
