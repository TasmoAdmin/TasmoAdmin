<?php

class Config {
	private $debug = FALSE;
	
	private $cfgFile = _DATADIR_ . "MyConfig.json";
	
	private $cfgFile140 = _DATADIR_ . "MyConfig.php";       //for tag 1.4.0 migration
	
	private $defaultConfigs
		= [
			"ota_server_ssl"        => "0", //0 = http, 1 = https
			"ota_server_ip"         => "",
			"ota_server_port"       => "",
			"username"              => "",
			"password"              => "",
			"refreshtime"           => "8",
			"current_git_tag"       => "",
			"update_automatic_lang" => "tasmota-sensors.bin",
			"nightmode"             => "auto",
			"login"                 => "1",
			"scan_from_ip"          => "192.168.178.2",
			"scan_to_ip"            => "192.168.178.254",
			"homepage"              => "start",
			"check_for_updates"     => "3",
			"minimize_resources"    => "1",
			"update_channel"        => "stable",
			"hide_copyright"        => "1",
			"show_search"           => "1",
		];
	
	function __construct() {
		
		//init default values
		$this->defaultConfigs["ota_server_ip"]   = !empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : "";
		$this->defaultConfigs["ota_server_port"] = !empty($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : "";
		
		if (!empty($_SERVER["SERVER_ADDR"])) {
			$ipBlocks                             = explode(".", $_SERVER["SERVER_ADDR"]);
			$ipBlocks[3]                          = 2;
			$this->defaultConfigs["scan_from_ip"] = implode(".", $ipBlocks);
			$ipBlocks[3]                          = 254;
			$this->defaultConfigs["scan_to_ip"]   = implode(".", $ipBlocks);
		}
		
		if (file_exists(_APPROOT_ . ".dockerenv")) {
			$this->defaultConfigs["update_channel"] = "docker";
		}
		
		//init default values end
		
		
		if (!is_dir(_DATADIR_)) {
			var_dump(debug_backtrace());
			die(_DATADIR_ . " is NO DIR! | __construct()");
		}
		if (!is_writable(_DATADIR_)) {
			var_dump(debug_backtrace());
			die(_DATADIR_ . " is NOT WRITEABLE! | __construct()");
		}
		
		
		if (!file_exists($this->cfgFile)) { //create file if not exists
			$fh = fopen($this->cfgFile, 'w+') or die(
			__(
				"ERROR_CANNOT_CREATE_FILE",
				"USER_CONFIG",
				["cfgFilePath" => $this->cfgFile]
			)
			);
			$config = [];
			/**
			 * MIGRATE FROM MyConfig.php tp MyConfig.json
			 * Read old data and save in new json format
			 * Tag 1.4.0
			 */
			if (file_exists($this->cfgFile140)) {
				$config = include $this->cfgFile140;
				
				if ($config === 1) { //its empty
					$config = [];
				}
			}
			
			$config     = array_merge($this->defaultConfigs, $config);
			$configJSON = json_encode($config, JSON_PRETTY_PRINT);
			if (!fwrite($fh, $configJSON)) {
				die("COULD NOT CREATE OR WRITE IN CONFIG FILE");
			}
			fclose($fh);
			
			
		}
		
		/**
		 * test file
		 */
		if (!$this->getCacheConfig()) {
			
			$this->clearCacheConfig();
			$config = $configJSON = NULL;    //reset
			
			$configJSON = file_get_contents($this->cfgFile);
			if ($configJSON === FALSE) {
				//					var_dump( debug_backtrace() );
				die("could not read MyConfig.json");
			}
			else {
				$config = json_decode($configJSON, TRUE);
			}
			if (json_last_error() != 0) {
				die("JSON CONFIG ERROR: " . json_last_error() . " => " . json_last_error_msg());
			}
			
		}
		
		//write default config if does not exists in file
		foreach ($this->defaultConfigs as $configName => $configValue) {
			$config = $this->read($configName, TRUE);
			if (!isset($config) || $config == "") {
				$this->write($configName, $configValue, TRUE);
			}
		}
		
		if (file_exists(_APPROOT_ . ".dockerenv")) {
			$this->write("update_channel", "docker");
		}
		
		
		//remove trash from config
		$config = $this->readAll(TRUE, TRUE);
		
		if (!empty($config["page"])) {
			unset($config["page"]);
			$configJSON = json_encode($config, JSON_PRETTY_PRINT);
			
			if ($this->debug) {
				debug("PERFORM WRITE (unset => page)");
			}
			
			if (!is_dir(_DATADIR_)) {
				var_dump(debug_backtrace());
				die(_DATADIR_ . " is NO DIR! | write()");
			}
			if (!is_writable(_DATADIR_)) {
				var_dump(debug_backtrace());
				die(_DATADIR_ . " is NOT WRITEABLE! | write()");
			}
			if (!is_writable($this->cfgFile)) {
				var_dump(debug_backtrace());
				die($this->cfgFile . " is NOT WRITEABLE! | write()");
			}
			
			if (empty($configJSON)) {
				var_dump($configJSON);
				var_dump(debug_backtrace());
				die("configJSON IS EMPTY! | write()");
			}
			
			
			file_put_contents($this->cfgFile, $configJSON, LOCK_EX);
			
		}
		
		
		if (!empty(getenv("BUILD_VERSION"))
			&& ($config["current_git_tag"] != getenv(
					"BUILD_VERSION"
				))) {
			$this->write("current_git_tag", getenv("BUILD_VERSION"), TRUE);
		}
		
		$this->setCacheConfig($config);
		
		
	}
	
	private function getCacheConfig($key = NULL) {
		if ($this->debug) {
			debug("COOKIE READ" . (!empty($key) ? " ( " . $key . " )" : ""));
		}
		if (empty($_SESSION["MyConfig"])) {
			return FALSE;
		}
		$configJSON = $_SESSION["MyConfig"];
		
		$config = json_decode($configJSON, TRUE);
		if (json_last_error() != 0) {
			return FALSE;
		}
		if (empty($config)) {
			return FALSE;
		}
		
		
		if (!empty($key)) {
			if ($key == "password") {
				$config = "im sure you expected a top secret pw here, but you failes :)";
			}
			else {
				if (!empty($config[$key])) {
					$config = $config[$key];
				}
				else {
					return FALSE;
				}
			}
		}
		
		
		return $config;
	}
	
	private function clearCacheConfig() {
		unset($_SESSION["MyConfig"]);
	}
	
	public function read($key, $skipCookie = FALSE) {
		$config = FALSE;
		if ($key !== "password") { //if pw requested, get from file
			$config = $this->getCacheConfig($key);
		}
		if (!$config) {
			if ($this->debug) {
				debug("PERFORM READ (" . $key . ")");
			}
			$configJSON = file_get_contents($this->cfgFile);
			if ($configJSON === FALSE) {
				var_dump(debug_backtrace());
				die("could not read MyConfig.json in read");
			}
			else {
				$config = json_decode($configJSON, TRUE);
			}
			if (json_last_error() != 0) {
				var_dump($configJSON);
				$this->clearCacheConfig();
				die("JSON CONFIG ERROR in read: " . json_last_error() . " => " . json_last_error_msg());
			}
			if (!$skipCookie) {
				$this->setCacheConfig($config);
			}
			
			$config = isset($config[$key]) ? $config[$key] : NULL;
		}
		
		return $config;
	}
	
	private function setCacheConfig($config) {
		if ((empty($_SESSION["login"]) || $_SESSION["login"] !== "1") && $config["login"] == "1") {
			
			return FALSE;
		}
		
		if ($this->debug) {
			debug("COOKIE WRITE");
			debug(debug_backtrace());
		}
		$config["password"] = "im sure you expected a top secret pw here, but you failed :)";
		
		$configJSON = json_encode($config);
		
		$_SESSION["MyConfig"] = $configJSON;
		
		//			debug( debug_backtrace() );
		//			debug( "set cookie" );
		
		return $configJSON;
	}
	
	public function write($key, $value, $skipCookie = FALSE) {
		if ($this->debug) {
			debug("PERFORM READ FOR WRITE");
		}
		$configJSON = file_get_contents($this->cfgFile);
		if ($configJSON === FALSE) {
			var_dump(debug_backtrace());
			die("could not read MyConfig.json in write");
		}
		else {
			$config = json_decode($configJSON, TRUE);
		}
		
		
		$value = trim($value);
		
		if (empty($value) && $value != 0) {
			$value = $this->defaultConfigs[$key];
		}
		
		$config[$key] = $value;
		$configJSON   = json_encode($config, JSON_PRETTY_PRINT);
		
		if ($this->debug) {
			debug("PERFORM WRITE (" . $key . " => " . $value . ")");
		}
		
		if (!is_dir(_DATADIR_)) {
			var_dump(debug_backtrace());
			die(_DATADIR_ . " is NO DIR! | write()");
		}
		if (!is_writable(_DATADIR_)) {
			var_dump(debug_backtrace());
			die(_DATADIR_ . " is NOT WRITEABLE! | write()");
		}
		if (!is_writable($this->cfgFile)) {
			var_dump(debug_backtrace());
			die($this->cfgFile . " is NOT WRITEABLE! | write()");
		}
		
		if (empty($configJSON)) {
			var_dump($configJSON);
			var_dump(debug_backtrace());
			die("configJSON IS EMPTY! | write()");
		}
		
		$tempfile = _DATADIR_ . uniqid(microtime(TRUE));
		if (file_put_contents($tempfile, $configJSON, LOCK_EX)) {
		
		}
		else {
			die("file_put_contents FAILED! | write()");
		}
		rename($tempfile, $this->cfgFile);
		
		
		if (!$skipCookie) {
			$this->setCacheConfig($config);
		}
		
		return TRUE;
	}
	
	public function readAll($inclPassword = FALSE, $skipCookie = FALSE) {
		$config = FALSE;
		if (!$inclPassword) { //if pw requested, get from file
			$config = $this->getCacheConfig();
		}
		if (!$config) {
			if ($this->debug) {
				debug("PERFORM READALL");
			}
			$configJSON = file_get_contents($this->cfgFile);
			if ($configJSON === FALSE) {
				var_dump(debug_backtrace());
				die("could not read MyConfig.json in readAll");
			}
			else {
				$config = json_decode($configJSON, TRUE);
			}
			if (json_last_error() != 0) {
				$this->clearCacheConfig();
				die("JSON CONFIG ERROR in readAll: " . json_last_error() . " => " . json_last_error_msg());
			}
			if (!$skipCookie) {
				$this->setCacheConfig($config);
			}
		}
		if (!$inclPassword) {
			unset($config["password"]);
		}
		
		
		return $config;
	}
}
