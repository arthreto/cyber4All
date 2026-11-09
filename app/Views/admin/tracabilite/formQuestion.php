<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Formulaire d'ajout / de modification d'une question
 *
 * Données attendues : $form, $themeList, $quizList, $quizAdded, $extraitList, $typesExtrait,
 *                     $infoDetail, $usageExtrait, $errors.
 *
 * Choix des quiz : on choisit d'abord le thème, puis on ajoute les quiz de ce thème un par un.
 * Seule exception à la règle 0 JS : public/js/sio-admin-theme.js renvoie le formulaire
 * quand le thème (ou une liste data-envoi-auto) change. Le bouton dans <noscript> sert de secours.
 *
 * Helpers utilisés : url (anchor, base_url), html (ul, script_tag),
 * form (form_open, form_fieldset, form_label, form_dropdown, form_input, form_textarea,
 * form_radio, form_checkbox, form_hidden, form_button, form_fieldset_close, form_close).
 *
 * Attention : form_dropdown et form_label n'échappent pas les libellés, d'où les esc().
 */

// Mode du formulaire : en modification, l'id de la question est dans l'URL
$estModification = ! empty($form->modifier);
$actionFormulaire = $estModification
    ? 'floribondant/theme/questions/modifier/' . (int) $form->modifier
    : 'floribondant/theme/questions/ajouter';

// Classe d'un champ, avec la classe d'erreur si le champ est en erreur
$classeChamp = static fn (string $classe, string $champ): string
    => $classe . (isset($errors[$champ]) ? ' sio-admin__input--erreur' : '');

// Message d'erreur sous un champ
$erreurChamp = static fn (string $champ): string
    => isset($errors[$champ]) ? '<p id="erreur-' . esc($champ, 'attr') . '" class="sio-admin__erreur">' . esc($errors[$champ]) . '</p>' : '';

// Classes des libellés
$label = ['class' => 'glossary-form__label'];
$labelRequis = ['class' => 'glossary-form__label glossary-form__label--required'];

// Thèmes et quiz pour les listes déroulantes (libellés échappés)
$optionsThemes = esc(array_column($themeList, 'nom', 'id'));
$optionsQuiz = esc(array_column($quizList, 'titre', 'id'));
$nomTheme = $optionsThemes[$form->id_theme] ?? '';

// Détail : mode choisi et extraits regroupés par type
$modeDetail = $form->detail_mode ?? 'aucun';
$optionsModes = [
    'aucun'    => 'Aucun',
    'existant' => 'Déjà existant',
    'nouveau'  => 'En créer un',
];

$optionsExtraits = ['' => '— Choisir un extrait —'];
$extraitChoisi = null;
foreach ($extraitList as $extrait) {
    $groupe = esc($typesExtrait[$extrait['type']] ?? $extrait['type']);
    $optionsExtraits[$groupe][$extrait['id']] = esc($extrait['titre']);

    if ((string) $extrait['id'] === (string) $form->id_extrait) {
        $extraitChoisi = $extrait;
    }
}
$optionsTypes = esc($typesExtrait);
?>
<div class="sio-admin sio-admin--formulaire">

    <?= anchor(base_url('floribondant/theme'), '← Retour aux questions', ['class' => 'sio-admin__retour']) ?>

    <div class="glossary-form">
        <div class="glossary-form__header">
            <h2 class="glossary-form__dialog-title"><?= $estModification ? 'Modifier la question' : 'Ajouter une question' ?></h2>
        </div>

        <?php if (! empty($errors)): ?>
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>Le formulaire contient <?= count($errors) ?> erreur(s).</strong>
                <?= ul(esc($errors), ['style' => 'margin-left: 5%;']) ?>
            </div>
        <?php endif; ?>

        <?= form_open(base_url($actionFormulaire), ['class' => 'glossary-form__form']) ?>

        <!-- Quiz de la question -->
        <?= form_fieldset('<span class="glossary-form__label">Quiz de la question</span>', ['class' => 'sio-admin__bloc-quiz']) ?>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <?= form_label('1. Thème', 'champ-theme', $label) ?>
                    <?= form_dropdown('id_theme', $optionsThemes, (string) $form->id_theme, [
                        'id'    => 'champ-theme',
                        'class' => 'sio-admin__select',
                    ]) ?>
                </div>
                <noscript>
                    <?= form_button([
                        'type'    => 'submit',
                        'name'    => 'action',
                        'value'   => 'choisir_theme',
                        'content' => 'Afficher les quiz',
                        'class'   => 'sio-admin__btn-secondaire',
                    ]) ?>
                </noscript>
            </div>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <?= form_label(trim('2. Quiz du thème ' . $nomTheme), 'champ-quiz', $label) ?>
                    <?= form_dropdown('id_quiz_ajout', $optionsQuiz, (string) ($form->id_quiz_ajout ?? ''), [
                        'id'    => 'champ-quiz',
                        'class' => 'sio-admin__select',
                    ]) ?>
                </div>
                <?= form_button([
                    'type'           => 'submit',
                    'name'           => 'action',
                    'value'          => 'ajouter_quiz',
                    'content'        => 'Ajouter ce quiz',
                    'class'          => 'sio-admin__btn-principal',
                    'formnovalidate' => true,
                ]) ?>
            </div>

            <div class="sio-admin__rattaches">
                <p class="glossary-form__label">Question rattachée à <?= count($quizAdded) ?> quiz</p>
                <ul class="sio-admin__rattaches-liste">
                    <?php foreach ($quizAdded as $idQuiz => $titreQuiz): ?>
                        <li class="sio-admin__rattache">
                            <span><?= esc($titreQuiz) ?></span>
                            <?= form_button([
                                'type'           => 'submit',
                                'name'           => 'retirer_quiz',
                                'value'          => (string) $idQuiz,
                                'content'        => 'Retirer',
                                'class'          => 'sio-admin__btn-supprimer',
                                'formnovalidate' => true,
                            ]) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?= form_fieldset_close() ?>

        <!-- Énoncé -->
        <div class="glossary-form__field">
            <?= form_label('Libellé', 'champ-libelle', $labelRequis) ?>
            <?= form_textarea([
                'name'     => 'libelle',
                'id'       => 'champ-libelle',
                'value'    => $form->libelle ?? '',
                'rows'     => '3',
                'class'    => $classeChamp('glossary-form__input', 'libelle'),
                'required' => true,
            ]) ?>
            <?= $erreurChamp('libelle') ?>
        </div>

        <!-- Image -->
        <div class="glossary-form__field">
            <?= form_label("Chemin de l'image (facultatif)", 'champ-image', $label) ?>
            <?= form_input([
                'name'             => 'image',
                'id'               => 'champ-image',
                'value'            => $form->image ?? '',
                'placeholder'      => 'images/questions/jwt-expire.png',
                'class'            => $classeChamp('glossary-form__input', 'image'),
                'aria-describedby' => 'aide-image',
            ]) ?>
            <p id="aide-image" class="glossary-form__help">Nom de l'image avec extension dans images/questions/</p>
            <?= $erreurChamp('image') ?>
        </div>

        <!-- Détail (extrait affiché au-dessus de l'énoncé) -->
        <?= form_fieldset('<span class="glossary-form__label">Détail (facultatif)</span>', ['class' => 'sio-admin__bloc-quiz']) ?>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <?= form_label('Extrait', 'champ-detail-mode', $label) ?>
                    <?= form_dropdown('detail_mode', $optionsModes, $modeDetail, [
                        'id'              => 'champ-detail-mode',
                        'class'           => 'sio-admin__select',
                        'data-envoi-auto' => 'true',
                    ]) ?>
                </div>
                <noscript>
                    <?= form_button([
                        'type'           => 'submit',
                        'name'           => 'action',
                        'value'          => 'choisir_theme',
                        'content'        => 'Appliquer',
                        'class'          => 'sio-admin__btn-secondaire',
                        'formnovalidate' => true,
                    ]) ?>
                </noscript>
            </div>

            <?= $erreurChamp('detail_mode') ?>

            <?php if (! empty($infoDetail)): ?>
                <p class="sio-admin__info-detail" role="status"><?= esc($infoDetail) ?></p>
            <?php endif; ?>

            <?= $erreurChamp('extrait') ?>

            <?php if ($modeDetail === 'existant'): ?>
                <!-- Choix parmi les extraits existants -->
                <div class="sio-admin__ligne">
                    <div class="sio-admin__champ">
                        <?= form_label('Extrait existant', 'champ-extrait', $labelRequis) ?>
                        <?= form_dropdown('id_extrait', $optionsExtraits, (string) ($extraitChoisi['id'] ?? ''), [
                            'id'              => 'champ-extrait',
                            'class'           => $classeChamp('sio-admin__select', 'id_extrait'),
                            'data-envoi-auto' => 'true',
                        ]) ?>
                    </div>
                    <noscript>
                        <?= form_button([
                            'type'           => 'submit',
                            'name'           => 'action',
                            'value'          => 'choisir_theme',
                            'content'        => "Voir l'extrait",
                            'class'          => 'sio-admin__btn-secondaire',
                            'formnovalidate' => true,
                        ]) ?>
                    </noscript>
                </div>

                <?= $erreurChamp('id_extrait') ?>

                <?php if ($extraitChoisi !== null): ?>
                    <?php if (($form->extrait_action ?? null) === 'modifier'): ?>
                        <!-- Modification de l'extrait choisi -->
                        <div class="sio-admin__edition-extrait">
                            <div class="glossary-form__field">
                                <?= form_label('Titre', 'champ-edit-titre', $labelRequis) ?>
                                <?= form_input([
                                    'name'      => 'edit_titre',
                                    'id'        => 'champ-edit-titre',
                                    'value'     => $form->edit_titre ?? '',
                                    'maxlength' => '100',
                                    'class'     => $classeChamp('glossary-form__input', 'edit_titre'),
                                ]) ?>
                                <?= $erreurChamp('edit_titre') ?>
                            </div>

                            <div class="glossary-form__field">
                                <?= form_label('Type', 'champ-edit-type', $labelRequis) ?>
                                <?= form_dropdown('edit_type', $optionsTypes, (string) ($form->edit_type ?? ''), [
                                    'id'    => 'champ-edit-type',
                                    'class' => $classeChamp('sio-admin__select', 'edit_type'),
                                ]) ?>
                                <?= $erreurChamp('edit_type') ?>
                            </div>

                            <div class="glossary-form__field">
                                <?= form_label('Contenu', 'champ-edit-contenu', $labelRequis) ?>
                                <?= form_textarea([
                                    'name'  => 'edit_contenu',
                                    'id'    => 'champ-edit-contenu',
                                    'value' => $form->edit_contenu ?? '',
                                    'rows'  => '6',
                                    'class' => $classeChamp('glossary-form__input sio-admin__code', 'edit_contenu'),
                                ]) ?>
                                <?= $erreurChamp('edit_contenu') ?>
                            </div>

                            <div class="sio-admin__ligne-actions">
                                <?= form_button([
                                    'type'           => 'submit',
                                    'name'           => 'action',
                                    'value'          => 'enregistrer_extrait',
                                    'content'        => "Enregistrer l'extrait",
                                    'class'          => 'sio-admin__btn-principal',
                                    'formnovalidate' => true,
                                ]) ?>
                                <?= form_button([
                                    'type'           => 'submit',
                                    'name'           => 'action',
                                    'value'          => 'annuler_extrait',
                                    'content'        => 'Annuler',
                                    'class'          => 'sio-admin__btn-secondaire',
                                    'formnovalidate' => true,
                                ]) ?>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Aperçu de l'extrait choisi -->
                        <pre class="sio-admin__apercu-extrait sio-admin__code"><?= esc($extraitChoisi['contenu']) ?></pre>

                        <div class="sio-admin__ligne-actions">
                            <?= form_button([
                                'type'           => 'submit',
                                'name'           => 'action',
                                'value'          => 'modifier_extrait',
                                'content'        => "Modifier l'extrait",
                                'class'          => 'sio-admin__btn-secondaire',
                                'formnovalidate' => true,
                            ]) ?>
                            <?= form_button([
                                'type'           => 'submit',
                                'name'           => 'action',
                                'value'          => 'supprimer_extrait',
                                'content'        => "Supprimer l'extrait",
                                'class'          => 'sio-admin__btn-supprimer',
                                'formnovalidate' => true,
                            ]) ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            <?php elseif ($modeDetail === 'nouveau'): ?>
                <!-- Création d'un nouvel extrait -->
                <div class="glossary-form__field">
                    <?= form_label('Titre', 'champ-extrait-titre', $labelRequis) ?>
                    <?= form_input([
                        'name'        => 'extrait_titre',
                        'id'          => 'champ-extrait-titre',
                        'value'       => $form->extrait_titre ?? '',
                        'maxlength'   => '100',
                        'placeholder' => 'JWT signé en HS256',
                        'class'       => $classeChamp('glossary-form__input', 'extrait_titre'),
                    ]) ?>
                    <?= $erreurChamp('extrait_titre') ?>
                </div>

                <div class="glossary-form__field">
                    <?= form_label('Type', 'champ-extrait-type', $labelRequis) ?>
                    <?= form_dropdown('extrait_type', ['' => '— Choisir un type —'] + $optionsTypes, (string) ($form->extrait_type ?? ''), [
                        'id'    => 'champ-extrait-type',
                        'class' => $classeChamp('sio-admin__select', 'extrait_type'),
                    ]) ?>
                    <?= $erreurChamp('extrait_type') ?>
                </div>

                <div class="glossary-form__field">
                    <?= form_label('Contenu', 'champ-extrait-contenu', $labelRequis) ?>
                    <?= form_textarea([
                        'name'  => 'extrait_contenu',
                        'id'    => 'champ-extrait-contenu',
                        'value' => $form->extrait_contenu ?? '',
                        'rows'  => '6',
                        'class' => $classeChamp('glossary-form__input sio-admin__code', 'extrait_contenu'),
                    ]) ?>
                    <?= $erreurChamp('extrait_contenu') ?>
                </div>
            <?php endif; ?>

        <?= form_fieldset_close() ?>

        <!-- Bonne réponse -->
        <?= form_fieldset('<span class="glossary-form__label glossary-form__label--required">Bonne réponse</span>', ['class' => 'glossary-form__field sio-admin__fieldset']) ?>
            <div class="sio-admin__radios">
                <?= form_label(
                    form_radio(['name' => 'bonne_reponse', 'id' => 'champ-vrai'], '1', $form->bonne_reponse == 1) . ' Vrai',
                    'champ-vrai',
                    ['class' => 'sio-admin__choix']
                ) ?>
                <?= form_label(
                    form_radio(['name' => 'bonne_reponse', 'id' => 'champ-faux'], '0', $form->bonne_reponse == 0) . ' Faux',
                    'champ-faux',
                    ['class' => 'sio-admin__choix']
                ) ?>
            </div>
        <?= form_fieldset_close() ?>

        <!-- Explication -->
        <div class="glossary-form__field">
            <?= form_label('Explication', 'champ-explication', $labelRequis) ?>
            <?php
            $attributsExplication = [
                'name'  => 'explication',
                'id'    => 'champ-explication',
                'value' => $form->explication ?? '',
                'rows'  => '4',
                'class' => $classeChamp('glossary-form__input', 'explication'),
            ];
            if (isset($errors['explication'])) {
                $attributsExplication['aria-invalid'] = 'true';
                $attributsExplication['aria-describedby'] = 'erreur-explication';
            }
            ?>
            <?= form_textarea($attributsExplication) ?>
            <?= $erreurChamp('explication') ?>
        </div>

        <!-- Active : le champ caché envoie 0 quand la case est décochée -->
        <div class="glossary-form__field">
            <?= form_label(
                form_hidden('actif', '0')
                . form_checkbox(['name' => 'actif', 'id' => 'champ-actif'], '1', $form->actif == 1)
                . ' Question active (visible dans les quiz)',
                'champ-actif',
                ['class' => 'sio-admin__choix']
            ) ?>
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

<!-- Exception autorisée à la règle 0 JS : renvoi du formulaire quand une liste change -->
<?= script_tag(['src' => 'js/sio-admin-theme.js?v=' . filemtime(FCPATH . 'js/sio-admin-theme.js'), 'defer' => null]) ?>
