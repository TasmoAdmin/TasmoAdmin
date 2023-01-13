<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();
$routes->add('index', new Route('/'));
$routes->add('start', new Route('/start'));
$routes->add('devices', new Route('/devices'));
$routes->add('upload_form', new Route('/upload_form'));
$routes->add('backup', new Route('/backup'));
$routes->add('devices_autoscan', new Route('/devices_autoscan'));
$routes->add('login', new Route('/login'));
$routes->add('site_config', new Route('/site_config'));
$routes->add('selfupdate', new Route('/selfupdate'));
$routes->add('site_config', new Route('/site_config'));


return $routes;
