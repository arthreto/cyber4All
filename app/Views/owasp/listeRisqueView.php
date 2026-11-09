 <main class="detail-preview">
    <article class="detail-page">

        <section class="api-top-ten">
            <div class="section-heading">
                <p class="eyebrow">Classement officiel</p>
                <h2>Top 10 des risques de sécurité API – 2023</h2>
                <p>Chaque élément présente la traduction française de la description officielle.</p>
            </div>

            <ol class="api-risk-list">
                <?php if (!empty($apiVulnerabilities)):
                    $compteur_temp = 0;
                    foreach ($apiVulnerabilities as $nom => $description):
                        $compteur_temp++;
                        $contenu = '
                            <span class="risk-rank">' . $compteur_temp . '</span>
                            <div class="api-risk-content">
                                <strong>' . esc($nom) . '</strong>
                                <span>' . esc($description) . '</span>
                            </div>
                            <span class="risk-list-arrow" aria-hidden="true">⟶</span>'; ?>
                        <li>
                            <?= anchor(base_url() . 'api/detail/' . $compteur_temp, $contenu, ['class' => 'api-risk-card']) ?>
                        </li>
                    <?php endforeach;
                endif; ?>
            </ol>
        </section>

    </article>
</main>
