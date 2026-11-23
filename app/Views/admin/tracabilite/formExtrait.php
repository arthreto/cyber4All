<?php
/**
 * ADMINISTRATION - Espace Traçabilité
 * Formulaire d'ajout / de modification d'un extrait
 *
 * Données préparées par le contrôleur (AdminExtraitController::extraitAjouter) :
 * - $form         : modifier (id de l'extrait en modification, vide en ajout), titre, type, contenu
 * - $typesExtrait : types possibles [clé => libellé] (ExtraitModel::TYPES)
 * - $questions    : questions qui utilisent l'extrait (tableaux : id, libelle, actif), vide en ajout
 * - $errors       : erreurs de validation [champ => message]
 *
 * Le type est le nom du thème au singulier : l'extrait n'est proposé qu'aux questions de ce thème.
 *
 * Attention : form_label et form_fieldset n'échappent pas les libellés, d'où les esc().
 * Styles : sio/adminSio.css, formMot.css
 *
 * @author Arthur
 */

$errors = $errors ?? [];

// Mode du formulaire : en modification, l'id de l'extrait est dans l'URL
$estModification = ! empty($form->modifier);
if ($estModification) {
    $actionFormulaire = 'floribondant/theme/extrait/modifier/' . (int) $form->modifier;
} else {
    $actionFormulaire = 'floribondant/theme/extrait/ajouter';
}

// Classes des libellés
$label = ['class' => 'glossary-form__label'];
$labelRequis = ['class' => 'glossary-form__label glossary-form__label--required'];

// Classe d'un champ, avec en plus la classe d'erreur si le champ est en erreur
$classeChamp = fn (string $classe, string $champ): string
    => $classe . (isset($errors[$champ]) ? ' sio-admin__input--erreur' : '');

// Message d'erreur à afficher sous un champ (texte vide s'il n'y a pas d'erreur)
$erreurChamp = fn (string $champ): string
    => isset($errors[$champ]) ? '<p class="sio-admin__erreur">' . esc($errors[$champ]) . '</p>' : '';

$nbQuestions = count($questions);
?>
<div class="sio-admin sio-admin--formulaire">

    <div class="glossary-form">

        <!-- Titre de la page et lien de retour sur la même ligne -->
        <div class="sio-admin__entete-form">
            <h2 class="glossary-form__dialog-title"><?= $estModification ? 'Modifier l\'extrait' : 'Nouvel extrait' ?></h2>
            <?= anchor('floribondant/theme/extrait', '← Retour aux extraits', ['class' => 'sio-admin__retour']) ?>
        </div>

        <?php if (! empty($errors)): ?>
            <!-- Récapitulatif des erreurs (ul() n'échappe pas les éléments : esc() sur tout le tableau) -->
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>L'extrait n'a pas été enregistré. Corrigez le(s) point(s) suivant(s) :</strong>
                <?= ul(esc($errors), ['class' => 'sio-admin__erreur-liste']) ?>
            </div>
        <?php endif; ?>

        <?= form_open($actionFormulaire, ['class' => 'glossary-form__form']) ?>

        <?= form_fieldset('<span class="glossary-form__label">Extrait</span>', ['class' => 'sio-admin__bloc-quiz']) ?>

            <!-- Titre -->
            <div class="glossary-form__field">
                <?= form_label('Titre', 'champ-titre', $labelRequis) ?>
                <?= form_input([
                    'name'        => 'titre',
                    'id'          => 'champ-titre',
                    'value'       => $form->titre ?? '',
                    'maxlength'   => '100',
                    'placeholder' => 'JWT signé en HS256',
                    'class'       => $classeChamp('glossary-form__input', 'titre'),
                    'required'    => true,
                ]) ?>
                <?= $erreurChamp('titre') ?>
            </div>

            <!-- Type : un bouton radio par type -->
            <?= form_fieldset('<span class="glossary-form__label glossary-form__label--required">Type</span>', ['class' => 'sio-admin__fieldset']) ?>
                <div class="sio-admin__radios sio-admin__radios--cartes<?= isset($errors['type']) ? ' sio-admin__radios--erreur' : '' ?>">
                    <?php foreach ($typesExtrait as $type => $libelle): ?>
                        <?= form_label(
                            form_radio([
                                'name'     => 'type',
                                'id'       => 'type-' . $type,
                                'value'    => $type,
                                'checked'  => $type === (string) ($form->type ?? ''),
                                'required' => true,
                            ]) . esc($libelle),
                            'type-' . $type,
                            ['class' => 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--petit']
                        ) ?>
                    <?php endforeach; ?>
                </div>
                <p class="glossary-form__help">L'extrait n'est proposé qu'aux questions du thème correspondant.</p>
                <?= $erreurChamp('type') ?>
            <?= form_fieldset_close() ?>

            <!-- Contenu : affiché tel quel au-dessus de l'énoncé de la question -->
            <div class="glossary-form__field">
                <?= form_label('Contenu', 'champ-contenu', $labelRequis) ?>
                <?= form_textarea([
                    'name'        => 'contenu',
                    'id'          => 'champ-contenu',
                    'value'       => $form->contenu ?? '',
                    'rows'        => '12',
                    'maxlength'   => '5000',
                    'spellcheck'  => 'false',
                    'placeholder' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
                    'class'       => $classeChamp('glossary-form__input sio-admin__textarea--fixe sio-admin__code', 'contenu'),
                    'required'    => true,
                ]) ?>
                <?= $erreurChamp('contenu') ?>
            </div>

        <?= form_fieldset_close() ?>

        <?php if ($estModification): ?>
            <!-- Questions qui utilisent l'extrait : une modification les concerne toutes -->
            <?= form_fieldset('<span class="glossary-form__label">Questions qui utilisent cet extrait</span>', ['class' => 'sio-admin__bloc-quiz']) ?>
                <?php if ($nbQuestions === 0): ?>
                    <p class="glossary-form__help">Aucune question n'utilise cet extrait pour l'instant.</p>
                <?php else: ?>
                    <p class="glossary-form__help">
                        <?= $nbQuestions ?> question<?= $nbQuestions > 1 ? 's' : '' ?> :
                        les modifications s'appliquent à toutes.
                    </p>
                    <?php
                    // ul() n'échappe pas : un lien par question, énoncé échappé
                    $liensQuestions = [];
                    foreach ($questions as $question) {
                        $texte = esc(mb_strimwidth((string) $question['libelle'], 0, 120, '…'));
                        if ((int) $question['actif'] !== 1) {
                            $texte .= ' <span class="mots-table__author--na">(inactive)</span>';
                        }
                        $liensQuestions[] = anchor('floribondant/theme/questions/modifier/' . (int) $question['id'], $texte, ['class' => 'mots-table__link']);
                    }
                    ?>
                    <?= ul($liensQuestions, ['class' => 'sio-admin__liste-questions']) ?>
                <?php endif; ?>
            <?= form_fieldset_close() ?>
        <?php endif; ?>

        <!-- Barre d'enregistrement : toujours visible en bas de l'écran -->
        <div class="sio-admin__barre">
            <?= anchor('floribondant/theme/extrait', 'Annuler', ['class' => 'sio-admin__btn-secondaire']) ?>
            <!-- form_button() et non form_submit() : le contrôleur attend action = "enregistrer" -->
            <?= form_button([
                'type'    => 'submit',
                'name'    => 'action',
                'value'   => 'enregistrer',
                'content' => $estModification ? 'Enregistrer les modifications' : 'Créer l\'extrait',
                'class'   => 'sio-admin__btn-principal',
            ]) ?>
        </div>

        <?= form_close() ?>
    </div>
</div>


