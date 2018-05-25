<?php


	/**
	 * Class Sonoff
	 */
	class Sonoff {

		public function getDeviceById( $id = NULL ) {
			if( !isset( $id ) || empty( $id ) ) {
				return NULL;
			}
			$file = fopen( _CSVFILE_, 'r' );
			while( ( $line = fgetcsv( $file ) ) !== FALSE ) {
				if( $line[ 0 ] == $id ) {
					$device = $this->createDeviceObject( $line );
					break;
				}
			}
			fclose( $file );

			return $device;
		}


		public function getDevices( $orderBy = "position" ) {

			$devices = [];

			$file = fopen( _CSVFILE_, 'r' );
			while( ( $line = fgetcsv( $file ) ) !== FALSE ) {
				$devices[] = $this->createDeviceObject( $line );


			}
			fclose( $file );

			if( $orderBy == "position" ) {
				$devicesTmp = [];
				$update     = FALSE;
				foreach( $devices as $device ) {
					if( $device->position == "" ) {
						$device->position = 1;
						$update           = TRUE;
					}
					while( isset( $devicesTmp[ $device->position ] ) ) {
						$device->position++;
					}
					if( $update ) {
						$this->setDeviceValue( $device->id, "position", $device->position );
					}
					$devicesTmp[ $device->position ] = $device;
				}
				ksort( $devicesTmp );
				$devices = $devicesTmp;
				unset( $devicesTmp );
			}

			return $devices;
		}

		private function createDeviceObject( $deviceLine = [] ) {
			if( !isset( $deviceLine ) || empty( $deviceLine ) ) {
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
			$device->position = isset( $deviceLine[ 6 ] ) && $deviceLine[ 6 ] != "" ? $deviceLine[ 6 ] : "";

			return $device;
		}

		public function setDeviceValue( $id = NULL, $field = NULL, $value = NULL ) {
			if( !isset( $id ) || empty( $id ) ) {
				return NULL;
			}
			$device = NULL;
			$file   = fopen( _CSVFILE_, 'r' );
			while( ( $line = fgetcsv( $file ) ) !== FALSE ) {
				if( $line[ 0 ] == $id ) {
					$device = $this->createDeviceObject( $line );
					break;
				}
			}
			fclose( $file );
			$device->$field = $value;
			$device         = $this->updateDevice( $device );

			return $device;
		}

		public function updateDevice( $device = NULL ) {
			if( !isset( $device ) || empty( $device ) || !isset( $device->id ) || empty( $device->id ) ) {
				return NULL;
			}
			$deviceArr[ 0 ] = $device->id;
			$deviceArr[ 1 ] = implode( "|", isset( $device->names ) && !empty( $device->names ) ? $device->names : [] );
			$deviceArr[ 2 ] = isset( $device->ip ) && !empty( $device->ip ) ? $device->ip : "";
			$deviceArr[ 3 ] = isset( $device->username ) && !empty( $device->username ) ? $device->username : "";
			$deviceArr[ 4 ] = isset( $device->password ) && !empty( $device->password ) ? $device->password : "";
			$deviceArr[ 5 ] = isset( $device->img ) && !empty( $device->img ) ? $device->img : "";
			$deviceArr[ 6 ] = isset( $device->position ) && !empty( $device->position ) ? $device->position : "";

			foreach( $deviceArr as $key => $field ) {
				if( is_array( $field ) ) {
					foreach( $field as $subkey => $subfield ) {
						$deviceArr[ $key ][ $field ][ $subkey ] = trim( $subfield );
					}
				} else {

					$deviceArr[ $key ] = trim( $field );
				}
			}

			$tempfile = @tempnam( _TMPDIR_, "tmp" ); // produce a temporary file name, in the current directory


			if( !$input = fopen( _CSVFILE_, 'r' ) ) {
				die( __( "ERROR_CANNOT_READ_CSV_FILE", "DEVICE_ACTIONS", [ "csvFilePath" => _CSVFILE_ ] ) );
			}
			if( !$output = fopen( $tempfile, 'w' ) ) {
				die( __( "ERROR_CANNOT_CREATE_TMP_FILE", "DEVICE_ACTIONS", [ "tmpFilePath" => $tempfile ] ) );
			}

			while( ( $data = fgetcsv( $input ) ) !== FALSE ) {
				if( $data[ 0 ] == $deviceArr[ 0 ] ) {
					$data = $deviceArr;
				}
				fputcsv( $output, $data );
			}

			fclose( $input );
			fclose( $output );

			unlink( _CSVFILE_ );
			rename( $tempfile, _CSVFILE_ );

			return $this->createDeviceObject( $deviceArr );
		}

		public function addDevice( $device = [] ) {
			die( "not done yet" ); //todo: use this to add device, decide if array or object param
			if( !isset( $device ) || empty( $device ) || !isset( $device[ "id" ] ) || empty( $device[ "id" ] ) ) {
				return NULL;
			}

			$fp          = file( _CSVFILE_ );
			$device[ 0 ] = isset( $device->id ) && !empty( $device->id ) ? $device->id : count( $fp )+1;
			$device[ 1 ] = implode( "|", isset( $device->names ) && !empty( $device->names ) ? $device->names : [] );
			$device[ 2 ] = isset( $device->ip ) && !empty( $device->ip ) ? $device->ip : "";
			$device[ 3 ] = isset( $device->username ) && !empty( $device->username ) ? $device->username : "";
			$device[ 4 ] = isset( $device->password ) && !empty( $device->password ) ? $device->password : "";
			$device[ 5 ] = isset( $device->img ) && !empty( $device->img ) ? $device->img : "";
			$device[ 6 ] = isset( $device->position ) && !empty( $device->position ) ? $device->position : "";


			$handle = fopen( _CSVFILE_, "a" );
			fputcsv( $handle, $device );
			fclose( $handle );
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
			if( strpos( $string, "POWER = " ) !== FALSE ) {
				$string = substr( $string, strpos( $string, "{" ) );
				$string = substr( $string, 0, strrpos( $string, "}" )+1 );
			}
			if( strpos( $string, "ERGEBNIS = " ) !== FALSE ) {
				$string = substr( $string, strpos( $string, "{" ) );
				$string = substr( $string, 0, strrpos( $string, "}" )+1 );
			}
			if( strpos( $string, "RESULT = " ) !== FALSE ) {
				$string = substr( $string, strpos( $string, "{" ) );
				$string = substr( $string, 0, strrpos( $string, "}" )+1 );
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
		public function buildCmndUrl( $device, $cmnd ) {
			$start = "?";
			if( isset( $device->password ) && $device->password != "" ) {
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


			//			if( $device->id == 6 ) {
			//				$url = "http://tasmoAdmin/dev/BME680.json";
			//			}


			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$result = curl_exec( $ch );


			if( !$result ) {
				$data        = new stdClass();
				$data->ERROR = __( "CURL_ERROR", "API" )." => ".curl_errno( $ch ).": ".curl_error( $ch );
			} else {

				$data = json_decode( $result );
				if( json_last_error() !== JSON_ERROR_NONE ) {
					$result = $this->fixJsonFormatv5100( $result );
					$data   = json_decode( $result );
					if( json_last_error() !== JSON_ERROR_NONE ) {
						$data        = new stdClass();
						$data->ERROR = __( "JSON_ERROR", "API" )." => ".json_last_error().": ".json_last_error_msg();
						$data->ERROR .= "<br/><strong>".__( "JSON_ERROR_CONTACT_DEV", "API", [ $result ] )."</strong>";
						$data->ERROR .= "<br/>".__( "JSON_ANSWER", "API" )." => ".print_r( $result, TRUE );

					}
				}

				if( isset( $data->WARNING ) && !empty( $data->WARNING ) && $try == 1 ) {
					$try++;
					//set web log level 2 and try again
					$webLog = $this->setWebLog( $device, 2, $try );
					if( !isset( $webLog->WARNING ) && empty( $webLog->WARNING ) ) {
						$data = $this->doRequest( $device, $cmnd, $try );
					}
				}

			}

			curl_close( $ch );

			return $data;
		}


		public function doAjax( $try = 1 ) {
			$device = $this->getDeviceById( $_POST[ "id" ] );
			$url    = $this->buildCmndUrl(
				$device,
				urldecode( $_POST[ "cmnd" ] )
			);


			//			if( $device->id == 6 ) {
			//				$url = "http://tasmoAdmin/dev/BME680.json";
			//			}

			$result = NULL;
			$ch     = curl_init();
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$result = curl_exec( $ch );
			if( !$result ) {
				$data        = new stdClass();
				$data->ERROR = __( "CURL_ERROR" )." => ".curl_errno( $ch ).": ".curl_error( $ch );
			} else {

				$data = json_decode( $result );
				if( json_last_error() !== JSON_ERROR_NONE ) {
					$result = $this->fixJsonFormatv5100( $result );
					$data   = json_decode( $result );
					if( json_last_error() !== JSON_ERROR_NONE ) {
						$data        = new stdClass();
						$data->ERROR = __( "JSON_ERROR", "API" )." => ".json_last_error().": ".json_last_error_msg();
						$data->ERROR .= "<br/><strong>".__( "JSON_ERROR_CONTACT_DEV", "API", [ $result ] )."</strong>";
						$data->ERROR .= "<br/>".__( "JSON_ANSWER", "API" )." => ".print_r( $result, TRUE );

					}
				}

				if( isset( $data->WARNING ) && !empty( $data->WARNING ) && $try < 1 ) {
					$try++;
					//set web log level 2 and try again
					$webLog = $this->setWebLog( parse_url( $url, PHP_URL_HOST ), 2, $try );
					if( !isset( $webLog->WARNING ) && empty( $webLog->WARNING ) ) {
						curl_close( $ch );
						$data = $this->doAjax( $url, $try );
					}
				}
			}

			curl_close( $ch );

			return $data;
		}

		public function doAjaxAll( $try = 1 ) {
			$result = NULL;
			ini_set( "max_execution_time", "99999999999" );

			$devices   = $this->getDevices();
			$cmnd      = "status 0";//urldecode( $_POST[ "cmnd" ] );
			$urlsClone = [];

			foreach( $devices as $device ) {
				$url = $this->buildCmndUrl(
					$device,
					$cmnd
				);

				//				if( $device->id == 6 ) {
				//					$url = "http://tasmoAdmin/dev/BME680.json";
				//				}

				$urls[ $url ] = $device;
				$urlsClone[]  = $url;
			}

			$results = [];
			// make sure the rolling window isn't greater than the # of urls
			$rolling_window = 2;
			$rolling_window = ( sizeof( $urls ) < $rolling_window ) ? sizeof( $urls ) : $rolling_window;
			$master         = curl_multi_init();
			// $curl_arr = array();
			// add additional curl options here
			$options = [
				CURLOPT_FOLLOWLOCATION => 0,
				CURLOPT_RETURNTRANSFER => 1,
				//				CURLOPT_NOSIGNAL       => 1,
				//				CURLOPT_HEADER         => 0,
				//				CURLOPT_HTTPHEADER     => [
				//					'Content-Type: application/json',
				//					'Accept: application/json',
				//				],
				//				CURLOPT_CONNECTTIMEOUT => 5,
				CURLOPT_TIMEOUT        => 8,
				CURLOPT_ENCODING       => '',
			];
			// start the first batch of requests

			for( $i = 0; $i < $rolling_window; $i++ ) {
				$ch                     = curl_init();
				$options[ CURLOPT_URL ] = $urlsClone[ $i ];
				curl_setopt_array( $ch, $options );
				curl_multi_add_handle( $master, $ch );
			}
			$i--;

			do {
				do {
					$mh_status = curl_multi_exec( $master, $running );
				} while( $mh_status == CURLM_CALL_MULTI_PERFORM );
				if( $mh_status != CURLM_OK ) {
					break;
				}

				// a request was just completed -- find out which one
				while( $done = curl_multi_info_read( $master ) ) {
					$info   = curl_getinfo( $done[ 'handle' ] );
					$output = curl_multi_getcontent( $done[ 'handle' ] );
					$device = $urls[ $info[ 'url' ] ];

					//					if ( curl_errno( $done[ 'handle' ] ) !== 0
					//					     || intval( $info[ 'http_code' ] ) !== 200 ) { //if server responded with http error
					//						var_dump( $info );
					//						var_dump( curl_errno( $done[ 'handle' ] ) );
					//						var_dump( curl_error( $done[ 'handle' ] ) );
					//						var_dump( $done[ 'handle' ] );
					//
					//						die();
					//					}

					if( !$output || $output == "" ) {
						$data        = new stdClass();
						$data->ERROR = __( "CURL_ERROR" )." => ".curl_errno( $done[ 'handle' ] ).": ".curl_error(
								$done[ 'handle' ]
							);
					} else {
						$data = json_decode( $output );
						if( json_last_error() !== JSON_ERROR_NONE ) {
							$outputTmp = $this->fixJsonFormatv5100( $output );
							$data      = json_decode( $outputTmp );
							unset( $outputTmp );

							if( json_last_error() !== JSON_ERROR_NONE ) {
								$data        = new stdClass();
								$data->ERROR = __( "JSON_ERROR", "API" )
								               ." => "
								               .json_last_error()
								               .": "
								               .json_last_error_msg();
								$data->ERROR .= "<br/><strong>"
								                .__( "JSON_ERROR_CONTACT_DEV", "API", [ $output ] )
								                ."</strong>";
								$data->ERROR .= "<br/>".__( "JSON_ANSWER", "API" )." => ".print_r( $output, TRUE );

							}
						}
					}
					$result[ $device->id ] = $data;

					// start a new request (it's important to do this before removing the old one)
					if( sizeof( $urls ) >= $i+1 ) {
						$ch                     = curl_init();
						$options[ CURLOPT_URL ] = $urlsClone[ $i++ ];  // increment i


						curl_setopt_array( $ch, $options );
						curl_multi_add_handle( $master, $ch );
					}
					// remove the curl handle that just completed
					curl_multi_remove_handle( $master, $done[ 'handle' ] );
					curl_close( $done[ "handle" ] );
				}
			} while( $running );
			curl_multi_close( $master );

			unset( $urlsClone );
			unset( $urls );

			ini_set( "max_execution_time", "60" );

			return $result;
		}


		public function search( $urls = [] ) {
			$result = [];
			ini_set( "max_execution_time", "99999999999" );

			$urlsClone = $urls;

			// make sure the rolling window isn't greater than the # of urls
			$rolling_window = 10;
			$rolling_window = ( sizeof( $urls ) < $rolling_window ) ? sizeof( $urls ) : $rolling_window;
			$master         = curl_multi_init();
			// $curl_arr = array();
			// add additional curl options here
			$options = [
				CURLOPT_FOLLOWLOCATION => FALSE,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_CONNECTTIMEOUT => 5,
				CURLOPT_TIMEOUT        => 8,
			];
			// start the first batch of requests

			for( $i = 0; $i < $rolling_window; $i++ ) {
				$ch                     = curl_init();
				$options[ CURLOPT_URL ] = $urlsClone[ $i ];
				curl_setopt_array( $ch, $options );
				curl_multi_add_handle( $master, $ch );
			}
			$i--;

			do {
				while( ( $execrun = curl_multi_exec( $master, $running ) ) == CURLM_CALL_MULTI_PERFORM ) {
					;
				}
				if( $execrun != CURLM_OK ) {
					break;
				}
				// a request was just completed -- find out which one
				while( $done = curl_multi_info_read( $master ) ) {
					$info   = curl_getinfo( $done[ 'handle' ] );
					$output = curl_multi_getcontent( $done[ 'handle' ] );

					if( !$output ) {

					} else {
						$data = json_decode( $output );
						if( json_last_error() !== JSON_ERROR_NONE ) {
							$outputTmp = $this->fixJsonFormatv5100( $output );
							$data      = json_decode( $outputTmp );
							unset( $outputTmp );

							if( json_last_error() !== JSON_ERROR_NONE ) {

							} else {
								$result[] = $data;
							}
						} else {
							$result[] = $data;
						}
					}

					// start a new request (it's important to do this before removing the old one)
					if( sizeof( $urls ) >= $i+1 ) {
						$ch                     = curl_init();
						$options[ CURLOPT_URL ] = $urlsClone[ $i++ ];  // increment i


						curl_setopt_array( $ch, $options );
						curl_multi_add_handle( $master, $ch );
					}
					// remove the curl handle that just completed
					curl_multi_remove_handle( $master, $done[ 'handle' ] );
					curl_close( $done[ "handle" ] );
				}
			} while( $running );
			curl_multi_close( $master );

			unset( $urlsClone );
			unset( $urls );


			ini_set( "max_execution_time", "60" );

			return $result;
		}
	}


