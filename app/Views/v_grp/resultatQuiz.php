<link rel="stylesheet" href="<?= base_url('/css/css_grp/grp.css') ?>">

<main>
    <div class="presentation">
        <div class="titre_quiz"><h1>Résultat</h1></div>
        <div class="sous_titre">
            <p>Tu as obtenu <?= esc($score) ?> / <?= esc($total) ?></p>
        </div>
        <div class="ligne-bouton">
            <?= anchor('quiz', 'Rejouer', ['class' => 'btn-lancer']) ?>
        </div>
    </div>
</main>
