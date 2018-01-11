<?php
	
	class Config {
		private $cfgFile = "data/MyConfig.php";
		
		function __construct() {
			if ( !file_exists( $this->cfgFile ) ) {
				fopen( $this->cfgFile, 'w' ) or die( "Can't create file" );
				$config = array( "ota_server_ip" );
				$config = var_export( $config, TRUE );
				file_put_contents( $this->cfgFile, "<?php return $config ; ?>" );
			}
			
		}
		
		public function read( $key ) {
			$config = include $this->cfgFile;
			
			
			return isset( $config[ $key ] ) ? $config[ $key ] : NULL;
		}
		
		public function write( $key, $value ) {
			$config         = include $this->cfgFile;
			$config[ $key ] = $value;
			$config         = var_export( $config, TRUE );
			file_put_contents( $this->cfgFile, "<?php return $config ; ?>" );
		}
	}