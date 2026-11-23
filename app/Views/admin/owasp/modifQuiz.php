<main class="admin-top-container">
    <?php if (isset($question)): ?>
        <h1>Question</h1>
        <?= form_label('Libellé', 'libelle-ques') ?>
        <?= form_input(['id' => 'libelle-ques', 'value' => $question['libelle_ques'], 'readonly' => true]) ?>
        <h2>Bonnes réponses</h2>
        <?php if ($reponses === []): ?>
            <p>Aucune bonne réponse associée.</p>
        <?php endif; ?>
        <ul>
            <?php foreach ($reponses as $reponse): ?>
                <li><?= anchor('floribondant/owasp/quiz/reponse/' . $reponse['id_prop'], esc($reponse['libelle_prop'])) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <h1>Réponse</h1>
        <?= form_label('Libellé', 'libelle-prop') ?>
        <?= form_input(['id' => 'libelle-prop', 'value' => $proposition['libelle_prop'], 'readonly' => true]) ?>
    <?php endif; ?>
    <p>L’enregistrement des modifications n’est pas encore disponible.</p>
    <?= anchor('floribondant/owasp/quiz', 'Retour aux quiz') ?>
</main>
