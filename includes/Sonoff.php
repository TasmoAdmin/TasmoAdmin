<?php
	
	
	/**
	 * Class Sonoff
	 */
	class Sonoff {
		
		public function getDeviceById( $id = NULL ) {
			if ( !isset( $id ) || empty( $id ) ) {
				return NULL;
			}
			$file = fopen( _CSVFILE_, 'r' );
			while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
				if ( $line[ 0 ] == $id ) {
					$device = $this->createDeviceObject( $line );
					break;
				}
			}
			fclose( $file );
			
			return $device;
		}
		
		public function getDevices() {
			
			$devices = [];
			$file    = fopen( _CSVFILE_, 'r' );
			while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
				$devices[] = $this->createDeviceObject( $line );
			}
			fclose( $file );
			
			return $devices;
		}
		
		private function createDeviceObject( $deviceLine = [] ) {
			if ( !isset( $deviceLine ) || empty( $deviceLine ) ) {
				return NULL;
			}
			
			$device           = new stdClass();
			$deviceLine[ 1 ]  = explode( "|", $deviceLine[ 1 ] );
			$device->id       = isset( $deviceLine[ 0 ] ) ? $deviceLine[ 0 ] : FALSE;
			$device->names    = isset( $deviceLine[ 1 ] ) ? $deviceLine[ 1 ] : FALSE;
			$device->ip       = isset( $deviceLine[ 2 ] ) ? $deviceLine[ 2 ] : FALSE;
			$device->username = isset( $deviceLine[ 3 ] ) ? $deviceLine[ 3 ] : FALSE;
			$device->password = isset( $deviceLine[ 4 ] ) ? $deviceLine[ 4 ] : FALSE;
			$device->img      = isset( $deviceLine[ 5 ] ) ? $deviceLine[ 5 ] : "bulb_1";
			
			return $device;
		}
		
		
		/**
		 * @param $ip
		 *
		 * @return mixed
		 */
		public function getAllStatus( $device ) {
			$cmnd = "Status 0";
			
			
			$status = $this->doRequest( $device, $cmnd );
			
			return $status;
		}
		
		/**
		 * @param $ip
		 *
		 * @return mixed
		 */
		public function getNTPStatus( $device ) {
			$cmnd = "NtpServer1";
			
			
			$status = $this->doRequest( $device, $cmnd );
			
			return $status;
		}
		
		public function toggle( $device ) {
			$cmnd = "Status 0";
			
			$status = $this->doRequest( $device, $cmnd );
			
			return $status;
		}
		
		
		public function saveConfig( $device, $backlog ) {
			$status = $this->doRequest( $device, $backlog );
			
			return $status;
		}
		
		/**
		 * @param     $ip
		 * @param int $level
		 * @param int $try
		 *
		 * @return mixed
		 */
		public function setWebLog( $device, $level = 2, $try = 1 ) {
			$cmnd = "Weblog ".$level;
			
			$weblog = $this->doRequest( $device, $cmnd, $try );
			
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
		private function buildCmndUrl( $device, $cmnd ) {
			$start = "?";
			if ( isset( $device->password ) && $device->password != "" ) {
				$start = "?user=".urlencode( $device->username )."&password=".urlencode( $device->password )."&";
			}
			$url = "http://".$device->ip."/cm".$start."cmnd=".urlencode( $cmnd );
			
			
			return $url;
		}
		
		
		/**
		 * @param     $ip
		 * @param     $cmnd
		 * @param int $try
		 *
		 * @return mixed
		 */
		private function doRequest( $device, $cmnd, $try = 1 ) {
			$url = $this->buildCmndUrl( $device, $cmnd );
			
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
					$webLog = $this->setWebLog( $device, 2, $try );
					if ( !isset( $webLog->WARNING ) && empty( $webLog->WARNING ) ) {
						$data = $this->doRequest( $device, $cmnd, $try );
					}
				}
				
			}
			
			curl_close( $ch );
			
			return $data;
		}
		
		
		public function doAjax( $try = 1 ) {
			$device = $this->getDeviceById( $_GET[ "id" ] );
			$url    = $this->buildCmndUrl(
				$device,
				urldecode( $_GET[ "cmnd" ] )
			);
			
			
			//			if ( $_GET[ "id" ] == 3 ) {
			//				$url = "http://sonweb/dev/DHT11.json";
			//			}
			
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
						curl_close( $ch );
						$data = $this->doAjax( $url, $try );
					}
				}
			}
			
			curl_close( $ch );
			
			return $data;
		}
	}
	
	
	