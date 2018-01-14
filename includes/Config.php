<?php
	
	class Config {
		private $cfgFile = "data/MyConfig.php";
		
		private $defaultConfigs
			= array(
				"ota_server_ip" => "Bitte Server IP eingeben!",
				"username"      => "",
				"password"      => "",
			);
		
		function __construct() {
			if ( !file_exists( $this->cfgFile ) ) {
				fopen( $this->cfgFile, 'w' ) or die( "Can't create file" );
				$config = $this->defaultConfigs;
				$config = var_export( $config, TRUE );
				file_put_contents( $this->cfgFile, "<?php return $config ; ?>" );
			}
			
			foreach ( $this->defaultConfigs as $configName => $configValue ) {
				$config = $this->read( $configName );
				if ( !isset( $config ) ) {
					$this->write( $configName, $configValue );
				}
			}
			
		}
		
		public function read( $key ) {
			$config = include $this->cfgFile;
			if ( $config === 1 ) { //its empty
				return NULL;
			}
			
			return isset( $config[ $key ] ) ? $config[ $key ] : NULL;
		}
		
		public function write( $key, $value ) {
			$config = include $this->cfgFile;
			if ( $config === 1 ) { //its empty
				$config = array();
			}
			
			$config[ $key ] = $value;
			$config         = var_export( $config, TRUE );
			file_put_contents( $this->cfgFile, "<?php return $config ; ?>" );
		}
	}