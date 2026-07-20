<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Côté opérateur (back-office)
$routes->group('admin', function ($routes) {
    $routes->get('prefixes', 'Admin\PrefixesController::index');
    $routes->post('prefixes', 'Admin\PrefixesController::store');
    $routes->post('prefixes/(:num)/update', 'Admin\PrefixesController::update/$1');
    $routes->post('prefixes/(:num)/delete', 'Admin\PrefixesController::delete/$1');
});
