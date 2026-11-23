<?= link_tag(base_url('/css/css_grp/grp.css')) ?>

<?php
$concepts = [
        1 => ['d', 'D', 'Disponibilité',   'Est-ce accessible ?'],
        2 => ['i', 'I', 'Intégrité',       'Est-ce intact ?'],
        3 => ['c', 'C', 'Confidentialité', 'Qui peut voir ?'],
        4 => ['t', 'T', 'Traçabilité',     'Qui a fait quoi ?'],
];
// Cases cochées qui sont aussi correctes
$bonnesCochees = array_intersect($correction['coches'], $correction['bonnes']);

if ($correction['juste']) {
    $statut = 'ok';
    $texte  = 'Bonne réponse ✅';
    $message = '1 points gagné';
} elseif (count($bonnesCochees) > 0) {
    $statut = 'partiel';
    $texte  = 'Réponse partiellement correcte ⚠️';
    $message = 'Vous ne gagnez pas de points';
} else {
    $statut = 'ko';
    $texte  = 'Mauvaise réponse ❌';
    $message = 'Vous ne gagnez pas de points';
}
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
                <span class="prog-score">Score : <?= esc($score) ?></span>
            </div>
            <progress class="prog-barre" value="<?= esc($numero) ?>" max="<?= esc($total) ?>"></progress>
        </div>
    </div>

    <div class="panneau">
        <div class="titre_quiz">
            <h1><?= esc($correction['mot']) ?></h1>
        </div>
        <p class="sous_titre">
        <p><?= esc($correction['question']) ?></p>
        <div class="ligne-message">
            <p class="msg-<?= $statut ?>">
                <?= $texte ?>
            </p>
            <p class="msgCorrection">
                <?= $message ?>
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
                <?= $fini ? 'Voir les résultats →' : 'Question suivante →' ?>
            </a>
        </div>
    </div>
</main>