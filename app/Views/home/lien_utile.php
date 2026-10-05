<?php
use App\Entities\LienUtile;
//récupère l'utilisateur
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
endif; ?>

<?php if(!empty($liensUtiles)):?>
    <h2 class="section-title">Liens utiles</h2>
    <div class="links-wrapper">
    <?php foreach($liensUtiles as $lienUtile):
        echo $lienUtile->afficherLienUtile($lienUtile);
    endforeach;?>
    </div>
<?php endif;?>