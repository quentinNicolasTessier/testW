<?php get_header(); ?>
<?php if (have_posts()) :
    while (have_posts()) :
        the_post(); ?>

    <div class="event-container">
            <!-- Affichage de l'event -->
            <?php get_template_part('parts/event'); ?>
    </div>

    <?php endwhile;
endif; ?>
<?php get_footer(); ?>
