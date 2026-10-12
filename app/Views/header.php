<?php
$logo_cyber4all = [
        "src"=>"images/logos/cyber4all_nobg_notxt.png",
        "alt"=>"",
        "class"=>"header__logo",
        "title"=>""
];
session();
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
endif;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cyber4All</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-solid.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-regular.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-light.css">
    <!-- CSS -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= link_tag(base_url('/css/style.css')) ?>
    <script src="<?= base_url('/js/script.js')?>" defer></script>
    <link rel="icon" type="image/x-icon" href="<?= base_url("images/logos/cyber4all_nobg_notxt.png")?>">
</head>
<body>
    <header id="header-site" class="header-site">
        <div class="header__wrapper">
            <?php if((isset($utilisateur) && $utilisateur->role === "admin")):?>
            <div class="header__marque">
               <?= img($logo_cyber4all) ?>
                <h1 class="header__titre">ADMINISTRATION</h1>

            </div>
            <?php else:?>
                <div class="header__marque">
                    <?= img($logo_cyber4all) ?>
                    <?= anchor(
                            base_url(),
                            '<h1 class="header__titre">CYBER<span class="header__titre--accent">4ALL</span></h1>',
                            [
                                    'title'=>"Retour à l'accueil du site cyber4all",
                                    'class'=>"header__lien-marque"
                            ]
                    ) ?>

                </div>
            <!-- Bouton hamburger pour mobile -->
            <button class="menu-hamburger" id="menu-hamburger" aria-label="Menu de navigation">
                <span class="menu-hamburger__ligne"></span>
                <span class="menu-hamburger__ligne"></span>
                <span class="menu-hamburger__ligne"></span>
            </button>
            <?php endif; ?>

            <nav class="header__navigation" id="header-navigation" role="navigation" aria-label="Navigation principale">
                <ul class="menu-nav">
                    <?php if(isset($utilisateur) && $utilisateur->role === "admin"):?>
                        <li class="menu-nav__item">
                            <?= anchor(
                                    base_url('floribondant/mots'),
                                    'Mots',
                                    [
                                            'title'=>"Se diriger vers l'espace d'administration",
                                            'class'=>"menu-nav__lien"
                                    ]
                            ) ?>
                        </li>
                        <li class="menu-nav__item">
                            <?= anchor(
                                    base_url('floribondant/utilisateurs'),
                                    'Utilisateurs',
                                    [
                                            'title'=>"Se diriger vers l'espace d'administration",
                                            'class'=>"menu-nav__lien"
                                    ]
                            ) ?>
                        </li>
                    <?php else: ?>

                    <?php
                    if(isset($utilisateur) && $utilisateur->role === "contributeur"):?>
                        <li class="menu-nav__item">
                            <?= anchor(
                                    base_url('communautaire'),
                                    'Communautaire',
                                    [
                                            'title'=>"Aller vers l'espace communautaire",
                                            'class'=>"menu-nav__lien"
                                    ]
                            ) ?>
                        </li>
                    <?php endif; ?>
                    <li class="menu-nav__item">
                        <?= anchor(
                                base_url('glossaire'),
                                'Glossaire',
                                [
                                        'title'=>"Tout le lexique de la cybersécurité",
                                        'class'=>"menu-nav__lien"
                                ]
                        ) ?>
                    </li>
                    <li class="menu-nav__item">
                            <?= anchor(
                                    base_url('quiz'),
                                    'Quiz',
                                    [
                                            'title'=>"Tout le lexique de la cybersécurité",
                                            'class'=>"menu-nav__lien"
                                    ]
                            ) ?>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="header__connexion">
                <?php if (!isset($utilisateur)): ?>
                    <?= anchor(
                            base_url('connexion'),
                            'Connexion',
                            [
                                    'title'=>"Se connecter ou s'inscrire",
                                    'class'=>"btn-connexion"
                            ]
                    ) ?>
                <?php else: ?>
                    <?= anchor(
                            base_url('compte'),
                            'Mon compte',
                            [
                                    'title'=>"Accéder à mon compte",
                                    'class'=>"btn-connexion"
                            ]
                    ) ?>
                    <?= anchor(
                            base_url("deconnexion"),
                            'Se déconnecter',
                            [
                                    'title'=>"Se déconnecter",
                                    'class'=>"btn-connexion"
                            ]
                    ) ?>
                <?php endif; ?>
            </div>
        </div>
    </header>
</body>