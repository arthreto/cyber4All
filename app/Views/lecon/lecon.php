<?php
/**
 * PAGE D'UNE LEÇON - Espace compréhension SIO
 *
 * Données attendues (depuis le contrôleur) :
 * - $lecon          : la leçon affichée
 * - $theme          : le thème de la leçon
 * - $idTheme        : l'id du thème (int)
 * - $numero         : la position de la leçon dans le thème (int, à partir de 1)
 * - $leconsTheme    : les leçons du même thème
 * - $leconSuivante  : la leçon suivante du thème (ou null)
 */

$dateMaj  = $lecon->date_modif ?? $lecon->date_ajout;
$nbLecons = count($leconsTheme);
?>

<?= link_tag(base_url('/css/lecon/lecon.css')) ?>

<div class="lecon-page">

    <!-- Fil d'Ariane -->
    <nav class="lecon-ariane" aria-label="Fil d'Ariane">
        <ol class="lecon-ariane__liste">
            <li class="lecon-ariane__item">Comprendre les accès</li>
            <li class="lecon-ariane__item">
                <a href="<?= site_url('theme/' . $idTheme . '/1') ?>" class="lecon-ariane__lien"><?= esc($theme->nom) ?></a>
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
                    <?php if ($auteur !== null): ?>
                        Rédigé par <span class="lecon__auteur"><?= esc($auteur->pseudo) ?></span> ·
                    <?php endif ?>
                    <?php if ($dateMaj !== null): ?>
                        Mis à jour le <time datetime="<?= esc($dateMaj->toDateString(), 'attr') ?>"><?= $dateMaj->format('d/m/Y') ?></time>
                    <?php endif ?>
                </p>
            </header>

            <!-- Markdown de la leçon, échappé par esc(). Aucun espace autour du PHP : 4 espaces en début de ligne = bloc de code en Markdown -->
            <div class="lecon__contenu"><?= esc($lecon->contenu) ?></div>

            <!-- Bibliothèques : marked (Markdown -> HTML) et DOMPurify (sécurité XSS). Versions majeures épinglées -->
            <script src="https://cdn.jsdelivr.net/npm/marked@18/lib/marked.umd.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>

            <!-- Doit rester après les deux scripts ci-dessus -->
            <script>
                const bloc = document.querySelector('.lecon__contenu');

                // Si une bibliothèque n'a pas chargé, on garde le Markdown brut
                if (window.marked && window.DOMPurify) {
                    // textContent : récupère le Markdown d'origine (annule l'échappement de esc())
                    const markdown = bloc.textContent;
                    // Conversion en HTML, puis suppression de tout code dangereux
                    const html = DOMPurify.sanitize(marked.parse(markdown));

                    bloc.innerHTML = html;
                } else {
                    console.error('marked ou DOMPurify non chargé : Markdown affiché brut');
                }
            </script>


            <!-- Leçon suivante du thème -->
            <?php if ($leconSuivante !== null): ?>
                <nav class="lecon-suite" aria-label="Leçon suivante">
                    <a href="<?= site_url('theme/' . $theme->id . '/' . ($numero + 1)) ?>" class="lecon-suite__lien">
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
                <?php foreach ($leconsTheme as $i => $l): ?>
                    <?php $estActive = ($i + 1) === $numero; ?>
                    <li>
                        <a href="<?= site_url('theme/' . $theme->id . '/' . ($i + 1)) ?>"
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