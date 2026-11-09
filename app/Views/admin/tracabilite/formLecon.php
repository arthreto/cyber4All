<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Formulaire d'ajout / de modification d'une leçon
 *
 * Même fonctionnement que formQuestion.php.
 * Données attendues : $form, $themeList, $errors.
 * - $form : modifier (id de la leçon en modification, vide en ajout), id_theme, titre, contenu,
 *           et en modification : auteur, auteur_modif, date_ajout, date_modif
 *
 * L'auteur n'est pas un champ du formulaire : le contrôleur met l'admin connecté.
 * Helpers utilisés : url (anchor), html (ul), form (form_open, form_label, form_dropdown,
 * form_input, form_textarea, form_button, form_close).
 */

// Mode du formulaire : modification si $form->modifier contient l'id d'une leçon
$estModification = ! empty($form->modifier);

// Adresse d'envoi du formulaire : l'id dans l'URL indique au contrôleur qu'il s'agit d'une modification
$actionFormulaire = $estModification
        ? 'floribondant/theme/lecons/modifier/' . (int) $form->modifier
        : 'floribondant/theme/lecons/ajouter';
?>
<div class="sio-admin sio-admin--formulaire">

    <?= anchor('floribondant/theme/lecons', '← Retour aux leçons', ['class' => 'sio-admin__retour']) ?>

    <div class="glossary-form">
        <div class="glossary-form__header">
            <h2 class="glossary-form__dialog-title"><?= $estModification ? 'Modifier la leçon' : 'Ajouter une leçon' ?></h2>
        </div>

        <?php if ($estModification): ?>
            <!-- Informations en lecture seule -->
            <p class="glossary-form__help">
                Rédigée par <strong><?= esc($form->auteur ?? '--') ?></strong>
                · Ajoutée le <?= esc($form->date_ajout ?? '--') ?>
                · Dernière modification : <?= esc($form->date_modif ?? '--') ?>
                <?php if (! empty($form->auteur_modif)): ?>
                    par <strong><?= esc($form->auteur_modif) ?></strong>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if (! empty($errors)): ?>
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>Le formulaire contient <?= count($errors) ?> erreur(s).</strong>
                <?= ul(esc($errors), ['class' => 'sio-admin__erreur-liste']) ?>
            </div>
        <?php endif; ?>

        <?= form_open($actionFormulaire, ['class' => 'glossary-form__form']) ?>

        <!-- Thème -->
        <div class="glossary-form__field">
            <?= form_label('Thème', 'champ-theme', ['class' => 'glossary-form__label glossary-form__label--required']) ?>
            <?= form_dropdown(
                    'id_theme',
                    esc(array_column($themeList, 'nom', 'id')),
                    (string) $form->id_theme,
                    [
                            'id'       => 'champ-theme',
                            'class'    => 'sio-admin__select' . (isset($errors['id_theme']) ? ' sio-admin__input--erreur' : ''),
                            'required' => 'required',
                    ]
            ) ?>

            <?php if (isset($errors['id_theme'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['id_theme']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Titre -->
        <div class="glossary-form__field">
            <?= form_label('Titre', 'champ-titre', ['class' => 'glossary-form__label glossary-form__label--required']) ?>
            <?= form_input([
                    'name'        => 'titre',
                    'id'          => 'champ-titre',
                    'value'       => $form->titre ?? '',
                    'maxlength'   => '150',
                    'placeholder' => 'Les tokens',
                    'class'       => 'glossary-form__input' . (isset($errors['titre']) ? ' sio-admin__input--erreur' : ''),
                    'required'    => 'required',
            ]) ?>

            <?php if (isset($errors['titre'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['titre']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Contenu (Markdown) -->
        <div class="glossary-form__field">
            <?= form_label('Contenu', 'champ-contenu', ['class' => 'glossary-form__label glossary-form__label--required']) ?>
            <?= form_textarea([
                    'name'             => 'contenu',
                    'id'               => 'champ-contenu',
                    'value'            => $form->contenu ?? '',
                    'rows'             => '18',
                    'class'            => 'glossary-form__input sio-admin__code' . (isset($errors['contenu']) ? ' sio-admin__input--erreur' : ''),
                    'aria-describedby' => 'aide-contenu',
                    'required'         => 'required',
            ]) ?>
            <p id="aide-contenu" class="glossary-form__help">Texte complet de la leçon en Markdown, affiché sur la page leçon.</p>

            <?php if (isset($errors['contenu'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['contenu']) ?></p>
            <?php endif; ?>
        </div>

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

<?= link_tag('https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.css') ?>
<?= script_tag('https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.js') ?>
<?= script_tag(['src' => 'js/sio-admin-editeur.js?v=' . filemtime(FCPATH . 'js/sio-admin-editeur.js'), 'defer' => null]) ?>