<?php use App\Models\UtilisateurModel;
use App\Models\VoteModel;

if(!empty($classements)):
$vote_model = new VoteModel();
?>
    <div class="leaderboard">
        <h2 class="section-title"> Classement des membres les plus actifs </h2>
        <div class="leaderboard-list">
            <?php $place = 0;
            foreach ($classements as $classement):
                $place++;?>
                <div class="leaderboard-item">
                    <h3 class="rank"><?= $place?></h3>
                    <div class="user-avatar"><?= substr($classement->pseudo, 0,2) ?></div>
                    <div class="user-details">
                        <h3 class="user-name"><?= $classement->pseudo ?></h3>
                        <p class="user-votes"><?= $vote_model->getNbVotesTotalByUtilisateurId($classement->id) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>