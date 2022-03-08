<?php

class UrlHelper
{

    public static function redirect($url = NULL, $status)
    {
        header('Location: ' . DIR . $url, TRUE, $status);
        exit;
    }

    public static function halt($status = 404, $message = 'Something went wrong.')
    {
        if (ob_get_level() !== 0) {
            ob_clean();
        }

			http_response_code( $status );
			$data[ 'status' ]  = $status;
			$data[ 'message' ] = $message;

			if( !file_exists( "views/error/$status.php" ) ) {
				$status = 'default';
			}
			require "views/error/$status.php";

			exit;
		}

		public static function STYLES($filename, $csspath = null) {
			if($csspath === null) {
				$csspath = _RESOURCESURL_."css/";
			}

			$cssreal = $csspath;

			$Config = new Config();

			$cacheTag = time();
			$min      = "";
			if( $Config->read( "minimize_resources" ) === "1" ) {
				$cacheTag = $Config->read( "current_git_tag" );
				if( empty( $cacheTag ) ) {
					$cacheTag = time();
				}
				$min = ".min";
			}
			$cacheTag = str_replace( ".", "", $cacheTag );
			$cacheTag = "?_=".$cacheTag;


			$path = $filename.$min.".css";
			if( file_exists( $cssreal.$path ) ) {
				$filepath = $cssreal.$path.$cacheTag;
			} else {
				$filepath = $csspath.$filename.".css".$cacheTag;
			}
		

			return $filepath;
		}


		public static function JS($filename, $jspath = null) {
			if ($jspath === null) {
				$jspath = _RESOURCESURL_."js/";
			}

			$jsreal = $jspath;

			$Config = new Config();

			$cacheTag = time();
			$min      = "";
			if( $Config->read( "minimize_resources" ) === "1" ) {
				$cacheTag = $Config->read( "current_git_tag" );
				if( empty( $cacheTag ) ) {
					$cacheTag = time();
				}
				$min = ".min";
			}
			$cacheTag = str_replace( ".", "", $cacheTag );
			$cacheTag = "?_=".$cacheTag;


			$path = $filename.$min.".js";
			if( file_exists( $jsreal.$path ) ) {
				$filepath = $jspath.$path.$cacheTag;
			} else {
				$filepath = $jspath.$filename.".js".$cacheTag;
			}
		
			return $filepath;
		}
	}
