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
    <!-- Font Awesome gratuit (les polices de site-assets.fontawesome.com ne se chargent pas) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <!-- Éditeur Markdown (EasyMDE) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.css">
    <!-- CSS -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= link_tag(base_url('/css/style.css')) ?>
    <?= link_tag(base_url('/css/sio/menuSio.css') . '?v=' . filemtime(FCPATH . 'css/sio/menuSio.css')) ?>
    <?= link_tag(base_url('/css/sio/lecon.css') . '?v=' . filemtime(FCPATH . 'css/sio/lecon.css')) ?>
    <?= link_tag(base_url('/css/sio/adminSio.css') . '?v=' . filemtime(FCPATH . 'css/sio/adminSio.css')) ?>
    <?= link_tag(base_url('/css/sio/quiz.css') . '?v=' . filemtime(FCPATH . 'css/sio/quiz.css')) ?>
    <script src="<?= base_url('/js/script.js')?>" defer></script>
    <!-- Éditeur Markdown : la bibliothèque avant le script qui l'utilise (les scripts defer s'exécutent dans l'ordre) -->
    <script src="https://cdn.jsdelivr.net/npm/easymde@2/dist/easymde.min.js" defer></script>
    <script src="<?= base_url('/js/sio-admin-editeur.js') . '?v=' . filemtime(FCPATH . 'js/sio-admin-editeur.js') ?>" defer></script>
    <!-- Administration : position de défilement, blocs du thème coché, formulaire d'un quiz -->
    <script src="<?= base_url('/js/sio-admin-theme.js') . '?v=' . filemtime(FCPATH . 'js/sio-admin-theme.js') ?>" defer></script>
    <link rel="icon" type="image/x-icon" href="<?= base_url("images/logos/cyber4all_nobg_notxt.png")?>">
</head>
<body>
    <header id="header-site" class="header-site">
        <div class="header__wrapper">
            <?php if((isset($utilisateur) && $utilisateur->role === "admin" && (strpos($_SERVER['REQUEST_URI'], 'floribondant') || strpos($_SERVER['REQUEST_URI'], 'compte')))):?>
            <div class="header__marque">



               <?= img($logo_cyber4all) ?>
                <?= anchor(
                        base_url(),
                        '<h1 class="header__titre">ADMINISTRATION</h1>',
                        [
                                'title'=>"Retour à l'accueil du site cyber4all",
                                'class'=>"header__lien-marque"
                        ]
                ) ?>

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
                    <?php if(isset($utilisateur) && $utilisateur->role === "admin"  && (strpos($_SERVER['REQUEST_URI'], 'floribondant') || strpos($_SERVER['REQUEST_URI'], 'compte'))):?>
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
                        <li class="menu-nav__item">
                            <?= anchor(
                                    base_url('floribondant/theme'),
                                    'Traçabilité',
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
                    <!-- Espace compréhension SIO : menu déroulant sans JavaScript -->
                    <li class="menu-nav__item menu-nav__item--sio">
                        <details class="menu-sio">
                            <summary class="menu-nav__lien menu-sio__titre">Comprendre les accès</summary>
                            <ul class="menu-sio__liste">
                                <?php
                                // Seulement les thèmes qui ont au moins une leçon active (sinon le lien mène à une 404)
                                $idsThemesAvecLecon = model(\App\Models\LeconModel::class)->getIdsThemesAvecLecon();
                                foreach ($themeList as $theme):
                                    if (! in_array((int) $theme->id, $idsThemesAvecLecon, true)) {
                                        continue;
                                    } ?>
                                    <li>
                                        <a href="<?= site_url('theme/' . $theme->id) ?>"
                                           class="dropdown-menu__item"
                                           title="Première leçon du thème <?= esc($theme->nom, 'attr') ?>">
                                            <?= esc($theme->nom) ?>
                                        </a>
                                    </li>
                                <?php endforeach ?>
                                <?php if (isset($utilisateur)): ?>
                                <li class="menu-sio__separateur"><a href="<?= site_url('quiz') ?>" class="dropdown-menu__item menu-sio__quiz" title="Choisir un quiz Vrai / Faux">Quiz Vrai / Faux</a></li>
                                <?php endif; ?>
                            </ul>
                        </details>
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