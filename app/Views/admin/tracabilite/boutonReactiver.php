<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Bouton « Réactiver » d'un élément supprimé (désactivé) dans une liste
 *
 * Données attendues :
 * - $url    : adresse de réactivation (ex. floribondant/theme/lecons/reactiver/12)
 * - $raison : raison qui empêche la réactivation, null si l'élément est valide
 *
 * Élément valide : petit formulaire POST, même design que « Supprimer » mais en vert.
 * Élément invalide : bouton grisé, la raison s'affiche au survol (le span porte l'infobulle,
 * car un bouton désactivé ne réagit pas au survol dans tous les navigateurs).
 * Le contrôleur revérifie de toute façon la validité avant de réactiver.
 * Styles : sio/adminSio.css (.sio-admin__btn-reactiver)
 *
 * @author Raphael Bierlaire
 */
?>
<?php if ($raison === null): ?>
    <?= form_open($url, ['class' => 'mots-table__delete-form']) ?>
    <?= form_button(['type' => 'submit', 'content' => 'Réactiver', 'class' => 'sio-admin__btn-reactiver']) ?>
    <?= form_close() ?>
<?php else: ?>
    <span class="sio-admin__reactiver-bloque" title="<?= esc('Réactivation impossible : ' . $raison, 'attr') ?>">
        <?= form_button([
            'type'       => 'button',
            'content'    => 'Réactiver',
            'class'      => 'sio-admin__btn-reactiver',
            'disabled'   => true,
            'aria-label' => 'Réactiver (impossible : ' . $raison . ')', // form_button() échappe déjà les attributs
        ]) ?>
    </span>
<?php endif; ?>
