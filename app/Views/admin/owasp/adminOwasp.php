<?php
?>
<div class="admin__pannel">
    <div id="admin__pannel-child-1" class="admin__pannel-child">
        <?php
        echo anchor(base_url('floribondant/contenu'),'Contenu', [
            'class' => 'glossary-form__trigger',
            'id'=>'contenu'
        ]);
        echo  anchor(base_url('floribondant/quiz'),'Quiz', [
            'class' => 'glossary-form__trigger',
            'id'=>'quiz'
        ]);?>
    </div>
</div>
