<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!function_exists('curl_init')) {
    echo 'ERROR: PHP cURL is missing.';
    echo 'Please enable PHP cURL extension and restart web-server.';

    exit;
}

if (!class_exists('ZipArchive')) {
    echo 'ERROR: PHP Zip is missing.';
    echo 'Please enable PHP Zip extension and restart web-server.';

    exit;
}

$subdir = dirname($_SERVER['PHP_SELF']).'/';
$subdir = $subdir = str_replace('\\', '/', $subdir);
$subdir = '//' == $subdir ? '/' : $subdir;

if ($baseurl_from_env = getenv('TASMO_BASEURL')) {
    $subdir = $baseurl_from_env;
}

define('_BASEURL_', $subdir);
define('_APPROOT_', dirname(dirname(__FILE__)).'/');
define('_TMPDIR_', getenv('TASMO_TMPDIR') ?: _APPROOT_.'tmp/');

define('_RESOURCESURL_', _BASEURL_.'resources/');
define('_INCLUDESDIR_', _APPROOT_.'includes/');
define('_HELPERSDIR_', _APPROOT_.'helpers/');
define('_RESOURCESDIR_', _APPROOT_.'resources/');
define('_LIBSDIR_', _APPROOT_.'libs/');
define('_PAGESDIR_', _APPROOT_.'pages/');
define('_DATADIR_', getenv('TASMO_DATADIR') ?: _APPROOT_.'data/');
define('_LANGDIR_', _APPROOT_.'lang/');
define('_CSVFILE_', _DATADIR_.'devices.csv');

// Sessions live next to the config so they survive container restarts.
define('_SESSIONDIR_', getenv('TASMO_SESSIONDIR') ?: _DATADIR_.'sessions/');
define('_SESSION_LIFETIME_', 30 * 24 * 3600);

if (!is_dir(_SESSIONDIR_)) {
    @mkdir(_SESSIONDIR_, 0o700, true);
}

$sessionCookieParams = [
    'lifetime' => _SESSION_LIFETIME_,
    'path' => '/',
    'secure' => (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'])
        || 'https' === ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''),
    'httponly' => true,
    'samesite' => 'Lax',
];

ini_set('session.gc_maxlifetime', (string) _SESSION_LIFETIME_);
session_save_path(is_dir(_SESSIONDIR_) ? _SESSIONDIR_ : _TMPDIR_.'sessions');
session_name('TASMO_SESSION');
session_set_cookie_params($sessionCookieParams);
session_start();

global $loggedin, $docker;
$loggedin = false;
$docker = false;

require_once _APPROOT_.'vendor/autoload.php';

use Selective\Container\Container;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\EnvironmentHelper;
use TasmoAdmin\Helper\JsonLanguageHelper;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/** @var Container $container */
$container = require _APPROOT_.'includes/container.php';

$debug = isset($_SERVER['TASMO_DEBUG']);
if ($debug) {
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler());
    $whoops->register();
}

if (file_exists(_APPROOT_.'.dockerenv')) {
    $docker = true;
}

$Config = $container->get(Config::class);
$i18n = $container->get(i18n::class);
$i18n->setCachePath(_TMPDIR_.'cache/i18n/');
$i18n->setFilePath(_LANGDIR_.'{LANGUAGE}/lang.ini'); // language file path
$i18n->setFallbackLang('en');
$i18n->setPrefix('__L');
$i18n->setSectionSeparator('_');
$i18n->setMergeFallback(true); // make keys available from the fallback language
$i18n->init();

$lang = $i18n->getAppliedLang();

$langHelper = new JsonLanguageHelper(
    $lang,
    _LANGDIR_."{$lang}/lang.ini",
    'en',
    _LANGDIR_.'en/lang.ini',
    _TMPDIR_.'cache/i18n/'
);
$langHelper->dumpJson();

if ((isset($_SESSION['login']) && '1' == $_SESSION['login'])
    || '0' == $Config->read('login')
    || EnvironmentHelper::isEnabled('NO_AUTH')
) {
    $loggedin = true;
}

// Slide the session cookie expiry forward while the user keeps using the app.
if (isset($_SESSION['login']) && '1' == $_SESSION['login'] && !headers_sent()) {
    setcookie(session_name(), session_id(), [
        'expires' => time() + _SESSION_LIFETIME_,
        'path' => $sessionCookieParams['path'],
        'secure' => $sessionCookieParams['secure'],
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function __(string $string, ?string $category = null, ?array $args = null)
{
    $cat = '';
    if (isset($category) && !empty($category)) {
        $cat = $category.'_';
    }
    $txt = $cat.$string;

    return __L($txt, $args);
}
