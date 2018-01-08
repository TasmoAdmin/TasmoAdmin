<?php
	
	class Config {
		private $cfgFile = "data/MyConfig.php";
		
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