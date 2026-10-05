<div class="top-mots-container">
    <div class="top-mots-header">
        <h1>🏆 Top Mots de la Semaine</h1>
        <p class="top-mots-header__subtitle">
            <?php
            $startDate = date('d/m/Y', strtotime('last monday -7 days'));
            $endDate = date('d/m/Y', strtotime('last sunday'));
            echo "Du $startDate au $endDate";
            ?>
        </p>
    </div>

    <?php if (!empty($topMots)): ?>
        <!-- Podium - Top 3 -->
        <div class="podium">
            <?php
            $medals = ['', '🥇', '🥈', '🥉'];
            for ($i = 0; $i < min(3, count($topMots)); $i++):
                $mot = $topMots[$i];
                $rank = $i + 1;
                ?>
                <div class="podium-card podium-card--<?= $rank ?>">
                    <div class="podium-medal"><?= $medals[$rank] ?></div>
                    <div class="podium-word"><?= esc($mot->mot) ?></div>
                    <div class="podium-votes"><?= $mot->nb_votes ?></div>
                    <div class="podium-label">vote<?= $mot->nb_votes > 1 ? 's' : '' ?></div>
                    <footer class="btn-more">
                        <?= anchor(
                                base_url('glossaire/mot/' . $mot->id),
                                'Découvrir <i class="fa-solid fa-arrow-right"></i>',
                        )?>
                    </footer>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Positions 4-5 -->
        <?php if (count($topMots) > 3): ?>
            <div class="autres-mots">
                <?php for ($i = 3; $i < count($topMots); $i++):
                    $mot = $topMots[$i];
                    $rank = $i + 1;
                    ?>
                    <div class="autre-card">
                        <div class="autre-rank">#<?= $rank ?></div>
                        <div class="autre-content">
                            <div class="autre-word"><?= esc($mot->mot) ?></div>
                            <div class="autre-votes">
                                <strong><?= $mot->nb_votes ?></strong> vote<?= $mot->nb_votes > 1 ? 's' : '' ?>
                            </div>
                        </div>
                        <footer class="btn-more">
                            <?= anchor(
                                    base_url('glossaire/mot/' . $mot->id),
                                    'Découvrir <i class="fa-solid fa-arrow-right"></i>',
                            )?>
                        </footer>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="top-mots-empty">
            <div class="top-mots-empty__icon">📊</div>
            <div class="top-mots-empty__text">Aucun vote durant la semaine dernière</div>
        </div>
    <?php endif; ?>
</div>