<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

ob_start();

include_once( "./includes/bootstrap.php" );

if( !$loggedin ) {
    header( "Location: "._BASEURL_."login" );
}

function getTitle(string $page): string
{
    switch( $page ) {
        case "device_action":
            $title = __( "MANAGE_DEVICE", "PAGE_TITLES" );
            if( isset( $_GET[ "action" ] ) && $_GET[ "action" ] === "add" ) {
                $title = __( "ADD_DEVICE", "PAGE_TITLES" );
            } elseif( isset( $_GET[ "action" ] ) && $_GET[ "action" ] === "edit" ) {
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
    $result = $matcher->match($request->getPathInfo());
    $page  = $result['_route'];
    if ($page === 'index') {
        $page = 'start';
    }

    $title = getTitle($page);
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
    var_dump($exception);
    $response = new Response('An error occurred', 500);
}

$response->send();
