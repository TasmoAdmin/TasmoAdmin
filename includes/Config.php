<?php

class Config {
	private $cfgFile = "data/MyConfig.php";
	
	private $defaultConfigs
		= [
			"ota_server_ip" => "",
			"username"      => "",
			"password"      => "",
			"refreshtime"   => "2",
		];
	
	function __construct() {
		if( !file_exists( $this->cfgFile ) ) {
			fopen( $this->cfgFile, 'w' ) or die(
			__(
				"ERROR_CANNOT_CREATE_FILE", "USER_CONFIG", [ "cfgFilePath" => $this->cfgFile ]
			)
			);
			$config                    = $this->defaultConfigs;
			$config[ "ota_server_ip" ] = __( "DEFAULT_HOST_IP_PLACEHOLDER", "USER_CONFIG" );
			$config                    = var_export( $config, TRUE );
			file_put_contents( $this->cfgFile, "<?php return $config ; ?>" );
		}
		
		foreach( $this->defaultConfigs as $configName => $configValue ) {
			$config = $this->read( $configName );
			if( !isset( $config ) ) {
				$this->write( $configName, $configValue );
			}
		}
		
	}
	
	public function readAll() {
		$config = include $this->cfgFile;
		if( $config === 1 ) { //its empty
			return [];
		}
		
		return $config;
	}
	
	public function read( $key ) {
		$config = include $this->cfgFile;
		if( $config === 1 ) { //its empty
			return [];
		}
		
		return isset( $config[ $key ] ) ? $config[ $key ] : null;
	}
	
	public function write( $key, $value ) {
		$config = include $this->cfgFile;
		if( $config === 1 ) { //its empty
			$config = [];
		}
		
		$config[ $key ] = $value;
		$config         = var_export( $config, TRUE );
		file_put_contents( $this->cfgFile, "<?php return $config ; ?>" );
	}
}