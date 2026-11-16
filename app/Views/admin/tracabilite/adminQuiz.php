<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Liste des quiz (les onglets sont affichés par onglets.php, appelé dans le contrôleur)
 *
 * Données préparées par le contrôleur (AdminQuizController::index) :
 * - $quizList     : quiz de la page affichée (tableaux : id, titre, theme_nom, nb_questions, actif)
 * - $totalQuiz    : nombre total de quiz
 * - $themeSelect  : thème affiché (-1 = tous les thèmes)
 * - $filtreThemes : liens du filtre ['nom', 'url', 'actif']
 * - $colonnes     : en-têtes du tableau ['texte', 'url', 'infobulle', 'aria_sort']
 * - $messageTri   : phrase qui rappelle le tri en cours, ou null
 * - $urlSansTri   : adresse pour revenir à l'ordre par défaut
 * - $pages        : numéros de page ['numero', 'url', 'actuelle']
 *
 * Styles : sio/adminSio.css (sections « Leçons » et « Questions »), tableMots.css, notifications.css
 *
 * @author Arthur
 */
// ul() attend un tableau de textes : on y met un lien anchor() par thème et par page
$liensThemes = [];
foreach ($filtreThemes as $lien) {
    $attributs = ['class' => 'sio-admin__theme-lien'];
    if ($lien['actif']) {
        $attributs['aria-current'] = 'true'; // thème affiché, mis en évidence par le CSS
    }
    $liensThemes[] = anchor($lien['url'], esc($lien['nom']), $attributs);
}

$liensPages = [];
foreach ($pages as $p) {
    if ($p['actuelle']) {
        $liensPages[] = '<span aria-current="page">' . $p['numero'] . '</span>'; // page affichée : pas de lien
    } else {
        $liensPages[] = anchor($p['url'], (string) $p['numero']);
    }
}
?>

<div class="sio-admin__section">

    <!-- Message affiché une seule fois après un ajout, une modification ou une suppression -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="notification notification--success" role="status">
            <h3><?= esc(session()->getFlashdata('success')) ?></h3>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification notification--error" role="alert">
            <h3><?= esc(session()->getFlashdata('error')) ?></h3>
        </div>
    <?php endif; ?>

    <!-- Nombre de quiz, filtre par thème et bouton d'ajout sur une seule ligne -->
    <div class="sio-admin__barre-liste">
        <h2 class="sio-admin__titre sio-admin__titre--section">
            <?php if ($totalQuiz === 0): ?>
                Aucun quiz
            <?php else: ?>
                <?= $totalQuiz ?> quiz
            <?php endif; ?>
        </h2>

        <nav class="sio-admin__themes" aria-label="Filtrer les quiz par thème">
            <span class="sio-admin__themes-titre">Thème</span>
            <?= ul($liensThemes, ['class' => 'sio-admin__themes-liste']) ?>
        </nav>

        <?= anchor('floribondant/theme/quiz/ajouter', 'Ajouter un quiz', ['class' => 'glossary-form__trigger']) ?>
    </div>

    <?php if ($totalQuiz === 0): ?>

        <!-- Aucun quiz à afficher -->
        <div class="sio-admin__vide">
            <?php if ($themeSelect > 0): ?>
                <p>Ce thème n'a encore aucun quiz.</p>
            <?php else: ?>
                <p>Aucun quiz n'a encore été créé.</p>
            <?php endif; ?>
            <?= anchor('floribondant/theme/quiz/ajouter', 'Ajouter un quiz', ['class' => 'sio-admin__btn-secondaire']) ?>
        </div>

    <?php else: ?>

        <!-- Rappel du tri en cours -->
        <?php if ($messageTri !== null): ?>
            <p class="sio-admin__etat">
                <?= $messageTri ?>
                <?= anchor($urlSansTri, 'Revenir à l\'ordre par défaut') ?>
            </p>
        <?php endif; ?>

        <!-- Tableau des quiz (classes du tableau des mots) -->
        <div class="mots-table">
            <div class="mots-table__wrapper">
                <table class="mots-table__table">
                    <thead class="mots-table__header">
                    <tr class="mots-table__header-row">
                        <!-- En-têtes triables : un clic trie (croissant, décroissant, puis ordre par défaut) -->
                        <?php foreach ($colonnes as $colonne): ?>
                            <th scope="col" class="mots-table__header-cell mots-table__header-cell--tri" aria-sort="<?= $colonne['aria_sort'] ?>">
                                <?= anchor($colonne['url'], $colonne['texte'], ['class' => 'mots-table__tri', 'title' => $colonne['infobulle']]) ?>
                            </th>
                        <?php endforeach; ?>
                        <th scope="col" class="mots-table__header-cell">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="mots-table__body">
                    <?php foreach ($quizList as $quiz): ?>
                        <?php
                        $urlModifier = 'floribondant/theme/quiz/modifier/' . $quiz['id'];
                        $nbQuestions = (int) $quiz['nb_questions'];

                        // Si la colonne actif n'existe pas, le quiz est considéré comme actif
                        $estActif = (int) ($quiz['actif'] ?? 1) === 1;
                        ?>
                        <tr class="mots-table__body-row<?= $estActif ? '' : ' sio-admin__ligne--inactive' ?>">
                            <td class="mots-table__cell">
                                <?= anchor($urlModifier, esc($quiz['titre']), ['class' => 'mots-table__link']) ?>
                            </td>
                            <td class="mots-table__cell">
                                <span class="sio-admin__badge"><?= esc($quiz['theme_nom'] ?? '--') ?></span>
                            </td>
                            <td class="mots-table__cell">
                                <?php if ($nbQuestions === 0): ?>
                                    <!-- Quiz vide : rien à proposer aux élèves -->
                                    <span class="sio-admin__badge sio-admin__badge--faux">Aucune question</span>
                                <?php elseif ($nbQuestions === 1): ?>
                                    1 question
                                <?php else: ?>
                                    <?= $nbQuestions ?> questions
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <?php if ($estActif): ?>
                                    Oui
                                <?php else: ?>
                                    <span class="mots-table__author--na">Non</span>
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <div class="sio-admin__ligne-actions">
                                    <?= anchor($urlModifier, 'Modifier', ['class' => 'sio-admin__btn-secondaire sio-admin__btn--petit']) ?>

                                    <!-- La confirmation rappelle le titre du quiz avant de supprimer -->
                                    <?= form_open('floribondant/theme/quiz/supprimer/' . $quiz['id'], [
                                        'class'    => 'mots-table__delete-form',
                                        'onsubmit' => "return confirm('Supprimer le quiz « " . esc($quiz['titre'], 'js') . " » ? Ses questions sont conservées. Cette action est définitive.');",
                                    ]) ?>
                                    <?= form_hidden('id_quiz', (string) $quiz['id']) ?>
                                    <?= form_button(['type' => 'submit', 'content' => 'Supprimer', 'class' => 'sio-admin__btn-supprimer']) ?>
                                    <?= form_close() ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (style .pagination de tableMots.css), affichée s'il y a plusieurs pages -->
            <?php if (count($liensPages) > 1): ?>
                <nav class="pagination" aria-label="Pages de la liste des quiz">
                    <?= ul($liensPages) ?>
                </nav>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

<!-- Confort : retour à la même position de la page après un rechargement -->
<?= script_tag(['src' => 'js/sio-admin-theme.js?v=' . filemtime(FCPATH . 'js/sio-admin-theme.js'), 'defer' => null]) ?>
