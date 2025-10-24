<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default routes
$routes->get('/', 'Home::index');
$routes->get('/home', 'Home::index');
$routes->get('/about', 'Home::about');
$routes->get('/contact', 'Home::contact');

$routes->get('/register', 'Auth::register');
$routes->post('/register', 'Auth::register');
// Support optional trailing slash
$routes->get('/register/', 'Auth::register');
$routes->post('/register/', 'Auth::register');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::login');
$routes->get('/dashboard', 'Auth::dashboard');
$routes->get('/logout', 'Auth::logout');

$routes->post('/course/enroll', 'Course::enroll');

$routes->get('/admin/course/(:num)/upload', 'Materials::upload/$1');
$routes->post('/admin/course/(:num)/upload', 'Materials::upload/$1');
$routes->get('/admin/course/(:num)/materials', 'Materials::view/$1');
$routes->get('/materials/delete/(:num)', 'Materials::delete/$1');
$routes->post('/materials/delete/(:num)', 'Materials::delete/$1');
$routes->delete('/materials/delete/(:num)', 'Materials::delete/$1');
$routes->get('/materials/download/(:num)', 'Materials::download/$1');
$routes->get('/materials/course/(:num)/materials', 'Materials::getMaterialsByCourse/$1');

// Debug routes
$routes->get('/debug/upload', 'Materials::debugUpload');
$routes->post('/debug/upload', 'Materials::debugUpload');
$routes->get('/simple-debug', 'Materials::simpleDebug');
$routes->post('/simple-debug', 'Materials::simpleDebug');

// TEMP debug route
