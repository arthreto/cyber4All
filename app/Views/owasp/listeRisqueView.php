 <main class="detail-preview">
    <article class="detail-page">
        <p class="eyebrow">Classement officiel</p>
        <section class="top-ten">
            <div class="section-heading">

                <h2>Top 10 des risques de sécurité</h2>
                <p>Chaque élément présente la traduction française de la description officielle.</p>
            </div>

            <ol class="risk-list">
                <?php if (!empty($Vulnerabilities)):
                    foreach ($Vulnerabilities as $nom => $id):
                        $contenu = '
                            <span class="risk-rank">' . $id . '</span>
                            <div class="risk-content">
                                <strong>' . esc($nom) . '</strong>
                            </div>
                            <span class="risk-list-arrow" aria-hidden="true">⟶</span>'; ?>
                        <li>
                            <?= anchor(base_url() . 'top10/api/detail/' . $id, $contenu, ['class' => 'risk-card']) ?>
                        </li>
                    <?php endforeach;
                endif; ?>
            </ol>
        </section>

    </article>
</main>
