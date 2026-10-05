<?php
?>
    <div class="admin__pannel">
        <div id="admin__pannel-child-1" class="admin__pannel-child">
            <?php
            echo view('recherche');
            echo anchor(base_url('floribondant/mots/ajouter'),'Ajouter un mot', [
                'class' => 'glossary-form__trigger',
                'id'=>'ajouter-mot'
            ]);
            echo  anchor(base_url('floribondant/mots/validations'),'Demandes de validations', [
                'class' => 'glossary-form__trigger',
                'id'=>'demander-mot'
            ]);?>
        </div>
        <div id="admin__pannel-child-2" class="admin__pannel-child">
            <?= view('admin/tabUtilisateurs') ?>
        </div>

        <div id="admin__pannel-child-3" class="admin__pannel-child">
            <?= view('signalement/listeSignalements'); ?>
        </div>


    </div>

<?php
