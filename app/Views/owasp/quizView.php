<main class="detail-preview">
    <article class="detail-page">

        <header class="detail-header">
            <div class="release-title-row">
                <span class="release-mark"><?= esc(strtoupper($nom_top)) ?></span>
                <div>
                    <h1>Quiz <?= esc(strtoupper($nom_top)) ?></h1>
                    <p class="detail-intro">
                        <?= count($questions) ?> questions, une seule bonne réponse par question.
                    </p>
                </div>
            </div>

            <?php if (!empty($resultat)): ?>
                <div class="quiz-score" role="status">
                    <?= esc($resultat['score']) ?> / <?= esc($resultat['total']) ?>
                    <span>bonnes réponses</span>
                </div>
            <?php endif; ?>
        </header>

        <?php if (empty($questions)): ?>
            <p class="quiz-empty">Aucune question n’est disponible pour ce quiz pour le moment.</p>
        <?php else: ?>

            <?= form_open('top10/quiz/' . $nom_top, ['class' => 'quiz-form']) ?>

            <?php foreach ($questions as $i => $q):
                $detail = $resultat['details'][$q['id_question']] ?? null; ?>

                <?= form_fieldset(
                    '<span class="quiz-num">' . ($i + 1) . '</span> ' . esc($q['texte_question']),
                    ['class' => 'quiz-question']
            ) ?>

                <?php if (!empty($q['code_question'])): ?>
                <pre class="quiz-code"><code><?= esc(trim($q['code_question'])) ?></code></pre>
            <?php endif; ?>

                <?php foreach ($q['reponses'] as $j => $r):
                $estChoisie = $detail && $r['id_reponse'] == $detail['choisie'];
                $classe = '';
                if ($detail) {
                    if ($r['id_reponse'] == $detail['bonne']) {
                        $classe = ' is-correct';
                    } elseif ($estChoisie) {
                        $classe = ' is-wrong';
                    }
                } ?>
                <label class="quiz-option<?= $classe ?>">
                    <?= form_radio(
                            'reponse[' . $q['id_question'] . ']',
                            $r['id_reponse'],
                            $estChoisie,
                            $detail ? 'disabled' : 'required'
                    ) ?>
                    <span class="quiz-letter"><?= chr(65 + $j) ?></span>
                    <span><?= esc($r['libelle_reponse']) ?></span>
                </label>
            <?php endforeach; ?>

                <?php if ($detail && !empty($q['explication_question'])): ?>
                <p class="quiz-explication"><?= esc($q['explication_question']) ?></p>
            <?php endif; ?>

                <?= form_fieldset_close() ?>
            <?php endforeach; ?>

            <?php if (empty($resultat)): ?>
                <?= form_submit('valider', 'Valider mes réponses', ['class' => 'back-button quiz-submit']) ?>
            <?php endif; ?>

            <?= form_close() ?>

        <?php endif; ?>

        <div class="detail-footer-actions">
            <?php if (!empty($resultat)): ?>
                <?= anchor('top10/quiz/' . $nom_top, 'Recommencer', ['class' => 'back-button']) ?>
            <?php endif; ?>
            <?= anchor('top10/quiz', 'Changer de quiz', ['class' => 'text-button']) ?>
            <?= anchor('top10/' . $nom_top, 'Retour au Top 10', ['class' => 'text-button']) ?>
        </div>

    </article>
</main>