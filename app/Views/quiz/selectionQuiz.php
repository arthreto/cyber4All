<?php
/**
 * Page VRAI / FAUX - Espace compréhension SIO
 *
 */
?>
<div class="quiz-page">

    <!-- Fil d'Ariane -->
    <nav class="quiz-ariane" aria-label="Fil d'Ariane">
        <ol class="quiz-ariane__liste">
            <li class="quiz-ariane__item">Comprendre les accès</li>
            <li class="quiz-ariane__item"><a href="<?= site_url('quiz') ?>" class="quiz-ariane__lien">Quiz Vrai / Faux</a></li>
            <li class="quiz-ariane__item" aria-current="page"><?= esc($themeActif->nom) ?></li>
        </ol>
    </nav>

    <header class="quiz-page__entete">
        <h1 class="quiz-page__titre">Quiz Vrai / Faux</h1>
        <p class="quiz-page__intro">Chaque réponse est corrigée avec son explication.</p>
    </header>

    <!-- Catégories : un onglet par thème -->
    <nav class="quiz-onglets" aria-label="Thèmes des quiz">
        <?php foreach ($themeList as $theme): ?>
            <?php $estActif = ((int) $theme->id === (int) $themeActif->id); ?>
            <a href="<?= site_url('quiz/' . $theme->id) ?>"
               class="quiz-onglets__lien<?= $estActif ? ' quiz-onglets__lien--actif' : '' ?>"
               <?= $estActif ? 'aria-current="page"' : '' ?>><?= esc($theme->nom) ?></a>
        <?php endforeach; ?>
    </nav>

    <section class="quiz-liste" aria-labelledby="quiz-liste-titre">
        <div class="quiz-liste__entete">
            <h2 id="quiz-liste-titre" class="quiz-liste__titre">Quiz du thème <?= esc($themeActif->nom) ?></h2>
            <?php if ($aDesLecons ?? true): ?>
                <a href="<?= site_url('theme/' . $themeActif->id) ?>" class="quiz-page__lecon">Revoir les leçons <span aria-hidden="true">→</span></a>
            <?php endif; ?>
        </div>

        <?php if ($quizList === []): ?>
            <p class="quiz-liste__vide">Aucun quiz pour ce thème pour l'instant.</p>
        <?php endif; ?>

        <ul class="quiz-liste__items">
            <!-- position : place fixe du quiz dans le thème (les quiz désactivés comptent), voir QuizController -->
            <?php foreach ($quizList as $index => $element):
                $quiz = $element['quiz'];
                $nb = (int) $element['nbQuestions'];
                $description = trim((string) ($quiz->description ?? ''));
            ?>
                <li class="quiz-carte<?= $nb === 0 ? ' quiz-carte--vide' : '' ?>">
                    <div class="quiz-carte__corps">
                        <span class="quiz-carte__numero" aria-hidden="true"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <div class="quiz-carte__infos">
                            <h3 class="quiz-carte__titre"><?= esc($quiz->titre) ?></h3>
                            <?php if ($description !== ''): ?>
                                <p class="quiz-carte__description"><?= esc($description) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="quiz-carte__pied">
                        <span class="quiz-carte__questions">
                            <?= $nb ?> question<?= $nb > 1 ? 's' : '' ?>
                        </span>

                        <?php if ($nb > 0): ?>
                            <?= anchor(
                                base_url('/quiz/' . $themeActif->id . '/' . $element['position']),
                                'Commencer <span aria-hidden="true">→</span>',
                                [
                                    'class'      => 'quiz-carte__btn',
                                    'aria-label' => 'Commencer le quiz ' . $quiz->titre . ', ' . $nb . ' question' . ($nb > 1 ? 's' : ''),
                                ]
                            ) ?>
                        <?php else: ?>
                            <span class="quiz-carte__bientot">Bientôt disponible</span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
