<?php

	error_reporting( E_ALL );
	ini_set( 'display_errors', '1' );


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

  if ($baseurl_from_env = getenv('TASMO_BASEURL')) {
	  $subdir = $baseurl_from_env;
	}

	define( "_BASEURL_", $subdir );
	define( '_APPROOT_', dirname( dirname( __FILE__ ) ).'/' );
	define( "_TMPDIR_", _APPROOT_."tmp/" );

	define( "_RESOURCESURL_", _BASEURL_."resources/" );
	define( "_INCLUDESDIR_", _APPROOT_."includes/" );
	define( "_HELPERSDIR_", _APPROOT_."helpers/" );
	define( "_RESOURCESDIR_", _APPROOT_."resources/" );
	define( "_LIBSDIR_", _APPROOT_."libs/" );
	define( "_PAGESDIR_", _APPROOT_."pages/" );
	define( "_DATADIR_", _APPROOT_."data/" );
	define( "_LANGDIR_", _APPROOT_."lang/" );
	define( "_CSVFILE_", _DATADIR_."devices.csv" );


	session_save_path( _APPROOT_."tmp/sessions/" );

	ini_set( 'session.gc_maxlifetime', 356*24*60*60 );
	session_set_cookie_params( 356*24*60*60 );
	session_name( "TASMO_SESSION" );
	session_start();

	//	setcookie( session_name(), session_id(), time()+30*24*60*60 );
	global $loggedin, $docker;
	$loggedin = FALSE;
	$docker   = FALSE;


	function autoloadsystem( $class ) {

		$filename = _HELPERSDIR_.strtolower( $class ).".php";
		if( file_exists( $filename ) ) {
			require $filename;
		} else {
			$filename = _HELPERSDIR_.$class.".php";
			if( file_exists( $filename ) ) {
				require $filename;
			}
		}


		$filename = _INCLUDESDIR_.$class.".php";
		if( file_exists( $filename ) ) {
			require $filename;
		} else {
			$filename = _INCLUDESDIR_.strtolower( $class ).".php";
			if( file_exists( $filename ) ) {
				require $filename;
			}
		}
	}

	spl_autoload_register( "autoloadsystem" );


	if( !empty( $_REQUEST[ "clean" ] ) ) {
		cleanTemps();
	}


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
	require_once _LIBSDIR_.'phpi18n/i18n.class.php';

	$Config = new Config();
	$Sonoff = new Sonoff();
	$i18n   = new i18n();

	$lang = isset( $_GET[ "lang" ] ) ? $_GET[ "lang" ] : NULL;
	if( isset( $lang ) ) {
		$_SESSION[ 'lang' ] = $lang;
		header(
			"Location: ".( empty( $_SERVER[ "HTTP_REFERER" ] ) ? $_SERVER[ "REDIRECT_BASE" ]
				: $_SERVER[ "HTTP_REFERER" ] )
		);
	}


	$i18n->setCachePath( _TMPDIR_.'cache/i18n/' );
	$i18n->setFilePath( _LANGDIR_.'lang_{LANGUAGE}.ini' ); // language file path
	$i18n->setFallbackLang( 'en' );
	$i18n->setPrefix( '__L' );
	$i18n->setSectionSeperator( '_' );
	$i18n->setMergeFallback( TRUE ); // make keys available from the fallback language
	$i18n->init();

	$lang = $i18n->getAppliedLang();


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
		echo "<pre style='background-color: black; color:#6eda6e; max-height: 300px; margin:0px; padding: 0px; font-size: 12px; overflow: auto;'>";
		print_r( $data ); // or var_dump($data);
		echo "</pre>";
	}


	function cleanTemps() {

		$Config = new Config();

		if( $Config->read( "login" ) == "1" && ( empty( $_SESSION[ "login" ] ) || $_SESSION[ "login" ] != "1" ) ) {
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
			$files = glob( _DATADIR_.'firmwares/*' ); // get all file names
			foreach( $files as $file ) { // iterate files
				if( is_file( $file ) && strpos( $file, ".empty" ) === FALSE
				    && strpos( $file, ".htaccess" ) === FALSE ) {
					@unlink( $file );
				} // delete file
			}
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
