<section class="top-ten">
    <div class="section-heading">
        <h2>Menaces et contre-mesures</h2>
        <p>Retrouvez les menaces associées ainsi que les solutions recommandées.</p>
    </div>

    <article class="threat-card">
        <div class="card-header">
            <h3><?= esc($nom ?? '') ?></h3>
        </div>
        <div class="card-content">
            <div class="info-block">
                <h4>Description</h4>
                <p><?= esc($description ?? '') ?></p>
            </div>
            <div class="code-block">
                <h4>Exemple de code</h4>
                <pre><code><?= esc($prog ?? '') ?></code></pre>
            </div>
</div>
        <?php if (!empty($prog_cm)): ?>
            <div class="countermeasures">
                <h4 class="counter-title">Contre-mesures associées</h4>
                <?php foreach ($prog_cm as $element): ?>
                    <div class="counter-card">
                        <pre><code><?= esc($element) ?></code></pre>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>