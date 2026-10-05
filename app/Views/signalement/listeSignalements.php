<?php
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
    if($utilisateur->role === "admin"): ?>
        <div class="signalements-container">
            <?php if(!empty($signalements)): ?>
            <div class="signalement__header mots-table__header">
                <?= isset($totalSignalements) &&  $totalSignalements > 1 ?  $totalSignalements . ' Signalements' :
                        $totalSignalements . ' Signalement' ?>
            </div>
            <?php
            // $signalements doit être passé depuis le contrôleur
            // Exemple: $signalements = $model->getSignalements();

                foreach($signalements as $signalement):
                    ?>
                    <div class="signalement-item">
                        <div class="signalement-id">#<?= $signalement->id_signalement ?></div>
                        <a href="<?= base_url(empty($actionAdmin)?
                                'floribondant/mots/update/'.$signalement->id_mot_cle
                                : $actionAdmin . $signalement->id_mot_cle) ?>" class="signalement-mot">
                            Mot: <?= esc($signalement->mot) ?>
                        </a>
                        <div class="signalement-motif"><?= htmlspecialchars($signalement->motif) ?></div>
                        <?php if(empty($actionAdmin)): ?>
                            <div class="mots-table__cell">
                                <?php
                                $attributs = [
                                        'method' => 'POST',
                                        'class' => 'mots-table__delete-form',
                                        'onsubmit' => 'return confirmDeleteSignalement(event, "' . esc($signalement->id_signalement, 'js') . '")'
                                ];
                                echo form_open(base_url('floribondant/signalement/supprimer'), $attributs);

                                $buttonAttributes = [
                                        'name' => 'supprimer',
                                        'class' => 'mots-table__delete-btn',
                                        'title' => 'Supprimer le signalement'
                                ];
                                echo form_hidden('idSignalement', (string) $signalement->id_signalement);
                                echo form_submit($buttonAttributes, '🗑️');
                                echo form_close();
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    endforeach;
                    else: ?>
                    <div style="padding: 20px; text-align: center; color: #64748b; font-size: 10px;">
                        Aucun signalement
                    </div>
                    <?php endif; ?>
                </div>
        <!-- Modal de confirmation -->
        <div class="delete-modal" id="deleteModalSignalement">
            <div class="delete-modal__content">
                <div class="delete-modal__icon">⚠️</div>
                <h2 class="delete-modal__title">Confirmer la suppression</h2>
                <p class="delete-modal__message">
                    Êtes-vous sûr de vouloir supprimer le signalement <span class="delete-modal__word" id="signalementToDelete"></span> ?
                    <br><br>
                    Cette action est irréversible.
                </p>
                <div class="delete-modal__actions">
                    <button type="button" class="delete-modal__btn delete-modal__btn--cancel" onclick="cancelDeleteSignalement()">
                        Annuler
                    </button>
                    <button type="button" class="delete-modal__btn delete-modal__btn--confirm" onclick="proceedDeleteSignalement()">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
        <script>
            let formToSubmitSignalementSignalement = null;

            function confirmDeleteSignalement(event, motName) {
                event.preventDefault();

                // Stocker le formulaire à soumettre
                formToSubmitSignalement = event.target;

                // Afficher le nom du mot dans le modal
                document.getElementById('signalementToDelete').textContent = motName;

                // Afficher le modal
                document.getElementById('deleteModalSignalement').classList.add('delete-modal--active');

                return false;
            }

            function cancelDeleteSignalement() {
                // Fermer le modal
                document.getElementById('deleteModalSignalement').classList.remove('delete-modal--active');
                formToSubmitSignalement = null;
            }

            function proceedDeleteSignalement() {
                // Soumettre le formulaire
                if (formToSubmitSignalement) {
                    formToSubmitSignalement.submit();
                }
            }

            // Fermer le modal en cliquant en dehors
            document.getElementById('deleteModalSignalement').addEventListener('click', function(event) {
                if (event.target === this) {
                    cancelDeleteSignalement();
                }
            });

            // Fermer le modal avec la touche Échap
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    cancelDeleteSignalement();
                }
            });
        </script>
           <?php endif;
endif; ?>
<script>
    function handleSignalement(idMot) {
        window.location.href = 'mots/update/' + idMot;
    }
</script>