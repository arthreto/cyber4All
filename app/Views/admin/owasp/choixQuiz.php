<?php
$lienTriId = site_url('floribondant/owasp/quiz') . '?' . http_build_query([
    'recherche' => $recherche,
    'ordre' => $ordre === 'id_asc' ? 'id_desc' : 'id_asc',
]);
$lienTriQuestion = site_url('floribondant/owasp/quiz') . '?' . http_build_query([
    'recherche' => $recherche,
    'ordre' => $ordre === 'question_asc' ? 'question_desc' : 'question_asc',
]);
?>
<main class="admin__pannel">
    <div id="admin__pannel-child-1" class="admin__pannel-child">
        <section id="recherche">
            <div class="container-recherche">
                <?= form_open('floribondant/owasp/quiz', ['method' => 'get', 'class' => 'form-recherche']) ?>
                <?= form_input(['type' => 'search', 'name' => 'recherche', 'class' => 'recherche-input', 'maxlength' => 100, 'placeholder' => 'Rechercher une question', 'value' => $recherche]) ?>
                <?= form_hidden('ordre', $ordre) ?>
                <?= form_submit(['class' => 'recherche-bouton-soumission'], 'Rechercher') ?>
                <?= form_close() ?>
            </div>
        </section>
        <?= anchor('floribondant/owasp/quiz/ajouter', 'Ajouter une question', ['class' => 'glossary-form__trigger']) ?>
    </div>

    <div id="admin__pannel-child-2" class="admin__pannel-child">
        <div class="container">
            <div class="mots-table">
                <div class="mots-table__wrapper">
                    <table class="mots-table__table">
                        <thead class="mots-table__header"><tr class="mots-table__header-row">
                            <th scope="col" class="mots-table__header-cell"><?= anchor($lienTriId, 'ID') ?></th>
                            <th scope="col" class="mots-table__header-cell"><?= anchor($lienTriQuestion, 'Question') ?></th>
                            <th scope="col" class="mots-table__header-cell">ID menace</th>
                        </tr></thead>
                        <tbody class="mots-table__body">
                        <?php foreach ($questions as $question): ?>
                            <tr class="mots-table__body-row">
                                <td class="mots-table__cell mots-table__cell--id"><?= esc($question['id_ques']) ?></td>
                                <td class="mots-table__cell"><?= anchor('floribondant/owasp/quiz/question/' . $question['id_ques'], esc($question['libelle_ques']), ['class' => 'mots-table__link']) ?></td>
                                <td class="mots-table__cell"><?= esc($question['menace_id']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($questions === []): ?>
                    <div class="signalement-empty"><div class="signalement-empty__text">Aucune question disponible.</div></div>
                <?php endif; ?>
                <?php if (! empty($pager)): ?>
                    <div class="pagination"><?= $pager->links('default', 'quiz') ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <aside id="admin__pannel-child-3" class="admin__pannel-child">
        <div class="mots-table">
            <div class="mots-table__wrapper">
                <table class="mots-table__table">
                    <thead class="mots-table__header"><tr class="mots-table__header-row">
                        <th scope="col" class="mots-table__header-cell">ID</th>
                        <th scope="col" class="mots-table__header-cell">Réponse</th>
                    </tr></thead>
                    <tbody class="mots-table__body">
                    <?php foreach ($propositions as $proposition): ?>
                        <tr class="mots-table__body-row">
                            <td class="mots-table__cell mots-table__cell--id"><?= esc($proposition['id_prop']) ?></td>
                            <td class="mots-table__cell"><?= anchor('floribondant/owasp/quiz/reponse/' . $proposition['id_prop'], esc($proposition['libelle_prop']), ['class' => 'mots-table__link']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($propositions === []): ?>
                <div class="signalement-empty"><div class="signalement-empty__text">Aucune réponse disponible.</div></div>
            <?php endif; ?>
            <?php if (! empty($pagerReponses)): ?>
                <div class="pagination"><?= $pagerReponses->links('reponses', 'quiz') ?></div>
            <?php endif; ?>
        </div>
    </aside>
</main>
