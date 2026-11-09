<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Formulaire d'ajout / de modification d'un quiz
 *
 * Helpers utilisés : url (anchor, base_url), html (ul, script_tag),
 * form (form_open, form_label, form_dropdown, form_input, form_textarea, form_checkbox,
 * form_hidden, form_fieldset, form_button, form_fieldset_close, form_close).
 *
 * Attention : form_dropdown et form_label n'échappent pas les libellés, d'où les esc().
 */
$estModification = ! empty($form->modifier);
$actionFormulaire = $estModification
    ? 'floribondant/theme/quiz/modifier/' . (int) $form->modifier
    : 'floribondant/theme/quiz/ajouter';

// Classe d'un champ, avec la classe d'erreur si le champ est en erreur
$classeChamp = static fn (string $classe, string $champ): string
    => $classe . (isset($errors[$champ]) ? ' sio-admin__input--erreur' : '');

// Message d'erreur sous un champ
$erreurChamp = static fn (string $champ): string
    => isset($errors[$champ]) ? '<p class="sio-admin__erreur">' . esc($errors[$champ]) . '</p>' : '';

$label = ['class' => 'glossary-form__label'];
$labelRequis = ['class' => 'glossary-form__label glossary-form__label--required'];

// Listes déroulantes (libellés échappés)
$optionsThemes = esc(array_column($themeList ?? [], 'nom', 'id'));
$optionsQuestions = esc(array_column($questionsDisponibles ?? [], 'libelle', 'id'));
$nbQuestions = count($questionsAjoutees ?? []);
?>
<div class="sio-admin sio-admin--formulaire">

    <?= anchor(base_url('floribondant/theme/quiz'), '← Retour aux quiz', ['class' => 'sio-admin__retour']) ?>

    <div class="glossary-form">
        <div class="glossary-form__header">
            <h2 class="glossary-form__dialog-title"><?= $estModification ? 'Modifier le quiz' : 'Ajouter un quiz' ?></h2>
        </div>

        <?php if (! empty($errors)): ?>
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>Le formulaire contient <?= count($errors) ?> erreur(s).</strong>
                <?= ul(esc($errors), ['style' => 'margin-left: 5%;']) ?>
            </div>
        <?php endif; ?>

        <?= form_open(base_url($actionFormulaire), ['class' => 'glossary-form__form']) ?>

        <!-- Thème -->
        <div class="glossary-form__field">
            <?= form_label('Thème', 'champ-theme-quiz', $labelRequis) ?>
            <?= form_dropdown('id_theme', $optionsThemes, (string) ($form->id_theme ?? ''), [
                'id'       => 'champ-theme-quiz',
                'class'    => $classeChamp('sio-admin__select', 'id_theme'),
                'required' => 'required',
            ]) ?>
            <?= $erreurChamp('id_theme') ?>
        </div>

        <!-- Titre -->
        <div class="glossary-form__field">
            <?= form_label('Titre', 'champ-titre', $labelRequis) ?>
            <?= form_input([
                'name'        => 'titre',
                'id'          => 'champ-titre',
                'value'       => $form->titre ?? '',
                'maxlength'   => '150',
                'placeholder' => 'Reconnaître un token valide',
                'class'       => $classeChamp('glossary-form__input', 'titre'),
                'required'    => true,
            ]) ?>
            <?= $erreurChamp('titre') ?>
        </div>

        <!-- Description -->
        <div class="glossary-form__field">
            <?= form_label('Description (facultatif)', 'champ-description', $label) ?>
            <?= form_textarea([
                'name'             => 'description',
                'id'               => 'champ-description',
                'value'            => $form->description ?? '',
                'rows'             => '4',
                'class'            => $classeChamp('glossary-form__input', 'description'),
                'aria-describedby' => 'aide-description',
            ]) ?>
            <p id="aide-description" class="glossary-form__help">Courte présentation affichée dans la liste des quiz.</p>
            <?= $erreurChamp('description') ?>
        </div>

        <!-- Actif : le champ caché envoie 0 quand la case est décochée -->
        <div class="glossary-form__field">
            <?= form_label(
                form_hidden('actif', '0')
                . form_checkbox(['name' => 'actif', 'id' => 'champ-actif'], '1', ($form->actif ?? 1) == 1)
                . ' Quiz actif (visible sur la page des quiz)',
                'champ-actif',
                ['class' => 'sio-admin__choix']
            ) ?>
        </div>

        <?= form_fieldset('<span class="glossary-form__label">Questions du quiz</span>', ['class' => 'sio-admin__bloc-quiz']) ?>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <?= form_label('1. Thème des questions', 'champ-theme-questions', $label) ?>
                    <?= form_dropdown('id_theme_questions', $optionsThemes, (string) ($form->id_theme_questions ?? ''), [
                        'id'              => 'champ-theme-questions',
                        'class'           => 'sio-admin__select',
                        'data-envoi-auto' => 'true',
                    ]) ?>
                </div>
                <noscript>
                    <?= form_button([
                        'type'           => 'submit',
                        'name'           => 'action',
                        'value'          => 'choisir_theme',
                        'content'        => 'Afficher les questions',
                        'class'          => 'sio-admin__btn-secondaire',
                        'formnovalidate' => true,
                    ]) ?>
                </noscript>
            </div>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <?= form_label('2. Question à ajouter', 'champ-question', $label) ?>
                    <?= form_dropdown('id_question_ajout', $optionsQuestions, '', [
                        'id'    => 'champ-question',
                        'class' => 'sio-admin__select',
                    ]) ?>
                </div>
                <?php
                $boutonAjout = [
                    'type'           => 'submit',
                    'name'           => 'action',
                    'value'          => 'ajouter_question',
                    'content'        => 'Ajouter cette question',
                    'class'          => 'sio-admin__btn-principal',
                    'formnovalidate' => true,
                ];
                // Les attributs booléens sont toujours écrits par CodeIgniter : on n'ajoute disabled que si besoin
                if ($optionsQuestions === []) {
                    $boutonAjout['disabled'] = true;
                }
                ?>
                <?= form_button($boutonAjout) ?>
            </div>

            <div class="sio-admin__rattaches">
                <p class="glossary-form__label"><?= $nbQuestions ?> question<?= $nbQuestions > 1 ? 's' : '' ?> dans ce quiz</p>
                <ol class="sio-admin__rattaches-liste">
                    <?php foreach ($questionsAjoutees ?? [] as $question): ?>
                        <li class="sio-admin__rattache">
                            <span>
                                <?= esc($question['libelle']) ?>
                                <strong>· <?= $question['bonne_reponse'] == 1 ? 'Vrai' : 'Faux' ?></strong>
                            </span>
                            <?= form_button([
                                'type'           => 'submit',
                                'name'           => 'retirer_question',
                                'value'          => (string) $question['id'],
                                'content'        => 'Retirer',
                                'class'          => 'sio-admin__btn-supprimer',
                                'formnovalidate' => true,
                            ]) ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <?= anchor(base_url('floribondant/theme/questions/ajouter'), '+ Créer une nouvelle question', ['class' => 'sio-admin__retour']) ?>

        <?= form_fieldset_close() ?>

        <div class="glossary-form__field glossary-form__field--row">
            <?= form_button([
                'type'    => 'submit',
                'name'    => 'action',
                'value'   => 'enregistrer',
                'content' => 'Enregistrer',
                'class'   => 'glossary-form__submit',
            ]) ?>
        </div>

        <?= form_close() ?>
    </div>
</div>

<?= script_tag(['src' => 'js/sio-admin-theme.js?v=' . filemtime(FCPATH . 'js/sio-admin-theme.js'), 'defer' => null]) ?>
