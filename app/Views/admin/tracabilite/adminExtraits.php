<?php
/**
 * ADMINISTRATION - Espace Traçabilité
 * Liste des extraits (les onglets sont affichés par onglets.php, appelé dans le contrôleur)
 *
 * Données préparées par le contrôleur (AdminExtraitController::index) :
 * - $extraitList   : extraits de la page affichée (tableaux : id, titre, type, debut, nb_questions, actif)
 * - $raisonsReactivation : [id d'un extrait inactif => raison qui empêche la réactivation, null si réactivable]
 * - $idNouveau     : id de l'extrait qui vient d'être ajouté ou modifié (0 si aucun)
 * - $totalExtraits : nombre total d'extraits
 * - $typeSelect    : type affiché (clé de ExtraitModel::TYPES), null pour tous
 * - $typesExtrait  : types possibles [clé => libellé]
 * - $filtreTypes   : liens du filtre ['nom', 'url', 'actif']
 * - $colonnes      : en-têtes du tableau ['texte', 'url', 'infobulle', 'aria_sort']
 * - $messageTri    : phrase qui rappelle le tri en cours, ou null
 * - $urlSansTri    : adresse pour revenir à l'ordre par défaut
 * - $pages         : numéros de page ['numero', 'url', 'actuelle']
 *
 * Styles : sio/adminSio.css, tableMots.css, notifications.css
 *
 * @author Arthur
 */
// ul() attend un tableau de textes : on y met un lien anchor() par type et par page
$liensTypes = [];
foreach ($filtreTypes as $lien) {
    $attributs = ['class' => 'sio-admin__theme-lien'];
    if ($lien['actif']) {
        $attributs['aria-current'] = 'true'; // type affiché, mis en évidence par le CSS
    }
    $liensTypes[] = anchor($lien['url'], esc($lien['nom']), $attributs);
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

    <!-- Nombre d'extraits, filtre par type et bouton d'ajout sur une seule ligne -->
    <div class="sio-admin__barre-liste">
        <h2 class="sio-admin__titre sio-admin__titre--section">
            <?php if ($totalExtraits === 0): ?>
                Aucun extrait
            <?php else: ?>
                <?= $totalExtraits ?> extrait<?= $totalExtraits > 1 ? 's' : '' ?>
            <?php endif; ?>
        </h2>

        <nav class="sio-admin__themes" aria-label="Filtrer les extraits par type">
            <span class="sio-admin__themes-titre">Type</span>
            <?= ul($liensTypes, ['class' => 'sio-admin__themes-liste']) ?>
        </nav>

        <?= anchor('floribondant/theme/extrait/ajouter', 'Ajouter un extrait', ['class' => 'glossary-form__trigger']) ?>
    </div>

    <?php if ($totalExtraits === 0): ?>

        <!-- Aucun extrait à afficher -->
        <div class="sio-admin__vide">
            <?php if ($typeSelect !== null): ?>
                <p>Aucun extrait de type « <?= esc($typesExtrait[$typeSelect]) ?> » pour l'instant.</p>
            <?php else: ?>
                <p>Aucun extrait n'a encore été créé.</p>
            <?php endif; ?>
            <?= anchor('floribondant/theme/extrait/ajouter', 'Ajouter un extrait', ['class' => 'sio-admin__btn-secondaire']) ?>
        </div>

    <?php else: ?>

        <!-- Rappel du tri en cours -->
        <?php if ($messageTri !== null): ?>
            <p class="sio-admin__etat">
                <?= $messageTri ?>
                <?= anchor($urlSansTri, 'Revenir à l\'ordre par défaut') ?>
            </p>
        <?php endif; ?>

        <!-- Tableau des extraits (classes du tableau des mots) -->
        <div class="mots-table">
            <div class="mots-table__wrapper">
                <table class="mots-table__table">
                    <thead class="mots-table__header">
                    <tr class="mots-table__header-row">
                        <!-- En-têtes : un clic trie (croissant, décroissant, puis ordre par défaut) -->
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
                    <?php foreach ($extraitList as $extrait): ?>
                        <?php
                        $urlModifier = 'floribondant/theme/extrait/modifier/' . $extrait['id'];
                        $nbQuestions = (int) $extrait['nb_questions'];

                        // Extrait supprimé (désactivé) : grisé, à la fin de la liste
                        $estActif = (int) ($extrait['actif'] ?? 1) === 1;

                        $classesLigne = 'mots-table__body-row';
                        if (! $estActif) {
                            $classesLigne .= ' sio-admin__ligne--inactive';
                        }
                        if ((int) $extrait['id'] === $idNouveau) {
                            $classesLigne .= ' sio-admin__ligne--nouvelle'; // vient d'être ajouté ou modifié
                        }

                        // Message de confirmation : les questions qui l'utilisent seront désactivées aussi.
                        // esc(..., 'js') sur tout le message : les apostrophes ne cassent pas le confirm('...')
                        $confirmation = 'Supprimer l\'extrait « ' . $extrait['titre'] . ' » ?';
                        if ($nbQuestions > 0) {
                            $confirmation .= ' Les ' . $nbQuestions . ' question(s) qui l\'utilisent seront désactivées, et les quiz qui n\'ont plus assez de questions aussi.';
                        }
                        $confirmation .= ' Vous pourrez le réactiver depuis cette liste.';
                        $confirmation = esc($confirmation, 'js');
                        ?>
                        <tr class="<?= $classesLigne ?>">
                            <td class="mots-table__cell">
                                <?= anchor($urlModifier, esc($extrait['titre']), ['class' => 'mots-table__link']) ?>
                                <?php if (! $estActif): ?>
                                    <span class="sio-admin__badge sio-admin__badge--faux">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <span class="sio-admin__badge"><?= esc($typesExtrait[$extrait['type']] ?? $extrait['type']) ?></span>
                            </td>
                            <td class="mots-table__cell">
                                <!-- Début du contenu ; contenu complet au survol (title) -->
                                <code class="sio-admin__extrait-debut" title="<?= esc((string) $extrait['contenu'], 'attr') ?>"><?= esc($extrait['debut']) ?></code>
                            </td>
                            <td class="mots-table__cell">
                                <?php if ($nbQuestions === 0): ?>
                                    <span class="mots-table__author--na">Aucune</span>
                                <?php elseif ($nbQuestions === 1): ?>
                                    1 question
                                <?php else: ?>
                                    <?= $nbQuestions ?> questions
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <div class="sio-admin__ligne-actions">
                                    <?= anchor($urlModifier, 'Modifier', ['class' => 'sio-admin__btn-secondaire sio-admin__btn--petit']) ?>

                                    <!-- Supprimer = désactiver l'extrait -->
                                    <?php if ($estActif): ?>
                                    <?= form_open('floribondant/theme/extrait/supprimer/' . $extrait['id'], [
                                        'class'    => 'mots-table__delete-form',
                                        'onsubmit' => 'return confirm(\'' . $confirmation . '\');',
                                    ]) ?>
                                    <?= form_button(['type' => 'submit', 'content' => 'Supprimer', 'class' => 'sio-admin__btn-supprimer']) ?>
                                    <?= form_close() ?>
                                    <?php else: ?>
                                    <!-- Réactiver : bouton grisé (raison en infobulle) si l'extrait n'est pas valide -->
                                    <?= view('admin/tracabilite/boutonReactiver', [
                                        'url'    => 'floribondant/theme/extrait/reactiver/' . $extrait['id'],
                                        'raison' => $raisonsReactivation[(int) $extrait['id']] ?? null,
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
                <nav class="pagination" aria-label="Pages de la liste des extraits">
                    <?= ul($liensPages) ?>
                </nav>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>


