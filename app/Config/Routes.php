<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('users', function($routes) {
    $routes->add('register', 'UserController::register');
    $routes->add('login', 'UserController::login');
    $routes->add('logout', 'UserController::logout');
    $routes->add('profile', 'UserController::profile');
    $routes->add('getid', 'UserController::getUserByName');
});

$routes->group('project', function($routes) {
    $routes->add('create', 'ProjectController::create');
    $routes->add('delete', 'ProjectController::delete');
    $routes->add('update', 'ProjectController::update');
    $routes->add('findAll', 'ProjectController::findAllProjects');
    $routes->add('findById', 'ProjectController::findProjectById');
    $routes->add('findByUser', 'ProjectController::findByUser');
});

$routes->group('portfolio', function($routes) {
    $routes->add('create', 'PortfolioController::create');
    $routes->add('delete', 'PortfolioController::delete');
    $routes->add('update', 'PortfolioController::update');
    $routes->add('findAll', 'PortfolioController::findAll');
    $routes->add('findById', 'PortfolioController::findById');
    $routes->add('findByCreator', 'PortfolioController::findByCreator');
    $routes->add('findByCategory', 'PortfolioController::findByCategory');
    $routes->add('like/(:num)', 'PortfolioController::like/$1');
});
