<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//$routes->get('/', 'Home::index');
$routes->get('/', 'AccueilController::index');

//Gestion du glossaire => accès aux mots, catégories
$routes->group('glossaire', static function ($routes) {
    $routes->get('/', 'GlossaireController::afficherGlossaire');
    $routes->post('recherche', 'GlossaireController::rechercherGlossaire');
    $routes->match(['GET', 'POST'], 'mot/(:num)', 'GlossaireController::afficherMot/$1');
    $routes->match(['GET', 'POST'], 'categorie/(:num)', 'GlossaireController::afficherCategorie/$1');
    $routes->match(['GET', 'POST'], 'categorie/(:num)', 'GlossaireController::afficherCategorie/$1');
});

//Gestion de l'espace de connexion
$routes->match(['GET', 'POST'], '/connexion', 'ConnexionController::connexion');
$routes->get('/deconnexion', 'ConnexionController::deconnexion');
$routes->match(['GET', 'POST'], '/inscription', 'ConnexionController::inscription');
$routes->match(['GET', 'POST'], '/compte', 'CompteController::index');

// Gestion de l'espace communautaire
$routes->group('communautaire', static function ($routes) {
    $routes->match(['GET', 'POST'], '/', 'communautaire\CommunautaireController::index');
    $routes->match(['GET', 'POST'], 'mots/ajouter', 'communautaire\CommunautaireController::ajouterDemandeMot');
    $routes->match(['GET', 'POST'], 'mots/update/(:num)', 'communautaire\CommunautaireController::updateMot/$1');
});


$routes->match(['GET', 'POST'], '/vote', 'VoteController::index');
$routes->match(['GET', 'POST'], '/signaler', 'SignalementController::ajouterSignalement');
$routes->match(['GET', 'POST'], '/ajoutLien', 'LienUtileController::insert');

// Gestion de l'espace administrateur'
$routes->group('floribondant', static function ($routes) {
    $routes->match(['GET', 'POST'], 'mots', 'admin\AdminMotController::index');
    $routes->match(['GET', 'POST'], 'mots/ajouter', 'admin\AdminMotController::addMotCle');
    $routes->match(['GET', 'POST'], 'mots/update/(:num)', 'admin\AdminMotController::updateMotCle/$1');
    $routes->match(['GET', 'POST'], 'mots/suppMot', 'admin\AdminMotController::suppMotCle');
    $routes->post('mots/switchStatutMotCle', 'admin\AdminMotController::switchStatutMotCle');
    $routes->match(['GET', 'POST'], 'mots/validations', 'admin\AdminMotController::afficherValidations');
    $routes->match(['GET', 'POST'], 'mots/validation/decision/(:num)', 'admin\AdminMotController::decisionMotCle/$1');
    $routes->post('signalement/supprimer', 'SignalementController::supprimerSignalement');
    $routes->match(['GET','POST'], 'utilisateurs', 'admin\AdminUtilisateurController::index');
    $routes->match(['GET','POST'], 'owasp', 'admin\owasp\AdminOwaspController::index');
    $routes->match(['GET','POST'], 'contenu', 'admin\owasp\AdminContenuOwaspController::index');
    $routes->get('contenu/(:segment)', 'admin\owasp\AdminContenuOwaspController::choixTop/$1');
    $routes->get('contenu/(:segment)/modifier/(:num)', 'admin\owasp\AdminContenuOwaspController::modifElement/$1/$2');
    $routes->post('contenu/(:segment)/modifier/(:num)', 'admin\owasp\AdminContenuOwaspController::enregistrerMenace/$1/$2');
    $routes->post('contenu/(:segment)/modifier/(:num)/contre-mesure/ajouter', 'admin\owasp\AdminContenuOwaspController::ajouterContreMesure/$1/$2');
    $routes->post('contenu/(:segment)/modifier/(:num)/contre-mesure/(:num)', 'admin\owasp\AdminContenuOwaspController::enregistrerContreMesure/$1/$2/$3');
    $routes->post('contenu/(:segment)/modifier/(:num)/contre-mesure/(:num)/supprimer', 'admin\owasp\AdminContenuOwaspController::supprimerContreMesure/$1/$2/$3');
    $routes->match(['GET','POST'], 'ajouter', 'admin\AdminContenuOwaspController::addElement');
});

$routes->group('top10', static function ($routes) {
    $routes->match(['GET', 'POST'], '(:segment)', 'Owasp\OwaspController::getMenaces/$1');
    $routes->match(['GET', 'POST'], 'api/detail/(:segment)/(:num)', 'Owasp\OwaspController::getMenace/$1/$2');
    $routes->match(['GET', 'POST'], 'quiz/(:segment)', 'Owasp\OwaspQuizController::getQuiz/$1');
});
