<?php

use CodeIgniter\Config\Services;

$data['mots']=$mots??[];
$data['motsPopulaires']=$motsPopulaires??[];
$data['utilisateurs']=$mots??[];
$data['nb_votes_total']= $nb_votes_total;
$data['nb_votes']= $nb_votes ;
//récupère l'utilisateur
$session = Services::session();
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
    if($utilisateur->role === 'contributeur'):?>
<section>
    <div class="container container--center">
        <div class="dashboard">
            <?= view('communautaire/communautaire_votes',$data)?>
            <?= view('communautaire/votes_contributeur',$data)?>
            <?= view('communautaire/classement'); ?>
            <div id="fonctionnalites">
                <?php $attributes = [
                        'class' => 'glossary-form__trigger',
                ];
                echo anchor(base_url('communautaire/mots/ajouter'),'Ajouter un mot', $attributes)?>
            </div>

        </div>

    </div>
</section>
<?php endif;
endif;
?>