<?php

class UrlHelper
{
	public static function STYLES(string $filename, $csspath = null)
	{
		if($csspath === null) {
			$csspath = _RESOURCESURL_."css/";
		}

		$cssreal = $csspath;

		$config = new Config();

		$cacheTag = time();
		$min      = "";
		if( $config->read( "minimize_resources" ) === "1" ) {
			$cacheTag = $config->read( "current_git_tag" );
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


	public static function JS(string $filename, $jspath = null) {
		if ($jspath === null) {
			$jspath = _RESOURCESURL_."js/";
		}

		$jsreal = $jspath;

		$config = new Config();

		$cacheTag = time();
		$min      = "";
		if( $config->read( "minimize_resources" ) === "1" ) {
			$cacheTag = $config->read( "current_git_tag" );
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
