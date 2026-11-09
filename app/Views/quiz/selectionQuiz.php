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
        <p class="quiz-page__intro">Choisissez un thème, puis un quiz. Chaque réponse est corrigée avec son explication.</p>
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
        <h2 id="quiz-liste-titre" class="quiz-liste__titre">Quiz du thème <?= esc($themeActif->nom) ?></h2>

        <ul class="quiz-liste__items">
            <!-- À brancher : foreach ($quizList as $quiz) -->
            <?php foreach ($quizList as $quiz): ?>
                <li class="quiz-carte">
                    <div class="quiz-carte__infos">
                        <h3 class="quiz-carte__titre"><?= esc($quiz->titre) ?></h3>
                    </div>
                    <a href="#" class="btn-connexion quiz-carte__btn">Commencer</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <a href="<?= site_url('theme/' . $themeActif->id) ?>" class="quiz-page__lecon">Revoir les leçons du thème <?= esc($themeActif->nom) ?></a>
</div>
