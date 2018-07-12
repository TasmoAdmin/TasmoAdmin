<?php

	class Config {
		private $debug   = FALSE;
		private $cfgFile = _DATADIR_."MyConfig.json";

		private $cfgFile140 = _DATADIR_."MyConfig.php";       //for tag 1.4.0 migration

		private $defaultConfigs
			= [
				"ota_server_ip"         => "",
				"ota_server_port"       => "",
				"username"              => "",
				"password"              => "",
				"refreshtime"           => "5",
				"current_git_tag"       => "",
				"update_automatic_lang" => "",
				"nightmode"             => "auto",
				"login"                 => "1",
				"scan_from_ip"          => "192.168.178.2",
				"scan_to_ip"            => "192.168.178.254",
				"homepage"              => "start",
				"check_for_updates"     => "1",
			];

		private function setCacheConfig( $config ) {
			if( $this->debug ) {
				debug( "COOKIE WRITE" );
				debug( debug_backtrace() );
			}
			$config[ "password" ] = "im sure you expected a top secret pw here, but you failed :)";

			$configJSON = json_encode( $config );

			setcookie( "MyConfig", $configJSON, intval( time()+( 8640000000*30 ) ) );

			return $configJSON;
		}

		private function getCacheConfig( $key = NULL ) {
			if( $this->debug ) {
				debug( "COOKIE READ".( !empty( $key ) ? " ( ".$key." )" : "" ) );
			}
			$configJSON = $_COOKIE[ "MyConfig" ];
			if( empty( $configJSON ) ) {
				return FALSE;
			}
			$config = json_decode( $configJSON, TRUE );
			if( json_last_error() != 0 ) {
				return FALSE;
			}
			if( empty( $config ) ) {
				return FALSE;
			}


			if( !empty( $key ) ) {
				if( $key == "password" ) {
					$config = "im sure you expected a top secret pw here, but you failes :)";
				} else {
					if( !empty( $config[ $key ] ) ) {
						$config = $config[ $key ];
					} else {
						return FALSE;
					}
				}
			}


			return $config;
		}

		function __construct() {
			if( !file_exists( $this->cfgFile ) ) { //create file if not exists
				$fh = fopen( $this->cfgFile, 'w+' ) or die(
				__(
					"ERROR_CANNOT_CREATE_FILE",
					"USER_CONFIG",
					[ "cfgFilePath" => $this->cfgFile ]
				)
				);
				$config = [];
				/**
				 * MIGRATE FROM MyConfig.php tp MyConfig.json
				 * Read old data and save in new json format
				 * Tag 1.4.0
				 */
				if( file_exists( $this->cfgFile140 ) ) {
					$config = include $this->cfgFile140;

					if( $config === 1 ) { //its empty
						$config = [];
					}
				}

				$config = array_merge( $this->defaultConfigs, $config );

				$configJSON = json_encode( $config, JSON_PRETTY_PRINT );
				if( !fwrite( $fh, $configJSON ) ) {
					die( "COULD NOT CREATE OR WRITE IN CONFIG FILE" );
				}
				fclose( $fh );


			}

			/**
			 * test file
			 */
			if( !$this->getCacheConfig() ) {

				$config     = $configJSON = NULL;    //reset
				$configJSON = file_get_contents( $this->cfgFile );
				if( !$configJSON ) {
					die( "could not read MyConfig.json" );
				} else {
					$config = json_decode( $configJSON, TRUE );
				}
				if( json_last_error() != 0 ) {
					die( "JSON CONFIG ERROR: ".json_last_error()." => ".json_last_error_msg() );
				}

				$this->setCacheConfig( $config );
			}

			//write default config if does not exists in file
			foreach( $this->defaultConfigs as $configName => $configValue ) {
				$config = $this->read( $configName );
				if( !isset( $config ) || $config == "" ) {
					$this->write( $configName, $configValue );
				}
			}


			//remove trash from config
			$config = $this->readAll();
			if( !empty( $config[ "page" ] ) ) {
				unset( $config[ "page" ] );
				$configJSON = json_encode( $config, JSON_PRETTY_PRINT );

				if( $this->debug ) {
					debug( "PERFORM WRITE (unset => page)" );
				}
				file_put_contents( $this->cfgFile, $configJSON );

				$this->setCacheConfig( $config );
			}

		}

		public function readAll( $inclPassword = FALSE ) {
			$config = FALSE;
			if( !$inclPassword ) { //if pw requested, get from file
				$config = $this->getCacheConfig();
			}
			if( !$config ) {
				if( $this->debug ) {
					debug( "PERFORM READALL" );
				}
				$configJSON = file_get_contents( $this->cfgFile );
				if( !$config ) {
					die( "could not read MyConfig.json" );
				} else {
					$config = json_decode( $configJSON, TRUE );
				}
				if( json_last_error() != 0 ) {
					die( "JSON CONFIG ERROR: ".json_last_error()." => ".json_last_error_msg() );
				}
				$this->setCacheConfig( $config );
			}
			if( !$inclPassword ) {
				unset( $config[ "password" ] );
			}


			return $config;
		}

		public function read( $key ) {
			$config = $this->getCacheConfig( $key );
			if( !$config ) {
				if( $this->debug ) {
					debug( "PERFORM READ (".$key.")" );
				}
				$configJSON = file_get_contents( $this->cfgFile );
				if( !$configJSON ) {
					die( "could not read MyConfig.json" );
				} else {
					$config = json_decode( $configJSON, TRUE );
				}
				if( json_last_error() != 0 ) {
					die( "JSON CONFIG ERROR: ".json_last_error()." => ".json_last_error_msg() );
				}
				$this->setCacheConfig( $config );

				$config = isset( $config[ $key ] ) ? $config[ $key ] : NULL;
			}

			return $config;
		}

		public function write( $key, $value ) {
			if( $this->debug ) {
				debug( "PERFORM READ FOR WRITE" );
			}
			$config = file_get_contents( $this->cfgFile );
			if( !$config ) {
				die( "could not read MyConfig.json" );
			} else {
				$config = json_decode( $config, TRUE );
			}

			$config[ $key ] = $value;
			$configJSON     = json_encode( $config, JSON_PRETTY_PRINT );

			if( $this->debug ) {
				debug( "PERFORM WRITE (".$key." => ".$value.")" );
			}
			file_put_contents( $this->cfgFile, $configJSON );


			$this->setCacheConfig( $config );

			return TRUE;
		}
	}
