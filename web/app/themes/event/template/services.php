<?php
/*
  Template Name: Services
*/
get_header();
if (have_posts()) :
    while (have_posts()) :
        the_post();
        ?>
    <div class="service-container">
        <h1><?php the_title(); ?></h1>
        <div class="content">
            <?php the_content(); ?>
        </div>
    </div>
        <?php
    endwhile;
endif;
?>
<?php get_footer(); ?>
