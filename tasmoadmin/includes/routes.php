<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();
$routes->add('index', new Route('/', [
    '_controller' => 'render_template',
], [  
    '_controller' => 'render_template',
]));
$routes->add('start', new Route('/start', [  
    '_controller' => 'render_template',
], [  
    '_controller' => 'render_template',
]));
$routes->add('change_language', new Route('/change_language/{new_lang}', [  
    '_controller' => 'render_raw',
]));
$routes->add('devices', new Route('/devices', [  
    '_controller' => 'render_template',
]));
$routes->add('device_action', new Route('/device_action/{action}/{device_id}', [
    'device_id' => '-1',
    '_controller' => 'render_template',
]));
$routes->add('device_config', new Route('/device_config/{device_id}', [  
    '_controller' => 'render_template',
]));
$routes->add('device_update', new Route('/device_update', [  
    '_controller' => 'render_template',
]));
$routes->add('devices_details', new Route('/devices_details', [  
    '_controller' => 'render_template',
]));
$routes->add('upload', new Route('/upload', [  
    '_controller' => 'render_template',
]));
$routes->add('upload_form', new Route('/upload_form', [  
    '_controller' => 'render_template',
]));
$routes->add('backup', new Route('/backup', [  
    '_controller' => 'render_template',
]));
$routes->add('devices_autoscan', new Route('/devices_autoscan', [  
    '_controller' => 'render_template',
]));
$routes->add('login', new Route('/login', [  
    '_controller' => 'render_template',
]));
$routes->add('logout', new Route('/logout', [  
    '_controller' => 'render_raw',
]));
$routes->add('site_config', new Route('/site_config', [  
    '_controller' => 'render_template',
]));
$routes->add('selfupdate', new Route('/selfupdate', [  
    '_controller' => 'render_template',
]));
$routes->add('site_config', new Route('/site_config', [  
    '_controller' => 'render_template',
]));
$routes->add('actions', new Route('actions', [  
    '_controller' => 'render_raw',
]));


return $routes;
