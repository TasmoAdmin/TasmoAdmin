<?php

namespace TasmoAdmin\Helper;

use TasmoAdmin\Config;

class UrlHelper
{
	public static function STYLES(string $filename, $csspath = NULL )
	{
		if( empty( $csspath ) ) {
			$cssreal = _RESOURCESDIR_."css/";
			$csspath = _RESOURCESURL_."css/";
		}


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
			$filepath = $csspath.$path.$cacheTag;
		} else {
			$filepath = $csspath.$filename.".css".$cacheTag;
		}
		

		return $filepath;
	}


	public static function JS(string $filename) {
		$jsreal = _RESOURCESDIR_."js/";
		$jspath = _RESOURCESURL_."js/";

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
