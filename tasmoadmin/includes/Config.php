<?php

	class Config {
		private $cfgFile = _DATADIR_."MyConfig.php";

		private $defaultConfigs
			= [
				"ota_server_ip"         => "",
				"username"              => "",
				"password"              => "",
				"refreshtime"           => "5",
				"current_git_tag"       => "",
				"update_automatic_lang" => "",
				"nightmode"             => "auto",
				"login"                 => "1",
				"scan_from_ip"          => "192.168.178.2",
				"scan_to_ip"            => "192.168.178.254",
			];

		function __construct() {
			if( !file_exists( $this->cfgFile ) ) { //create file if not exists
				$fh = fopen( $this->cfgFile, 'w+' ) or die(
				__(
					"ERROR_CANNOT_CREATE_FILE",
					"USER_CONFIG",
					[ "cfgFilePath" => $this->cfgFile ]
				)
				);
				$config = $this->defaultConfigs;

				$config = var_export( $config, TRUE );
				if( !fwrite( $fh, "<?php return $config ; ?>" ) ) {
					die( "COULD NOT CREATE OR WRITE IN CONFIG FILE" );
				}
				fclose( $fh );
			}

			//write default config if does not exists in file
			foreach( $this->defaultConfigs as $configName => $configValue ) {
				$config = $this->read( $configName );
				if( !isset( $config ) || $config == "" ) {
					$this->write( $configName, $configValue );
				}
			}

		}

		public function readAll( $inclPassword = FALSE ) {
			$config = include $this->cfgFile;
			if( $config === 1 ) { //its empty
				return [];
			}
			if( !$inclPassword ) {
				unset( $config[ "password" ] );
			}

			return $config;
		}

		public function read( $key ) {
			$config = file_get_contents( $this->cfgFile );
			$config = str_replace( [ "<?php", "?>" ], "", $config );
			$config = eval( $config );
			if( $config === 1 ) { //its empty
				$config = [];
			}

			return isset( $config[ $key ] ) ? $config[ $key ] : NULL;
		}

		public function write( $key, $value ) {
			$config = include $this->cfgFile;

			if( $config === 1 ) { //its empty
				$config = [];
			}

			$config[ $key ] = $value;
			$config         = var_export( $config, TRUE );
			file_put_contents( $this->cfgFile, "<?php return $config ; ?>" );

			return TRUE;
		}
	}
