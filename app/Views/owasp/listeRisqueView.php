<main class="detail-preview">
    <article class="detail-page">

        <section class="api-top-ten">
            <div class="section-heading">
                <p class="eyebrow">Classement officiel</p>
                <h2>Top 10 des risques de sécurité API – 2023</h2>
                <p>Chaque élément présente la traduction française de la description officielle.</p>
            </div>

            <ol class="api-risk-list">
                <?php if (!empty($Vulnerabilities)):
                    foreach ($Vulnerabilities as $x => $y):
                        $contenu = '
                            <span class="risk-rank">' . $y . '</span>
                            <div class="api-risk-content">
                                <strong>' . esc($x) . '</strong>
                            </div>
                            <span class="risk-list-arrow" aria-hidden="true">⟶</span>'; ?>
                        <li>
                            <?= anchor(base_url() . 'top10/api/detail/' . $y, $contenu, ['class' => 'api-risk-card']) ?>
                        </li>
                    <?php endforeach;
                endif; ?>
            </ol>
        </section>

    </article>
</main>
