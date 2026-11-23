<?php
/**
 * PAGE D'UN QUIZ VRAI / FAUX - Espace compréhension SIO
 * Une question affichée à la fois, navigation par numéros à droite (public/js/quiz.js).
 */
$corrige = $corrige ?? false;
$reponsesUtilisateur = $reponsesUtilisateur ?? [];
$quizTitre = (isset($quizData->titre) && $quizData->titre !== '') ? $quizData->titre : 'Quiz';
?>
<div class="quiz-page quiz-joueur<?= $corrige ? ' quiz-joueur--corrige' : '' ?>">

    <?= anchor(base_url('/quiz/' . $themeId), '← Retour aux quiz', ['class' => 'quiz-page__retour']) ?>

    <header class="quiz-page__entete">
        <h1 class="quiz-page__titre"><?= esc($quizTitre) ?></h1>
        <p class="quiz-page__intro">Thème : <?= esc($themeNom) ?> · <?= esc($nbQuestions) ?> questions</p>
    </header>

    <?php if ($corrige): ?>
        <div id="resultat" class="quiz-resultat" role="status">
            <p class="quiz-resultat__score">Résultat : <?= $bonneReponseResultat ?> / <?= esc($nbQuestions) ?></p>
            <p><?= $bonneReponseResultat ?> bonne<?= $bonneReponseResultat > 1 ? 's' : '' ?> réponse<?= $bonneReponseResultat > 1 ? 's' : '' ?>,
               <?= $nbQuestions - $bonneReponseResultat ?> mauvaise<?= ($nbQuestions - $bonneReponseResultat) > 1 ? 's' : '' ?>.</p>
        </div>
    <?php endif; ?>

    <?= form_open(site_url('quiz/' . $themeId . "/" . $quizNum), ['class' => 'quiz-form quiz-joueur__corps', 'id' => 'quiz-form']) ?>
        <?= form_hidden('id_quiz', '3') ?>

        <div class="quiz-joueur__questions">
            <?php
            $num = 1;
            foreach ($questionQuizList as $question):
                $reponse = $reponsesUtilisateur[$num] ?? null;
                $attendue = (string) $question['bonne_reponse'];
                $estBonne = $corrige && $resultatParQuestion[$num] === 'Vrai';
            ?>

            <fieldset id="question-<?= $num ?>" class="quiz-question<?= $corrige ? ($estBonne ? ' quiz-question--bonne' : ' quiz-question--mauvaise') : '' ?>" data-num="<?= $num ?>">
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
                    <?php foreach (['1' => 'VRAI', '0' => 'FAUX'] as $valeur => $texte):
                        $valeur = (string) $valeur;
                        $coche = $reponse !== null && (string) $reponse === $valeur;
                        $etat = '';
                        if ($corrige) {
                            if ($valeur === $attendue) {
                                $etat = ' quiz-choix--attendue';
                            } elseif ($coche) {
                                $etat = ' quiz-choix--fausse';
                            }
                        }
                    ?>
                        <label for="q<?= $num ?>-<?= $valeur ?>" class="quiz-choix<?= $etat ?>">
                            <input type="radio" id="q<?= $num ?>-<?= $valeur ?>" name="reponses[<?= $num ?>]" value="<?= $valeur ?>"
                                <?= $coche ? 'checked' : '' ?> <?= $corrige ? 'disabled' : 'required' ?>>
                            <?= $texte ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <?php if ($corrige): ?>
                    <div class="quiz-correction quiz-correction--<?= $estBonne ? 'bonne' : 'mauvaise' ?>">
                        <p class="quiz-correction__resume">
                            <span aria-hidden="true"><?= $estBonne ? '✓' : '✗' ?></span>
                            <?= $estBonne ? 'Bonne réponse' : ($reponse === null ? 'Sans réponse' : 'Mauvaise réponse') ?>
                        </p>
                        <div class="quiz-correction__contenu">
                            <p>Votre réponse : <strong><?= $reponse === null ? '—' : ((string) $reponse === '1' ? 'Vrai' : 'Faux') ?></strong>
                               · Réponse attendue : <strong><?= $attendue === '1' ? 'Vrai' : 'Faux' ?></strong></p>
                            <?php if (isset($question['explication'])): ?>
                                <p><strong>Pourquoi ?</strong> <?= esc($question['explication']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="quiz-question__pied">
                    <button type="button" class="quiz-bouton quiz-bouton--secondaire" data-quiz-precedent <?= $num === 1 ? 'disabled' : '' ?>>← Précédent</button>
                    <?php if ($num < $nbQuestions): ?>
                        <button type="button" class="quiz-bouton" data-quiz-suivant>Suivant →</button>
                    <?php elseif (! $corrige): ?>
                        <button type="submit" class="quiz-bouton quiz-bouton--valider">Valider mes réponses</button>
                    <?php endif; ?>
                </div>
            </fieldset>

            <?php $num++; endforeach; ?>
        </div>

        <aside class="quiz-nav" aria-label="Navigation entre les questions">
            <p class="quiz-nav__titre">Questions</p>
            <ol class="quiz-nav__grille">
                <?php for ($i = 1; $i <= $nbQuestions; $i++):
                    $etatNav = '';
                    if ($corrige) {
                        $etatNav = $resultatParQuestion[$i] === 'Vrai' ? ' quiz-nav__case--bonne' : ' quiz-nav__case--mauvaise';
                    }
                ?>
                    <li>
                        <a href="#question-<?= $i ?>" class="quiz-nav__case<?= $etatNav ?>" data-quiz-aller="<?= $i ?>"
                           aria-label="Question <?= $i ?><?= $corrige ? ($resultatParQuestion[$i] === 'Vrai' ? ', bonne réponse' : ', mauvaise réponse') : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ol>

            <?php if ($corrige): ?>
                <?= anchor(base_url('/quiz/' . $themeId . '/' . $quizNum), 'Recommencer le quiz', ['class' => 'quiz-bouton quiz-nav__action']) ?>
            <?php else: ?>
                <p class="quiz-nav__progression"><span data-quiz-repondues>0</span> / <?= esc($nbQuestions) ?> répondues</p>
                <div class="quiz-nav__barre" role="progressbar" aria-label="Questions répondues"
                     aria-valuemin="0" aria-valuemax="<?= esc($nbQuestions) ?>" aria-valuenow="0" data-quiz-barre>
                    <span class="quiz-nav__barre-remplie"></span>
                </div>
                <button type="submit" class="quiz-bouton quiz-bouton--valider quiz-nav__action">Terminer le quiz</button>
                <p class="quiz-nav__alerte" data-quiz-alerte role="alert" hidden></p>
            <?php endif; ?>
        </aside>
    <?= form_close() ?>
</div>
<script src="<?= base_url('/js/quiz.js') . '?v=' . filemtime(FCPATH . 'js/quiz.js') ?>" defer></script>
