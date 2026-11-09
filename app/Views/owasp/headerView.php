<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php
    $nom = $Top->nom_top;
    $edition = $Top->edition_top;
    $libelle = $Top->libelle_top;
    ?>
    <title>OWASP Top 10 <?= esc($nom) ?> : <?= esc($edition) ?></title>
    <?= link_tag('css/owasp/stylePresentation.css') ?>
</head>
<body class="<?= esc(strtolower($nom)) ?>">
<article class="detail-preview">

    <header class="detail-header">
        <div class="release-title-row">
            <span class="release-mark"><?= esc($nom) ?></span>
            <div>
                <h1>OWASP Top 10 <?= esc($nom) ?> : <?= esc($edition) ?></h1>
                <p class="detail-intro"><?= esc($libelle) ?></p>
            </div>
        </div>
    </header>

    <section class="release-introduction">
        <div class="release-lead">
            <p>L'OWASP <?= esc($nom) ?> Security Top 10 sensibilise les équipes de développement et sécurité
                aux principales faiblesses rencontrées dans la conception, l'implémentation et l'exploitation de ces dernières.</p>
        </div>
        <div class="release-note">
            <span>Edition <?= esc($edition) ?></span>
            <p>Sélectionnez un risque pour ouvrir sa page dédiée.
                Les informations détaillées seront ajoutées progressivement</p>
        </div>
    </section>