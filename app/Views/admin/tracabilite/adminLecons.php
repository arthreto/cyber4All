<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Liste des leçons (les onglets sont affichés par onglets.php, appelé dans le contrôleur)
 *
 * Données préparées par le contrôleur (AdminLeconController::index) :
 * - $leconList    : leçons de la page affichée (avec nom_theme, user_auteur, user_modificateur)
 * - $totalLecons  : nombre total de leçons
 * - $themeSelect  : thème affiché (-1 = tous les thèmes)
 * - $filtreThemes : liens du filtre ['nom', 'url', 'actif']
 * - $colonnes     : en-têtes du tableau ['texte', 'url', 'infobulle', 'aria_sort']
 * - $messageTri   : phrase qui rappelle le tri en cours, ou null
 * - $urlSansTri   : adresse pour revenir à l'ordre par défaut
 * - $pages        : numéros de page ['numero', 'url', 'actuelle']
 *
 * Styles : sio/adminSio.css (section « Leçons »), tableMots.css, notifications.css
 *
 * @author Raphael Bierlaire
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

    <!-- Nombre de leçons, filtre par thème et bouton d'ajout sur une seule ligne -->
    <div class="sio-admin__barre-liste">
        <h2 class="sio-admin__titre sio-admin__titre--section">
            <?php if ($totalLecons === 0): ?>
                Aucune leçon
            <?php elseif ($totalLecons === 1): ?>
                1 leçon
            <?php else: ?>
                <?= $totalLecons ?> leçons
            <?php endif; ?>
        </h2>

        <nav class="sio-admin__themes" aria-label="Filtrer les leçons par thème">
            <span class="sio-admin__themes-titre">Thème</span>
            <?= ul($liensThemes, ['class' => 'sio-admin__themes-liste']) ?>
        </nav>

        <?= anchor('floribondant/theme/lecons/ajouter', 'Ajouter une leçon', ['class' => 'glossary-form__trigger']) ?>
    </div>

    <?php if ($totalLecons === 0): ?>

        <!-- Aucune leçon à afficher -->
        <div class="sio-admin__vide">
            <?php if ($themeSelect > 0): ?>
                <p>Ce thème ne contient pas encore de leçon.</p>
            <?php else: ?>
                <p>Aucune leçon n'a encore été créée.</p>
            <?php endif; ?>
            <?= anchor('floribondant/theme/lecons/ajouter', 'Ajouter une leçon', ['class' => 'sio-admin__btn-secondaire']) ?>
        </div>

    <?php else: ?>

        <!-- Rappel du tri en cours -->
        <?php if ($messageTri !== null): ?>
            <p class="sio-admin__etat">
                <?= $messageTri ?>
                <?= anchor($urlSansTri, 'Revenir à l\'ordre par défaut') ?>
            </p>
        <?php endif; ?>

        <!-- Tableau des leçons (classes du tableau des mots) -->
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
                    <?php foreach ($leconList as $lecon): ?>
                        <?php
                        $dateAjout = strtotime((string) $lecon->date_ajout);
                        $dateModif = strtotime((string) $lecon->date_modif);

                        // À la création, la date de modification est identique à la date d'ajout
                        $estModifiee = $dateModif !== false && $dateModif !== $dateAjout;

                        // Leçon supprimée (désactivée) : grisée, à la fin de la liste
                        $estActive = (int) ($lecon->actif ?? 1) === 1;

                        $classesLigne = 'mots-table__body-row';
                        if (! $estActive) {
                            $classesLigne .= ' sio-admin__ligne--inactive';
                        }
                        if ((int) $lecon->id === $idNouveau) {
                            $classesLigne .= ' sio-admin__ligne--nouvelle'; // vient d'être ajoutée ou modifiée
                        }
                        ?>
                        <tr class="<?= $classesLigne ?>">
                            <td class="mots-table__cell">
                                <?= anchor('floribondant/theme/lecons/modifier/' . $lecon->id, esc($lecon->titre), ['class' => 'mots-table__link mots-table__titre']) ?>
                                <?php if (! $estActive): ?>
                                    <span class="sio-admin__badge sio-admin__badge--faux">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <span class="sio-admin__badge"><?= esc($lecon->nom_theme) ?></span>
                            </td>
                            <td class="mots-table__cell">
                                <?php if (isset($lecon->user_auteur->pseudo)): ?>
                                    <span class="mots-table__author"><?= esc($lecon->user_auteur->pseudo) ?></span>
                                <?php else: ?>
                                    <span class="mots-table__author--na">Inconnu</span>
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <time class="mots-table__date" datetime="<?= date('Y-m-d', $dateAjout) ?>"><?= date('d/m/Y', $dateAjout) ?></time>
                            </td>
                            <td class="mots-table__cell">
                                <?php if ($estModifiee): ?>
                                    <time class="mots-table__date" datetime="<?= date('Y-m-d', $dateModif) ?>"><?= date('d/m/Y', $dateModif) ?></time>
                                    <?php if (isset($lecon->user_modificateur->pseudo)): ?>
                                        <span class="mots-table__precision">par <?= esc($lecon->user_modificateur->pseudo) ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="mots-table__author--na">Jamais modifiée</span>
                                <?php endif; ?>
                            </td>
                            <td class="mots-table__cell">
                                <div class="sio-admin__ligne-actions">
                                    <?= anchor('floribondant/theme/lecons/modifier/' . $lecon->id, 'Modifier', ['class' => 'sio-admin__btn-secondaire sio-admin__btn--petit']) ?>

                                    <!-- Supprimer = désactiver la leçon -->
                                    <?php if ($estActive): ?>
                                    <?= form_open('floribondant/theme/lecons/supprimer/' . $lecon->id, [
                                        'class'    => 'mots-table__delete-form',
                                        'onsubmit' => "return confirm('Supprimer la leçon « " . esc($lecon->titre, 'js') . " » ? Elle ne sera plus affichée sur le site. Vous pourrez la réactiver depuis cette liste.');",
                                    ]) ?>
                                    <?= form_button(['type' => 'submit', 'content' => 'Supprimer', 'class' => 'sio-admin__btn-supprimer']) ?>
                                    <?= form_close() ?>
                                    <?php else: ?>
                                    <!-- Réactiver : bouton grisé (raison en infobulle) si la leçon n'est pas valide -->
                                    <?= view('admin/tracabilite/boutonReactiver', [
                                        'url'    => 'floribondant/theme/lecons/reactiver/' . $lecon->id,
                                        'raison' => $raisonsReactivation[(int) $lecon->id] ?? null,
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
                <nav class="pagination" aria-label="Pages de la liste des leçons">
                    <?= ul($liensPages) ?>
                </nav>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>


