<main class="detail-preview">
    <article class="detail-page">

        <header class="detail-header">
            <div class="release-title-row">
                <h1>OWASP Top 10 API : 2023</h1>
            </div>
            <p class="detail-intro">Les dix risques de sécurité les plus critiques pour les API.</p>
        </header>

        <div class="release-introduction">
            <div class="release-lead">
                <p>L'OWASP API Security Top 10 sensibilise les équipes de développement et de sécurité aux principales faiblesses rencontrées dans les API.</p>
            </div>
            <div class="api-release-note">
                <span>Édition 2023</span>
                <p>Sélectionnez un risque pour ouvrir sa page dédiée. Les informations détaillées seront ajoutées progressivement.</p>
            </div>
        </div>

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
                        $badge = 'API' . $compteur_temp . ':2023';
                        $contenu = '
                            <span class="risk-rank">' . $compteur_temp . '</span>
                            <span class="risk-list-code">' . $badge . '</span>
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
