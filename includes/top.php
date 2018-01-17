<?php
	ini_set( 'session.gc_maxlifetime', 2678400 );
	session_start();
	
	if ( !function_exists( 'curl_version' ) ) {
		die( "CURL is required, please install CURL for your Webserver!" );
	}
	
	define( "_APPROOT_", "./" );
	define( "_RESOURCESDIR_", _APPROOT_."resources/" );
	define( "_INCLUDESDIR_", _APPROOT_."includes/" );
	define( "_LIBSDIR_", _APPROOT_."libs/" );
	define( "_PAGESDIR_", _APPROOT_."pages/" );
	define( "_DATADIR_", _APPROOT_."data/" );
	define( "_LANGDIR_", _APPROOT_."lang/" );
	define( "_TMPDIR_", _APPROOT_."tmp/" );
	
	/**
	 * @property Sonoff Sonoff
	 */
	require_once _INCLUDESDIR_."Config.php";
	require_once _INCLUDESDIR_."Sonoff.php";
	require_once _LIBSDIR_.'phpi18n/i18n.class.php';
	require_once _INCLUDESDIR_."Config.php";
	
	$i18n = new i18n();
	
	$lang = isset( $_GET[ "lang" ] ) ? $_GET[ "lang" ] : NULL;
	if ( isset( $lang ) ) {
		$_SESSION[ 'lang' ] = $lang;
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
	
	
	function __( $string, $category = NULL, $args = NULL ) {
		$cat = "";
		if ( isset( $category ) && !empty( $category ) ) {
			$cat = $category."_";
		}
		$txt        = $cat.$string;
		$translated = @__L::$txt( $args );
		
		if ( $translated == "" ) {
			$myfile = fopen( _LANGDIR_."lang_new.ini", "a" ) or die( "Unable to open file!" );
			$txt = "";
			if ( $category != "" ) {
				$txt .= "\n[".$category."]\n";
			}
			$txt .= $string."\n";
			//$txt .= $string." = \"MISSING_TRANSLATION\"\n";
			fwrite( $myfile, $txt );
			fclose( $myfile );
			$translated = $txt;//"MISSING_TRANSLATION";
			$files      = glob( _TMPDIR_.'cache/i18n/*' ); // get all file names
			foreach ( $files as $file ) { // iterate files
				if ( is_file( $file ) ) {
					unlink( $file );
				}
			}
			
		}
		
		return $translated;
	}
	
	
	$Sonoff = new Sonoff();
	
	if ( isset( $_GET[ "doAjax" ] ) && !empty( $_GET[ "doAjax" ] ) ) {
		
		$action = $_GET[ "doAjax" ];
		
		
		$result = $Sonoff->doAjax( $action );
		
		echo json_encode( $result );
		exit;
	}