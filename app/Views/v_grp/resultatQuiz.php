<?= link_tag(base_url('/css/css_grp/grp.css')) ?>

<?php
$pourcentage = $total > 0 ? (int) round($score / $total * 100) : 0;

if ($pourcentage === 100) {
    $titre = 'Parfait !';           $message = 'Aucune erreur, tu maîtrises les piliers du DICT.';
} elseif ($pourcentage >= 70) {
     $titre = 'Très bien !';         $message = 'Tu as de solides bases en cybersécurité.';
} elseif ($pourcentage >= 40) {
    ; $titre = 'Pas mal !';           $message = 'Encore un petit effort pour tout retenir.';
} else {
    ; $titre = 'Continue !';          $message = 'Rejoue pour t\'entraîner, tu vas progresser.';
}
?>

<main>
    <div class="resultat-ligne">
        <div class="panneau resultat">

            <p class="nom_train">Entraînement DICT terminé</p>
            <h1 class="resultat-titre"><?= esc($titre) ?></h1>
            <p class="resultat-message"><?= esc($message) ?></p>

            <div class="resultat-cercle" style="--pct: <?= $pourcentage ?>">
                <div class="resultat-cercle-interieur">
                    <span class="resultat-score"><?= esc($score) ?><small>/<?= esc($total) ?></small></span>
                    <span class="resultat-pct"><?= $pourcentage ?> %</span>
                </div>
            </div>

            <div class="resultat-stats">
                <div class="stat stat-ok">
                    <strong><?= esc($score) ?></strong>
                    <span>bonne<?= $score > 1 ? 's' : '' ?> réponse<?= $score > 1 ? 's' : '' ?></span>
                </div>
                <div class="stat stat-ko">
                    <strong><?= esc($total - $score) ?></strong>
                    <span>erreur<?= ($total - $score) > 1 ? 's' : '' ?></span>
                </div>
            </div>

            <div class="resultat-boutons">
                <?= anchor('quiz', 'Rejouer ↻', ['class' => 'btn-lancer']) ?>
                <?= anchor('/', 'Accueil', ['class' => 'btn-secondaire']) ?>
            </div>

        </div>
    </div>
</main>