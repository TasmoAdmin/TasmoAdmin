<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Whoops\Handler\PrettyPageHandler;

include_once( "./includes/bootstrap.php" );


function getTitle(string $page, ?string $action = null): string
{
    switch( $page ) {
        case "device_action":
            $title = __( "MANAGE_DEVICE", "PAGE_TITLES" );
            if( $action === "add" ) {
                $title = __( "ADD_DEVICE", "PAGE_TITLES" );
            } elseif( $action === "edit" ) {
                $title = __( "EDIT_DEVICE", "PAGE_TITLES" );
            }
            break;
        case "devices":
            $title = __( "DEVICES", "PAGE_TITLES" );
            break;
        case "device_update":
        case "update_devices":
        case "upload_form":
        case "upload":
            $title = __( "DEVICE_UPDATE", "PAGE_TITLES" );
            break;
        case "device_config":
            $title = __( "DEVICE_CONFIG", "PAGE_TITLES" );
            break;
        case "site_config":
            $title = __( "SITE_CONFIG", "PAGE_TITLES" );
            break;
        case "selfupdate":
            $title = __( "SITE_SELFUPDATE", "PAGE_TITLES" );
            break;
        default:
            $title = __( strtoupper( str_replace( " ", "_", $page ) ), "PAGE_TITLES" );
    }

    return $title;
}


function render_template(Request $request): Response
{
    extract($request->attributes->all(), EXTR_SKIP);
    if ($page === 'index') {
        $page = $Config->read("homepage");
    }

    if (!isset($action)) {
        $action = null;
    }

    $title = getTitle($page, $action);
    ob_start();
    include_once( _INCLUDESDIR_."header.php" );
    include sprintf('%s%s.php', _PAGESDIR_, $page);
    include_once( _INCLUDESDIR_."footer.php" );
    return new Response(ob_get_clean());
}

function render_raw(Request $request): Response
{
    extract($request->attributes->all(), EXTR_SKIP);
    ob_start();
    include sprintf('%s%s.php', _PAGESDIR_, $page);
    return new Response(ob_get_clean());
}


$request = Request::createFromGlobals();
$routes = include './includes/routes.php';
$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);

$authByPassedPages = ['login', 'change_language'];

try {
    $matched = $matcher->match($request->getPathInfo());
    if (!$loggedin && !in_array($matched['_route'], $authByPassedPages)) {
        if ($matched['_controller'] === 'render_template') {        
            header( "Location: "._BASEURL_."login" );
            exit();
        } else {
            ob_start();
            http_response_code(401);
            echo 'You must be logged in to perform this action';
            exit();
        }
    }

    $request->attributes->add($matched);
    $request->attributes->add([
        'loggedin' => $loggedin,
        'docker' => $docker,
        'Config' => $Config,
        'container'=> $container,
        'lang' => $lang,
        'page' => $matched['_route'],
    ]);
    $response = call_user_func($request->attributes->get('_controller'), $request);
} catch (ResourceNotFoundException $exception) {
    $response = new Response('Not Found', 404);
} catch (Exception $exception) {
    $debug = isset($_SERVER['TASMO_DEBUG']);
    if ($debug) {
        $whoops = new Whoops\Run();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler(new PrettyPageHandler());
        $response =new Response($whoops->handleException($exception), 500);
    } else {
        $response = new Response(var_dump($exception), 500);
    }
}

$maxMb =5;

throw new RuntimeException(__("UPLOAD_FIRMWARE_FULL_TOO_BIG", "DEVICE_UPDATE", [sprintf('%sMB', $maxMb)]));


$response->send();
