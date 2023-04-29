<?php

error_reporting( E_ALL );
ini_set('display_errors', 1);


if( !function_exists( "curl_init" ) ) {
    echo "ERROR: PHP cURL is missing.";
    echo "Please enable PHP cURL extension and restart web-server.";
    die();
}

if( !class_exists( "ZipArchive" ) ) {
    echo "ERROR: PHP Zip is missing.";
    echo "Please enable PHP Zip extension and restart web-server.";
    die();
}


$subdir = dirname( $_SERVER[ 'PHP_SELF' ] )."/";
$subdir = $subdir = str_replace( "\\", "/", $subdir );
$subdir = $subdir == "//" ? "/" : $subdir;

if ($baseurl_from_env = getenv('TASMO_BASEURL')) {
  $subdir = $baseurl_from_env;
}

define( "_BASEURL_", $subdir );
define( '_APPROOT_', dirname( dirname( __FILE__ ) ).'/' );
define( "_TMPDIR_",  getenv('TASMO_TMPDIR') ?: _APPROOT_."tmp/" );

define( "_RESOURCESURL_", _BASEURL_."resources/" );
define( "_INCLUDESDIR_", _APPROOT_."includes/" );
define( "_HELPERSDIR_", _APPROOT_."helpers/" );
define( "_RESOURCESDIR_", _APPROOT_."resources/" );
define( "_LIBSDIR_", _APPROOT_."libs/" );
define( "_PAGESDIR_", _APPROOT_."pages/" );
define( "_DATADIR_", getenv('TASMO_DATADIR') ?: _APPROOT_."data/" );
define( "_LANGDIR_", _APPROOT_."lang/" );
define( "_CSVFILE_", _DATADIR_."devices.csv" );


session_save_path( _TMPDIR_."sessions" );
ini_set( 'session.gc_maxlifetime', 356*24*60*60 );
session_set_cookie_params( 356*24*60*60 );
session_name( "TASMO_SESSION" );
session_start();

global $loggedin, $docker;
$loggedin = FALSE;
$docker   = FALSE;

require_once _APPROOT_ . 'vendor/autoload.php';

use Selective\Container\Container;
use TasmoAdmin\Backup\BackupHelper;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\JsonLanguageHelper;
use TasmoAdmin\Helper\FirmwareFolderHelper;
use TasmoAdmin\Sonoff;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/** @var Container $container */
$container = require  _APPROOT_ . 'includes/container.php';

$debug = isset($_SERVER['TASMO_DEBUG']);
if ($debug) {
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler);
    $whoops->register();
}

$filename = _CSVFILE_; //csv file name
if( !file_exists( $filename ) ) {
    fopen( $filename, 'w' ) or die( "Can't create file" );
}

if( file_exists( _APPROOT_.".dockerenv" ) ) {
    $docker = TRUE;
}

$Config = $container->get(Config::class);

if( !empty( $_REQUEST[ "clean" ] ) ) {
    cleanTemps($Config);
}

$Sonoff = $container->get(Sonoff::class);
$i18n = $container->get(i18n::class);

$i18n->setCachePath( _TMPDIR_.'cache/i18n/' );
$i18n->setFilePath( _LANGDIR_.'{LANGUAGE}/lang.ini' ); // language file path
$i18n->setFallbackLang( 'en' );
$i18n->setPrefix( '__L' );
$i18n->setSectionSeparator( '_' );
$i18n->setMergeFallback( true ); // make keys available from the fallback language
$i18n->init();

$lang = $i18n->getAppliedLang();

$langHelper = new JsonLanguageHelper(
    $lang,
    _LANGDIR_."{$lang}/lang.ini",
    'en',
    _LANGDIR_."en/lang.ini",
    _TMPDIR_.'cache/i18n/');
$langHelper->dumpJson();

if( ( isset ( $_SESSION[ "login" ] ) && $_SESSION[ "login" ] == "1" ) || $Config->read( "login" ) == "0" ) {
    $loggedin = TRUE;
}

function __( $string, $category = NULL, $args = NULL ) {
    $cat = "";
    if( isset( $category ) && !empty( $category ) ) {
        $cat = $category."_";
    }
    $txt = $cat.$string;
    return __L($txt, $args);
}

if( isset( $_GET ) ) {
    if( isset( $_GET[ "doAjax" ] ) ) {
        session_write_close(); //stop blocking other ajax biatch
        if( isset( $_REQUEST[ "target" ] ) ) {
            $data = $Sonoff->setDeviceValue( $_REQUEST[ "id" ], $_REQUEST[ "field" ], $_REQUEST[ "newvalue" ] );
        } else {
            $data = $Sonoff->doAjax($_REQUEST["id"], urldecode($_REQUEST['cmnd']));
        }
        header( 'Content-Type: application/json' );
        echo json_encode( $data );
        die();
    }
    if( isset( $_GET[ "doAjaxAll" ] ) ) {
        session_write_close(); //stop blocking other ajax biatch
        $data = $Sonoff->doAjaxAll();

        header( 'Content-Type: application/json' );
        echo json_encode( $data );
        die();
    }

    if (isset($_GET['downloadBackup'])) {
        $backup = $container->get(BackupHelper::class);

        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="tasmota-backup.zip"');
        header('Content-Length: '.filesize($backup->getBackupZipPath()));
        ob_clean();
        flush();
        readfile($backup->getBackupZipPath());
        die();
    }
}

function debug( $data ) {
    echo "<pre style='background-color: black; color:#6eda6e; max-height: 300px; margin:0px; padding: 0px; font-size: 12px; overflow: auto;'>";
    print_r( $data ); // or var_dump($data);
    echo "</pre>";
}


function cleanTemps(Config $config) {
    if( $config->read( "login" ) === "1" && ( empty( $_SESSION[ "login" ] ) || $_SESSION[ "login" ] != "1" ) ) {
        debug( "pls login before you clean" );
        echo "<a href='/'>Back to login</a>";
        die();
    }

    debug( "start cleaning" );

    $what = explode( "_", $_REQUEST[ "clean" ] );
    //sessions

    if( in_array( "sessions", $what ) ) {
        debug( "cleanup sessions dir" );
        $files = glob( _TMPDIR_."/sessions/*" ); // get all file names
        foreach( $files as $file ) { // iterate files
            if( is_file( $file ) && strpos( $file, ".empty" ) === FALSE ) {
                @unlink( $file );
            } // delete file
        }
    }


    if( in_array( "i18n", $what ) ) {
        debug( "cleanup i18n dir" );
        $files = glob( _TMPDIR_.'/cache/i18n/*' ); // get all file names present in folder
        foreach( $files as $file ) { // iterate files
            if( is_file( $file ) ) {
                @unlink( $file );
            }
        }
    }

    //firmwares
    if( in_array( "firmwares", $what ) ) {
        debug( "cleanup firmwares dir" );
        FirmwareFolderHelper::clean(_DATADIR_ . "firmwares/");
    }


		if( in_array( "config", $what ) ) {
			debug( "cleanup config" );
			$files = glob( _DATADIR_.'/*' ); // get all file names
			foreach( $files as $file ) { // iterate files
				if( is_file( $file ) && ( strpos( $file, "MyConfig.json" ) || strpos( $file, "MyConfig.php" ) ) ) {
					@unlink( $file );
				} // delete file
			}
			session_destroy();
		}
		if( in_array( "devices", $what ) ) {
			debug( "cleanup devices" );
			$files = glob( _DATADIR_.'/*' ); // get all file names
			foreach( $files as $file ) { // iterate files
				if( is_file( $file ) && ( strpos( $file, "devices.csv" ) ) ) {
					@unlink( $file );
				} // delete file
			}
		}

		debug( "done cleaning" );
		echo "<a href='/'>Back to start</a>";
		die();
	}
