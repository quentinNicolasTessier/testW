<?php

require_once __DIR__ . '/Model/Event.php';
require_once __DIR__ . '/Model/Inscription.php';

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Model\Event;
use Model\Inscription;

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
            if ($event->getBillet()) {
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
    $columns['export'] = 'Exporter';
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

function handle_export_event(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'));
    }

    if (isset($_GET['post_id']) && check_admin_referer('export_cpt_' . intval($_GET['post_id']))) {
        $post_id = intval($_GET['post_id']);
        $post = get_post($post_id);

        if ($post) {
            // Définir le type de fichier (Excel ou CSV)
            $fileType = 'xlsx';

            // Créer un writer
            $writer = new Writer();
            // Ouvrir le writer
            $writer->openToBrowser("export-inscription-event-{$post_id}.{$fileType}");
            get_file_excel($post_id, $writer);
        } else {
            wp_die(__('Post non trouvé.'));
        }
    } else {
        wp_die(__('Requête invalide.'));
    }
}
function get_file_excel($post_id, $writer): void
{
    // Créer un style pour l'en-tête
    $style = new Style();
    $style->setFontBold();
    $args = [
        'post_type' => 'inscription',
        'meta_query' => [
            [
                'key' => 'evenement_id',
                'value' => $post_id,
                'compare' => '='
            ]
        ]
    ];
    //Recuperation de la liste des inscription pour l'event correspondant
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        // Ajouter les en-têtes des colonnes
        $rowFromValues = Row::fromValues(['Nom', 'Prenom', 'Email', "Date de naissance", "Statut"], $style);
        try {
            $writer->addRow($rowFromValues);
        } catch (IOException | WriterNotOpenedException $e) {
            wp_die(__($e->getMessage()));
        }
        // Ajouter Les inscriptions au fichier
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $inscription = new Inscription(get_field("nom", $post_id), get_field("prenom", $post_id), get_field("email", $post_id), get_field("date_naissance", $post_id), get_field("status", $post_id), $post_id);
            // Récupérer les informations des inscriptions
            $rowFromValues =  Row::fromValues([$inscription->getNom(), $inscription->getPrenom(), $inscription->getEmail(), $inscription->getDateNaissance(), $inscription->getStatut()]);
            try {
                $writer->addRow($rowFromValues);
            } catch (IOException | WriterNotOpenedException $e) {
                wp_die(__($e->getMessage()));
            }
        }
        // Fermer le writer
        $writer->close();
    }
}

/**
 * @throws IOException
 */
function excel_file_cron()
{
    $args = [
        'post_type' => 'event',
        'posts_per_page' => 3,
        'order' => 'DESC',
        'orderby' => 'date',
    ];
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $excelFile = [];
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            // Définir le type de fichier (Excel ou CSV)
            $fileType = 'xlsx';
            $filePath = __DIR__ . "/cron/export-inscription-event-{$post_id}.{$fileType}";

            // Créer un writer
            $writer = new Writer();
            // Ouvrir le writer
            $writer->openToFile($filePath);
            get_file_excel($post_id, $writer);
            $excelFile[] = __DIR__  . "/cron/export-inscription-event-{$post_id}.{$fileType}";
        }
        $to = "testglob@yopmail.com";
        $subject = 'Liste des participants event';
        $body = 'Voici la liste des participants au 3 dernier event';
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail($to, $subject, $body, $headers, $excelFile);
    }
}

add_action('mon_event_cron', 'excel_file_cron');


add_action('admin_post_export_cpt', 'handle_export_event');
add_action('manage_inscription_posts_custom_column', 'custom_display_inscription_column', 10);
add_action('manage_event_posts_custom_column', 'custom_display_event_column', 10);

if (!wp_next_scheduled('mon_event_cron')) {
    wp_schedule_event(time(), 'daily', 'mon_event_cron');
}
