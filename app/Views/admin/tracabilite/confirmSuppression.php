<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Confirmation de suppression (remplace le confirm() JavaScript)
 * Vue en dur. Données prévues : $question.
 */
?>
<div class="sio-admin sio-admin--formulaire">
    <div class="glossary-form sio-admin__confirmation">
        <h2 class="glossary-form__dialog-title">Supprimer cette question ?</h2>
        <blockquote class="sio-admin__citation">
            Un JWT dont la date d'expiration (exp) est dépassée reste valide tant que sa signature est correcte.
        </blockquote> <!-- À brancher : esc($question->enonce) -->
        <p>Elle sera retirée de tous ses quiz et les réponses déjà données seront supprimées. Cette action est irréversible.</p>

        <div class="sio-admin__ligne">
            <?= form_open(base_url('floribondant/theme/questions/supprimer')) ?>
                <?= form_hidden('id_question', '12') ?>
                <?= form_hidden('confirmation', '1') ?>
                <button type="submit" class="glossary-form__submit glossary-form__submit--refuser">Supprimer définitivement</button>
            <?= form_close() ?>
            <?= anchor(base_url('floribondant/theme'), 'Annuler', ['class' => 'sio-admin__btn-secondaire']) ?>
        </div>
    </div>
</div>
