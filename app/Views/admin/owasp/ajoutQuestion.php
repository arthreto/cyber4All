<main class="admin-top-container">
    <h1>Ajouter une question</h1>
    <?php foreach ($erreurs as $erreur): ?>
        <p><?= esc($erreur) ?></p>
    <?php endforeach; ?>
    <?php if ($menaces === []): ?>
        <p>Aucune menace disponible pour associer une question.</p>
    <?php elseif ($propositions === []): ?>
        <p>Aucune réponse disponible pour associer une question.</p>
    <?php else: ?>
        <?= form_open('floribondant/owasp/quiz/ajouter') ?>
        <?= form_label('Question', 'libelle-ques') ?>
        <?= form_input(['name' => 'libelle_ques', 'id' => 'libelle-ques', 'value' => set_value('libelle_ques'), 'maxlength' => 50, 'required' => true]) ?>
        <?= form_label('Menace', 'menace-id') ?>
        <?php $options = ['' => 'Choisir une menace']; ?>
        <?php foreach ($menaces as $menace): ?>
            <?php $options[$menace->id_menace] = $menace->nom_menace . ' (' . $menace->tops_id . ')'; ?>
        <?php endforeach; ?>
        <?= form_dropdown('menace_id', $options, set_value('menace_id'), ['id' => 'menace-id', 'required' => true]) ?>
        <?= form_label('Bonne réponse', 'proposition-id') ?>
        <?php $optionsReponses = ['' => 'Choisir une réponse']; ?>
        <?php foreach ($propositions as $proposition): ?>
            <?php $optionsReponses[$proposition['id_prop']] = $proposition['libelle_prop']; ?>
        <?php endforeach; ?>
        <?= form_dropdown('proposition_id', $optionsReponses, set_value('proposition_id'), ['id' => 'proposition-id', 'required' => true]) ?>
        <?= form_submit('ajouter', 'Ajouter') ?>
        <?= form_close() ?>
    <?php endif; ?>
    <?= anchor('floribondant/owasp/quiz', 'Retour aux quiz') ?>
</main>