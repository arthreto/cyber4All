<?php
// Récupère l'ordre actuel depuis l'URL, ex: ida, idd, moa, mod, ...
$ordre = $_GET['ordre'] ?? 'ida';

// Fonction qui switch le sens pour une colonne donnée
function getSortLink(string $col, string $ordreActuel): string
{
    $prefix = substr($col, 0, 2); // 2 premières lettres pour le champ
    $currentDir = (substr($ordreActuel, 0, 2) === $prefix) ? substr($ordreActuel, 2, 1) : 'd';
    $newDir = ($currentDir === 'a') ? 'd' : 'a';
    return "?ordre={$prefix}{$newDir}";
}

?>
<div class="container">
    <p><?= $indication ?? "" ?></p>

    <div class="mots-table">
        <?php if (empty ($motsContributeur)): ?>
            <div class="signalement-empty">
                <div class="signalement-empty__icon">📭</div>
                <div class="signalement-empty__text">Aucun mot suggéré à ajouter au glossaire</div>
            </div>

        <?php else: ?>
            <div class="mots-table__wrapper">
                <table class="mots-table__table">
                    <thead class="mots-table__header">
                    <tr class="mots-table__header-row">
                        <th scope="col" class="mots-table__header-cell"><a
                                    href="<?= getSortLink('id', $ordre) ?>">ID</a></th>
                        <?php if (empty($actionAdmin)): ?>
                            <th scope="col" class="mots-table__header-cell"><a href="<?= getSortLink('pu', $ordre) ?>">Publié</a>
                            </th>
                        <?php endif; ?>
                        <th scope="col" class="mots-table__header-cell"><a
                                    href="<?= getSortLink('mo', $ordre) ?>">Mot</a></th>
                        <th scope="col" class="mots-table__header-cell"><a href="<?= getSortLink('dp', $ordre) ?>">Date
                                Publication</a></th>
                        <th scope="col" class="mots-table__header-cell"><a href="<?= getSortLink('dm', $ordre) ?>">Dernière
                                Modification</a></th>
                        <th scope="col" class="mots-table__header-cell">Status</th>
                    </tr>
                    </thead>
                    <tbody class="mots-table__body">
                    <?php foreach ($motsContributeur as $mot): ?>
                        <tr class="mots-table__body-row">
                            <td class="mots-table__cell mots-table__cell--id"><?= $mot->id ?></td>
                            <?php if (empty($actionAdmin)): ?>
                                <td class="mots-table__cell">
                                    <?php
                                    switch ($mot->statut):
                                        case 0:
                                            $statusClass = 'unpublished';
                                            $statutTitle = 'Est dépublié';
                                            $statusValue = 'N';
                                            break;
                                        case 1:
                                            $statusClass = 'published';
                                            $statutTitle = 'Est publié';
                                            $statusValue = 'Y';
                                            break;
                                        case 2:
                                            $statusClass = 'unpublished';
                                            $statutTitle = 'A été refusé';
                                            $statusValue = 'X';
                                            break;
                                        default:
                                            $statusClass = 'disabled';
                                            $statutTitle = 'En attente de validation';
                                            $statusValue = 'X';
                                            break;
                                    endswitch; ?>
                                    <div class="mots-table__status-form">
                                        <div class="mots-table__status
                                            mots-table__status--<?= $statusClass ?>"
                                             title="<?= $statutTitle ?>">
                                            <?= $statusValue ?>
                                        </div>
                                    </div>
                                </td>
                            <?php endif; ?>
                            <td class="mots-table__cell">
                                <!--    Permettra d'afficher le mot en détail
                                        <a href="floribondant/mot/-->
                                <?php if ($mot->statut !== 2) :?><!--" class="mots-table__link">-->
                                    <a href="<?= base_url(empty($actionAdmin) ?
                                            'communautaire/mots/update/' . $mot->id
                                            : $actionAdmin . $mot->id) ?>" class="mots-table__link">
                                        <?= esc($mot->mot) ?>
                                    </a>
                                <?php else :?>
                                <p>
                                    <?= esc($mot->mot) ?>
                                </p>
                                <?php endif; ?>
                            </td>

                            <td class="mots-table__cell mots-table__date"><?= $mot->date_ajout ?></td>
                            <td class="mots-table__cell mots-table__date"><?= $mot->date_modif ?? '--' ?></td>
                            <td class="mots-table__cell">
                                <?php
                                echo form_hidden('idMotCle', (string)$mot->id);

                                switch ($mot->statut):
                                    case 2:
                                        echo '<i class="fa-solid fa-xmark" title="refuser" style="color: #d40c0c;"></i>';
                                        break;
                                    case 3:
                                        echo '<i class="fa-solid fa-hourglass-half" title="en attente de validation" style="color: #d98817;"></i>';
                                        break;
                                    default:
                                        echo '<i class="fa-solid fa-check" title="valider" style="color: #44d011;"></i>';
                                        break;
                                endswitch;

                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        <?php if (!empty($pager)): ?>
            <!-- Pagination -->
            <div class="pagination">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>

            <!-- Modal de confirmation -->
            <div class="delete-modal" id="deleteModal">
                <div class="delete-modal__content">
                    <div class="delete-modal__icon">⚠️</div>
                    <h2 class="delete-modal__title">Confirmer la suppression</h2>
                    <p class="delete-modal__message">
                        Êtes-vous sûr de vouloir supprimer le mot <span class="delete-modal__word"
                                                                        id="wordToDelete"></span> ?
                        <br><br>
                        Cette action est irréversible.
                    </p>
                    <div class="delete-modal__actions">
                        <button type="button" class="delete-modal__btn delete-modal__btn--cancel"
                                onclick="cancelDelete()">
                            Annuler
                        </button>
                        <button type="button" class="delete-modal__btn delete-modal__btn--confirm"
                                onclick="proceedDelete()">
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>

            <script>
                let formToSubmit = null;

                function confirmDelete(event, motName) {
                    event.preventDefault();

                    // Stocker le formulaire à soumettre
                    formToSubmit = event.target;

                    // Afficher le nom du mot dans le modal
                    document.getElementById('wordToDelete').textContent = motName;

                    // Afficher le modal
                    document.getElementById('deleteModal').classList.add('delete-modal--active');

                    return false;
                }

                function cancelDelete() {
                    // Fermer le modal
                    document.getElementById('deleteModal').classList.remove('delete-modal--active');
                    formToSubmit = null;
                }

                function proceedDelete() {
                    // Soumettre le formulaire
                    if (formToSubmit) {
                        formToSubmit.submit();
                    }
                }

                // Fermer le modal en cliquant en dehors
                document.getElementById('deleteModal').addEventListener('click', function (event) {
                    if (event.target === this) {
                        cancelDelete();
                    }
                });

                // Fermer le modal avec la touche Échap
                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        cancelDelete();
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</div>
