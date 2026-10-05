<section id="recherche">
    <div class="container-recherche">
        <?php
        $attributesRecherche = [
                'method' => 'post',
                'class' => 'form-recherche'
        ];

        $dataRecherche = [
                'type' => 'search',
                'name' => 'recherche',
                'class' => 'recherche-input',
                'maxlength' => 100,
                'placeholder' => $placeholderAdmin ?? 'Explorez les termes clés de la cybersécurité !',
                'value' => esc($recherche ?? '') // pré-remplissage si PRG
        ];

        $dataSubmit = [
                'class'=> 'recherche-bouton-soumission'
        ];

        // Ouvre le formulaire, CSRF token ajouté automatiquement si activé
        echo form_open($actionRecherche ?? 'glossaire/recherche', $attributesRecherche);

        // Champ recherche
        echo form_input($dataRecherche);

        // Bouton soumission
        echo form_submit($dataSubmit, 'Rechercher');

        // Ferme le formulaire
        echo form_close();
        ?>
    </div>
</section>