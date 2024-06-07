<?php
$titre = get_field('titre');
$visuel = get_field('visuel');
$adresse = get_field('adresse');
$date = get_field('date');
$description = get_field('description');
?>
<div class="event_content">
    <?php if ($visuel) : ?>
        <div>
            <?php
            $image_id = get_field('visuel'); // On récupère cette fois l'ID
            if ($image_id) {
                echo wp_get_attachment_image($image_id);
            } ?>
        </div>
    <?php endif;
    if ($titre) : ?>
        <div>
            <strong>Nom :</strong>
            <?php the_field('titre'); ?>
        </div>
    <?php endif;
    if ($adresse) : ?>
        <div>
            <strong>Adresse :</strong>
            <?php the_field('adresse'); ?>
        </div>
    <?php endif;
    if ($date) : ?>
        <div>
            <strong>Date :</strong>
            <?php the_field('date'); ?>
        </div>
    <?php endif;
    if ($description) : ?>
        <div>
            <strong>Description :</strong>
            <?php the_field('description'); ?>
        </div>
    <?php endif;

    $nombre_places = get_field('number_place');
    $inscriptions_count = compter_inscriptions(get_the_ID());
    $places_restantes = get_field('number_available_place', get_the_ID());
    $unlimited_place = get_field('unlimited_place', get_the_ID());
    if (!isset($_GET['event'])) :
        if ($unlimited_place === true || ($unlimited_place === false && $places_restantes > 0)) : ?>
            <a href="<?php the_permalink(); ?>"> M'inscrire </a>
        <?php else : ?>
            <div>
                <strong class="complete-event">
                    <span>Evenement complet</span>
                </strong>
            </div>
        <div>
            <a href="<?php the_permalink(); ?>"> Voir plus </a>
        </div>
        <?php endif;?>
    <?php else : ?>
        <div>
            <?php if (isset($_GET['inscription'])) :
                if ($_GET['inscription'] == 'success') :
                    $billet_entree = get_field('billet');
                    $url_billet = $billet_entree ? wp_get_attachment_url($billet_entree) : '';
                    if ($billet_entree) : ?>
                        <p class="submit-container success-inscription">
                            <strong>
                                <span class="complete-inscription">Inscription Réussie</span>
                            </strong>
                            <a href="<?php echo esc_url($url_billet); ?>" download>Télécharger votre billet d'entrée</a>
                        </p>
                    <?php endif;
                endif;
            else :
                if ($unlimited_place === true || ($unlimited_place === false && $places_restantes > 0)) : ?>
                    <h2>Inscription à l'evenement</h2>
                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                        <input type="hidden" name="action" value="inscription_evenement">
                        <input type="hidden" name="evenement_id" value="<?php echo get_the_ID(); ?>">
                        <p>
                            <label for="nom">Nom:</label>
                            <input type="text" id="nom" name="nom" required>
                        </p>
                        <p>
                            <label for="prenom">Prenom:</label>
                            <input type="text" id="prenom" name="prenom" required>
                        </p>
                        <p>
                            <label for="date_naissance">Date de naissance:</label>
                            <input type="date" id="date_naissance" name="date_naissance" required>
                        </p>
                        <p>
                            <label for="status">Status:</label>
                            <select id="status" name="status">
                                <option value="Etudiant">Etudiant</option>
                                <option value="Salarié">Salarié</option>
                                <option value="Retraité">Retraité</option>
                                <option value="Chômeur">Chômeur</option>
                                <option value="Autre">Autre</option>
                             </select>
                        </p>
                        <p>
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </p>
                        <p class="submit-container">
                            <input type="submit" class="submit-form" value="S'inscrire">
                        </p>
                    </form>
                <?php else : ?>
                    <strong class="complete-event">
                        <span>Evenement complet</span>
                    </strong>
                <?php endif;
            endif; ?>
        </div>
    <?php endif;?>
</div>
