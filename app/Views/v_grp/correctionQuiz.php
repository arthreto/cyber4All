<?= link_tag(base_url('/css/css_grp/grp.css')) ?>

<?php
$concepts = [
        1 => ['d', 'D', 'Disponibilité',   'Est-ce accessible ?'],
        2 => ['i', 'I', 'Intégrité',       'Est-ce intact ?'],
        3 => ['c', 'C', 'Confidentialité', 'Qui peut voir ?'],
        4 => ['t', 'T', 'Traçabilité',     'Qui a fait quoi ?'],
];
?>

<main>
    <div class="presentation">
        <div class="retour-question">
            <?= anchor(base_url('quiz'), '← Quitter', ['class' => 'btn-retour-question']) ?>
        </div>
    </div>

    <div class="progression-ligne">
        <div class="progression">
            <div class="prog-infos">
                <span class="prog-question">Question <?= esc($numero) ?> / <?= esc($total) ?></span>
                <span class="prog-restantes"><?= esc($restantes) ?> restante<?= $restantes > 1 ? 's' : '' ?></span>
                <span class="prog-score">Score : <?= esc($score) ?></span>
            </div>
            <progress class="prog-barre" value="<?= esc($numero) ?>" max="<?= esc($total) ?>"></progress>
        </div>
    </div>

    <div class="panneau">
        <div class="titre_quiz">
            <h1><?= esc($correction['mot']) ?></h1>
        </div>
        <div class="sous_titre">
            <p><?= esc($correction['question']) ?></p>
            <p class="<?= $correction['juste'] ? 'msg-ok' : 'msg-ko' ?>">
                <?= $correction['juste'] ? 'Bonne réponse ✅' : 'Mauvaise réponse ❌' ?>
            </p>
        </div>

        <div class="cartes">
            <?php foreach ($concepts as $id => [$cls, $lettre, $nom, $sous]): ?>
                <?php
                $coche   = in_array($id, $correction['coches'], true);
                $bonne   = in_array($id, $correction['bonnes'], true);
                $etat    = '';
                if ($coche && $bonne)  $etat = 'ok';      // cochée et correcte
                elseif ($coche)        $etat = 'ko';      // cochée mais fausse
                elseif ($bonne)        $etat = 'oubli';   // correcte mais oubliée
                ?>
                <label class="carte-question carte-correction carte--<?= $cls ?> <?= $etat ?>">
                    <input type="checkbox" disabled <?= $coche ? 'checked' : '' ?>>
                    <span class="lettre"><?= $lettre ?></span>
                    <span><strong><?= $nom ?></strong><br><small><?= $sous ?></small></span>
                </label>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($correction['explication'])): ?>
            <div class="explication">
                <strong>Explication :</strong>
                <p><?= esc($correction['explication']) ?></p>
            </div>
        <?php endif; ?>

        <div class="ligne-bouton">
            <a href="<?= base_url('quiz/question') ?>" class="btn-val">
                <?= $fini ? 'Voir le résultat →' : 'Question suivante →' ?>
            </a>
        </div>
    </div>
</main>