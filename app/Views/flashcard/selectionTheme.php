<section class="intro intro--theme">
    <h1 class="intro__titre">Choisissez votre thème<span class="intro__point">.</span></h1>

    <p class="intro__texte">
        Sélectionnez le thème sur lequel vous  tester vos connaissances.
    </p>
    <?php
    if($themes == false) :
    echo "<h3>Aucun personnage trouvé</h3>";
    else:
    ?>
    <div class="themes">

        <?php foreach ($themes as $theme): ?>
            <?php
            if ($mode == 'entrainement') {
                $url = base_url('apprend');
            } else {
                $url = base_url('exerce');
            }
            ?>
            <?= anchor($url,
                    '<span class="num"></span>
            <h3>' . esc($theme->titre) . '</h3>
            <span class="arrow"></span>
            <p>' . esc($theme->libelle) . '</p>',
                    [
                            'title' => esc($theme->titre),
                            'class' => 'btn-theme'
                    ]
            ) ?>
        <?php endforeach; ?>
        <?= $mode ?>

</section>
<?php endif; ?>