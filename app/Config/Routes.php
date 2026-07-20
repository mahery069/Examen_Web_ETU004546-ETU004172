<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');

$routes->get('connexion', 'Auth::index', ['as' => 'connexion']);
$routes->post('connexion', 'Auth::login', ['as' => 'login']);
$routes->get('deconnexion', 'Auth::logout', ['as' => 'logout']);

$routes->group('client', ['filter' => 'clientAuth'], static function ($routes) {
    $routes->get('tableau-de-bord', 'Client::tableauDeBord', ['as' => 'tableau_de_bord']);
    $routes->get('solde', 'Client::solde', ['as' => 'solde']);
    $routes->get('depot', 'Client::depot', ['as' => 'depot']);
    $routes->post('depot', 'Client::deposer', ['as' => 'deposer']);
});
