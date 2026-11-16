<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Début d'une page affichée dans un modal (iframe), à la place de header.php :
 * mêmes feuilles de style, mais sans l'en-tête du site.
 *
 * Utilisé par AdminQuestionController::questionAjouter avec ?modal=1
 * (bouton « Créer une nouvelle question » du formulaire d'un quiz).
 *
 * Le jeton CSRF est régénéré à chaque envoi : data-csrf-* le transmet à la page du quiz
 * (js/sio-admin-modal-question.js), sinon son formulaire serait refusé à l'enregistrement.
 *
 * @author Arthur
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber4All</title>
    <?= link_tag(base_url('/css/style.css')) ?>
    <?= link_tag(base_url('/css/sio/adminSio.css') . '?v=' . filemtime(FCPATH . 'css/sio/adminSio.css')) ?>
    <?= script_tag(['src' => 'js/sio-admin-modal-question.js?v=' . filemtime(FCPATH . 'js/sio-admin-modal-question.js'), 'defer' => null]) ?>
</head>
<body class="sio-admin-modal" data-csrf-nom="<?= esc(csrf_token(), 'attr') ?>" data-csrf-valeur="<?= esc(csrf_hash(), 'attr') ?>">
