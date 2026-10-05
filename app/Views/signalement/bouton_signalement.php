<!-- Bouton pour ouvrir la modal -->
<?php
use App\Models\VoteModel;
use App\Entities\Vote;
use App\Entities\MotCle;

$vote_model = new VoteModel();
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
    if ($utilisateur->role === "contributeur"):
        if (isset($mot) && isset($motsSignales) && in_array($mot->id, $motsSignales)): ?>

            <button type="button" class="btn-signaler btn-signaler--disabled" disabled>
                <i class="fa-solid fa-flag"></i>
                Signaler
            </button>

        <?php else: ?>

            <button type="button" title="Vous avez déjà signaler ce mot" class="btn-signaler" data-mot-id="<?= $mot->id?>">
                <i class="fa-solid fa-flag"></i>
                Signaler
            </button>

        <?php endif;
    endif;
endif; ?>
