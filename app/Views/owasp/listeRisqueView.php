<?php
// Cartes de la liste : un lien par menace
$liens = [];
foreach ($menaces as $position => $item) {
    $contenu = '<span class="risk-rank">' . $position . '</span>'
            . '<div class="risk-content"><strong>' . esc($item->nom_menace) . '</strong></div>'
            . '<span class="risk-list-arrow" aria-hidden="true">→</span>';

    $liens[] = anchor(
            'top10/api/detail/' . $Top->id_top . '/' . $position,
            $contenu,
            ['class' => 'risk-card']
    );
}
?>
<main class="detail-preview">
    <article class="detail-page">
        <p class="eyebrow">Classement officiel</p>

        <section class="top-ten">
            <div class="section-heading">
                <h2>Top 10 des risques de sécurité</h2>
                <p>Chaque élément présente la traduction française de la description officielle.</p>
            </div>

            <?= ol($liens, ['class' => 'risk-list']) ?>
        </section>

    </article>
</main>