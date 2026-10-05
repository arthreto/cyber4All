<?php
use App\Models\VoteModel;
use App\Entities\Vote;
use App\Entities\MotCle;

$vote_model = new VoteModel();
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
endif;

if(isset($utilisateur) && ($utilisateur->role === "contributeur")):
    $dataForm = [
        'method' => 'POST',
        'class'=> 'btn-vote-communautaire'
    ];
    echo form_open('vote', $dataForm);

    echo form_hidden('id_mot_vote',(string)$mot->id);
    echo form_hidden('from', (string)$from );

    // CAS 1 : L'utilisateur a déjà voté pour ce mot
    if (in_array($mot->id, $tab_id_mots ?? [])):
        $attributs = [
            'name'=>'suppVote',
            'class'=>'suppvote',
            'title'=>"Supprimer mon vote"
        ];
        echo form_submit($attributs,"Se désister");

        // Récupérer le vote et vérifier qu'il existe
        $vote = $vote_model->getUnVoteSemaineEnCours($utilisateur->id, $mot->id);
        if($vote !== null):
            echo form_hidden('id_vote', (string)$vote->id_vote);
        endif;

    // CAS 2 : L'utilisateur n'a pas voté pour ce mot ET n'a pas atteint la limite
    elseif ($nb_votes < NB_VOTES_MAX):
        $attributs = [
            'name'=>'voter',
            'class'=>'vote',
            'title'=>"Voter pour ce mot"
        ];
        echo form_submit($attributs,"voter");

    // CAS 3 : L'utilisateur n'a pas voté pour ce mot MAIS a atteint sa limite
    else:
        $attributs = [
            'name'=>'voter',
            'class'=>'vote-disabled',
            'title'=>"Vous avez atteint votre limite de votes",
            'disabled'=>'disabled'
        ];
        echo form_submit($attributs,"Limite atteinte");
    endif;
    echo form_close();
endif;
?>