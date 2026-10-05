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
        <?php if (empty($utilisateurs)): ?>
            <div class="signalement-empty">
                <div class="signalement-empty__icon">📭</div>
                <div class="signalement-empty__text">Aucun mot</div>
            </div>

        <?php else: ?>
            <div class="mots-table__wrapper">
                <table class="mots-table__table">
                    <thead class="mots-table__header">
                    <tr class="mots-table__header-row">
                        <th scope="col" class="mots-table__header-cell"><a
                                    href="<?= getSortLink('id', $ordre) ?>">ID</a></th>
                        <th scope="col" class="mots-table__header-cell"><a
                                    href="<?= getSortLink('ro', $ordre) ?>">Rôle</a></th>
                        <?php if (empty($actionAdmin)): ?>
                            <th scope="col" class="mots-table__header-cell"><a href="<?= getSortLink('ps', $ordre) ?>">Pseudo</a>
                            </th>
                        <?php endif; ?>
                        <th scope="col" class="mots-table__header-cell"><a
                                    href="<?= getSortLink('em', $ordre) ?>">Email</a></th>
                        <th scope="col" class="mots-table__header-cell"><a href="<?= getSortLink('dc', $ordre) ?>">Date
                                Création</a></th>
                        <th scope="col" class="mots-table__header-cell"><a href="<?= getSortLink('dm', $ordre) ?>">Dernière
                                Modification</a></th>
                        <?php if (empty($actionAdmin)): ?>
                            <th scope="col" class="mots-table__header-cell">Action</th>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody class="mots-table__body">
                    <?php foreach ($utilisateurs as $utilisateur): ?>
                        <tr class="mots-table__body-row">
                            <td class="mots-table__cell mots-table__cell--id"><?= $utilisateur->id ?></td>
                            <td class="mots-table__cell"><?= $utilisateur->role ?></td>
                            <td class="mots-table__cell">
                                <!--    Permettra d'afficher le mot en détail
                                        <a href="floribondant/mot/-->
                                <?php //= $utilisateur->id ?><!--" class="mots-table__link">-->
                                <a href="<?= base_url(empty($actionAdmin) ?
                                        'floribondant/utilisateurs/update/' . $utilisateur->id
                                        : $actionAdmin . $utilisateur->id) ?>" class="mots-table__link">
                                    <?= esc($utilisateur->pseudo) ?>
                                </a>
                            </td>
                            <td class="mots-table__cell"><?= $utilisateur->email ?? '--' ?></td>

                            <td class="mots-table__cell mots-table__date"><?= $utilisateur->date_creation ?></td>
                            <td class="mots-table__cell mots-table__date"><?= $utilisateur->date_modif ?? '--' ?></td>
                            <?php if (empty($actionAdmin)): ?>
                                <td class="mots-table__cell">
                                    <?php
                                    $attributs = [
                                            'method' => 'POST',
                                            'class' => 'mots-table__delete-form',
                                            'onsubmit' => 'return confirmDelete(event, "' . esc($utilisateur->pseudo, 'js') . '")'
                                    ];
                                    echo form_open(base_url('floribondant/utilisateurs/suppUtilisateur'), $attributs);
                                    echo form_hidden('idMotCle', (string)$utilisateur->id);

                                    $buttonAttributes = [
                                            'name' => 'supprimer',
                                            'class' => 'mots-table__delete-btn',
                                            'title' => 'Supprimer le mot'
                                    ];
                                    echo form_hidden('idMotCle', (string)$utilisateur->id);
                                    echo form_submit($buttonAttributes, '🗑️');
                                    echo form_close();
                                    ?>
                                </td>
                            <?php endif; ?>
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
                        Êtes-vous sûr de vouloir supprimer l'utilisateur <span class="delete-modal__word"
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
