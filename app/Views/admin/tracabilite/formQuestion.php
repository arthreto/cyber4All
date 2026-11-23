<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Formulaire d'ajout / de modification d'une question
 *
 * Données préparées par le contrôleur (AdminQuestionController::questionAjouter) :
 * - $form         : valeurs du formulaire (modifier, id_theme, libelle, bonne_reponse, explication, actif, image,
 *                   extrait_mode, id_extrait, extrait_titre, extrait_contenu...)
 * - $blocsThemes  : un bloc par thème, avec ses quiz et ses extraits
 *                   [['id', 'nom', 'choisi' => true/false,
 *                     'quiz' => [['id', 'titre', 'coche', 'actif'], ...],
 *                     'extraits' => [['id', 'titre', 'contenu', 'debut', 'choisi', 'actif'], ...]], ...]
 * - $errors          : erreurs de validation [champ => message]
 * - $raisonActivation : question inactive en base, raison qui l'empêche d'être activée (null sinon) ;
 *                      un extrait supprimé n'y figure pas, il porte un badge « Inactif »
 *
 * Les extraits se modifient et se suppriment dans l'onglet « Extrait » (AdminExtraitController) :
 * ici, on en choisit un existant ou on en crée un nouveau.
 * - $modal           : true si le formulaire est affiché dans le modal du formulaire d'un quiz
 *                      (iframe, adresse avec ?modal=1) : pas de lien de retour, « Annuler » ferme le modal
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

$estModification = ! empty($form->modifier);
if ($estModification) {
    $actionFormulaire = 'floribondant/theme/questions/modifier/' . (int) $form->modifier;
} else {
    $actionFormulaire = 'floribondant/theme/questions/ajouter';
}

$modal = $modal ?? false;
if ($modal) {
    $actionFormulaire = site_url($actionFormulaire) . '?modal=1';
}

// Option choisie pour l'extrait (aucun, existant, nouveau)
$modeExtrait = $form->extrait_mode ?? 'aucun';

// Classes des libellés
$label = ['class' => 'glossary-form__label'];
$labelRequis = ['class' => 'glossary-form__label glossary-form__label--required'];

$classeChamp = fn (string $classe, string $champ): string
    => $classe . (isset($errors[$champ]) ? ' sio-admin__input--erreur' : '');

$erreurChamp = fn (string $champ): string
    => isset($errors[$champ]) ? '<p class="sio-admin__erreur">' . esc($errors[$champ]) . '</p>' : '';
?>
<div class="sio-admin sio-admin--formulaire sio-admin--formulaire-xl">

    <div class="glossary-form">

        <!-- Titre de la page et lien de retour sur la même ligne (dans le modal, le titre est dans l'en-tête du modal) -->
        <?php if (! $modal): ?>
            <div class="sio-admin__entete-form">
                <h2 class="glossary-form__dialog-title"><?= $estModification ? 'Modifier la question' : 'Nouvelle question' ?></h2>
                <?= anchor('floribondant/theme', '← Retour aux questions', ['class' => 'sio-admin__retour']) ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($errors)): ?>
            <!-- Récapitulatif des erreurs (ul() n'échappe pas les éléments : esc() sur tout le tableau) -->
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>La question n'a pas été enregistrée. Corrigez le(s) point(s) suivant(s) :</strong>
                <?= ul(esc($errors), ['class' => 'sio-admin__erreur-liste']) ?>
            </div>
        <?php elseif (! empty($raisonActivation)): ?>
            <!-- Question inactive qui ne peut pas être activée (un extrait supprimé a son badge « Inactif ») -->
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>Cette question ne peut pas être activée :</strong>
                <?= esc($raisonActivation) ?>
            </div>
        <?php endif; ?>

        <!-- form_open_multipart : enctype="multipart/form-data", nécessaire pour envoyer le fichier de l'image -->
        <?= form_open_multipart($actionFormulaire, ['class' => 'glossary-form__form']) ?>

        <div class="sio-admin__colonnes">

            <!-- ===== Colonne de gauche : la question ===== -->
            <div class="sio-admin__colonne">

                <?= form_fieldset('<span class="glossary-form__label">Question</span>', ['class' => 'sio-admin__bloc-quiz']) ?>

                    <!-- Thème : il choisit les quiz et les extraits proposés. -->
                    <?php
                    $attributsTheme = ['class' => 'sio-admin__fieldset'];
                    if ($modal) {
                        $attributsTheme['hidden'] = 'hidden';
                    }
                    ?>
                    <?= form_fieldset('<span class="glossary-form__label glossary-form__label--required">Thème</span>', $attributsTheme) ?>
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
                                                // Extrait supprimé (désactivé) : la question ne peut pas être active avec lui
                                                . ($extrait['actif'] ? '' : '<span class="sio-admin__badge sio-admin__badge--faux">Inactif</span>')
                                                . '<span class="sio-admin__extrait-titre">' . esc($extrait['titre']) . '</span>'
                                                . '<code class="sio-admin__extrait-debut">' . esc($extrait['debut']) . '</code>'
                                                . '</span>',
                                                'extrait-' . $extrait['id'],
                                                [
                                                    'class' => 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--petit sio-admin__choix--extrait',
                                                    'title' => esc((string) $extrait['contenu'], 'attr'),
                                                ]
                                            ) ?>

                                            <!-- Aperçu de l'extrait coché. Modifier ou supprimer un extrait :
                                                 onglet « Extrait » (AdminExtraitController) -->
                                            <div class="sio-admin__extrait-contenu">
                                                <pre class="sio-admin__apercu-extrait sio-admin__code"><?= esc($extrait['contenu']) ?></pre>
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
                    <p class="glossary-form__help">Cochez les quiz où la question apparaît.</p>
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
                                        ])
                                        // Quiz supprimé (désactivé) : badge « Inactif » devant le titre
                                        . ($quiz['actif'] ? '' : '<span class="sio-admin__badge sio-admin__badge--faux">Inactif</span>')
                                        . esc($quiz['titre']),
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

                        <!-- Retire l'image de la question à l'enregistrement -->
                        <?= form_label(
                            form_checkbox(['name' => 'retirer_image', 'id' => 'champ-retirer-image'], '1', false)
                            . 'Retirer l\'image',
                            'champ-retirer-image',
                            ['class' => 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--petit']
                        ) ?>
                    <?php endif; ?>

                    <!-- Fichier envoyé au serveur (AdminQuestionController::gererImage), rangé dans images/questions/ -->
                    <div class="glossary-form__field">
                        <?= form_label(empty($form->image) ? 'Fichier' : 'Remplacer par', 'champ-image', $label) ?>
                        <?= form_upload([
                            'name'             => 'fichier_image',
                            'id'               => 'champ-image',
                            'accept'           => 'image/png,image/jpeg,image/webp',
                            'class'            => $classeChamp('glossary-form__input', 'image'),
                            'aria-describedby' => 'aide-image',
                        ]) ?>
                        <p id="aide-image" class="glossary-form__help">PNG, JPG ou WEBP, 2 Mo au maximum.</p>
                        <?= $erreurChamp('image') ?>
                    </div>
                <?= form_fieldset_close() ?>
            </div>
        </div>

        <!-- Barre d'enregistrement : toujours visible en bas de l'écran -->
        <div class="sio-admin__barre">
            <?php if ($modal): ?>
                <!-- Ferme le modal sans enregistrer (js/sio-admin-modal-question.js) -->
                <?= form_button([
                    'type'              => 'button',
                    'content'           => 'Annuler',
                    'class'             => 'sio-admin__btn-secondaire',
                    'data-modal-fermer' => '',
                ]) ?>
            <?php else: ?>
                <?= anchor('floribondant/theme', 'Annuler', ['class' => 'sio-admin__btn-secondaire']) ?>
            <?php endif; ?>
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


