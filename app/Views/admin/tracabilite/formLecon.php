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
 * Helpers utilisés : url (anchor), html (script_tag, link_tag, ul), form (form_open, form_label,
 * form_radio, form_input, form_textarea, form_button, form_close).
 * Styles : sio/adminSio.css (section « Leçons »), formMot.css
 *
 * @author Raphael Bierlaire
 */

$errors = $errors ?? [];

// Mode du formulaire : modification si $form->modifier contient l'id d'une leçon
$estModification = ! empty($form->modifier);

// Adresse d'envoi du formulaire : l'id dans l'URL indique au contrôleur qu'il s'agit d'une modification
$actionFormulaire = $estModification
    ? 'floribondant/theme/lecons/modifier/' . (int) $form->modifier
    : 'floribondant/theme/lecons/ajouter';
?>
<!-- Éditeur Markdown, Font Awesome et sio-admin-theme.js : chargés par header.php -->
<div class="sio-admin sio-admin--formulaire sio-admin--formulaire-large">

    <div class="glossary-form">

        <!-- Titre de la page et lien de retour sur la même ligne -->
        <div class="sio-admin__entete-form">
            <h2 class="glossary-form__dialog-title"><?= $estModification ? 'Modifier la leçon' : 'Nouvelle leçon' ?></h2>
            <?= anchor('floribondant/theme/lecons', '← Retour aux leçons', ['class' => 'sio-admin__retour']) ?>
        </div>

        <?php if ($estModification): ?>
            <!-- Informations en lecture seule -->
            <p class="glossary-form__help sio-admin__infos">
                Rédigée par <?= esc($form->auteur ?? 'un auteur inconnu') ?>, ajoutée le <?= esc($form->date_ajout ?? '--') ?>.
                <?php if (! empty($form->auteur_modif)): ?>
                    Dernière modification le <?= esc($form->date_modif ?? '--') ?> par <?= esc($form->auteur_modif) ?>.
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <!-- Récapitulatif des erreurs (ul() n'échappe pas les éléments : esc() sur tout le tableau) -->
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>La leçon n'a pas été enregistrée. Corrigez le(s) point(s) suivant(s) :</strong>
                <?= ul(esc($errors), ['class' => 'sio-admin__erreur-liste']) ?>
            </div>
        <?php endif; ?>

        <?= form_open($actionFormulaire, ['class' => 'glossary-form__form']) ?>

        <!-- Thème : un bouton radio par thème -->
        <fieldset class="sio-admin__fieldset">
            <legend class="glossary-form__label glossary-form__label--required">Thème</legend>

            <div class="sio-admin__radios sio-admin__radios--cartes<?= isset($errors['id_theme']) ? ' sio-admin__radios--erreur' : '' ?>">
                <?php foreach ($themeList as $theme): ?>
                    <?= form_label(
                        form_radio([
                            'name'     => 'id_theme',
                            'id'       => 'theme-' . (int) $theme->id,
                            'value'    => (string) $theme->id,
                            'checked'  => (string) $theme->id === (string) $form->id_theme,
                            'required' => true, // le navigateur demande de choisir un thème avant l'envoi
                        ]) . esc($theme->nom),
                        'theme-' . (int) $theme->id,
                        ['class' => 'sio-admin__choix sio-admin__choix--carte']
                    ) ?>
                <?php endforeach; ?>
            </div>

            <?php if (isset($errors['id_theme'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['id_theme']) ?></p>
            <?php endif; ?>
        </fieldset>

        <!-- Titre (150 caractères maximum : le champ bloque la saisie au-delà) -->
        <div class="glossary-form__field">
            <?= form_label('Titre', 'champ-titre', ['class' => 'glossary-form__label glossary-form__label--required']) ?>
            <?= form_input([
                'name'      => 'titre',
                'id'        => 'champ-titre',
                'value'     => $form->titre ?? '',
                'maxlength' => '150',
                'class'     => 'glossary-form__input' . (isset($errors['titre']) ? ' sio-admin__input--erreur' : ''),
                'required'  => 'required',
            ]) ?>

            <?php if (isset($errors['titre'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['titre']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Contenu (Markdown) -->
        <div class="glossary-form__field<?= isset($errors['contenu']) ? ' sio-admin__champ--erreur' : '' ?>">

            <!-- Libellé à gauche, aide-mémoire repliable à droite (ouvert, il passe sur toute la largeur) -->
            <div class="sio-admin__entete-champ">
                <?= form_label('Contenu', 'champ-contenu', ['class' => 'glossary-form__label glossary-form__label--required']) ?>

                <details class="sio-admin__memo">
                    <summary class="sio-admin__memo-titre">Aide-mémoire Markdown</summary>
                    <table class="sio-admin__memo-tableau">
                        <thead>
                        <tr>
                            <th scope="col">Pour obtenir</th>
                            <th scope="col">Écrivez</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><th scope="row">Un titre de section (le titre de la leçon est déjà en haut de la page)</th><td><code>## Titre de la section</code></td></tr>
                        <tr><th scope="row">Un sous-titre</th><td><code>### Sous-titre</code></td></tr>
                        <tr><th scope="row">Du texte en gras</th><td><code>**texte**</code></td></tr>
                        <tr><th scope="row">Du texte en italique</th><td><code>*texte*</code></td></tr>
                        <tr><th scope="row">Une liste à puces</th><td><code>- Premier élément</code></td></tr>
                        <tr><th scope="row">Une liste numérotée</th><td><code>1. Premier élément</code></td></tr>
                        <tr><th scope="row">Un lien</th><td><code>[texte du lien](https://exemple.fr)</code></td></tr>
                        <tr><th scope="row">Du code dans une phrase</th><td><code>`code`</code></td></tr>
                        <tr><th scope="row">Un bloc de code</th><td><code>```bash<?= "\n" ?>sudo systemctl restart apache2<?= "\n" ?>```</code></td></tr>
                        <tr><th scope="row">Un tableau</th><td><code>| Commande | Rôle |<?= "\n" ?>|---|---|<?= "\n" ?>| ls | Liste les fichiers |</code></td></tr>
                        </tbody>
                    </table>
                </details>
            </div>

            <?php if (isset($errors['contenu'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['contenu']) ?></p>
            <?php endif; ?>

            <?= form_textarea([
                'name'     => 'contenu',
                'id'       => 'champ-contenu',
                'value'    => $form->contenu ?? '',
                'rows'     => '18',
                'class'    => 'glossary-form__input sio-admin__code' . (isset($errors['contenu']) ? ' sio-admin__input--erreur' : ''),
                'required' => 'required',
            ]) ?>
        </div>

        <!-- Visibilité : le champ caché envoie 0 quand la case est décochée -->
        <div class="glossary-form__field">
            <?= form_label(
                form_hidden('actif', '0')
                . form_checkbox(['name' => 'actif', 'id' => 'champ-actif'], '1', (string) ($form->actif ?? '1') === '1')
                . 'Leçon active',
                'champ-actif',
                ['class' => 'sio-admin__choix sio-admin__choix--carte']
            ) ?>
            <p class="glossary-form__help">Une leçon inactive reste enregistrée mais n'est plus affichée sur le site.</p>
        </div>

        <!-- Barre d'enregistrement : reste visible en bas de l'écran pendant la saisie -->
        <div class="sio-admin__barre">
            <?= anchor('floribondant/theme/lecons', 'Annuler', ['class' => 'sio-admin__btn-secondaire']) ?>
            <!-- form_button() et non form_submit() : le contrôleur attend action = "enregistrer" -->
            <?= form_button([
                'type'    => 'submit',
                'name'    => 'action',
                'value'   => 'enregistrer',
                'content' => $estModification ? 'Enregistrer les modifications' : 'Créer la leçon',
                'class'   => 'sio-admin__btn-principal',
            ]) ?>
        </div>

        <?= form_close() ?>
    </div>
</div>

