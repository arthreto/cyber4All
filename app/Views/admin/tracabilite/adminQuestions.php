<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Liste des questions (les onglets sont affichés par onglets.php, appelé dans le contrôleur)
 *
 * Données préparées par le contrôleur (AdminQuestionController::index) :
 * - $questionList   : questions de la page affichée (tableaux : id, libelle, image, bonne_reponse,
 *                     actif, date_ajout, date_modif, quiz)
 * - $totalQuestions : nombre total de questions
 * - $themeSelect    : thème affiché (-1 = tous les thèmes)
 * - $filtreThemes   : liens du filtre ['nom', 'url', 'actif']
 * - $colonnes       : en-têtes du tableau ['texte', 'url' (null si non triable), 'infobulle', 'aria_sort']
 * - $messageTri     : phrase qui rappelle le tri en cours, ou null
 * - $urlSansTri     : adresse pour revenir à l'ordre par défaut
 * - $pages          : numéros de page ['numero', 'url', 'actuelle']
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

    <!-- Nombre de questions, filtre par thème et bouton d'ajout sur une seule ligne -->
    <div class="sio-admin__barre-liste">
        <h2 class="sio-admin__titre sio-admin__titre--section">
            <?php if ($totalQuestions === 0): ?>
                Aucune question
            <?php elseif ($totalQuestions === 1): ?>
                1 question
            <?php else: ?>
                <?= $totalQuestions ?> questions
            <?php endif; ?>
        </h2>

        <nav class="sio-admin__themes" aria-label="Filtrer les questions par thème">
            <span class="sio-admin__themes-titre">Thème</span>
            <?= ul($liensThemes, ['class' => 'sio-admin__themes-liste']) ?>
        </nav>

        <?= anchor('floribondant/theme/questions/ajouter', 'Ajouter une question', ['class' => 'glossary-form__trigger']) ?>
    </div>

    <?php if ($totalQuestions === 0): ?>

        <!-- Aucune question à afficher -->
        <div class="sio-admin__vide">
            <?php if ($themeSelect > 0): ?>
                <p>Aucun quiz de ce thème ne contient encore de question.</p>
            <?php else: ?>
                <p>Aucune question n'a encore été créée.</p>
            <?php endif; ?>
            <?= anchor('floribondant/theme/questions/ajouter', 'Ajouter une question', ['class' => 'sio-admin__btn-secondaire']) ?>
        </div>

    <?php else: ?>

        <!-- Rappel du tri en cours -->
        <?php if ($messageTri !== null): ?>
            <p class="sio-admin__etat">
                <?= $messageTri ?>
                <?= anchor($urlSansTri, 'Revenir à l\'ordre par défaut') ?>
            </p>
        <?php endif; ?>

        <!-- Tableau des questions (classes du tableau des mots) -->
        <div class="mots-table">
            <div class="mots-table__wrapper">
                <table class="mots-table__table">
                    <thead class="mots-table__header">
                    <tr class="mots-table__header-row">
                        <!-- En-têtes : un clic trie (croissant, décroissant, puis ordre par défaut) ; la colonne Quiz n'est pas triable -->
                        <?php foreach ($colonnes as $colonne): ?>
                            <?php if ($colonne['url'] === null): ?>
                                <th scope="col" class="mots-table__header-cell"><?= $colonne['texte'] ?></th>
                            <?php else: ?>
                                <th scope="col" class="mots-table__header-cell mots-table__header-cell--tri" aria-sort="<?= $colonne['aria_sort'] ?>">
                                    <?= anchor($colonne['url'], $colonne['texte'], ['class' => 'mots-table__tri', 'title' => $colonne['infobulle']]) ?>
                                </th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <th scope="col" class="mots-table__header-cell">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="mots-table__body">
                    <?php foreach ($questionList as $question): ?>
                        <?php
                        $estActive = (string) $question['actif'] === '1';

                        // Date affichée : dernière modification, sinon date d'ajout
                        if (! empty($question['date_modif'])) {
                            $date = strtotime($question['date_modif']);
                        } else {
                            $date = strtotime((string) $question['date_ajout']);
                        }

                        // Début de l'énoncé (60 caractères), pour la confirmation de suppression
                        $debutEnonce = mb_strimwidth((string) $question['libelle'], 0, 60, '…');
                        ?>
                        <?php
                        $classesLigne = 'mots-table__body-row';
                        if (! $estActive) {
                            $classesLigne .= ' sio-admin__ligne--inactive';
                        }
                        if ((int) $question['id'] === $idNouveau) {
                            $classesLigne .= ' sio-admin__ligne--nouvelle'; // vient d'être ajoutée ou modifiée
                        }
                        ?>
                        <tr class="<?= $classesLigne ?>">
                            <td class="mots-table__cell">
                                <div class="sio-admin__enonce">
                                    <?php if (! empty($question['image'])): ?>
                                        <?= img([
                                            'src'     => esc('images/questions/' . $question['image'], 'attr'),
                                            'alt'     => 'Illustration de la question',
                                            'class'   => 'sio-admin__miniature',
                                            'loading' => 'lazy',
                                        ]) ?>
                                    <?php endif; ?>
                                    <?= anchor('floribondant/theme/questions/modifier/' . $question['id'], esc($question['libelle']), ['class' => 'mots-table__link']) ?>
                                </div>
                            </td>
                            <td class="mots-table__cell">
                                <?php if (empty($question['quiz'])): ?>
                                    <span class="mots-table__author--na">Aucun quiz</span>
                                <?php else: ?>
                                    <ul class="sio-admin__quiz-liste">
                                        <?php foreach ($question['quiz'] as $quiz): ?>
                                            <li><?= esc($quiz['titre']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <?php if ((string) $question['bonne_reponse'] === '1'): ?>
                                    <span class="sio-admin__badge sio-admin__badge--vrai">Vrai</span>
                                <?php else: ?>
                                    <span class="sio-admin__badge sio-admin__badge--faux">Faux</span>
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <?php if ($estActive): ?>
                                    Oui
                                <?php else: ?>
                                    <span class="mots-table__author--na">Non</span>
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <?php if ($date !== false): ?>
                                    <time class="mots-table__date" datetime="<?= date('Y-m-d', $date) ?>"><?= date('d/m/Y', $date) ?></time>
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <div class="sio-admin__ligne-actions">
                                    <?= anchor('floribondant/theme/questions/modifier/' . $question['id'], 'Modifier', ['class' => 'sio-admin__btn-secondaire sio-admin__btn--petit']) ?>

                                    <!-- Supprimer = désactiver la question -->
                                    <?php if ($estActive): ?>
                                        <?= form_open('floribondant/theme/questions/supprimer/' . $question['id'], [
                                            'class'    => 'mots-table__delete-form',
                                            'onsubmit' => "return confirm('Supprimer la question « " . esc($debutEnonce, 'js') . " » ? Elle ne sera plus proposée dans les quiz. Vous pourrez la réactiver depuis cette liste.');",
                                        ]) ?>
                                        <?= form_hidden('id_question', (string) $question['id']) ?>
                                        <?= form_button(['type' => 'submit', 'content' => 'Supprimer', 'class' => 'sio-admin__btn-supprimer']) ?>
                                        <?= form_close() ?>
                                    <?php else: ?>
                                        <!-- Réactiver : bouton grisé (raison en infobulle) si la question n'est pas valide -->
                                        <?= view('admin/tracabilite/boutonReactiver', [
                                            'url'    => 'floribondant/theme/questions/reactiver/' . $question['id'],
                                            'raison' => $raisonsReactivation[(int) $question['id']] ?? null,
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (style .pagination de tableMots.css), affichée s'il y a plusieurs pages -->
            <?php if (count($liensPages) > 1): ?>
                <nav class="pagination" aria-label="Pages de la liste des questions">
                    <?= ul($liensPages) ?>
                </nav>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

