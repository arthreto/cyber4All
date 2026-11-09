<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Formulaire d'ajout / de modification d'un quiz
 */
$estModification = ! empty($form->modifier);
$actionFormulaire = $estModification
    ? 'floribondant/theme/quiz/modifier/' . (int) $form->modifier
    : 'floribondant/theme/quiz/ajouter';
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
            <label for="champ-theme-quiz" class="glossary-form__label glossary-form__label--required">Thème</label>
            <select id="champ-theme-quiz" name="id_theme" required
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
                   placeholder="Reconnaître un token valide"
                   class="glossary-form__input <?= isset($errors['titre']) ? 'sio-admin__input--erreur' : '' ?>">

            <?php if (isset($errors['titre'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['titre']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <div class="glossary-form__field">
            <label for="champ-description" class="glossary-form__label">Description (facultatif)</label>
            <textarea id="champ-description" name="description" rows="4" aria-describedby="aide-description"
                      class="glossary-form__input <?= isset($errors['description']) ? 'sio-admin__input--erreur' : '' ?>"><?= esc($form->description ?? '') ?></textarea>
            <p id="aide-description" class="glossary-form__help">Courte présentation affichée dans la liste des quiz.</p>

            <?php if (isset($errors['description'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['description']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Actif -->
        <div class="glossary-form__field">
            <label for="champ-actif" class="sio-admin__choix">
                <input type="hidden" name="actif" value="0">
                <input type="checkbox" id="champ-actif" name="actif" value="1" <?= ($form->actif ?? 1) == 1 ? 'checked' : '' ?>>
                Quiz actif (visible sur la page des quiz)
            </label>
        </div>

        <fieldset class="sio-admin__bloc-quiz">
            <legend class="glossary-form__label">Questions du quiz</legend>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <label for="champ-theme-questions" class="glossary-form__label">1. Thème des questions</label>
                    <select id="champ-theme-questions" name="id_theme_questions" class="sio-admin__select" data-envoi-auto>
                        <?php foreach ($themeList ?? [] as $theme): ?>
                            <option value="<?= esc($theme->id) ?>" <?= ($form->id_theme_questions ?? null) == $theme->id ? 'selected' : '' ?>>
                                <?= esc($theme->nom) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <noscript>
                    <button type="submit" name="action" value="choisir_theme" formnovalidate class="sio-admin__btn-secondaire">Afficher les questions</button>
                </noscript>
            </div>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <label for="champ-question" class="glossary-form__label">2. Question à ajouter</label>
                    <select id="champ-question" name="id_question_ajout" class="sio-admin__select" aria-describedby="aide-question">
                        <?php foreach ($questionsDisponibles ?? [] as $question): ?>
                            <option value="<?= esc($question['id']) ?>"><?= esc($question['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="action" value="ajouter_question" formnovalidate
                        class="sio-admin__btn-principal" <?= empty($questionsDisponibles) ? 'disabled' : '' ?>>
                    Ajouter cette question
                </button>
            </div>

            <div class="sio-admin__rattaches">
                <p class="glossary-form__label">
                    <?= count($questionsAjoutees ?? []) ?> question<?= count($questionsAjoutees ?? []) > 1 ? 's' : '' ?> dans ce quiz
                </p>
                <ol class="sio-admin__rattaches-liste">
                    <?php foreach ($questionsAjoutees ?? [] as $question): ?>
                        <li class="sio-admin__rattache">
                            <span>
                                <?= esc($question['libelle']) ?>
                                <strong>· <?= $question['bonne_reponse'] == 1 ? 'Vrai' : 'Faux' ?></strong>
                            </span>
                            <button type="submit" name="retirer_question" value="<?= esc($question['id']) ?>"
                                    formnovalidate class="sio-admin__btn-supprimer">Retirer</button>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <?= anchor(base_url('floribondant/theme/questions/ajouter'), '+ Créer une nouvelle question', ['class' => 'sio-admin__retour']) ?>
        </fieldset>

        <div class="glossary-form__field glossary-form__field--row">
            <button type="submit" name="action" value="enregistrer" class="glossary-form__submit">Enregistrer</button>
        </div>

        <?= form_close() ?>
    </div>
</div>

<?= script_tag(['src' => 'js/sio-admin-theme.js', 'defer' => null]) ?>
