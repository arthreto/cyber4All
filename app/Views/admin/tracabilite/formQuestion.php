<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Formulaire d'ajout / de modification d'une question
 *
 * Données préparées par le contrôleur (AdminQuestionController::questionAjouter) :
 * - $form         : valeurs du formulaire (modifier, id_theme, libelle, bonne_reponse, explication, actif, image,
 *                   extrait_mode, id_extrait, extrait_action, edit_titre, edit_type, edit_contenu...)
 * - $blocsThemes  : un bloc par thème, avec ses quiz et ses extraits
 *                   [['id', 'nom', 'choisi' => true/false,
 *                     'quiz' => [['id', 'titre', 'coche'], ...],
 *                     'extraits' => [['id', 'titre', 'contenu', 'debut', 'choisi'], ...]], ...]
 * - $typesExtrait : types d'extrait [valeur => libellé] (modification d'un extrait)
 * - $infoExtrait     : message après une action sur un extrait (modifié, supprimé), ou null
 * - $errors          : erreurs de validation [champ => message]
 *
 * Mise en page sur deux colonnes :
 * - à gauche, la question (thème, énoncé, bonne réponse, explication) et l'extrait facultatif ;
 * - à droite, les quiz, la visibilité et l'image.
 *
 * Thème : les extraits et les quiz sont rangés dans un bloc par thème (data-theme="id du thème").
 * Le script js/sio-admin-theme.js (partie 2) n'affiche que le bloc du thème coché.
 * Sans JavaScript, tous les blocs restent affichés et le contrôleur vérifie le thème à l'enregistrement.
 *
 * Le bloc affiché pour l'extrait dépend de l'option choisie (aucun, existant, nouveau) :
 * le changement se fait en CSS, sans recharger la page (voir adminSio.css, section « Questions »).
 *
 * Attention : form_dropdown, form_label et form_fieldset n'échappent pas les libellés, d'où les esc().
 *
 * @author Arthur
 */

$errors = $errors ?? [];

// Mode du formulaire : en modification, l'id de la question est dans l'URL
$estModification = ! empty($form->modifier);
if ($estModification) {
    $actionFormulaire = 'floribondant/theme/questions/modifier/' . (int) $form->modifier;
} else {
    $actionFormulaire = 'floribondant/theme/questions/ajouter';
}

// Option choisie pour l'extrait (aucun, existant, nouveau), et extrait en cours de modification
$modeExtrait = $form->extrait_mode ?? 'aucun';
$extraitEnModification = ($form->extrait_action ?? null) === 'modifier';

// Classes des libellés
$label = ['class' => 'glossary-form__label'];
$labelRequis = ['class' => 'glossary-form__label glossary-form__label--required'];

// Types d'extrait pour les listes déroulantes (libellés échappés)
$optionsTypes = esc($typesExtrait);

// Deux petites fonctions anonymes (des fonctions sans nom, rangées dans une variable).
// On les appelle comme une fonction normale : $erreurChamp('libelle').
// fn (...) => ... est l'écriture courte : la fonction renvoie directement le résultat après =>.
// use ($errors) n'est pas nécessaire avec fn : elle voit automatiquement les variables du fichier.

// Classe d'un champ, avec en plus la classe d'erreur si le champ est en erreur
$classeChamp = fn (string $classe, string $champ): string
    => $classe . (isset($errors[$champ]) ? ' sio-admin__input--erreur' : '');

// Message d'erreur à afficher sous un champ (texte vide s'il n'y a pas d'erreur)
$erreurChamp = fn (string $champ): string
    => isset($errors[$champ]) ? '<p class="sio-admin__erreur">' . esc($errors[$champ]) . '</p>' : '';
?>
<div class="sio-admin sio-admin--formulaire sio-admin--formulaire-xl">

    <div class="glossary-form">

        <!-- Titre de la page et lien de retour sur la même ligne -->
        <div class="sio-admin__entete-form">
            <h2 class="glossary-form__dialog-title"><?= $estModification ? 'Modifier la question' : 'Nouvelle question' ?></h2>
            <?= anchor('floribondant/theme', '← Retour aux questions', ['class' => 'sio-admin__retour']) ?>
        </div>

        <?php if (! empty($errors)): ?>
            <!-- Récapitulatif des erreurs (ul() n'échappe pas les éléments : esc() sur tout le tableau) -->
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>La question n'a pas été enregistrée. Corrigez les points suivants :</strong>
                <?= ul(esc($errors), ['class' => 'sio-admin__erreur-liste']) ?>
            </div>
        <?php endif; ?>

        <?= form_open($actionFormulaire, ['class' => 'glossary-form__form']) ?>

        <div class="sio-admin__colonnes">

            <!-- ===== Colonne de gauche : la question ===== -->
            <div class="sio-admin__colonne">

                <?= form_fieldset('<span class="glossary-form__label">Question</span>', ['class' => 'sio-admin__bloc-quiz']) ?>

                    <!-- Thème : il choisit les quiz et les extraits proposés -->
                    <?= form_fieldset('<span class="glossary-form__label glossary-form__label--required">Thème</span>', ['class' => 'sio-admin__fieldset']) ?>
                        <div class="sio-admin__radios sio-admin__radios--cartes<?= isset($errors['id_theme']) ? ' sio-admin__radios--erreur' : '' ?>">
                            <?php foreach ($blocsThemes as $theme): ?>
                                <?= form_label(
                                    form_radio(['name' => 'id_theme', 'id' => 'theme-' . $theme['id'], 'required' => true], (string) $theme['id'], $theme['choisi']) . esc($theme['nom']),
                                    'theme-' . $theme['id'],
                                    ['class' => 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--petit']
                                ) ?>
                            <?php endforeach; ?>
                        </div>
                        <?= $erreurChamp('id_theme') ?>
                    <?= form_fieldset_close() ?>

                    <!-- Énoncé -->
                    <div class="glossary-form__field">
                        <?= form_label('Énoncé', 'champ-libelle', $labelRequis) ?>
                        <?= form_textarea([
                            'name'        => 'libelle',
                            'id'          => 'champ-libelle',
                            'value'       => $form->libelle ?? '',
                            'rows'        => '3',
                            'placeholder' => 'Une affirmation à juger vraie ou fausse',
                            'class'       => $classeChamp('glossary-form__input sio-admin__textarea--fixe', 'libelle'),
                            'required'    => true,
                        ]) ?>
                        <?= $erreurChamp('libelle') ?>
                    </div>

                    <!-- Bonne réponse : rien n'est coché pour une nouvelle question -->
                    <?= form_fieldset('<span class="glossary-form__label glossary-form__label--required">Bonne réponse</span>', ['class' => 'sio-admin__fieldset']) ?>
                        <div class="sio-admin__radios sio-admin__radios--cartes<?= isset($errors['bonne_reponse']) ? ' sio-admin__radios--erreur' : '' ?>">
                            <?= form_label(
                                form_radio(['name' => 'bonne_reponse', 'id' => 'champ-vrai', 'required' => true], '1', (string) $form->bonne_reponse === '1') . 'Vrai',
                                'champ-vrai',
                                ['class' => 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--vrai']
                            ) ?>
                            <?= form_label(
                                form_radio(['name' => 'bonne_reponse', 'id' => 'champ-faux', 'required' => true], '0', (string) $form->bonne_reponse === '0') . 'Faux',
                                'champ-faux',
                                ['class' => 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--faux']
                            ) ?>
                        </div>
                        <?= $erreurChamp('bonne_reponse') ?>
                    <?= form_fieldset_close() ?>

                    <!-- Explication -->
                    <div class="glossary-form__field">
                        <?= form_label('Explication', 'champ-explication', $labelRequis) ?>
                        <?= form_textarea([
                            'name'        => 'explication',
                            'id'          => 'champ-explication',
                            'value'       => $form->explication ?? '',
                            'rows'        => '4',
                            'placeholder' => 'Affichée après la réponse : pourquoi est-ce vrai ou faux ?',
                            'class'       => $classeChamp('glossary-form__input sio-admin__textarea--fixe', 'explication'),
                            'required'    => true,
                        ]) ?>
                        <?= $erreurChamp('explication') ?>
                    </div>

                <?= form_fieldset_close() ?>

                <!-- Extrait facultatif : le bloc affiché dépend de l'option choisie -->
                <?= form_fieldset('<span class="glossary-form__label">Extrait affiché au-dessus de l\'énoncé (facultatif)</span>', ['class' => 'sio-admin__bloc-quiz sio-admin__bloc-extrait']) ?>

                    <div class="sio-admin__radios sio-admin__radios--cartes">
                        <?php foreach (['aucun' => 'Aucun extrait', 'existant' => 'Choisir un extrait existant', 'nouveau' => 'Créer un extrait'] as $valeur => $libelle): ?>
                            <?= form_label(
                                form_radio(['name' => 'extrait_mode', 'id' => 'mode-extrait-' . $valeur], $valeur, $modeExtrait === $valeur) . $libelle,
                                'mode-extrait-' . $valeur,
                                ['class' => 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--petit']
                            ) ?>
                        <?php endforeach; ?>
                    </div>

                    <?= $erreurChamp('extrait_mode') ?>

                    <?php if (! empty($infoExtrait)): ?>
                        <p class="sio-admin__info-extrait" role="status"><?= esc($infoExtrait) ?></p>
                    <?php endif; ?>

                    <?= $erreurChamp('extrait') ?>

                    <!-- Choix d'un extrait existant : un bloc par thème, l'aperçu s'ouvre sous l'extrait coché -->
                    <div class="sio-admin__extrait-bloc sio-admin__extrait-bloc--existant">
                        <?= $erreurChamp('id_extrait') ?>

                        <!-- Affiché par le script tant qu'aucun thème n'est coché -->
                        <p class="glossary-form__help sio-admin__choisir-theme" hidden>Choisissez d'abord le thème de la question.</p>

                        <?php foreach ($blocsThemes as $theme): ?>
                            <div class="sio-admin__par-theme" data-theme="<?= $theme['id'] ?>">
                                <p class="sio-admin__par-theme-titre">Extraits du thème <?= esc($theme['nom']) ?></p>

                                <?php if ($theme['extraits'] === []): ?>
                                    <p class="glossary-form__help">Aucun extrait pour ce thème : choisissez « Créer un extrait ».</p>
                                <?php endif; ?>

                                <div class="sio-admin__extraits">
                                    <?php foreach ($theme['extraits'] as $extrait): ?>
                                        <div class="sio-admin__extrait">
                                            <!-- Titre, puis début du contenu ; contenu complet au survol (title).
                                                 form_label() n'échappe pas ses attributs : esc() est nécessaire -->
                                            <?= form_label(
                                                form_radio(['name' => 'id_extrait', 'id' => 'extrait-' . $extrait['id']], (string) $extrait['id'], $extrait['choisi'])
                                                . '<span class="sio-admin__extrait-texte">'
                                                . '<span class="sio-admin__extrait-titre">' . esc($extrait['titre']) . '</span>'
                                                . '<code class="sio-admin__extrait-debut">' . esc($extrait['debut']) . '</code>'
                                                . '</span>',
                                                'extrait-' . $extrait['id'],
                                                [
                                                    'class' => 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--petit sio-admin__choix--extrait',
                                                    'title' => esc((string) $extrait['contenu'], 'attr'),
                                                ]
                                            ) ?>

                                            <div class="sio-admin__extrait-contenu">
                                                <?php if ($extrait['choisi'] && $extraitEnModification): ?>
                                                    <!-- Modification de l'extrait choisi -->
                                                    <div class="glossary-form__field">
                                                        <?= form_label('Titre de l\'extrait', 'champ-edit-titre', $labelRequis) ?>
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
                                                        <?= form_label('Contenu de l\'extrait', 'champ-edit-contenu', $labelRequis) ?>
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
                                                            'content'        => 'Enregistrer l\'extrait',
                                                            'class'          => 'sio-admin__btn-principal sio-admin__btn--petit',
                                                            'formnovalidate' => true,
                                                        ]) ?>
                                                        <?= form_button([
                                                            'type'           => 'submit',
                                                            'name'           => 'action',
                                                            'value'          => 'annuler_extrait',
                                                            'content'        => 'Annuler',
                                                            'class'          => 'sio-admin__btn-secondaire sio-admin__btn--petit',
                                                            'formnovalidate' => true,
                                                        ]) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <!-- Aperçu de l'extrait -->
                                                    <pre class="sio-admin__apercu-extrait sio-admin__code"><?= esc($extrait['contenu']) ?></pre>

                                                    <div class="sio-admin__ligne-actions">
                                                        <?= form_button([
                                                            'type'           => 'submit',
                                                            'name'           => 'action',
                                                            'value'          => 'modifier_extrait',
                                                            'content'        => 'Modifier cet extrait',
                                                            'class'          => 'sio-admin__btn-secondaire sio-admin__btn--petit',
                                                            'formnovalidate' => true,
                                                        ]) ?>
                                                        <?= form_button([
                                                            'type'           => 'submit',
                                                            'name'           => 'action',
                                                            'value'          => 'supprimer_extrait',
                                                            'content'        => 'Supprimer cet extrait',
                                                            'class'          => 'sio-admin__btn-supprimer',
                                                            'formnovalidate' => true,
                                                            'onclick'        => "return confirm('Supprimer définitivement cet extrait ? Il ne sera plus disponible pour aucune question.');",
                                                        ]) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Création d'un nouvel extrait : son type est celui du thème de la question -->
                    <div class="sio-admin__extrait-bloc sio-admin__extrait-bloc--nouveau">
                        <div class="glossary-form__field">
                            <?= form_label('Titre de l\'extrait', 'champ-extrait-titre', $labelRequis) ?>
                            <?= form_input([
                                'name'        => 'extrait_titre',
                                'id'          => 'champ-extrait-titre',
                                'value'       => $form->extrait_titre ?? '',
                                'maxlength'   => '100',
                                'placeholder' => 'JWT signé en HS256',
                                'class'       => $classeChamp('glossary-form__input', 'extrait_titre'),
                            ]) ?>
                            <?= $erreurChamp('extrait_titre') ?>
                            <?= $erreurChamp('extrait_type') ?>
                        </div>

                        <div class="glossary-form__field">
                            <?= form_label('Contenu de l\'extrait', 'champ-extrait-contenu', $labelRequis) ?>
                            <?= form_textarea([
                                'name'  => 'extrait_contenu',
                                'id'    => 'champ-extrait-contenu',
                                'value' => $form->extrait_contenu ?? '',
                                'rows'  => '6',
                                'class' => $classeChamp('glossary-form__input sio-admin__code', 'extrait_contenu'),
                            ]) ?>
                            <?= $erreurChamp('extrait_contenu') ?>
                        </div>
                    </div>

                <?= form_fieldset_close() ?>
            </div>

            <!-- ===== Colonne de droite : quiz, visibilité, image ===== -->
            <div class="sio-admin__colonne">

                <!-- Quiz : un bloc par thème, seul celui du thème coché est affiché -->
                <?= form_fieldset('<span class="glossary-form__label">Quiz de la question</span>', ['class' => 'sio-admin__bloc-quiz']) ?>
                    <p class="glossary-form__help">Cochez les quiz où la question apparaît. Sans quiz, elle n'est proposée nulle part.</p>
                    <?= $erreurChamp('quiz') ?>

                    <!-- Affiché par le script tant qu'aucun thème n'est coché -->
                    <p class="glossary-form__help sio-admin__choisir-theme" hidden>Choisissez d'abord le thème de la question.</p>

                    <?php foreach ($blocsThemes as $theme): ?>
                        <div class="sio-admin__par-theme" data-theme="<?= $theme['id'] ?>">
                            <p class="sio-admin__par-theme-titre">Quiz du thème <?= esc($theme['nom']) ?></p>

                            <?php if ($theme['quiz'] === []): ?>
                                <p class="glossary-form__help">Ce thème n'a pas encore de quiz.</p>
                            <?php endif; ?>

                            <div class="sio-admin__radios sio-admin__radios--cartes">
                                <?php foreach ($theme['quiz'] as $quiz): ?>
                                    <?= form_label(
                                        form_checkbox([
                                            'name'    => 'quiz[]',
                                            'id'      => 'quiz-' . $quiz['id'],
                                            'value'   => (string) $quiz['id'],
                                            'checked' => $quiz['coche'],
                                        ]) . esc($quiz['titre']),
                                        'quiz-' . $quiz['id'],
                                        ['class' => 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--petit']
                                    ) ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?= form_fieldset_close() ?>

                <!-- Visibilité : le champ caché envoie 0 quand la case est décochée -->
                <?= form_fieldset('<span class="glossary-form__label">Visibilité</span>', ['class' => 'sio-admin__bloc-quiz']) ?>
                    <?= form_label(
                        form_hidden('actif', '0')
                        . form_checkbox(['name' => 'actif', 'id' => 'champ-actif'], '1', (string) $form->actif === '1')
                        . 'Question active',
                        'champ-actif',
                        ['class' => 'sio-admin__choix sio-admin__choix--carte']
                    ) ?>
                    <p class="glossary-form__help">Une question inactive reste enregistrée mais n'est plus proposée dans les quiz.</p>
                <?= form_fieldset_close() ?>

                <!-- Image facultative -->
                <?= form_fieldset('<span class="glossary-form__label">Image (facultatif)</span>', ['class' => 'sio-admin__bloc-quiz']) ?>
                    <?php if (! empty($form->image)): ?>
                        <?= img([
                            'src'   => esc('images/questions/' . $form->image, 'attr'),
                            'alt'   => 'Image actuelle de la question',
                            'class' => 'sio-admin__apercu-image',
                        ]) ?>
                    <?php endif; ?>

                    <div class="glossary-form__field">
                        <?= form_label('Nom du fichier', 'champ-image', $label) ?>
                        <?= form_input([
                            'name'             => 'image',
                            'id'               => 'champ-image',
                            'value'            => $form->image ?? '',
                            'placeholder'      => 'jwt-expire.png',
                            'class'            => $classeChamp('glossary-form__input', 'image'),
                            'aria-describedby' => 'aide-image',
                        ]) ?>
                        <p id="aide-image" class="glossary-form__help">Fichier placé dans images/questions/ (PNG, JPG, WEBP ou AVIF).</p>
                        <?= $erreurChamp('image') ?>
                    </div>
                <?= form_fieldset_close() ?>
            </div>
        </div>

        <!-- Barre d'enregistrement : toujours visible en bas de l'écran -->
        <div class="sio-admin__barre">
            <?= anchor('floribondant/theme', 'Annuler', ['class' => 'sio-admin__btn-secondaire']) ?>
            <!-- form_button() et non form_submit() : le contrôleur attend action = "enregistrer" -->
            <?= form_button([
                'type'    => 'submit',
                'name'    => 'action',
                'value'   => 'enregistrer',
                'content' => $estModification ? 'Enregistrer les modifications' : 'Créer la question',
                'class'   => 'sio-admin__btn-principal',
            ]) ?>
        </div>

        <?= form_close() ?>
    </div>
</div>

<!-- Confort : position de défilement, et n'affiche que les quiz et les extraits du thème coché -->
<?= script_tag(['src' => 'js/sio-admin-theme.js?v=' . filemtime(FCPATH . 'js/sio-admin-theme.js'), 'defer' => null]) ?>
