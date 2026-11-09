<?php
/**
 * Onglets de l'administration de l'Espace compréhension SIO
 * À inclure dans chaque vue de section : <?= view('admin/tracabilite/onglets') ?>
 *
 * L'onglet actif vient de $onglet ('lecons', 'quiz' ou 'questions') si le contrôleur le passe.
 * Sinon, il est déduit de l'URL : floribondant/theme/lecons, floribondant/theme/quiz,
 * et floribondant/theme pour les questions.
 */



$onglets = [
    'lecons'    => ['libelle' => 'Leçons',    'url' => 'floribondant/theme/lecons'],
    'quiz'      => ['libelle' => 'Quiz',      'url' => 'floribondant/theme/quiz'],
    'questions' => ['libelle' => 'Questions', 'url' => 'floribondant/theme'],
];

$ongletActif = $onglet;
?>

<div class="sio-admin">
    <div class="sio-admin" style="padding-bottom: 0">
    <header class="sio-admin__entete">
        <p class="sio-admin__ariane">Administration</p>
        <h1 class="sio-admin__titre">Espace Traçabilité</h1>
    </header>

<nav class="sio-admin__onglets" aria-label="Sections de l'Espace compréhension SIO">
        <?php foreach ($onglets as $cle => $infos): ?>
            <?php
            $estActif  = ($cle === $ongletActif);
            $attributs = ['class' => 'sio-admin__onglet' . ($estActif ? ' sio-admin__onglet--actif' : '')];
            if ($estActif) {
                $attributs['aria-current'] = 'page';
            }
            ?>
            <?= anchor(base_url($infos['url']), esc($infos['libelle']), $attributs) ?>
        <?php endforeach; ?>
    </nav>
</div>
