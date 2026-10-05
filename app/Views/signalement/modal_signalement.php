<?php use App\Models\VoteModel; use App\Entities\Vote; use App\Entities\MotCle;

$vote_model = new VoteModel();
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');?>

<!-- Modal de signalement (UNE SEULE à mettre en dehors de la boucle) -->
<dialog id="modal-signalement" class="modal-signalement">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Signaler ce contenu</h3>
            <button type="button" class="btn-close-modal" aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <?php
            if(isset($utilisateur) && ($utilisateur->role === "contributeur")):
                $dataForm = [
                        'method' => 'POST',
                        'class'=> 'form-signalement',
                        'id' => 'form-signalement'
                ];
                echo form_open('signaler', $dataForm);

                echo '<div class="form-group">';
                echo form_label('Motif du signalement', 'motif');
                echo form_textarea([
                        'name' => 'motif',
                        'id' => 'motif',
                        'class' => 'form-control',
                        'rows' => 5,
                        'placeholder' => 'Décrivez la raison de votre signalement...',
                        'required' => true
                ]);
                echo '</div>';

                $attributs = [
                        'name' => 'signalement',
                        'class' => 'btn-submit-signalement',
                ];
                echo form_submit($attributs, "Envoyer le signalement");

                // CORRECTION : Syntaxe correcte pour form_hidden
                echo '<input type="hidden" name="idMot" id="hidden-mot-id" value="">';
                // OU en utilisant form_hidden correctement :
                // echo form_hidden('idMot', '');
                // Puis ajouter l'ID avec JavaScript ou avec un input manuel

                echo form_close();
            else:
                echo '<p class="alert-info">Vous devez être connecté en tant que contributeur pour signaler ce contenu.</p>';
            endif;
            ?>
        </div>
    </div>
</dialog>

<script>
    (function() {
        const modal = document.getElementById('modal-signalement');
        const btnClose = document.querySelector('.btn-close-modal');
        const hiddenMotId = document.getElementById('hidden-mot-id');

        // Gérer tous les boutons "Signaler"
        document.querySelectorAll('.btn-signaler').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                // Récupérer l'ID du mot depuis l'attribut data
                const motId = this.getAttribute('data-mot-id');

                // Mettre à jour le champ caché avec l'ID du mot
                if (hiddenMotId) {
                    hiddenMotId.value = motId;
                }

                // Réinitialiser le textarea
                const textarea = document.getElementById('motif');
                if (textarea) {
                    textarea.value = '';
                }

                // Ouvrir la modal
                if (modal) {
                    modal.showModal();
                }
            });
        });

        // Fermer avec le bouton X
        if (btnClose) {
            btnClose.addEventListener('click', function() {
                modal.close();
            });
        }

        // Fermer en cliquant sur le backdrop
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.close();
                }
            });
        }
    })();
</script>
<?php endif; ?>