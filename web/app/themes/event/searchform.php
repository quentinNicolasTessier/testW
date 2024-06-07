<form class="search-form" action="<?php echo home_url('/'); ?>" method="get">
    <input type="text" name="s" id="search" placeholder="Rechercher" value="<?php the_search_query(); ?>" />
    <input class="submit-form" type="submit" alt="Search" value="Rechercher"/>
</form>
