<?php get_header(); ?>
    <h1 class="site__heading">
        <?php if (!isset($_GET['s'])) :
            echo "Liste des Evenements";
        else :
            echo "Resultat pour la recherche de: " . $_GET['s'];
        endif; ?>
    </h1>
    <div class="site__blog">
        <main>
            <div class="wrapper">
            <?php if (!isset($_GET['s'])) :
                $my_query = new WP_Query([ 'post_type' => 'event','order' => 'DESC', 'orderby' => 'date', ]);
                // Boucle personnalisée
                if ($my_query->have_posts()) :
                    while ($my_query->have_posts()) :
                        $my_query->the_post();
                        get_template_part('parts/event');
                    endwhile;
                endif;
                wp_reset_postdata();
            else :
                if (have_posts()) :
                    while (have_posts()) :
                        the_post(); ?>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <?php endwhile;
                endif;
            endif;?>
            <div class="site__navigation">
                <div class="site__navigation__prev">
                    <?php previous_posts_link('Page Précédente'); ?>
                </div>
                <div class="site__navigation__next">
                    <?php next_posts_link('Page Suivante'); ?>
                </div>
            </div>
            </div>
        </main>
    </div>
<?php get_footer(); ?>
