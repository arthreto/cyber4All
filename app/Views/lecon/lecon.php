<?php
/**
 * PAGE D'UNE LEÇON - Espace compréhension SIO
 *
 * Données attendues (depuis le contrôleur) :
 * - $lecon          : la leçon affichée
 * - $theme          : le thème de la leçon
 * - $leconsTheme    : les leçons du même thème
 * - $leconSuivante  : la leçon suivante du thème (ou null)
 */

$dateMaj  = $lecon->date_modif ?? $lecon->date_ajout;
$nbLecons = count($leconsTheme);
?>

<div class="lecon-page">

    <!-- Fil d'Ariane -->
    <nav class="lecon-ariane" aria-label="Fil d'Ariane">
        <ol class="lecon-ariane__liste">
            <li class="lecon-ariane__item">Comprendre les accès</li>
            <li class="lecon-ariane__item">
                <a href="<?= site_url('theme/' . $theme->id) ?>" class="lecon-ariane__lien"><?= esc($theme->nom) ?></a>
            </li>
            <li class="lecon-ariane__item" aria-current="page"><?= esc($lecon->titre) ?></li>
        </ol>
    </nav>

    <div class="lecon-page__grille">

        <!-- ===== Contenu de la leçon ===== -->
        <article class="lecon">

            <header class="lecon__entete">
                <p class="lecon__theme">Thème : <?= esc($theme->nom) ?></p>
                <h1 class="lecon__titre"><?= esc($lecon->titre) ?></h1>
                <p class="lecon__meta">
                    <?php if (! empty($lecon->auteur)): ?>
                        Rédigé par <span class="lecon__auteur"><?= esc($lecon->auteur) ?></span> ·
                    <?php endif ?>
                    <?php if ($dateMaj !== null): ?>
                        Mis à jour le <time datetime="<?= esc($dateMaj->toDateString(), 'attr') ?>"><?= $dateMaj->format('d/m/Y') ?></time>
                    <?php endif ?>
                </p>
            </header>

            <!-- Markdown de la leçon, échappé par esc(). Aucun espace autour du PHP : 4 espaces en début de ligne = bloc de code en Markdown -->
            <div class="lecon__contenu"><?= esc($lecon->contenu) ?></div>

            <!-- Bibliothèque qui convertit le Markdown en HTML -->
            <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
            <script>
                // Récupère le bloc contenant le Markdown
                const bloc = document.querySelector('.lecon__contenu');

                // textContent relit le Markdown d'origine (les &lt; de esc() redeviennent des <),
                // marked.parse() le convertit en HTML, qui remplace le contenu du bloc
                bloc.innerHTML = marked.parse(bloc.textContent);
            </script>

            <!-- Leçon suivante du thème -->
            <?php if ($leconSuivante !== null): ?>
                <nav class="lecon-suite" aria-label="Leçon suivante">
                    <a href="<?= site_url('theme/' . $theme->id . '/' . $leconSuivante->id) ?>" class="lecon-suite__lien">
                        Leçon suivante : <?= esc($leconSuivante->titre) ?> →
                    </a>
                </nav>
            <?php endif ?>
        </article>

        <!-- ===== Leçons du thème ===== -->
        <aside class="lecon-sommaire" aria-labelledby="lecon-sommaire-titre">
            <h2 id="lecon-sommaire-titre" class="lecon-sommaire__titre">
                Thème <?= esc($theme->nom) ?> · <?= $nbLecons ?> leçon<?= $nbLecons > 1 ? 's' : '' ?>
            </h2>
            <ol class="lecon-sommaire__liste">
                <?php foreach ($leconsTheme as $l): ?>
                    <?php $estActive = (int) $l->id === (int) $lecon->id; ?>
                    <li>
                        <a href="<?= site_url('theme/' . $theme->id . '/' . $l->id) ?>"
                           class="lecon-sommaire__lien<?= $estActive ? ' lecon-sommaire__lien--actif' : '' ?>"
                                <?= $estActive ? 'aria-current="page"' : '' ?>>
                            <?= esc($l->titre) ?>
                        </a>
                    </li>
                <?php endforeach ?>
            </ol>
        </aside>

    </div>
</div>