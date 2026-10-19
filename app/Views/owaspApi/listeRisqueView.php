<section class="api-top-ten">
    <div class="section-heading">
        <p class="eyebrow">Classement Officiel</p>
        <h2>Top 10 des risques de sécurité API - 2023</h2>
        <p>Chaque élément présente la traduction française de la description officielle</p>
    </div>
</section>

<ol class="api-risk-list">
    <?php if (!empty($apiVulnerabilities)):
        $compteur_temp = 0;
        foreach ($apiVulnerabilities as $nom => $description):
            // Initialisation du compteur
            $compteur_temp += 1;
            // Mise en place du formatage du compteur : 01, 02, 03 ... 09, 10
            $format = sprintf('%02d', $compteur_temp);
            // Le contenu HTMl interne de la carte cliquable
            $contenu = '
                <span class="risk-list-code">' . $format . '</span>
                <div class="api-risk-content">
                    <strong>' . esc($nom) . '</strong>
                    <span>' . esc($description) . '</span>
                </div>
                <span class="risk-list-arrow" aria-hidden="true">⟶</span>
                '; ?>
            <li class="api-risk-card">
                <?= anchor(base_url() . 'api/detail/' . $compteur_temp, $contenu, ['class' => 'api-risk-card']) ?>
            </li>
        <?php endforeach;
    endif; ?>
</ol>
