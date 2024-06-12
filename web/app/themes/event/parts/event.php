<?php
require_once __DIR__ . '/../Model/Event.php';
use Model\Event;
//Recuperation des infos de l'evenement
$event = new Event(
    get_field('titre', get_the_ID()),
    get_field('description', get_the_ID()),
    get_field('adresse', get_the_ID()),
    get_field('date', get_the_ID()),
    get_field('visuel', get_the_ID()),
    get_field('billet', get_the_ID()),
    get_field('unlimited_place', get_the_ID())
);
if ($event->isPlaceIllimite() === false) {
    $event->setNombrePlace(get_field('number_place', get_the_ID()));
}

?>
<div class="event_content">
    <?php if ($event->getVisuel()) : ?>
        <div>
            <?php
            echo wp_get_attachment_image($event->getVisuel());
            ?>
        </div>
    <?php endif;
    if ($event->getTitre()) : ?>
        <div>
            <strong>Nom :</strong>
            <?php echo $event->getTitre() ?>
        </div>
    <?php endif;
    if ($event->getAdresse()) : ?>
        <div>
            <strong>Adresse :</strong>
            <?php echo $event->getAdresse(); ?>
        </div>
    <?php endif;
    if ($event->getDate()) : ?>
        <div>
            <strong>Date :</strong>
            <?php echo $event->getDate(); ?>
        </div>
    <?php endif;
    if ($event->getDescription()) : ?>
        <div>
            <strong>Description :</strong>
            <?php echo $event->getDescription() ?>
        </div>
    <?php endif;
    //Verifier le nombre de place disponible et empcher l'inscription si complet
    $inscriptions_count = compter_inscriptions(get_the_ID());
    if (!isset($_GET['event'])) :
        if ($event->isPlaceIllimite() === true || ($event->isPlaceIllimite() === false && $inscriptions_count < $event->getNombrePlace())) : ?>
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
            <?php //Verifier que l'inscription est un succes et ajouter bouton pour telecharger le billet de l'event s'il existe
            if (isset($_GET['inscription_event'])) :
                if ($_GET['inscription_event'] == 'success') :
                    $url_billet = $event->getBillet() ? wp_get_attachment_url($event->getBillet()) : '';?>
                        <p class="submit-container success-inscription">
                            <strong>
                                <span class="complete-inscription">Inscription Réussie</span>
                            </strong>
                            <?php if ($url_billet) : ?>
                                <a href="<?php echo esc_url($url_billet); ?>" download>Télécharger votre billet d'entrée</a>
                            <?php endif;?>
                        </p>
                <?php endif;
            else :
                //Verification que l'evenement n'est pas complet pour afficher le formulaire d'inscription
                if ($event->isPlaceIllimite() === true || ($event->isPlaceIllimite() === false && $inscriptions_count < $event->getNombrePlace())) : ?>
                    <h2>Inscription à l'evenement</h2>
                    <form id="form-inscription" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                        <input type="hidden" name="action" value="inscription_evenement">
                        <input type="hidden" name="evenement_id" value="<?php echo get_the_ID(); ?>">
                        <p>
                            <input placeholder="Email" aria-label="email"  type="email" id="email" name="email" required>
                        </p>
                        <p>
                            <input placeholder="Nom" aria-label="Nom" type="text" id="nom" name="nom" required>
                        </p>
                        <p>
                            <input placeholder="Prenom" aria-label="prenom"  type="text" id="prenom" name="prenom" required>
                        </p>
                        <p>
                            <label for="date_naissance">Date de naissance:</label>
                            <input placeholder="Date de Naissance" type="date" id="date_naissance" name="date_naissance" max="<?= date('Y-m-d'); ?>" required>
                        </p>
                        <p>
                            <label for="status">Statut:</label>
                            <select id="status" name="status">
                                <option value="Etudiant">Etudiant</option>
                                <option value="Salarié">Salarié</option>
                                <option value="Retraité">Retraité</option>
                                <option value="Chômeur">Chômeur</option>
                                <option value="Autre">Autre</option>
                             </select>
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
