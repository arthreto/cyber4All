<?php
/**
 * PAGE D'UN QUIZ VRAI / FAUX - Espace compréhension SIO
 */
$corrige = $corrige ?? false;
?>
<div class="quiz-page">

    <?= anchor(base_url('/quiz/' . $themeId), '← Retour aux quiz', ['class' => 'quiz-page__retour']) ?>


    <header class="quiz-page__entete">
        <h1 class="quiz-page__titre">Reconnaître un token valide</h1>
        <p class="quiz-page__intro">Thème : <?= esc($themeNom) ?> · <?= esc($nbQuestions) ?> questions · Répondez à toutes les questions, puis validez en bas de page.</p>
    </header>

    <?php if ($corrige): ?>
        <div id="resultat" class="quiz-resultat" role="status">
            <p class="quiz-resultat__score">Résultat : <?= $bonneReponseResultat ?> / <?= esc($nbQuestions) ?></p>
            <p><?= $bonneReponseResultat ?> bonnes réponses, <?= esc($nbQuestions) ?> mauvaise. Ouvrez une correction pour voir la réponse attendue et l'explication.</p>
        </div>
    <?php endif; ?>

    <?= form_open(site_url('quiz/' . $themeId . "/" . $quizNum), ['class' => 'quiz-form']) ?>
        <?= form_hidden('id_quiz', '3') ?>

        <?php
        $num = 1;
        foreach ($questionQuizList as $question): ?>

        <fieldset id="question-<?= $num ?>" class="quiz-question">
            <legend class="quiz-question__numero">Question <?= $num ?> sur <?= esc($nbQuestions) ?></legend>

            <?php if (isset($question['titre'])): ?>
                <figure class="quiz-question__exemple">
                    <figcaption class="quiz-question__legende"><?= esc($question['titre']) ?></figcaption>
                    <pre class="quiz-question__detail"><?= esc($question['contenu']) ?></pre>
                </figure>
            <?php endif; ?>

            <?php if (isset($question['image'])): ?>
                <figure class="quiz-question__exemple">
                    <?= img([
                            'src'     => 'images/questions/' . esc($question['image']),
                            'class'   => 'quiz-question__image',
                            'loading' => 'lazy',
                    ]) ?>
                </figure>
            <?php endif; ?>


            <p class="quiz-question__enonce"><?= esc($question['libelle']) ?></p>

            <div class="quiz-question__choix">
                <label for="q<?= $num ?>-vrai" class="quiz-choix">
                    <input type="radio" id="q<?= $num ?>-vrai" name="reponses[<?= $num ?>]" value="1" <?= $corrige ? 'checked' : '' ?> required>
                    VRAI
                </label>
                <label for="q<?= $num ?>-faux" class="quiz-choix">
                    <input type="radio" id="q<?= $num ?>-faux" name="reponses[<?= $num ?>]" value="0">
                    FAUX
                </label>
            </div>

            <?php if ($corrige):
                $correctionResume = 'Mauvaise réponse';
                $correctionSymbol = '✗';
                $inverseReponse = 'Vrai';
                $cssClass = 'mauvaise';
                if ($resultatParQuestion[$num] == 'Vrai') {
                    $correctionResume = 'Bonne réponse';
                    $correctionSymbol = '✓';
                    $inverseReponse = 'Faux';
                    $cssClass = 'bonne';
                }
                ?>
                <details class="quiz-correction quiz-correction--<?= $cssClass ?>">
                    <summary class="quiz-correction__resume">
                        <span aria-hidden="true"><?= $correctionSymbol ?></span> <?= $correctionResume ?>
                        <span class="quiz-correction__voir">Voir le détail</span>
                    </summary>
                    <div class="quiz-correction__contenu">
                        <p>Votre réponse : <strong><?= $resultatParQuestion[$num] ?></strong> · Réponse attendue : <strong><?= $inverseReponse ?></strong></p>
                        <?php if(isset($question['explication'])): ?>
                        <p><strong>Pourquoi ?</strong> <?= esc($question['explication']) ?></p>
                        <?php endif; ?>
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
                <?= anchor(base_url('/quiz/' . $themeId . '/' . quizNum), 'Recommencer le quiz', ['class' => 'quiz-page__lecon']) ?>
            <?php endif; ?>
        </div>
    <?= form_close() ?>
</div>
