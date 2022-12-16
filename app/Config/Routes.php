<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
// $routes->get('/', 'Home::index');

$routes->post('auth/login', 'AuthController::login');
$routes->post('auth/register', 'AuthController::register');

$routes->post('admin/auth/login', 'AuthController::admin_login');
$routes->post('admin/auth/register', 'AuthController::admin_register');
// $routes->group('auth', function ($routes) {
// });

$routes->group('v1', ['filter' => 'token'], function ($routes) {
    $routes->group('materi', function ($routes) {
        $routes->get('/', 'Materi::index');
    });
    $routes->get('materi/(:any)', 'Materi::show/$1');


    $routes->group('video', function ($routes) {
        $routes->get('/', 'Video::index');
    });
    $routes->get('video/(:any)', 'Video::show/$1');


    $routes->group('bab', function ($routes) {
        $routes->get('/', 'Bab::index');
    });
    $routes->get('bab/(:any)', 'Bab::show/$1');

    $routes->group('template', function ($routes) {
        $routes->get('/', 'Template::index');
        $routes->get('bottom-nav', 'Template::bottomNav');
        $routes->get('banner', 'Template::banner');
        $routes->get('landing', 'Template::landingMenu');
    });

    $routes->group('profile', function ($routes) {
        $routes->get('/', 'Profile::index');
    });

    $routes->group('landing', function ($routes) {
        $routes->get('/', 'Landing::index');
    });

    // NEW ENDPOINT FOR ALL NEW MEMODUL
    $routes->group('objectives', function ($routes) {
        $routes->get('/', 'ObjectiveController::index');
        $routes->post('/', 'ObjectiveController::create');
    });
    $routes->get('objectives/(:any)', 'ObjectiveController::show/$1');
    $routes->put('objectives/(:any)', 'ObjectiveController::change/$1');
    $routes->delete('objectives/(:any)', 'ObjectiveController::remove/$1');

    $routes->group('about', function ($routes) {
        $routes->get('/', 'AboutController::index');
    });
    $routes->post('about/photo', 'AboutController::change_photo');
    $routes->post('about/banner', 'AboutController::change_banner');
    $routes->put('about/profile', 'AboutController::change_profile');
    $routes->put('about/info', 'AboutController::change_about');

    $routes->group('menus', function ($routes) {
        $routes->get('/', 'MenuController::index');
        $routes->post('/', 'MenuController::create');
    });

    $routes->group('glossaries', function ($routes) {
        $routes->get('/', 'GlossaryController::index');
        $routes->post('/', 'GlossaryController::create');
    });
    $routes->get('glossaries/(:any)', 'GlossaryController::show/$1');
    $routes->put('glossaries/(:any)', 'GlossaryController::change/$1');
    $routes->delete('glossaries/(:any)', 'GlossaryController::remove/$1');

    $routes->group('bibliographies', function ($routes) {
        $routes->get('/', 'BibliographyController::index');
        $routes->post('/', 'BibliographyController::create');
    });
    $routes->get('bibliographies/(:any)', 'BibliographyController::show/$1');
    $routes->put('bibliographies/(:any)', 'BibliographyController::change/$1');
    $routes->delete('bibliographies/(:any)', 'BibliographyController::remove/$1');

    $routes->group('indicators', function ($routes) {
        $routes->get('/', 'IndicatorController::index');
        $routes->post('/', 'IndicatorController::create');
    });
    $routes->get('indicators/(:any)', 'IndicatorController::show/$1');
    $routes->put('indicators/(:any)', 'IndicatorController::change/$1');
    $routes->delete('indicators/(:any)', 'IndicatorController::remove/$1');

    $routes->group('materials', function ($routes) {
        $routes->get('/', 'MaterialController::index');
        $routes->post('/', 'MaterialController::create');
    });
    $routes->get('materials/(:any)', 'MaterialController::show/$1');
    $routes->put('materials/(:any)', 'MaterialController::change/$1');
    $routes->post('materials/banner/(:any)', 'MaterialController::change_banner/$1');

    $routes->group('topics', function ($routes) {
        $routes->get('/', 'TopicController::index');
        $routes->post('/', 'TopicController::create');
    });
    $routes->get('topics/(:any)', 'TopicController::show/$1');
    $routes->put('topics/(:any)', 'TopicController::change/$1');
    $routes->delete('topics/(:any)', 'TopicController::remove/$1');
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
