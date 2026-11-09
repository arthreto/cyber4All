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
                    $compteur_temp = 0;
                    foreach ($Vulnerabilities as $nom => $description):
                        $compteur_temp++;
                        $contenu = '
                            <span class="risk-rank">' . $compteur_temp . '</span>
                            <div class="risk-content">
                                <strong>' . esc($nom) . '</strong>
                                <span>' . esc($description) . '</span>
                            </div>
                            <span class="risk-list-arrow" aria-hidden="true">⟶</span>'; ?>
                        <li>
                            <?= anchor(base_url() . '/detail/' . $compteur_temp, $contenu, ['class' => 'risk-card']) ?>
                        </li>
                    <?php endforeach;
                endif; ?>
            </ol>
        </section>

    </article>
</main>
