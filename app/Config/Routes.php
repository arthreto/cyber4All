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

// Gestion des leçons et thèmes
$routes->get('theme/(:num)', 'ThemeController::afficherPageLecon/$1');
$routes->get('theme/(:num)/(:num)', 'ThemeController::afficherPageLecon/$1/$2', ['as' => 'lecon']);

// Quiz Vrai / Faux
$routes->get('quiz', 'QuizController::index');
$routes->get('quiz/(:num)', 'QuizController::index/$1');
$routes->match(['GET', 'POST'], 'quiz/(:num)/(:num)', 'QuizController::jouerQuiz/$1/$2');

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

    $routes->match(['GET', 'POST'], 'theme', 'admin\AdminThemeController::index');
    $routes->match(['GET', 'POST'], 'theme/quiz', 'admin\AdminThemeController::quizSection');
    $routes->match(['GET', 'POST'], 'theme/lecons', 'admin\AdminThemeController::leconsSection');
    $routes->match(['GET', 'POST'], 'theme/questions/ajouter', 'admin\AdminThemeController::questionAjouter');
    $routes->match(['GET', 'POST'], 'theme/questions/modifier/(:num)', 'admin\AdminThemeController::questionAjouter/$1');
    $routes->match(['GET', 'POST'], 'theme/questions/supprimer/(:num)', 'admin\AdminThemeController::questionSupprimer/$1');

    $routes->match(['GET', 'POST'], 'theme/quiz/ajouter', 'admin\AdminQuizController::quizAjouter');
    $routes->match(['GET', 'POST'], 'theme/quiz/modifier/(:num)', 'admin\AdminQuizController::quizAjouter/$1');
    $routes->match(['GET', 'POST'], 'theme/quiz/supprimer/(:num)', 'admin\AdminQuizController::quizSupprimer/$1');


    $routes->post('signalement/supprimer', 'SignalementController::supprimerSignalement');
    $routes->match(['GET','POST'], 'utilisateurs', 'admin\AdminUtilisateurController::index');
});
