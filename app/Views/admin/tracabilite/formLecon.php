<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Formulaire d'ajout / de modification d'une leçon
 *
 * Même fonctionnement que formQuestion.php.
 * Données attendues :
 * - $form      : entité du formulaire (modifier, id_theme, titre, contenu,
 *                et en modification : auteur, date_ajout, date_modif)
 *                modifier = id de la leçon en modification, 0 ou vide en ajout
 * - $themeList : liste des thèmes (id, nom)
 * - $errors    : erreurs de validation, indexées par nom de champ (facultatif)
 *
 * L'auteur n'est pas un champ du formulaire : le contrôleur met l'admin connecté.
 */
$estModification = ! empty($form->modifier);
$actionFormulaire = $estModification
    ? 'floribondant/theme/lecons/modifier/' . (int) $form->modifier
    : 'floribondant/theme/lecons/ajouter';
?>
<div class="sio-admin sio-admin--formulaire">

    <?= anchor(base_url('floribondant/theme/lecons'), '← Retour aux leçons', ['class' => 'sio-admin__retour']) ?>

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
            </p>
        <?php endif; ?>

        <?php if (! empty($errors)): ?>
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>Le formulaire contient <?= count($errors) ?> erreur(s).</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li style="margin-left: 5%;"><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?= form_open(base_url($actionFormulaire), ['class' => 'glossary-form__form']) ?>

        <!-- Thème -->
        <div class="glossary-form__field">
            <label for="champ-theme" class="glossary-form__label glossary-form__label--required">Thème</label>
            <select id="champ-theme" name="id_theme" required
                    class="sio-admin__select <?= isset($errors['id_theme']) ? 'sio-admin__input--erreur' : '' ?>">
                <?php foreach ($themeList ?? [] as $theme): ?>
                    <option value="<?= esc($theme->id) ?>" <?= ($form->id_theme ?? null) == $theme->id ? 'selected' : '' ?>>
                        <?= esc($theme->nom) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (isset($errors['id_theme'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['id_theme']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Titre -->
        <div class="glossary-form__field">
            <label for="champ-titre" class="glossary-form__label glossary-form__label--required">Titre</label>
            <input type="text" id="champ-titre" name="titre" required maxlength="150"
                   value="<?= esc($form->titre ?? '') ?>"
                   placeholder="Les tokens"
                   class="glossary-form__input <?= isset($errors['titre']) ? 'sio-admin__input--erreur' : '' ?>">

            <?php if (isset($errors['titre'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['titre']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Contenu -->
        <div class="glossary-form__field">
            <label for="champ-contenu" class="glossary-form__label glossary-form__label--required">Contenu</label>
            <textarea id="champ-contenu" name="contenu" rows="18" required aria-describedby="aide-contenu"
                      class="glossary-form__input sio-admin__code <?= isset($errors['contenu']) ? 'sio-admin__input--erreur' : '' ?>"><?= esc($form->contenu ?? '') ?></textarea>
            <p id="aide-contenu" class="glossary-form__help">Texte complet de la leçon, affiché sur la page leçon.</p>

            <?php if (isset($errors['contenu'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['contenu']) ?></p>
            <?php endif; ?>
        </div>

        <div class="glossary-form__field glossary-form__field--row">
            <button type="submit" name="action" value="enregistrer" class="glossary-form__submit">Enregistrer</button>
        </div>

        <?= form_close() ?>
    </div>
</div>
