<?php

require_once __DIR__ . '/Model/Event.php';
require_once __DIR__ . '/Model/Inscription.php';
require_once __DIR__ . '/functions/inscription.php';
require_once __DIR__ . '/functions/event.php';
require_once __DIR__ . '/functions/export.php';

// Ajouter la prise en charge des images mises en avant
add_theme_support('post-thumbnails');

// Ajouter automatiquement le titre du site dans l'en-tête du site
add_theme_support('title-tag');

wp_enqueue_style('style', get_stylesheet_uri());

register_nav_menus([
    'main' => 'Menu Principal',
    'footer' => 'Bas de page',
]);


function custom_wp_mail_from($email)
{
    return 'no-reply@localhost.com';
}

add_filter('wp_mail_from', 'custom_wp_mail_from');
