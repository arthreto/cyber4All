<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Page affichée dans le modal après l'enregistrement d'une question.
 *
 * js/sio-admin-modal-question.js lit data-question et l'envoie à la page du quiz,
 * qui ferme le modal et ajoute la question (cochée) au quiz.
 *
 * Données préparées par le contrôleur (AdminQuestionController::questionAjouter) :
 * - $question : id, libelle, bonne_reponse, id_theme, quiz (identifiants des quiz cochés)
 *
 * @author Arthur
 */
?>
<div class="sio-admin" id="question-enregistree" data-question="<?= esc(json_encode($question), 'attr') ?>">
    <p role="status">Question enregistrée.</p>
</div>
