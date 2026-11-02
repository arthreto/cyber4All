<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Formulaire d'ajout / de modification d'une question
 *
 * Vue en dur (exemple : modification de la question 12).
 * Zones à brancher signalées par « À brancher ».
 * Données prévues : $question, $themes, $idThemeChoisi, $quizDuTheme, $quizRattaches, $errors.
 *
 * Choix des quiz : on choisit d'abord le thème, puis on ajoute les quiz de ce thème un par un.
 * Seule exception à la règle 0 JS : public/js/sio-admin-theme.js renvoie le formulaire
 * quand le thème change. Le bouton dans <noscript> sert de secours.
 */
?>
<div class="sio-admin sio-admin--formulaire">

    <a href="<?= base_url('floribondant/theme') ?>" class="sio-admin__retour">← Retour aux questions</a>

    <div class="glossary-form">
        <div class="glossary-form__header">
            <h2 class="glossary-form__dialog-title"><?= (isset($question)) ? 'Modifier la question' : 'Ajouter une question' ?></h2>
        </div>

        <!-- Exemple d'erreur de validation (à afficher seulement si $errors n'est pas vide) -->
        <?php if(isset($error)):
        echo '<div class="sio-admin__erreur-globale" role="alert">
            <strong>Le formulaire contient 1 erreur.</strong> Corrigez le champ signalé puis enregistrez.
        </div>';
        endif; ?>
        <?= form_open(base_url('floribondant/theme/questions/ajouter'), ['class' => 'glossary-form__form']) ?>

        <!-- Quiz de la question -->
        <fieldset class="sio-admin__bloc-quiz">
            <legend class="glossary-form__label">Quiz de la question</legend>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <label for="champ-theme" class="glossary-form__label">1. Thème</label>
                    <select id="champ-theme" name="id_theme" class="sio-admin__select">
                        <!-- À brancher : foreach ($themes as $theme) -->
                        <?php foreach ($themeList as $theme): ?>
                            <option value="<?= esc($theme->id) ?>"
                                    <?= $form->id_theme == $theme->id ? 'selected' : '' ?>>
                                <?= esc($theme->nom) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <noscript>
                    <button type="submit" name="action" value="choisir_theme" class="sio-admin__btn-secondaire">Afficher les quiz</button>
                </noscript>
            </div>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <label for="champ-quiz" class="glossary-form__label">2. Quiz du thème Tokens</label>
                    <select id="champ-quiz" name="id_quiz_ajout" class="sio-admin__select">
                        <?php foreach ($quizList as $quiz):
                            echo '<option value="', $quiz->id, '">', $quiz->titre, '</option>';
                        endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="action" value="ajouter_quiz"
                        formnovalidate class="sio-admin__btn-principal">
                    Ajouter ce quiz
                </button>
            </div>

            <div class="sio-admin__rattaches">
                <p class="glossary-form__label">Question rattachée à <?=count($quizAdded) ?> quiz</p>
                <ul class="sio-admin__rattaches-liste">
                    <!-- À brancher : foreach ($quizRattaches as $quiz) + champ caché quiz[] -->
                    <?php foreach ($quizAdded as $id => $quiz):
                        echo '
                            <li class="sio-admin__rattache">
                                <span>', esc($quiz), '</span>
                                <button type="submit" name="retirer_quiz" value="', esc($id), '" formnovalidate class="sio-admin__btn-supprimer">Retirer</button>
                            </li>
                        ';
                    endforeach; ?>
                </ul>
            </div>
        </fieldset>

        <!-- Énoncé -->
        <div class="glossary-form__field">
            <label for="champ-enonce" class="glossary-form__label glossary-form__label--required">Énoncé</label>
            <textarea id="champ-enonce" name="enonce" rows="3" class="glossary-form__input" required>Un JWT dont la date d'expiration (exp) est dépassée reste valide tant que sa signature est correcte.</textarea>
        </div>

        <!-- Image -->
        <div class="glossary-form__field">
            <label for="champ-image" class="glossary-form__label">Chemin de l'image (facultatif)</label>
            <input type="text" id="champ-image" name="image" value="<?= esc($form->image ?? '') ?>"
                   placeholder="images/questions/jwt-expire.png" class="glossary-form__input" aria-describedby="aide-image">
            <p id="aide-image" class="glossary-form__help">Chemin de l'image à partir du dossier public (PNG ou JPG). Affichée au-dessus de l'énoncé.</p>
        </div>

        <!-- Détail -->
        <div class="glossary-form__field">
            <label for="champ-detail" class="glossary-form__label">Détail (facultatif)</label>
            <textarea id="champ-detail" name="detail" rows="6" class="glossary-form__input sio-admin__code" aria-describedby="aide-detail">{
  "sub": 42,
  "scope": "lecture",
  "exp": 1791500000
}</textarea>
            <p id="aide-detail" class="glossary-form__help">Exemple affiché en police à chasse fixe au-dessus de l'énoncé : token, requête, ligne de log…</p>
        </div>

        <!-- Bonne réponse -->
        <fieldset class="glossary-form__field sio-admin__fieldset">
            <legend class="glossary-form__label glossary-form__label--required">Bonne réponse</legend>
            <div class="sio-admin__radios">
                <label for="champ-vrai" class="sio-admin__choix"><input type="radio" id="champ-vrai" name="bonne_reponse" value="1"> Vrai</label>
                <label for="champ-faux" class="sio-admin__choix"><input type="radio" id="champ-faux" name="bonne_reponse" value="0" checked> Faux</label>
            </div>
        </fieldset>

        <!-- Explication (exemple en erreur) -->
        <div class="glossary-form__field">
            <label for="champ-explication" class="glossary-form__label glossary-form__label--required">Explication</label>
            <textarea id="champ-explication" name="explication" rows="4" class="glossary-form__input sio-admin__input--erreur" aria-invalid="true" aria-describedby="erreur-explication" required></textarea>
            <p id="erreur-explication" class="sio-admin__erreur">Erreur : l'explication est obligatoire. Indiquez pourquoi l'affirmation est vraie ou fausse.</p>
        </div>

        <!-- Active -->
        <div class="glossary-form__field">
            <label for="champ-actif" class="sio-admin__choix">
                <input type="hidden" name="actif" value="0">
                <input type="checkbox" id="champ-actif" name="actif" value="1" checked> Question active (visible dans les quiz)
            </label>
        </div>

        <div class="glossary-form__field glossary-form__field--row">
            <button type="submit" name="action" value="enregistrer" class="glossary-form__submit">Enregistrer</button>
        </div>

        <?= form_close() ?>
    </div>
</div>

<!-- Exception autorisée à la règle 0 JS : uniquement pour le sélecteur de thème -->
<script src="<?= base_url('js/sio-admin-theme.js') ?>" defer></script>
