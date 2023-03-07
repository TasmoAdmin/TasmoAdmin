<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Whoops\Handler\PrettyPageHandler;

ob_start();

include_once( "./includes/bootstrap.php" );


function getTitle(string $page, ?string $action = null): string
{
    if (in_array($page, ['logout', 'change_language'])) {
        return '';
    }

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


$request = Request::createFromGlobals();
$routes = include './includes/routes.php';
$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);

try {
    extract($matcher->match($request->getPathInfo()), EXTR_SKIP);
    $page  = $_route;

    if( !$loggedin && $page !== 'login' ) {
        header( "Location: "._BASEURL_."login" );
        exit();
    }

    if ($page === 'index') {
        $page = $Config->read("homepage");
    }

    if (!isset($action)) {
        $action = null;
    }

    $title = getTitle($page, $action);
    ob_start();
    include_once( _INCLUDESDIR_."header.php" );
    ?>
<main class='container-fluid' id='content' data-refreshtime='<?php echo $Config->read( "refreshtime" ); ?>'>
	<div id='content-holder'>
		<?php include sprintf('%s%s.php', _PAGESDIR_, $page); ?>
	</div>
</main>
<?php
    include_once( _INCLUDESDIR_."footer.php" );
    $response = new Response(ob_get_clean());
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

$response->send();
