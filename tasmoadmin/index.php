<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use TasmoAdmin\DeviceCredentialException;
use TasmoAdmin\Helper\RequestHelper;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

function getTitle(string $page, ?string $action = null): string
{
    switch ($page) {
        case 'device_action':
            $title = __('MANAGE_DEVICE', 'PAGE_TITLES');
            if ('add' === $action) {
                $title = __('ADD_DEVICE', 'PAGE_TITLES');
            } elseif ('edit' === $action) {
                $title = __('EDIT_DEVICE', 'PAGE_TITLES');
            }

            break;

        case 'devices':
            $title = __('DEVICES', 'PAGE_TITLES');

            break;

        case 'device_update':
        case 'update_devices':
        case 'upload_form':
        case 'upload':
            $title = __('DEVICE_UPDATE', 'PAGE_TITLES');

            break;

        case 'device_config':
            $title = __('DEVICE_CONFIG', 'PAGE_TITLES');

            break;

        case 'site_config':
            $title = __('SITE_CONFIG', 'PAGE_TITLES');

            break;

        case 'selfupdate':
            $title = __('SITE_SELFUPDATE', 'PAGE_TITLES');

            break;

        default:
            $title = __(strtoupper(str_replace(' ', '_', $page)), 'PAGE_TITLES');
    }

    return $title;
}

function render_template(Request $request): Response
{
    extract($request->attributes->all(), EXTR_SKIP);
    if ('index' === $page) {
        $page = $Config->read('homepage');
    }

    if (!isset($action)) {
        $action = null;
    }

    $title = getTitle($page, $action);
    ob_start();

    include_once _INCLUDESDIR_.'header.php';

    include sprintf('%s%s.php', _PAGESDIR_, $page);

    include_once _INCLUDESDIR_.'footer.php';

    return new Response(ob_get_clean());
}

function render_raw(Request $request): Response
{
    extract($request->attributes->all(), EXTR_SKIP);
    ob_start();

    include sprintf('%s%s.php', _PAGESDIR_, $page);

    return new Response(ob_get_clean());
}

function createCredentialErrorMessage(DeviceCredentialException $exception): string
{
    $message = 'Device credential storage error. Check the configured password key and stored device data.';
    if (function_exists('__')) {
        $message = __('ERROR_DEVICE_CREDENTIAL_STORAGE', 'DEVICE_ACTIONS');
    }

    return sprintf('%s %s', $message, $exception->getMessage());
}

function renderCredentialErrorPage(DeviceCredentialException $exception): Response
{
    $title = 'Device Credential Storage Error';
    if (function_exists('__')) {
        $title = __('ERROR_DEVICE_CREDENTIAL_STORAGE', 'DEVICE_ACTIONS');
    }

    $body = sprintf(
        '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>%1$s</title></head><body><h1>%1$s</h1><p>%2$s</p></body></html>',
        htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(createCredentialErrorMessage($exception), ENT_QUOTES, 'UTF-8')
    );

    return new Response($body, 500);
}

function renderCredentialErrorJson(DeviceCredentialException $exception): Response
{
    return new Response(
        json_encode(['ERROR' => createCredentialErrorMessage($exception)]),
        500,
        ['Content-Type' => 'application/json']
    );
}

function shouldReturnCredentialErrorJson(?array $matched, Request $request): bool
{
    if ('render_raw' === ($matched['_controller'] ?? null)) {
        return true;
    }

    return $request->query->has('doAjax')
        || $request->query->has('doAjaxAll')
        || $request->request->has('doAjax')
        || $request->request->has('doAjaxAll');
}

$request = Request::createFromGlobals();
$matched = null;

$authByPassedPages = ['login', 'change_language'];

try {
    include_once './includes/bootstrap.php';
    $routes = include './includes/routes.php';
    $context = new RequestContext();
    $context->fromRequest($request);
    $matcher = new UrlMatcher($routes, $context);
    $isPublicI18nRequest = RequestHelper::isPublicI18nRequest($request);
    $matched = $matcher->match($request->getPathInfo());
    if (
        !$loggedin
        && !$isPublicI18nRequest
        && !in_array($matched['_route'], $authByPassedPages)
    ) {
        if ('render_template' === $matched['_controller']) {
            header('Location: '._BASEURL_.'login');

            exit;
        }
        ob_start();
        http_response_code(401);
        echo 'You must be logged in to perform this action';

        exit;
    }

    $request->attributes->add($matched);
    $request->attributes->add([
        'loggedin' => $loggedin,
        'docker' => $docker,
        'Config' => $Config,
        'container' => $container,
        'lang' => $lang,
        'page' => $matched['_route'],
    ]);
    $response = call_user_func($request->attributes->get('_controller'), $request);
} catch (DeviceCredentialException $exception) {
    $response = shouldReturnCredentialErrorJson($matched, $request)
        ? renderCredentialErrorJson($exception)
        : renderCredentialErrorPage($exception);
} catch (ResourceNotFoundException $exception) {
    $response = new Response('Not Found', 404);
} catch (Exception $exception) {
    $debug = isset($_SERVER['TASMO_DEBUG']);
    if ($debug) {
        $whoops = new Run();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler(new PrettyPageHandler());
        $response = new Response($whoops->handleException($exception), 500);
    } else {
        $response = new Response(var_dump($exception), 500);
    }
}

$response->send();
