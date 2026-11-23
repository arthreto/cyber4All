<main class="admin-top-container">
    <h1>Modifier une menace</h1>

    <label for="nom-menace">Nom</label>
    <input id="nom-menace" type="text" value="<?= esc($menace->nom_menace, 'attr') ?>">

    <label for="description-menace">Description</label>
    <textarea id="description-menace"><?= esc($menace->desc_menace) ?></textarea>

    <label for="code-menace">Exemple de code</label>
    <textarea id="code-menace"><?= esc($menace->prog_menace) ?></textarea>

    <h2>Contre-mesures</h2>
    <?php if ($contreMesures === []): ?>
        <p>Aucune contre-mesure associée.</p>
    <?php endif; ?>
    <?php foreach ($contreMesures as $index => $contreMesure): ?>
        <section>
            <h3>Contre-mesure <?= $index + 1 ?></h3>
            <label for="description-cm-<?= $index ?>">Description</label>
            <textarea id="description-cm-<?= $index ?>"><?= esc($contreMesure->desc_cm) ?></textarea>

            <label for="code-cm-<?= $index ?>">Exemple de code</label>
            <textarea id="code-cm-<?= $index ?>"><?= esc($contreMesure->prog_cm) ?></textarea>
        </section>
    <?php endforeach; ?>
    <p>L’enregistrement des modifications n’est pas encore disponible.</p>
</main>