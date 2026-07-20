<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Authentification côté opérateur (back-office) — non protégée par le filtre
$routes->get('admin/login', 'Admin\AuthController::showLogin');
$routes->post('admin/login', 'Admin\AuthController::login');
$routes->post('admin/logout', 'Admin\AuthController::logout');

// Côté opérateur (back-office) — protégé par le filtre adminAuth
$routes->group('admin', ['filter' => 'adminAuth'], function ($routes) {
    $routes->get('', 'Admin\DashboardController::index');

    $routes->get('prefixes', 'Admin\PrefixesController::index');
    $routes->post('prefixes', 'Admin\PrefixesController::store');
    $routes->post('prefixes/(:num)/update', 'Admin\PrefixesController::update/$1');
    $routes->post('prefixes/(:num)/delete', 'Admin\PrefixesController::delete/$1');

    $routes->get('baremes', 'Admin\BaremesFraisController::index');
    $routes->post('baremes', 'Admin\BaremesFraisController::store');
    $routes->post('baremes/(:num)/update', 'Admin\BaremesFraisController::update/$1');
    $routes->post('baremes/(:num)/delete', 'Admin\BaremesFraisController::delete/$1');

    $routes->get('gains', 'Admin\GainsController::index');

    $routes->get('comptes-clients', 'Admin\ComptesClientsController::index');
});

// Authentification côté client (connexion automatique par numéro de téléphone)
$routes->get('connexion', 'Auth::index', ['as' => 'connexion']);
$routes->post('connexion', 'Auth::login', ['as' => 'login']);
$routes->get('deconnexion', 'Auth::logout', ['as' => 'logout']);

// Côté client — protégé par le filtre clientAuth
$routes->group('client', ['filter' => 'clientAuth'], static function ($routes) {
    $routes->get('tableau-de-bord', 'Client::tableauDeBord', ['as' => 'tableau_de_bord']);
    $routes->get('solde', 'Client::solde', ['as' => 'solde']);
    $routes->get('depot', 'Client::depot', ['as' => 'depot']);
    $routes->post('depot', 'Client::deposer', ['as' => 'deposer']);
    $routes->get('retrait', 'Client::retrait', ['as' => 'retrait']);
    $routes->post('retrait', 'Client::retirer', ['as' => 'retirer']);
    $routes->get('transfert', 'Client::transfert', ['as' => 'transfert']);
    $routes->post('transfert', 'Client::transfertApercu', ['as' => 'transfert_apercu']);
    $routes->post('transfert/confirmer', 'Client::transferer', ['as' => 'transferer']);
    $routes->get('envoi-multiple', 'Client::envoiMultiple', ['as' => 'envoi_multiple']);
    $routes->post('envoi-multiple', 'Client::envoiMultipleApercu', ['as' => 'envoi_multiple_apercu']);
    $routes->post('envoi-multiple/confirmer', 'Client::envoiMultipleConfirmer', ['as' => 'envoi_multiple_confirmer']);
    $routes->get('historique', 'Client::historique', ['as' => 'historique']);
});
