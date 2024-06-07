<?php get_header(); ?>
<!-- Recuperation des 3 derniers Event -->
<?php $my_query = new WP_Query([ 'post_type' => 'event','posts_per_page' => 3,'order' => 'DESC', 'orderby' => 'date', ]);?>
<div class="wrapper">

<?php
    // Boucle sur les event
if ($my_query->have_posts()) :
    while ($my_query->have_posts()) :
        $my_query->the_post();?>
            <?php get_template_part('parts/event'); ?>
    <?php endwhile;
endif;
    //Recuperation de l'url de la category event pour afficher le bouton qui emmene vers la page de liste
    $category_slug = 'event';

    // Obtenir l'objet catégorie par son slug
    $category = get_category_by_slug($category_slug);
    // Obtenir l'ID de la catégorie
    $category_id = $category->term_id;
    // Obtenir l'URL de la catégorie
    $category_link = get_category_link($category_id);
?>
</div>
<div class="read-more">
    <a href="<?php echo esc_url($category_link); ?>">Voir tous les evenements</a>
</div>
<?php wp_reset_postdata();?>

<?php get_footer(); ?>

