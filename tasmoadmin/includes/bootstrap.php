<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


if(!function_exists("curl_init")) {
    echo "ERROR: PHP cURL is missing.";
    echo "Please enable PHP cURL extension and restart web-server.";
    die();
}

if(!class_exists("ZipArchive")) {
    echo "ERROR: PHP Zip is missing.";
    echo "Please enable PHP Zip extension and restart web-server.";
    die();
}


$subdir = dirname($_SERVER[ 'PHP_SELF' ])."/";
$subdir = $subdir = str_replace("\\", "/", $subdir);
$subdir = $subdir == "//" ? "/" : $subdir;

if ($baseurl_from_env = getenv('TASMO_BASEURL')) {
    $subdir = $baseurl_from_env;
}

define("_BASEURL_", $subdir);
define('_APPROOT_', dirname(dirname(__FILE__)).'/');
define("_TMPDIR_", getenv('TASMO_TMPDIR') ?: _APPROOT_."tmp/");

define("_RESOURCESURL_", _BASEURL_."resources/");
define("_INCLUDESDIR_", _APPROOT_."includes/");
define("_HELPERSDIR_", _APPROOT_."helpers/");
define("_RESOURCESDIR_", _APPROOT_."resources/");
define("_LIBSDIR_", _APPROOT_."libs/");
define("_PAGESDIR_", _APPROOT_."pages/");
define("_DATADIR_", getenv('TASMO_DATADIR') ?: _APPROOT_."data/");
define("_LANGDIR_", _APPROOT_."lang/");
define("_CSVFILE_", _DATADIR_."devices.csv");


session_save_path(_TMPDIR_."sessions");
session_name("TASMO_SESSION");
session_start();

global $loggedin, $docker;
$loggedin = false;
$docker   = false;

require_once _APPROOT_ . 'vendor/autoload.php';

use Selective\Container\Container;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\JsonLanguageHelper;
use TasmoAdmin\Sonoff;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/** @var Container $container */
$container = require  _APPROOT_ . 'includes/container.php';

$debug = isset($_SERVER['TASMO_DEBUG']);
if ($debug) {
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler());
    $whoops->register();
}

$filename = _CSVFILE_; //csv file name
if(!file_exists($filename)) {
    fopen($filename, 'w') or die("Can't create file");
}

if(file_exists(_APPROOT_.".dockerenv")) {
    $docker = true;
}

$Config = $container->get(Config::class);
$Sonoff = $container->get(Sonoff::class);
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
    _LANGDIR_."en/lang.ini",
    _TMPDIR_.'cache/i18n/'
);
$langHelper->dumpJson();

if((isset($_SESSION[ "login" ]) && $_SESSION[ "login" ] == "1") || $Config->read("login") == "0") {
    $loggedin = true;
}

function __($string, $category = null, $args = null)
{
    $cat = "";
    if(isset($category) && !empty($category)) {
        $cat = $category."_";
    }
    $txt = $cat.$string;
    return __L($txt, $args);
}
