<?php
	error_reporting( E_ALL );
	ini_set( 'display_errors', '1' );

	ini_set( 'session.gc_maxlifetime', 30*24*60*60 );
	session_set_cookie_params( 30*24*60*60 );
	session_start();

	global $loggedin, $docker;
	$loggedin = FALSE;
	$docker   = FALSE;
	if( !function_exists( "curl_init" ) ) {
		echo "ERROR: PHP Curl is missing.";
		echo "Please install PHP Curl";
		echo "sudo apt-get install php7.0-curl";
		echo "and restart webserver";
		die();
	}

	if( !class_exists( "ZipArchive" ) ) {
		echo "ERROR: PHP Zip is missing.";
		echo "Please install PHP Zip";
		echo "sudo apt-get install php7.0-zip";
		echo "and restart webserver";
		die();
	}
	$subdir = dirname( $_SERVER[ 'PHP_SELF' ] )."/";

	$subdir
		    = $subdir = str_replace( "\\", "/", $subdir );
	$subdir = $subdir == "//" ? "/" : $subdir;


	define( "_BASEURL_", $subdir );
	define( "_RESOURCESURL_", _BASEURL_."resources/" );


	define( '_APPROOT_', dirname( dirname( __FILE__ ) ).'/' );
	define( "_INCLUDESDIR_", _APPROOT_."includes/" );
	define( "_RESOURCESDIR_", _APPROOT_."resources/" );
	define( "_LIBSDIR_", _APPROOT_."libs/" );
	define( "_PAGESDIR_", _APPROOT_."pages/" );
	define( "_DATADIR_", _APPROOT_."data/" );
	define( "_LANGDIR_", _APPROOT_."lang/" );
	define( "_TMPDIR_", _APPROOT_."tmp/" );
	define( "_CSVFILE_", _DATADIR_."devices.csv" );


	$filename = _CSVFILE_; //csv file name
	if( !file_exists( $filename ) ) {
		fopen( $filename, 'w' ) or die( "Can't create file" );
	}

	if( file_exists( _APPROOT_.".dockerenv" ) ) {
		$docker = TRUE;
	}

	/**
	 * @property Sonoff Sonoff
	 */
	require_once _INCLUDESDIR_."Config.php";
	require_once _INCLUDESDIR_."Sonoff.php";
	require_once _LIBSDIR_.'phpi18n/i18n.class.php';
	require_once _INCLUDESDIR_."Config.php";

	$i18n = new i18n();

	$lang = isset( $_GET[ "lang" ] ) ? $_GET[ "lang" ] : NULL;
	if( isset( $lang ) ) {
		$_SESSION[ 'lang' ] = $lang;
		header( "Location: ".$_SERVER[ "HTTP_REFERER" ] );
	}


	$i18n->setCachePath( _TMPDIR_.'cache/i18n/' );
	$i18n->setFilePath( _LANGDIR_.'lang_{LANGUAGE}.ini' ); // language file path
	$i18n->setFallbackLang( 'en' );
	$i18n->setPrefix( '__L' );
	$i18n->setSectionSeperator( '_' );
	$i18n->setMergeFallback( TRUE ); // make keys available from the fallback language
	$i18n->init();

	$lang = $i18n->getAppliedLang();


	$Config = new Config();
	$Sonoff = new Sonoff();


	if( ( isset ( $_SESSION[ "login" ] ) && $_SESSION[ "login" ] == "1" ) || $Config->read( "login" ) == "0" ) {
		$loggedin = TRUE;
	}

	function __( $string, $category = NULL, $args = NULL ) {
		$cat = "";
		if( isset( $category ) && !empty( $category ) ) {
			$cat = $category."_";
		}
		$txt        = $cat.$string;
		$translated = @__L::$txt( $args );

		if( $translated == "" ) {
			$translated = $category."::".$string;
			//			$myfile = fopen( _LANGDIR_."lang_new.ini", "a" ) or die( "Unable to open file!" );
			//			fwrite( $myfile, $txt."\n" );
			//			fclose( $myfile );
			//			$files = glob( _TMPDIR_.'cache/i18n/*' ); // get all file names
			//			foreach ( $files as $file ) { // iterate files
			//				if ( is_file( $file ) ) {
			//					//unlink( $file );
			//				}
			//			}

		}

		return $translated;
	}

	if( isset( $_GET ) ) {
		if( isset( $_GET[ "doAjax" ] ) ) {
			session_write_close(); //stop blocking other ajax biatch
			if( isset( $_REQUEST[ "target" ] ) ) {
				$data = $Sonoff->setDeviceValue( $_REQUEST[ "id" ], $_REQUEST[ "field" ], $_REQUEST[ "newvalue" ] );
			} else {
				$data = $Sonoff->doAjax();
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
	}

	function debug( $data ) {
		echo "<pre style='background-color: black; color:green; max-height: 300px; margin:0px; padding: 0px; font-size: 12px;'>";
		print_r( $data ); // or var_dump($data);
		echo "</pre>";
	}