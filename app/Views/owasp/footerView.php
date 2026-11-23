<?php
$estApi       = (($Top->id_top ?? 'WEB') === 'API');
$classeRetour = $estApi ? 'back-button-api' : 'back-button';
?>

<?php if (!empty($autresTops)): ?>
    <section class="mot-seul-section top10-choix">
        <p class="top10-eyebrow">Consulter l'autre classement</p>
        <h2 class="section-title">Voir aussi</h2>

        <div class="top10-cards">
            <?php foreach ($autresTops as $autre):
                $carte = '<span class="top10-tag">' . esc($autre->id_top) . ' · ' . esc($autre->edition_top) . '</span>'
                        . '<h3>' . esc($autre->libelle_top) . '</h3>'
                        . '<span class="top10-link">Consulter le classement</span>';

                echo anchor(
                        'top10/' . $autre->id_top,
                        $carte,
                        ['class' => 'top10-card top10-card--' . strtolower($autre->id_top)]
                );
            endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<footer class="detail-footer-actions">
    <?= anchor('glossaire/mot/21', '← Retour à OWASP', ['class' => $classeRetour]) ?>
</footer>

</article>
</body>
</html>