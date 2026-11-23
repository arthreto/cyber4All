<?php $action = 'floribondant/contenu/' . $topId . '/modifier/' . $menaceId; ?>
<main class="owasp-edit">
    <?= anchor(base_url('floribondant/contenu/' . $topId), '← Retour aux menaces', ['class' => 'owasp-edit__back']) ?>
    <header class="owasp-edit__heading">
        <p>Administration OWASP</p>
        <h1>Modifier une menace</h1>
        <span>Modifiez la menace et ses contre-mesures indépendamment.</span>
    </header>

    <?php if (session()->getFlashdata('succes')): ?>
        <p class="owasp-edit__notice" role="status"><?= esc(session()->getFlashdata('succes')) ?></p>
    <?php endif; ?>
    <?php if (session()->getFlashdata('erreur')): ?>
        <p class="owasp-edit__notice owasp-edit__notice--error" role="alert"><?= esc(session()->getFlashdata('erreur')) ?></p>
    <?php endif; ?>

    <?= form_open($action, ['class' => 'owasp-edit__card']) ?>
        <h2>Menace</h2>
        <div class="owasp-edit__field">
            <?= form_label('Nom', 'nom-menace') ?>
            <?= form_input(['name' => 'nom_menace', 'id' => 'nom-menace', 'value' => old('nom_menace', $menace->nom_menace ?? ''), 'required' => 'required']) ?>
        </div>
        <div class="owasp-edit__grid">
            <div class="owasp-edit__field">
                <?= form_label('Description', 'description-menace') ?>
                <?= form_textarea(['name' => 'desc_menace', 'id' => 'description-menace', 'rows' => 10], old('desc_menace', $menace->desc_menace ?? '')) ?>
            </div>
            <div class="owasp-edit__field">
                <?= form_label('Exemple de code', 'code-menace') ?>
                <?= form_textarea(['name' => 'prog_menace', 'id' => 'code-menace', 'rows' => 10, 'class' => 'owasp-edit__code'], old('prog_menace', $menace->prog_menace ?? '')) ?>
            </div>
        </div>
        <?= form_submit('enregistrer', 'Enregistrer la menace', ['class' => 'owasp-edit__button']) ?>
    <?= form_close() ?>

    <h2 class="owasp-edit__section-title">Contre-mesures</h2>
    <?php if ($contreMesures === []): ?>
        <p class="owasp-edit__empty">Aucune contre-mesure associée.</p>
    <?php endif; ?>
    <?php foreach ($contreMesures as $index => $contreMesure): ?>
        <?= form_open($action . '/contre-mesure/' . $contreMesure->id_cm, ['class' => 'owasp-edit__card']) ?>
            <h3>Contre-mesure <?= $index + 1 ?></h3>
            <div class="owasp-edit__grid">
                <div class="owasp-edit__field">
                    <?= form_label('Description', 'description-cm-' . $index) ?>
                    <?= form_textarea(['name' => 'desc_cm', 'id' => 'description-cm-' . $index, 'rows' => 8], $contreMesure->desc_cm ?? '') ?>
                </div>
                <div class="owasp-edit__field">
                    <?= form_label('Exemple de code', 'code-cm-' . $index) ?>
                    <?= form_textarea(['name' => 'prog_cm', 'id' => 'code-cm-' . $index, 'rows' => 8, 'class' => 'owasp-edit__code'], $contreMesure->prog_cm ?? '') ?>
                </div>
            </div>
            <?= form_submit('enregistrer', 'Enregistrer cette contre-mesure', ['class' => 'owasp-edit__button']) ?>
        <?= form_close() ?>
        <?= form_open($action . '/contre-mesure/' . $contreMesure->id_cm . '/supprimer', ['onsubmit' => "return confirm('Supprimer cette contre-mesure ?');"]) ?>
            <?= form_submit('supprimer', 'Supprimer cette contre-mesure', ['class' => 'owasp-edit__button owasp-edit__button--delete']) ?>
        <?= form_close() ?>
    <?php endforeach; ?>
    <details class="owasp-edit__card" <?= session()->getFlashdata('ajout_cm_ouvert') ? 'open' : '' ?>>
        <summary class="owasp-edit__button">Ajouter une contre-mesure</summary>
        <?= form_open($action . '/contre-mesure/ajouter') ?>
            <div class="owasp-edit__grid" style="margin-top: 1.25rem;">
                <div class="owasp-edit__field">
                    <?= form_label('Description', 'nouvelle-description-cm') ?>
                    <?= form_textarea(['name' => 'nouvelle_desc_cm', 'id' => 'nouvelle-description-cm', 'rows' => 8, 'required' => 'required'], old('nouvelle_desc_cm', '')) ?>
                </div>
                <div class="owasp-edit__field">
                    <?= form_label('Exemple de code', 'nouveau-code-cm') ?>
                    <?= form_textarea(['name' => 'nouveau_prog_cm', 'id' => 'nouveau-code-cm', 'rows' => 8, 'class' => 'owasp-edit__code'], old('nouveau_prog_cm', '')) ?>
                </div>
            </div>
            <?= form_submit('ajouter', 'Enregistrer la contre-mesure', ['class' => 'owasp-edit__button']) ?>
        <?= form_close() ?>
    </details>
</main>