<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Côté opérateur (back-office)
$routes->group('admin', function ($routes) {
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
