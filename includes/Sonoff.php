<?php
	
	
	/**
	 * Class Sonoff
	 */
	class Sonoff {
		
		
		/**
		 * @param $ip
		 *
		 * @return mixed
		 */
		public function getAllStatus( $ip ) {
			$cmnd = "Status 0";
			
			
			$status = $this->doRequest( $ip, $cmnd );
			
			return $status;
		}
		
		public function toggle( $ip ) {
			$cmnd = "Status 0";
			
			$status = $this->doRequest( $ip, $cmnd );
			
			return $status;
		}
		
		
		public function saveConfig( $ip, $backlog ) {
			$status = $this->doRequest( $ip, $backlog );
			
			return $status;
		}
		
		/**
		 * @param     $ip
		 * @param int $level
		 * @param int $try
		 *
		 * @return mixed
		 */
		public function setWebLog( $ip, $level = 2, $try = 1 ) {
			$cmnd = "Weblog ".$level;
			
			$weblog = $this->doRequest( $ip, $cmnd, $try );
			
			return $weblog;
		}
		
		
		/**
		 *
		 * This fixes wrong formated json answer form Tasmota Version 5.10.0
		 * Example wrong format: dev/json_error_5100.json
		 *
		 * @param $string
		 *
		 * @return mixed
		 */
		private function fixJsonFormatV5100( $string ) {
			
			
			$string = substr( $string, strpos( $string, "STATUS = " ) );
			if ( strpos( $string, "POWER = " ) !== FALSE ) {
				$string = substr( $string, strpos( $string, "{" ) );
				$string = substr( $string, 0, strrpos( $string, "}" ) + 1 );
			}
			if ( strpos( $string, "ERGEBNIS = " ) !== FALSE ) {
				$string = substr( $string, strpos( $string, "{" ) );
				$string = substr( $string, 0, strrpos( $string, "}" ) + 1 );
			}
			if ( strpos( $string, "RESULT = " ) !== FALSE ) {
				$string = substr( $string, strpos( $string, "{" ) );
				$string = substr( $string, 0, strrpos( $string, "}" ) + 1 );
			}
			
			
			$remove  = [
				PHP_EOL,
				"\n",
				"STATUS = ",
				"}STATUS1 = {",
				"}STATUS2 = {",
				"}STATUS3 = {",
				"}STATUS4 = {",
				"}STATUS5 = {",
				"}STATUS6 = {",
				"}STATUS7 = {",
				"}in = {",
				"}STATUS8 = {",
				"}STATUS9 = {",
				"}STATUS10 = {",
				"}STATUS11 = {",
				"STATUS2 = ",
			];
			$replace = [
				"",
				"",
				"",
				",",
				",",
				",",
				",",
				",",
				",",
				",",
				",",
				",",
				",",
				",",
				",",
				"",
			];
			
			$string = str_replace( $remove, $replace, $string );
			
			
			return $string;
		}
		
		
		/**
		 * @param $ip
		 * @param $cmnd
		 *
		 * @return mixed|string
		 */
		private function buildCmndUrl( $ip, $cmnd ) {
			$url = "http://".$ip."/cm?cmnd=".$cmnd;
			$url = str_replace( " ", "%20", $url );
			
			return $url;
		}
		
		
		/**
		 * @param     $ip
		 * @param     $cmnd
		 * @param int $try
		 *
		 * @return mixed
		 */
		private function doRequest( $ip, $cmnd, $try = 1 ) {
			$url = $this->buildCmndUrl( $ip, $cmnd );
			
			$result = NULL;
			
			
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$result = curl_exec( $ch );
			
			
			if ( !$result ) {
				$data        = new stdClass();
				$data->ERROR = __( "CURL_ERROR", "API" )." => ".curl_errno( $ch ).": ".curl_error( $ch );
			} else {
				
				$data = json_decode( $result );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					$result = $this->fixJsonFormatv5100( $result );
					$data   = json_decode( $result );
					if ( json_last_error() !== JSON_ERROR_NONE ) {
						$data        = new stdClass();
						$data->ERROR = __( "JSON_ERROR", "API" )." => ".json_last_error().": ".json_last_error_msg();
						$data->ERROR .= "<br/><strong>".__( "JSON_ERROR_CONTACT_DEV", "API", [ $result ] )."</strong>";
						$data->ERROR .= "<br/>".__( "JSON_ANSWER", "API" )." => ".print_r( $result, TRUE );
						
					}
				}
				
				if ( isset( $data->WARNING ) && !empty( $data->WARNING ) && $try == 1 ) {
					$try++;
					//set web log level 2 and try again
					$webLog = $this->setWebLog( $ip, 2, $try );
					if ( !isset( $webLog->WARNING ) && empty( $webLog->WARNING ) ) {
						$data = $this->doRequest( $ip, $cmnd, $try );
					}
				}
				
			}
			
			curl_close( $ch );
			
			return $data;
		}
		
		public function doAjax( $url, $try = 1 ) {
			
			$result = NULL;
			$ch     = curl_init();
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$result = curl_exec( $ch );
			
			if ( !$result ) {
				$data        = new stdClass();
				$data->ERROR = __( "CURL_ERROR" )." => ".curl_errno( $ch ).": ".curl_error( $ch );
			} else {
				
				$data = json_decode( $result );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					$result = $this->fixJsonFormatv5100( $result );
					$data   = json_decode( $result );
					if ( json_last_error() !== JSON_ERROR_NONE ) {
						$data        = new stdClass();
						$data->ERROR = __( "JSON_ERROR", "API" )." => ".json_last_error().": ".json_last_error_msg();
						$data->ERROR .= "<br/><strong>".__( "JSON_ERROR_CONTACT_DEV", "API", [ $result ] )."</strong>";
						$data->ERROR .= "<br/>".__( "JSON_ANSWER", "API" )." => ".print_r( $result, TRUE );
						
					}
				}
				
				if ( isset( $data->WARNING ) && !empty( $data->WARNING ) && $try < 1 ) {
					$try++;
					//set web log level 2 and try again
					$webLog = $this->setWebLog( parse_url( $url, PHP_URL_HOST ), 2, $try );
					if ( !isset( $webLog->WARNING ) && empty( $webLog->WARNING ) ) {
						$data = $this->doAjax( $url, $try );
					}
				}
			}
			
			curl_close( $ch );
			
			return $data;
		}
	}
	
	
	