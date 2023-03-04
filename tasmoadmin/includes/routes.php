<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();
$routes->add('index', new Route('/'));
$routes->add('start', new Route('/start'));
$routes->add('change_language', new Route('/change_language/{new_lang}'));
$routes->add('devices', new Route('/devices'));
$routes->add('device_action', (new Route('/device_action/{action}/{device_id}'))->addDefaults(['device_id' => '-1']));
$routes->add('device_config', new Route('/device_config/{device_id}'));
$routes->add('device_update', new Route('/device_update'));
$routes->add('devices_details', new Route('/devices_details'));
$routes->add('upload', new Route('/upload'));
$routes->add('upload_form', new Route('/upload_form'));
$routes->add('backup', new Route('/backup'));
$routes->add('devices_autoscan', new Route('/devices_autoscan'));
$routes->add('login', new Route('/login'));
$routes->add('logout', new Route('/logout'));
$routes->add('site_config', new Route('/site_config'));
$routes->add('selfupdate', new Route('/selfupdate'));
$routes->add('site_config', new Route('/site_config'));


return $routes;
