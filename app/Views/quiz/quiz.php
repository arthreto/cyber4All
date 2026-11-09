<?php
/**
 * PAGE D'UN QUIZ VRAI / FAUX - Espace compréhension SIO
 */
$corrige = $corrige ?? false;
?>
<div class="quiz-page">

    <?= anchor(base_url('/quiz/' . $themeId), '← Retour aux quiz', ['class' => 'quiz-page__retour']) ?>


    <header class="quiz-page__entete">
        <h1 class="quiz-page__titre">Reconnaître un token valide</h1> <!-- À brancher -->
        <p class="quiz-page__intro">Thème : <?= esc($themeNom) ?> · <?= esc($nbQuestions) ?> questions · Répondez à toutes les questions, puis validez en bas de page.</p>
    </header>

    <?php if ($corrige): ?>
        <!-- Résultat (après validation) -->
        <div id="resultat" class="quiz-resultat" role="status">
            <p class="quiz-resultat__score">Résultat : 2 / 3</p>
            <p>2 bonnes réponses, 1 mauvaise. Ouvrez une correction pour voir la réponse attendue et l'explication.</p>
        </div>
    <?php endif; ?>

    <?= form_open(site_url('quiz/valider'), ['class' => 'quiz-form']) ?> <!-- À brancher : route de validation -->
        <?= form_hidden('id_quiz', '3') ?>

        <!-- À brancher : foreach ($questions as $numero => $question) -->
        <?php
        $num = 1;
        foreach ($questionQuizList as $question): ?>

        <!-- ===== Question 1 : avec un détail ===== -->
        <fieldset id="question-12" class="quiz-question">
            <legend class="quiz-question__numero">Question <?= $num ?> sur <?= esc($nbQuestions) ?></legend>

            <?php if (isset($question['details'])): ?>
                <figure class="quiz-question__exemple">
                    <figcaption class="quiz-question__legende">Détail</figcaption>
                    <pre class="quiz-question__detail">{
                      "sub": 42,
                      "scope": "lecture",
                      "exp": 1791500000
                    }
                    exp = 09/10/2026 à 00:53 · Heure du serveur : 12/10/2026 à 10:36
                    Signature : valide</pre>
                </figure>
            <?php endif; ?>

            <p class="quiz-question__enonce">Un JWT dont la date d'expiration (exp) est dépassée reste valide tant que sa signature est correcte.</p>

            <div class="quiz-question__choix">
                <label for="q12-vrai" class="quiz-choix">
                    <input type="radio" id="q12-vrai" name="reponses[12]" value="1" <?= $corrige ? 'checked' : '' ?> required>
                    VRAI
                </label>
                <label for="q12-faux" class="quiz-choix">
                    <input type="radio" id="q12-faux" name="reponses[12]" value="0">
                    FAUX
                </label>
            </div>

            <?php if ($corrige): ?>
                <details class="quiz-correction quiz-correction--mauvaise">
                    <summary class="quiz-correction__resume">
                        <span aria-hidden="true">✗</span> Mauvaise réponse
                        <span class="quiz-correction__voir">Voir le détail</span>
                    </summary>
                    <div class="quiz-correction__contenu">
                        <p>Votre réponse : <strong>Vrai</strong> · Réponse attendue : <strong>Faux</strong></p>
                        <p><strong>Pourquoi ?</strong> La signature prouve seulement que le token n'a pas été modifié. Elle ne dit rien de sa durée de vie : le serveur doit refuser tout token dont le champ exp est antérieur à l'heure actuelle.</p>
                    </div>
                </details>
            <?php endif; ?>
        </fieldset>

        <!-- ===== Question 2 : avec une image ===== -->
        <fieldset id="question-13" class="quiz-question">
            <legend class="quiz-question__numero">Question 2 sur 3</legend>

            <figure class="quiz-question__exemple">
                <?= img([
                    'src'     => 'images/questions/tokentest.avif',
                    'alt'     => "Un utilisateur se déconnecte, puis son ancien token est renvoyé au serveur",
                    'class'   => 'quiz-question__image',
                    'loading' => 'lazy',
                ]) ?>
            </figure>

            <p class="quiz-question__enonce">Après une déconnexion, le serveur doit pouvoir refuser un token même s'il n'est pas encore expiré.</p>

            <div class="quiz-question__choix">
                <label for="q13-vrai" class="quiz-choix">
                    <input type="radio" id="q13-vrai" name="reponses[13]" value="1" <?= $corrige ? 'checked' : '' ?> required>
                    VRAI
                </label>
                <label for="q13-faux" class="quiz-choix">
                    <input type="radio" id="q13-faux" name="reponses[13]" value="0">
                    FAUX
                </label>
            </div>

            <?php if ($corrige): ?>
                <details class="quiz-correction quiz-correction--bonne">
                    <summary class="quiz-correction__resume">
                        <span aria-hidden="true">✓</span> Bonne réponse
                        <span class="quiz-correction__voir">Voir le détail</span>
                    </summary>
                    <div class="quiz-correction__contenu">
                        <p>Votre réponse : <strong>Vrai</strong> · Réponse attendue : <strong>Vrai</strong></p>
                        <p><strong>Pourquoi ?</strong> Un token reste lisible jusqu'à son expiration. Pour qu'une déconnexion ait un effet réel, le serveur doit le révoquer, par exemple avec une liste noire consultée à chaque requête.</p>
                    </div>
                </details>
            <?php endif; ?>
        </fieldset>

        <!-- ===== Question 3 : avec un détail ===== -->
        <fieldset id="question-14" class="quiz-question">
            <legend class="quiz-question__numero">Question 3 sur 3</legend>

            <figure class="quiz-question__exemple">
                <figcaption class="quiz-question__legende">Détail · requête reçue par le serveur</figcaption>
                <pre class="quiz-question__detail">DELETE /api/utilisateurs/7
                Authorization: Bearer eyJhbGciOiJIUzI1NiJ9…

                Contenu du token : { "sub": 42, "scope": "lecture", "exp": 1791900000 }
                Signature : valide</pre>
            </figure>

            <p class="quiz-question__enonce">Un token dont la signature est valide donne accès à toutes les ressources de l'application.</p>

            <div class="quiz-question__choix">
                <label for="q14-vrai" class="quiz-choix">
                    <input type="radio" id="q14-vrai" name="reponses[14]" value="1" required>
                    VRAI
                </label>
                <label for="q14-faux" class="quiz-choix">
                    <input type="radio" id="q14-faux" name="reponses[14]" value="0" <?= $corrige ? 'checked' : '' ?>>
                    FAUX
                </label>
            </div>

            <?php if ($corrige): ?>
                <details class="quiz-correction quiz-correction--bonne">
                    <summary class="quiz-correction__resume">
                        <span aria-hidden="true">✓</span> Bonne réponse
                        <span class="quiz-correction__voir">Voir le détail</span>
                    </summary>
                    <div class="quiz-correction__contenu">
                        <p>Votre réponse : <strong>Faux</strong> · Réponse attendue : <strong>Faux</strong></p>
                        <p><strong>Pourquoi ?</strong> Une signature valide prouve que le token vient bien du serveur. Les droits dépendent de son scope : « lecture » ne permet pas une suppression, le serveur doit répondre 403.</p>
                    </div>
                </details>
            <?php endif; ?>
        </fieldset>

        <?php $num++; endforeach; ?>

        <div class="quiz-form__actions">
            <button type="submit" class="btn-connexion quiz-form__valider">
                <?= $corrige ? 'Valider à nouveau' : 'Valider mes réponses' ?>
            </button>
            <?php if ($corrige): ?>
                <a href="#" class="quiz-page__lecon">Recommencer le quiz</a> <!-- À brancher : URL du quiz -->
            <?php else: ?>
                <span class="quiz-form__aide">Les corrections s'affichent sous chaque question après validation.</span>
            <?php endif; ?>
        </div>
    <?= form_close() ?>
</div>
