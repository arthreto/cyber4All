<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Formulaire d'ajout / de modification d'un quiz
 *
 * Données préparées par le contrôleur (AdminQuizController::quizAjouter) :
 * - $form          : modifier (id du quiz en modification, vide en ajout), id_theme, titre,
 *                    description, nb_questions, actif, questions (identifiants des questions du quiz)
 * - $themeList     : liste des thèmes (id, nom)
 * - $questionsQuiz : questions du quiz (tableaux : id, libelle, bonne_reponse, id_theme,
 *                    autre_theme = true si la question n'est pas du thème du quiz)
 * - $blocsThemes   : questions à ajouter, un bloc par thème
 *                    [['id', 'nom', 'choisi' => true/false, 'questions' => [...]], ...]
 * - $errors        : erreurs de validation [champ => message]
 * - $raisonActivation : quiz inactif en base, raison qui l'empêche d'être activé (null sinon) ;
 *                    les questions inactives n'y figurent pas, elles portent un badge « Inactive »
 *
 * Mise en page sur deux colonnes :
 * - à gauche, le quiz (thème, titre, description, nombre de questions) et sa visibilité ;
 * - à droite, ses questions : une case à cocher par question. Cocher ajoute, décocher retire.
 *   Tout est enregistré en une fois avec le bouton du bas.
 *
 * Règles : un quiz n'a qu'un thème et seulement des questions de ce thème ;
 * il ne dépasse jamais son « nombre de questions » (nb_questions, entre 5 et 20).
 * Avec moins de questions (même aucune), il est enregistré mais reste inactif jusqu'à être complet.
 *
 * JavaScript, facultatif : le formulaire fonctionne sans (le contrôleur vérifie les règles).
 * js/sio-admin-theme.js n'affiche que les questions du thème coché (partie 2), et gère la recherche,
 * la limite du nombre de questions et le récapitulatif dans la barre du bas (partie 3).
 *
 * Attention : form_label et form_fieldset n'échappent pas les libellés, d'où les esc().
 * Styles : sio/adminSio.css (sections « Leçons », « Questions » et « Quiz »), formMot.css
 *
 * @author Arthur
 */

$errors = $errors ?? [];

// Mode du formulaire : en modification, l'id du quiz est dans l'URL
$estModification = ! empty($form->modifier);
if ($estModification) {
    $actionFormulaire = 'floribondant/theme/quiz/modifier/' . (int) $form->modifier;
} else {
    $actionFormulaire = 'floribondant/theme/quiz/ajouter';
}

$nbQuestions = count($questionsQuiz);

// Classes des libellés
$label = ['class' => 'glossary-form__label'];
$labelRequis = ['class' => 'glossary-form__label glossary-form__label--required'];

// Fonctions anonymes : des fonctions sans nom, rangées dans une variable.
// On les appelle comme une fonction normale : $erreurChamp('titre').
// fn (...) => ... est l'écriture courte : la fonction renvoie directement le résultat après =>,
// et elle voit automatiquement les variables du fichier (ici $errors).

// Classe d'un champ, avec en plus la classe d'erreur si le champ est en erreur
$classeChamp = fn (string $classe, string $champ): string
    => $classe . (isset($errors[$champ]) ? ' sio-admin__input--erreur' : '');

// Message d'erreur à afficher sous un champ (texte vide s'il n'y a pas d'erreur)
$erreurChamp = fn (string $champ): string
    => isset($errors[$champ]) ? '<p class="sio-admin__erreur">' . esc($errors[$champ]) . '</p>' : '';

// Case à cocher d'une question : case, énoncé, puis badge Vrai / Faux.
// Elle sert dans « Dans ce quiz » et dans les questions à ajouter : une fonction évite d'écrire ce code deux fois.
// Écriture longue (function ... { ... return ...; }) car elle contient plusieurs lignes.
$caseQuestion = function (array $question, bool $cochee): string {
    $id = 'question-' . $question['id'];

    if ((string) $question['bonne_reponse'] === '1') {
        $badge = '<span class="sio-admin__badge sio-admin__badge--vrai">Vrai</span>';
    } else {
        $badge = '<span class="sio-admin__badge sio-admin__badge--faux">Faux</span>';
    }

    // Question d'un autre thème que le quiz : à décocher pour pouvoir enregistrer
    $classe = 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--question';
    if (! empty($question['autre_theme'])) {
        $classe .= ' sio-admin__choix--autre-theme';
        $badge = '<span class="sio-admin__badge sio-admin__badge--faux">Autre thème</span>' . $badge;
    }

    // Question supprimée (désactivée) encore liée au quiz : à décocher, elle n'est plus affichée aux élèves
    if (isset($question['actif']) && (int) $question['actif'] === 0) {
        $classe .= ' sio-admin__choix--autre-theme';
        $badge = '<span class="sio-admin__badge sio-admin__badge--faux">Supprimée</span>' . $badge;
    }

    $case = form_checkbox([
        'name'    => 'questions[]',
        'id'      => $id,
        'value'   => (string) $question['id'],
        'checked' => $cochee,
    ]);

    $libelle = '<span class="sio-admin__question-libelle">' . esc($question['libelle']) . '</span>';

    // Question supprimée (désactivée) : badge « Inactive » devant l'énoncé.
    // Elle ne compte pas pour que le quiz soit complet : à décocher et remplacer.
    if (isset($question['actif']) && (int) $question['actif'] !== 1) {
        $libelle = '<span class="sio-admin__badge sio-admin__badge--faux">Inactive</span>' . $libelle;
    }

    return form_label($case . $libelle . $badge, $id, ['class' => $classe]);
};
?>
<!-- Modal de création d'une question -->
<?= script_tag(['src' => 'js/sio-admin-modal-question.js?v=' . filemtime(FCPATH . 'js/sio-admin-modal-question.js'), 'defer' => null]) ?>

<div class="sio-admin sio-admin--formulaire sio-admin--formulaire-xl">

    <div class="glossary-form">

        <!-- Titre de la page et lien de retour sur la même ligne -->
        <div class="sio-admin__entete-form">
            <h2 class="glossary-form__dialog-title"><?= $estModification ? 'Modifier le quiz' : 'Nouveau quiz' ?></h2>
            <?= anchor('floribondant/theme/quiz', '← Retour aux quiz', ['class' => 'sio-admin__retour']) ?>
        </div>

        <?php if (! empty($errors)): ?>
            <!-- Récapitulatif des erreurs (ul() n'échappe pas les éléments : esc() sur tout le tableau) -->
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>Le quiz n'a pas été enregistré. Corrigez le(s) point(s) suivant(s) :</strong>
                <?= ul(esc($errors), ['class' => 'sio-admin__erreur-liste']) ?>
            </div>
        <?php elseif (! empty($raisonActivation)): ?>
            <!-- Quiz inactif qui ne peut pas être activé (les questions inactives ont leur badge « Inactive ») -->
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>Ce quiz ne peut pas être activé :</strong>
                <?= esc($raisonActivation) ?>
            </div>
        <?php endif; ?>

        <?= form_open($actionFormulaire, ['class' => 'glossary-form__form']) ?>

        <div class="sio-admin__colonnes sio-admin__colonnes--quiz">

            <!-- ===== Colonne de gauche : le quiz ===== -->
            <div class="sio-admin__colonne">

                <?= form_fieldset('<span class="glossary-form__label">Quiz</span>', ['class' => 'sio-admin__bloc-quiz']) ?>

                    <!-- Thème : un bouton radio par thème -->
                    <?= form_fieldset('<span class="glossary-form__label glossary-form__label--required">Thème</span>', ['class' => 'sio-admin__fieldset']) ?>
                        <div class="sio-admin__radios sio-admin__radios--cartes<?= isset($errors['id_theme']) ? ' sio-admin__radios--erreur' : '' ?>">
                            <?php foreach ($themeList as $theme): ?>
                                <?= form_label(
                                    form_radio([
                                        'name'     => 'id_theme',
                                        'id'       => 'theme-' . (int) $theme->id,
                                        'value'    => (string) $theme->id,
                                        'checked'  => (string) $theme->id === (string) ($form->id_theme ?? ''),
                                        'required' => true, // le navigateur demande de choisir un thème avant l'envoi
                                    ]) . esc($theme->nom),
                                    'theme-' . (int) $theme->id,
                                    ['class' => 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--petit']
                                ) ?>
                            <?php endforeach; ?>
                        </div>
                        <?= $erreurChamp('id_theme') ?>
                    <?= form_fieldset_close() ?>

                    <!-- Titre (mêmes limites que les règles du contrôleur : 14 à 150 caractères) -->
                    <div class="glossary-form__field">
                        <?= form_label('Titre', 'champ-titre', $labelRequis) ?>
                        <?= form_input([
                            'name'        => 'titre',
                            'id'          => 'champ-titre',
                            'value'       => $form->titre ?? '',
                            'minlength'   => '14',
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
                            'rows'             => '3',
                            'maxlength'        => '1000',
                            'placeholder'      => 'Courte présentation affichée dans la liste des quiz',
                            'class'            => $classeChamp('glossary-form__input sio-admin__textarea--fixe', 'description'),
                        ]) ?>
                        <?= $erreurChamp('description') ?>
                    </div>

                    <!-- Nombre de questions : nombre exact de questions du quiz (colonne nb_questions) -->
                    <div class="glossary-form__field">
                        <?= form_label('Nombre de questions', 'champ-nb-question', $labelRequis) ?>
                        <?= form_input([
                            'type'             => 'number',
                            'name'             => 'nb_questions',
                            'id'               => 'champ-nb-question',
                            'value'            => (string) ($form->nb_questions ?? ''),
                            'min'              => '5',
                            'max'              => '20',
                            'class'            => $classeChamp('glossary-form__input sio-admin__input--court', 'nb_questions'),
                            'required'         => true,
                            'aria-describedby' => 'aide-nb-question',
                        ]) ?>
                        <p id="aide-nb-question" class="glossary-form__help">Entre 5 et 20. Tant que le quiz n'a pas ce nombre de questions, il est enregistré inactif.</p>
                        <?= $erreurChamp('nb_questions') ?>
                    </div>

                <?= form_fieldset_close() ?>

                <!-- Visibilité : le champ caché envoie 0 quand la case est décochée -->
                <?= form_fieldset('<span class="glossary-form__label">Visibilité</span>', ['class' => 'sio-admin__bloc-quiz']) ?>
                    <?= form_label(
                        form_hidden('actif', '0')
                        . form_checkbox(['name' => 'actif', 'id' => 'champ-actif'], '1', (string) ($form->actif ?? '1') === '1')
                        . 'Quiz actif',
                        'champ-actif',
                        ['class' => 'sio-admin__choix sio-admin__choix--carte']
                    ) ?>
                    <p class="glossary-form__help">Un quiz inactif reste enregistré mais n'apparaît plus sur la page des quiz. Un quiz incomplet est toujours enregistré inactif.</p>
                <?= form_fieldset_close() ?>
            </div>

            <!-- ===== Colonne de droite : les questions du quiz ===== -->
            <div class="sio-admin__colonne">

                <?= form_fieldset('<span class="glossary-form__label">Questions</span>', ['class' => 'sio-admin__bloc-quiz']) ?>

                    <!-- Recherche : masquée sans JavaScript, affichée par sio-admin-theme.js.
                         Pas d'attribut name : le texte recherché n'est pas envoyé avec le formulaire -->
                    <div class="glossary-form__field sio-admin__recherche" hidden>
                        <?= form_label('Rechercher une question', 'champ-recherche', $label) ?>
                        <?= form_input([
                            'type'         => 'search',
                            'id'           => 'champ-recherche',
                            'placeholder'  => 'Un mot de l\'énoncé, par exemple : JWT',
                            'class'        => 'glossary-form__input',
                            'autocomplete' => 'off',
                        ]) ?>
                        <p class="glossary-form__help sio-admin__recherche-resultat" aria-live="polite"></p>
                    </div>

                <!-- Questions du quiz : toutes cochées, dans une zone qui défile.
                 Le nombre et la liste sont mis à jour en direct par sio-admin-theme.js :
                 une question cochée plus bas vient s'ajouter ici. -->
                <p class="sio-admin__sous-titre">
                    Dans ce quiz :
                    <span class="sio-admin__nb-quiz">
                            <?php if ($nbQuestions === 0): ?>
                                aucune question
                            <?php else: ?>
                                <?= $nbQuestions ?> question<?= $nbQuestions > 1 ? 's' : '' ?>
                            <?php endif; ?>
                        </span>
                </p>

                <!-- Message et zone toujours présents : le CSS affiche l'un ou l'autre selon que la zone contient
                     des questions ou non (sio-admin__quiz-vide) -->
                <p class="glossary-form__help sio-admin__quiz-vide">Aucune question pour l'instant : cochez-en ci-dessous.</p>
                <div class="sio-admin__questions sio-admin__questions--quiz">
                    <?php foreach ($questionsQuiz as $question): ?>
                        <?= $caseQuestion($question, true) ?>
                    <?php endforeach; ?>
                </div>

                    <!-- Questions à ajouter : un bloc par thème, seul celui du thème coché est affiché -->
                    <p class="sio-admin__sous-titre">Ajouter des questions</p>

                    <!-- Affiché par le script tant qu'aucun thème n'est coché -->
                    <p class="glossary-form__help sio-admin__choisir-theme" hidden>Choisissez d'abord le thème du quiz.</p>

                    <?php foreach ($blocsThemes as $theme): ?>
                        <div class="sio-admin__par-theme" data-theme="<?= $theme['id'] ?>">
                            <p class="sio-admin__par-theme-titre">Questions du thème <?= esc($theme['nom']) ?></p>

                            <?php if ($theme['questions'] === []): ?>
                                <p class="glossary-form__help">Aucune autre question dans ce thème.</p>
                            <?php else: ?>
                            <div class="sio-admin__questions sio-admin__questions--quiz">
                                <div class="sio-admin__questions">
                                    <?php foreach ($theme['questions'] as $question): ?>
                                        <?= $caseQuestion($question, false) ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Avec JavaScript : ouvre le formulaire de question dans un modal, sur cette page
                         (thème et quiz présélectionnés). Sans JavaScript : nouvel onglet,
                         le quiz en cours de saisie n'est pas perdu. -->
                    <?= anchor('floribondant/theme/questions/ajouter', 'Créer une nouvelle question', [
                        'class'               => 'sio-admin__retour',
                        'target'              => '_blank',
                        'rel'                 => 'noopener',
                        'data-modal-question' => '',
                        'data-quiz'           => $estModification ? (string) (int) $form->modifier : '',
                    ]) ?>
                <?= form_fieldset_close() ?>
            </div>
        </div>

        <!-- Barre d'enregistrement : toujours visible en bas de l'écran -->
        <div class="sio-admin__barre">
            <!-- Récapitulatif des changements, rempli par sio-admin-theme.js -->
            <p class="sio-admin__recap" aria-live="polite" hidden></p>
            <?= anchor('floribondant/theme/quiz', 'Annuler', ['class' => 'sio-admin__btn-secondaire']) ?>
            <!-- form_button() et non form_submit() : le contrôleur attend action = "enregistrer" -->
            <?= form_button([
                'type'    => 'submit',
                'name'    => 'action',
                'value'   => 'enregistrer',
                'content' => $estModification ? 'Enregistrer les modifications' : 'Créer le quiz',
                'class'   => 'sio-admin__btn-principal',
            ]) ?>
        </div>

        <?= form_close() ?>
    </div>
</div>

<!-- Modal « Nouvelle question » : le formulaire de question est chargé dans l'iframe
     (adresse avec ?modal=1). Ouvert et fermé par js/sio-admin-modal-question.js -->
<dialog class="sio-admin__dialog" id="modal-question" aria-labelledby="modal-question-titre">
    <div class="sio-admin__dialog-entete">
        <h2 class="glossary-form__dialog-title" id="modal-question-titre">Nouvelle question</h2>
        <button type="button" class="sio-admin__dialog-fermer" data-modal-fermer aria-label="Fermer sans enregistrer">&times;</button>
    </div>
    <iframe class="sio-admin__dialog-cadre" title="Formulaire de création d'une question"></iframe>
</dialog>


