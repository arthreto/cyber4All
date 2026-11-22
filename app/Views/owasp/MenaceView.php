<?php
$descriptionsCm = array_filter(array_column($contreMesures, 'desc_cm'));
$codesCm        = array_filter(array_column($contreMesures, 'prog_cm'));
?>
<main class="detail-preview">
    <article class="detail-page">
        <div class="detail-layout">

            <!-- Navigation latérale : positions du Top consulté -->
            <nav class="top-sidebar" aria-label="Navigation du Top 10">
                <h2 class="top-sidebar-title">Top 10 <?= esc($Top->nom_top) ?></h2>
                <ol class="top-sidebar-list">
                    <?php foreach ($menaces as $pos => $item): ?>
                        <?php $actif = ($pos == $position); ?>
                        <li>
                            <a href="<?= base_url('top10/api/detail/' . $Top->id_top . '/' . $pos) ?>"
                               class="top-sidebar-link<?= $actif ? ' active' : '' ?>"
                                    <?= $actif ? 'aria-current="page"' : '' ?>>
                                <span class="top-sidebar-rank"><?= $pos ?></span>
                                <span class="top-sidebar-name"><?= esc($item->nom_menace) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>

            <!-- Contenu de la menace -->
            <div class="detail-main">
                <section class="top-ten">
                    <div class="section-heading">
                        <h2>Menaces et contre-mesures</h2>
                        <p>Retrouvez les menaces associées ainsi que les solutions recommandées.</p>
                    </div>

                    <article class="threat-card">
                        <div class="card-header">
                            <h3><?= esc($menace->nom_menace) ?></h3>
                        </div>

                        <div class="card-content">
                            <div class="info-block">
                                <h4>Description</h4>
                                <p><?= esc($menace->desc_menace) ?></p>
                            </div>
                            <?php if (!empty($menace->prog_menace)): ?>
                                <div class="code-block">
                                    <h4>Exemple de code</h4>
                                    <pre><code><?= esc($menace->prog_menace) ?></code></pre>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($descriptionsCm)): ?>
                            <div class="countermeasures">
                                <h4 class="counter-title">Contre-mesures associées</h4>
                                <?php foreach ($descriptionsCm as $texte): ?>
                                    <div class="counter-card">
                                        <pre><code><?= esc($texte) ?></code></pre>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($codesCm)): ?>
                            <div class="countermeasures">
                                <h4 class="counter-title">Code de contre-mesures</h4>
                                <?php foreach ($codesCm as $code): ?>
                                    <div class="counter-card">
                                        <pre><code><?= esc($code) ?></code></pre>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                </section>
            </div>

        </div>
    </article>
</main>