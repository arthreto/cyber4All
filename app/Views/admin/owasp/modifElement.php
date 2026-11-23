<main class="admin-top-container">
    <h1>Modifier une menace</h1>

    <?= form_open('', ['onsubmit' => 'return false;']) ?>
    <?= form_label('Nom', 'nom-menace') ?>
    <?= form_input(['name' => 'nom_menace', 'id' => 'nom-menace', 'value' => $menace->nom_menace ?? '']) ?>

    <?= form_label('Description', 'description-menace') ?>
    <?= form_textarea(['name' => 'desc_menace', 'id' => 'description-menace'], $menace->desc_menace ?? '') ?>

    <?= form_label('Exemple de code', 'code-menace') ?>
    <?= form_textarea(['name' => 'prog_menace', 'id' => 'code-menace'], $menace->prog_menace ?? '') ?>

    <h2>Contre-mesures</h2>
    <?php if ($contreMesures === []): ?>
        <p>Aucune contre-mesure associée.</p>
    <?php endif; ?>
    <?php foreach ($contreMesures as $index => $contreMesure): ?>
        <section>
            <h3>Contre-mesure <?= $index + 1 ?></h3>
            <?= form_label('Description', 'description-cm-' . $index) ?>
            <?= form_textarea(['name' => 'contre_mesures[' . $index . '][desc_cm]', 'id' => 'description-cm-' . $index], $contreMesure->desc_cm ?? '') ?>

            <?= form_label('Exemple de code', 'code-cm-' . $index) ?>
            <?= form_textarea(['name' => 'contre_mesures[' . $index . '][prog_cm]', 'id' => 'code-cm-' . $index], $contreMesure->prog_cm ?? '') ?>
        </section>
    <?php endforeach; ?>
    <?= form_close() ?>
    <p>L’enregistrement des modifications n’est pas encore disponible.</p>
</main>