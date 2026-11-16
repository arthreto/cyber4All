<section class="api-top-ten">
            <div class="section-heading">
                <h2>Menaces et contre-mesures</h2>
                <p>
                    Retrouvez les menaces associées ainsi que les solutions recommandées.
                </p>
            </div>
                <article class="threat-card">
                    <div class="card-header">
                        <h3><?php if (!empty($nom)): ?>
                            <pre><?= esc($nom) ?></pre>
                            <?php endif; ?></h3>
                    </div>
                    <div class="card-content">
                        <div class="info-block">
                            <h4>Description</h4>
                            <p>
                                <?php if (!empty($description)): ?>
                                <?= esc($description) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="code-block">
                            <h4>Exemple de code</h4>
                            <pre><code><?php if (!empty($prog)): ?>
                                        <pre><?=esc($prog)?></pre>
                                <?php endif; ?> ?></code></pre>
                        </div>
                    </div>
                    <?php if (!empty($prog_cm)): ?>
                    <?php foreach ($prog_cm as $i => $element): ?>
                        <div class="countermeasures">
                            <h4 class="counter-title">
                                Contre-mesures associées
                            </h4>
                                <div class="counter-card">

                                    <p>
                                        <?= esc($element) ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
        </section>