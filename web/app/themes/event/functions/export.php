<?php

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Model\Inscription;

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


if (!wp_next_scheduled('mon_event_cron')) {
    wp_schedule_event(time(), 'daily', 'mon_event_cron');
}
