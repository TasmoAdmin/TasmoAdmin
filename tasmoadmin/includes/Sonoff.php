<?php


/**
 * Class Sonoff
 */
class Sonoff {
	
	public function addDevice($device = []) {
		die("not done yet"); //todo: use this to add device, decide if array or object param
		if (!isset($device) || empty($device) || !isset($device["id"]) || empty($device["id"])) {
			return NULL;
		}
		
		$fp        = file(_CSVFILE_);
		$device[0] = isset($device->id) && !empty($device->id) ? $device->id : count($fp) + 1;
		$device[1] = implode("|", isset($device->names) && !empty($device->names) ? $device->names : []);
		$device[2] = isset($device->ip) && !empty($device->ip) ? $device->ip : "";
		$device[3] = isset($device->username) && !empty($device->username) ? $device->username : "";
		$device[4] = isset($device->password) && !empty($device->password) ? $device->password : "";
		$device[5] = isset($device->img) && !empty($device->img) ? $device->img : "";
		$device[6] = isset($device->position) && !empty($device->position) ? $device->position : "";
		
		
		$handle = fopen(_CSVFILE_, "a");
		fputcsv($handle, $device);
		fclose($handle);
	}
	
	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	public function getAllStatus($device) {
		$cmnd = "Status 0";
		
		
		$status = $this->doRequest($device, $cmnd);
		
		return $status;
	}
	
	/**
	 * @param     $ip
	 * @param     $cmnd
	 * @param int $try
	 *
	 * @return mixed
	 */
	private function doRequest($device, $cmnd, $try = 1) {
		$url = $this->buildCmndUrl($device, $cmnd);
		
		$result = NULL;
		
		
		//            if( $device->id == 6 ) {
		//                $url = "http://tasmoAdmin/dev/BME680.json";
		//            }
		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		
		
		if (!$result) {
			$data        = new stdClass();
			$data->ERROR = __("CURL_ERROR", "API") . " => " . curl_errno($ch) . ": " . curl_error($ch);
		}
		else {
			
			$data = json_decode($result);
			
			if (json_last_error() == JSON_ERROR_CTRL_CHAR) {  // https://github.com/reloxx13/TasmoAdmin/issues/78
				$result = preg_replace('/[[:cntrl:]]/', '', $result);
				$data   = json_decode($result);
			}
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				$result = $this->fixJsonFormatv5100($result);
				$data   = json_decode($result);
			}
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				$result = $this->fixJsonFormatv8500($result);
				$data   = json_decode($result);
			}
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				$data        = new stdClass();
				$data->ERROR = __("JSON_ERROR", "API") . " => " . json_last_error() . ": " . json_last_error_msg();
				$data->ERROR .= "<br/><strong>" . __("JSON_ERROR_CONTACT_DEV", "API", [$result]) . "</strong>";
				$data->ERROR .= "<br/>" . __("JSON_ANSWER", "API") . " => " . print_r($result, TRUE);
				
			}
			$skipWarning = FALSE;
			if (strpos($cmnd, "Backlog") !== FALSE) {
				$skipWarning = TRUE;
			}
			
			if (!$skipWarning && isset($data->WARNING) && !empty($data->WARNING) && $try == 1) {
				$try++;
				//set web log level 2 and try again
				$webLog = $this->setWebLog($device, 2, $try);
				if (!isset($webLog->WARNING) && empty($webLog->WARNING)) {
					$data = $this->doRequest($device, $cmnd, $try);
				}
			}
			else {
				if (empty($data->ERROR)) {
					$data = $this->compatibility($data);
				}
			}
			
		}
		
		curl_close($ch);
		
		$data = $this->stateTextsDetection($data);
		
		return $data;
	}
	
	/**
	 * @param $ip
	 * @param $cmnd
	 *
	 * @return mixed|string
	 */
	public function buildCmndUrl($device, $cmnd) {
		$start = "?";
		if (isset($device->password) && $device->password != "") {
			$start = "?user=" . urlencode($device->username) . "&password=" . urlencode($device->password) . "&";
		}
		$url = "http://" . $device->ip . "/cm" . $start . "cmnd=" . urlencode($cmnd);
		
		
		return $url;
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
	private function fixJsonFormatV5100($string) {
		
		
		$string = substr($string, strpos($string, "STATUS = "));
		if (strpos($string, "POWER = ") !== FALSE) {
			$string = substr($string, strpos($string, "{"));
			$string = substr($string, 0, strrpos($string, "}") + 1);
		}
		if (strpos($string, "ERGEBNIS = ") !== FALSE) {
			$string = substr($string, strpos($string, "{"));
			$string = substr($string, 0, strrpos($string, "}") + 1);
		}
		if (strpos($string, "RESULT = ") !== FALSE) {
			$string = substr($string, strpos($string, "{"));
			$string = substr($string, 0, strrpos($string, "}") + 1);
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
			":nan,",
			":nan}",
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
			":\"NaN\",",
			":\"NaN\"}",
		];
		
		$string = str_replace($remove, $replace, $string);
		
		//remove everything befor ethe first {
		$string = strstr($string, '{');
		
		//			var_dump( $string );
		//			var_dump( json_decode( $string ) );
		//			var_dump( json_last_error_msg() );
		//			die();
		
		return $string;
	}
	
	/**
	 *
	 * This fixes wrong formated json answer form Tasmota Version 8.5.0.x
	 * Example wrong format: dev/json_error_8500.json
	 *
	 * Shutters missed a } at the end
	 * https://github.com/reloxx13/TasmoAdmin/issues/398
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	private function fixJsonFormatv8500($string) {
		$string = $string . "}";
		
		return $string;
	}
	
	/**
	 * @param     $ip
	 * @param int $level
	 * @param int $try
	 *
	 * @return mixed
	 */
	public function setWebLog($device, $level = 2, $try = 1) {
		$cmnd = "Weblog " . $level;
		
		$weblog = $this->doRequest($device, $cmnd, $try);
		
		return $weblog;
	}
	
	public function compatibility($status) {
		/**
		 * < 5.12.0
		 * $status->StatusNET->IP
		 * >= 5.12.0
		 * $status->StatusNET->IPAddress
		 * https://github.com/reloxx13/TasmoAdmin/issues/107
		 **/
		if (!empty($status->StatusNET->IP)) {
			$status->StatusNET->IPAddress = $status->StatusNET->IP;
		}
		
		
		return $status;
	}
	
	public function stateTextsDetection($status) {
		/**
		 * v6.2.0.2 2018-09-04
		 *  MQTT Changed Statetext is send in JSON, this is fail cuz it can be translated and not detected by other softwares.
		 *
		 * This function tries to detect the state by hardcoded keywords.
		 */
		
		$offArray = explode(
			", ",
			
			strtolower(
				""
				
				/**
				 * EN
				 */ . "off, down, offline, out, "
				
				/**
				 * DE
				 */ . "aus, unten, runter, schließen, schliessen, zu, "
				
				/**
				 * PL
				 */ . "z, poniżej, ponizej, blisko, do, zamknięte, zamkniete"
			)
		);
		$onArray  = explode(
			", ",
			
			strtolower(
				""
				
				/**
				 * EN
				 */ . "on, up, online, in, "
				
				/**
				 * DE
				 */ . "an, oben, hoch, öffnen, oeffnen, offen, "
				
				/**
				 * PL
				 */ . "do, powyżej, powyzej, wysoki, otwarte"
			)
		);
		
		
		$state = NULL;
		
		//status 0 request for 1 relais
		if (isset($status->StatusSTS->POWER)) {
			$state = $status->StatusSTS->POWER;
			if (isset($status->StatusSTS->POWER->STATE)) {
				$state = $status->StatusSTS->POWER->STATE;
			}
			//try to detect OFF
			if (in_array(strtolower($state), $offArray)) {
				$state = "OFF";
			}
			elseif (in_array(strtolower($state), $onArray)) {
				$state = "ON";
			}
			
			
			if (!empty($state)) {
				if (isset($status->StatusSTS->POWER->STATE)) {
					$status->StatusSTS->POWER->STATE = $state;
				}
				else {
					$status->StatusSTS->POWER = $state;
				}
			}
		}
		
		//toggle request for 1 relais
		if (isset($status->POWER)) {
			$state = $status->POWER;
			//try to detect OFF
			if (in_array(strtolower($state), $offArray)) {
				$state = "OFF";
			}
			elseif (in_array(strtolower($state), $onArray)) {
				$state = "ON";
			}
			
			if (!empty($state)) {
				$status->POWER = $state;
			}
		}
		
		$i     = 1;
		$power = "POWER" . $i;
		
		//status 0 request for multi relais
		while (isset($status->StatusSTS->$power)) {
			$state = NULL;
			
			
			$state = $status->StatusSTS->$power;
			if (isset($status->StatusSTS->$power->STATE)) {
				$state = $status->StatusSTS->$power->STATE;
			}
			//try to detect OFF
			if (in_array(strtolower($state), $offArray)) {
				$state = "OFF";
			}
			elseif (in_array(strtolower($state), $onArray)) {
				$state = "ON";
			}
			
			if (!empty($state)) {
				if (isset($status->StatusSTS->$power->STATE)) {
					$status->StatusSTS->$power->STATE = $state;
				}
				else {
					$status->StatusSTS->$power = $state;
				}
			}
			
			
			$i++;
			$power = "POWER" . $i;
		}
		
		
		$i     = 1;
		$power = "POWER" . $i;
		
		//toggle request for multi relais
		while (isset($status->$power)) {
			$state = NULL;
			
			
			$state = $status->$power;
			if (isset($status->$power->STATE)) {
				$state = $status->$power->STATE;
			}
			
			//try to detect OFF
			if (in_array(strtolower($state), $offArray)) {
				$state = "OFF";
			}
			elseif (in_array(strtolower($state), $onArray)) {
				$state = "ON";
			}
			
			if (!empty($state)) {
				if (isset($status->$power->STATE)) {
					$status->$power->STATE = $state;
				}
				else {
					$status->$power = $state;
				}
				$status->$power = $state;
			}
			
			
			$i++;
			$power = "POWER" . $i;
		}
		
		
		return $status;
	}
	
	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	public function getNTPStatus($device) {
		$cmnd = "NtpServer1";
		
		
		$status = $this->doRequest($device, $cmnd);
		if (!empty($status->Command) && $status->Command == "Unknown") {
			return "";
		}
		
		return $status;
	}
	
	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	public function getFullTopic($device) {
		$cmnd = "FullTopic";
		
		
		$status = $this->doRequest($device, $cmnd);
		if (!empty($status->Command) && $status->Command == "Unknown") {
			return "";
		}
		
		if (!empty($status->ERROR)) {
			return "";
		}
		
		return $status->FullTopic;
	}
	
	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	public function getSwitchTopic($device) {
		$cmnd = "SwitchTopic";
		
		
		$status = $this->doRequest($device, $cmnd);
		
		if (!empty($status->Command) && $status->Command == "Unknown") {
			return "";
		}
		
		if (!empty($status->ERROR)) {
			return "";
		}
		
		return $status->SwitchTopic;
	}
	
	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	public function getMqttRetry($device) {
		$cmnd = "MqttRetry";
		
		
		$status = $this->doRequest($device, $cmnd);
		if (!empty($status->Command) && $status->Command == "Unknown") {
			return "";
		}
		
		if (!empty($status->ERROR)) {
			return "";
		}
		
		return $status->MqttRetry;
	}
	
	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	public function getTelePeriod($device) {
		$cmnd = "TelePeriod";
		
		
		$status = $this->doRequest($device, $cmnd);
		if (!empty($status->Command) && $status->Command == "Unknown") {
			return "";
		}
		
		if (!empty($status->ERROR)) {
			return "";
		}
		
		return $status->TelePeriod;
	}
	
	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	public function getSensorRetain($device) {
		$cmnd = "SensorRetain";
		
		
		$status = $this->doRequest($device, $cmnd);
		if (!empty($status->Command) && $status->Command == "Unknown") {
			return "";
		}
		
		if (!empty($status->ERROR)) {
			return "";
		}
		
		return $status->SensorRetain;
	}
	
	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	public function getMqttFingerprint($device) {
		$cmnd = "MqttFingerprint";
		
		
		$status = $this->doRequest($device, $cmnd);
		if (!empty($status->Command) && $status->Command == "Unknown") {
			return "";
		}
		if (!empty($status->ERROR)) {
			return "";
		}
		
		if (empty($status->MqttFingerprint)) {
			return "";
		}
		
		return $status->MqttFingerprint;
	}
	
	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	public function getPrefixe($device) {
		$cmnds = ["Prefix1", "Prefix2", "Prefix3"];
		
		$status = new stdClass();
		foreach ($cmnds as $cmnd) {
			$tmp = $this->doRequest($device, $cmnd);
			
			if (!empty($tmp->Command) && $tmp->Command == "Unknown") {
				$status->$cmnd = "";
			}
			else {
				
				if (!empty($status->ERROR) || empty($status)) {
					$status->$cmnd = "";
				}
				else {
					$status->$cmnd = $tmp->$cmnd;
				}
			}
			
		}
		
		unset($tmp);
		
		return $status;
	}
	
	/**
	 * @param $ip
	 *
	 * @return mixed
	 */
	public function getStateTexts($device) {
		$cmnds = ["StateText1", "StateText2", "StateText3", "StateText4"];
		
		$status = new stdClass();
		foreach ($cmnds as $cmnd) {
			$tmp = $this->doRequest($device, $cmnd);
			if (!empty($tmp->Command) && $tmp->Command == "Unknown") {
				$status->$cmnd = "";
			}
			else {
				if (!empty($status->ERROR) || empty($status)) {
					$status->$cmnd = "";
				}
				else {
					$status->$cmnd = $tmp->$cmnd;
				}
			}
		}
		
		unset($tmp);
		
		return $status;
	}
	
	public function toggle($device) {
		$cmnd = "Status 0";
		
		$status = $this->doRequest($device, $cmnd);
		
		return $status;
	}
	
	public function saveConfig($device, $backlog) {
		$status = $this->doRequest($device, $backlog);
		
		return $status;
	}
	
	public function doAjax($try = 1) {
		$device = $this->getDeviceById($_REQUEST["id"]);
		$url    = $this->buildCmndUrl(
			$device,
			urldecode($_REQUEST["cmnd"])
		);
		
		
		//		if ($device->id == 1) {
		//			$url = "http://192.168.178.10/dev/test.json";
		//		}
		
		
		$result = NULL;
		$ch     = curl_init();
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		
		if (!$result) {
			$data        = new stdClass();
			$data->ERROR = __("CURL_ERROR") . " => " . curl_errno($ch) . ": " . curl_error($ch);
		}
		else {
			
			$backupResult = $result;
			
			$data = json_decode($result);
			
			if (json_last_error() == JSON_ERROR_CTRL_CHAR) {  // https://github.com/reloxx13/TasmoAdmin/issues/78
				$result = preg_replace('/[[:cntrl:]]/', '', $result);
				$data   = json_decode($result);
			}
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				$result = $this->fixJsonFormatv5100($result);
				$data   = json_decode($result);
			}
			
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				$result = $backupResult;
				$result = $this->fixJsonFormatv8500($result);
				$data   = json_decode($result);
			}
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				$data        = new stdClass();
				$data->ERROR = __("JSON_ERROR", "API") . " => " . json_last_error() . ": " . json_last_error_msg();
				$data->ERROR .= "<br/><strong>" . __("JSON_ERROR_CONTACT_DEV", "API", [$result]) . "</strong>";
				$data->ERROR .= "<br/>" . __("JSON_ANSWER", "API") . " => " . print_r($result, TRUE);
				
			}
			
			if (isset($data->WARNING) && !empty($data->WARNING) && $try < 1) {
				$try++;
				//set web log level 2 and try again
				$webLog = $this->setWebLog(parse_url($url, PHP_URL_HOST), 2, $try);
				if (!isset($webLog->WARNING) && empty($webLog->WARNING)) {
					curl_close($ch);
					$data = $this->doAjax($url, $try);
				}
			}
			else {
				if (empty($data->ERROR)) {
					$data = $this->compatibility($data);
				}
			}
		}
		
		curl_close($ch);
		
		
		$data = $this->stateTextsDetection($data);
		
		
		return $data;
	}
	
	public function getDeviceById($id = NULL) {
		if (!isset($id) || empty($id)) {
			return NULL;
		}
		$file = fopen(_CSVFILE_, 'r');
		while (($line = fgetcsv($file)) !== FALSE) {
			if ($line[0] == $id) {
				$device = $this->createDeviceObject($line);
				break;
			}
		}
		fclose($file);
		
		return $device;
	}
	
	private function createDeviceObject($deviceLine = []) {
		if (!isset($deviceLine) || empty($deviceLine)) {
			return NULL;
		}
		
		$device                     = new stdClass();
		$deviceLine[1]              = explode("|", $deviceLine[1]);
		$device->id                 = isset($deviceLine[0]) ? $deviceLine[0] : FALSE;
		$device->names              = isset($deviceLine[1]) ? $deviceLine[1] : FALSE;
		$device->ip                 = isset($deviceLine[2]) ? $deviceLine[2] : FALSE;
		$device->username           = isset($deviceLine[3]) ? $deviceLine[3] : FALSE;
		$device->password           = isset($deviceLine[4]) ? $deviceLine[4] : FALSE;
		$device->img                = isset($deviceLine[5]) ? $deviceLine[5] : "bulb_1";
		$device->position           = isset($deviceLine[6]) && $deviceLine[6] != "" ? $deviceLine[6] : "";
		$device->device_all_off     = isset($deviceLine[7]) ? $deviceLine[7] : 1;
		$device->device_protect_on  = isset($deviceLine[8]) ? $deviceLine[8] : 0;
		$device->device_protect_off = isset($deviceLine[9]) ? $deviceLine[9] : 0;
		
		$keywords   = [];
		$keywords[] = count($device->names) > 1 ? "multi" : "single";
		$keywords[] = "IP#" . $device->ip;
		$keywords[] = "ID#" . $device->id;
		$keywords[] = "POS#" . $device->position;
		
		$device->keywords = $keywords;
		return $device;
	}
	
	public function doAjaxAll($try = 1) {
		$result = NULL;
		ini_set("max_execution_time", "99999999999");
		
		$devices   = $this->getDevices();
		$cmnd      = "status 0";//urldecode( $_REQUEST[ "cmnd" ] );
		$urlsClone = [];
		
		foreach ($devices as $device) {
			$url = $this->buildCmndUrl(
				$device,
				$cmnd
			);
			
			//			if ($device->id == 1) {
			//				$url = "http://192.168.178.10/dev/test.json";
			//			}
			
			//$url = "http://tasmoAdmin/dev/test.json";
			
			
			$urls[$url]  = $device;
			$urlsClone[] = $url;
		}
		
		$results = [];
		// make sure the rolling window isn't greater than the # of urls
		$rolling_window = 2;
		$rolling_window = (sizeof($urls) < $rolling_window) ? sizeof($urls) : $rolling_window;
		$master         = curl_multi_init();
		// $curl_arr = array();
		// add additional curl options here
		$options = [
			CURLOPT_FOLLOWLOCATION => 0,
			CURLOPT_RETURNTRANSFER => 1,
			//                CURLOPT_NOSIGNAL       => 1,
			//                CURLOPT_HEADER         => 0,
			//                CURLOPT_HTTPHEADER     => [
			//                    'Content-Type: application/json',
			//                    'Accept: application/json',
			//                ],
			//                CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT        => 8,
			CURLOPT_ENCODING       => '',
		];
		// start the first batch of requests
		
		for ($i = 0; $i < $rolling_window; $i++) {
			$ch                   = curl_init();
			$options[CURLOPT_URL] = $urlsClone[$i];
			curl_setopt_array($ch, $options);
			curl_multi_add_handle($master, $ch);
		}
		$i--;
		
		do {
			do {
				$mh_status = curl_multi_exec($master, $running);
			} while ($mh_status == CURLM_CALL_MULTI_PERFORM);
			if ($mh_status != CURLM_OK) {
				break;
			}
			
			// a request was just completed -- find out which one
			while ($done = curl_multi_info_read($master)) {
				$info   = curl_getinfo($done['handle']);
				$output = curl_multi_getcontent($done['handle']);
				$device = $urls[$info['url']];
				
				//                    if ( curl_errno( $done[ 'handle' ] ) !== 0
				//                         || intval( $info[ 'http_code' ] ) !== 200 ) { //if server responded with http error
				//                        var_dump( $info );
				//                        var_dump( curl_errno( $done[ 'handle' ] ) );
				//                        var_dump( curl_error( $done[ 'handle' ] ) );
				//                        var_dump( $done[ 'handle' ] );
				//
				//                        die();
				//                    }
				
				if (!$output || $output == "") {
					$data        = new stdClass();
					$data->ERROR = __("CURL_ERROR") . " => " . curl_errno($done['handle']) . ": " . curl_error(
							$done['handle']
						);
				}
				else {
					$data = json_decode($output);
					
					
					if (json_last_error()
						== JSON_ERROR_CTRL_CHAR) {  // https://github.com/reloxx13/TasmoAdmin/issues/78
						$result = preg_replace('/[[:cntrl:]]/', '', $result);
						$data   = json_decode($result);
					}
					
					if (json_last_error() !== JSON_ERROR_NONE) {
						$outputTmp = $this->fixJsonFormatv5100($output);
						$data      = json_decode($outputTmp);
						unset($outputTmp);
						
					}
					if (json_last_error() !== JSON_ERROR_NONE) {
						$outputTmp = $this->fixJsonFormatv8500($output);
						$data      = json_decode($outputTmp);
						unset($outputTmp);
						
					}
					if (json_last_error() !== JSON_ERROR_NONE) {
						$data        = new stdClass();
						$data->ERROR = __("JSON_ERROR", "API")
							. " => "
							. json_last_error()
							. ": "
							. json_last_error_msg();
						$data->ERROR .= "<br/><strong>"
							. __("JSON_ERROR_CONTACT_DEV", "API", [$output])
							. "</strong>";
						$data->ERROR .= "<br/>" . __("JSON_ANSWER", "API") . " => " . print_r($output, TRUE);
						
					}
				}
				if (empty($data->ERROR)) {
					$data = $this->compatibility($data);
				}
				
				$data                = $this->stateTextsDetection($data);
				$result[$device->id] = $data;
				
				// start a new request (it's important to do this before removing the old one)
				if (sizeof($urls) >= $i + 1) {
					$ch                   = curl_init();
					$options[CURLOPT_URL] = $urlsClone[$i++];  // increment i
					
					
					curl_setopt_array($ch, $options);
					curl_multi_add_handle($master, $ch);
				}
				// remove the curl handle that just completed
				curl_multi_remove_handle($master, $done['handle']);
				curl_close($done["handle"]);
			}
		} while ($running);
		curl_multi_close($master);
		
		unset($urlsClone);
		unset($urls);
		
		ini_set("max_execution_time", "60");
		
		return $result;
	}
	
	public function getDevices($orderBy = "position") {
		
		$devices = [];
		
		$file = fopen(_CSVFILE_, 'r');
		while (($line = fgetcsv($file)) !== FALSE) {
			$devices[] = $this->createDeviceObject($line);
			
			
		}
		fclose($file);
		
		if ($orderBy == "position") {
			$devicesTmp = [];
			$update     = FALSE;
			foreach ($devices as $device) {
				if ($device->position == "") {
					$device->position = 1;
					$update           = TRUE;
				}
				while (isset($devicesTmp[$device->position])) {
					$device->position++;
				}
				if ($update) {
					$this->setDeviceValue($device->id, "position", $device->position);
				}
				$devicesTmp[$device->position] = $device;
			}
			ksort($devicesTmp);
			$devices = $devicesTmp;
			unset($devicesTmp);
		}
		
		return $devices;
	}
	
	public function setDeviceValue($id = NULL, $field = NULL, $value = NULL) {
		if (!isset($id) || empty($id)) {
			return NULL;
		}
		$device = NULL;
		$file   = fopen(_CSVFILE_, 'r');
		while (($line = fgetcsv($file)) !== FALSE) {
			if ($line[0] == $id) {
				$device = $this->createDeviceObject($line);
				break;
			}
		}
		fclose($file);
		$device->$field = $value;
		$device         = $this->updateDevice($device);
		
		return $device;
	}
	
	public function updateDevice($device = NULL) {
		if (!isset($device) || empty($device) || !isset($device->id) || empty($device->id)) {
			return NULL;
		}
		$deviceArr[0] = $device->id;
		$deviceArr[1] = implode("|", isset($device->names) && !empty($device->names) ? $device->names : []);
		$deviceArr[2] = isset($device->ip) && !empty($device->ip) ? $device->ip : "";
		$deviceArr[3] = isset($device->username) && !empty($device->username) ? $device->username : "";
		$deviceArr[4] = isset($device->password) && !empty($device->password) ? $device->password : "";
		$deviceArr[5] = isset($device->img) && !empty($device->img) ? $device->img : "";
		$deviceArr[6] = isset($device->position) && !empty($device->position) ? $device->position : "";
		
		foreach ($deviceArr as $key => $field) {
			if (is_array($field)) {
				foreach ($field as $subkey => $subfield) {
					$deviceArr[$key][$field][$subkey] = trim($subfield);
				}
			}
			else {
				
				$deviceArr[$key] = trim($field);
			}
		}
		
		$tempfile = @tempnam(_TMPDIR_, "tmp"); // produce a temporary file name, in the current directory
		
		
		if (!$input = fopen(_CSVFILE_, 'r')) {
			die(__("ERROR_CANNOT_READ_CSV_FILE", "DEVICE_ACTIONS", ["csvFilePath" => _CSVFILE_]));
		}
		if (!$output = fopen($tempfile, 'w')) {
			die(__("ERROR_CANNOT_CREATE_TMP_FILE", "DEVICE_ACTIONS", ["tmpFilePath" => $tempfile]));
		}
		
		while (($data = fgetcsv($input)) !== FALSE) {
			if ($data[0] == $deviceArr[0]) {
				$data = $deviceArr;
			}
			fputcsv($output, $data);
		}
		
		fclose($input);
		fclose($output);
		
		unlink(_CSVFILE_);
		rename($tempfile, _CSVFILE_);
		
		return $this->createDeviceObject($deviceArr);
	}
	
	public function search($urls = []) {
		$result = [];
		ini_set("max_execution_time", "99999999999");
		
		$urlsClone = $urls;
		
		// make sure the rolling window isn't greater than the # of urls
		$rolling_window = 10;
		$rolling_window = (sizeof($urls) < $rolling_window) ? sizeof($urls) : $rolling_window;
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
		
		for ($i = 0; $i < $rolling_window; $i++) {
			$ch                   = curl_init();
			$options[CURLOPT_URL] = $urlsClone[$i];
			curl_setopt_array($ch, $options);
			curl_multi_add_handle($master, $ch);
		}
		$i--;
		
		do {
			while (($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM) {
				;
			}
			if ($execrun != CURLM_OK) {
				break;
			}
			// a request was just completed -- find out which one
			while ($done = curl_multi_info_read($master)) {
				$info   = curl_getinfo($done['handle']);
				$output = curl_multi_getcontent($done['handle']);
				
				if (!$output) {
				
				}
				else {
					$data = json_decode($output);
					
					
					if (json_last_error() == JSON_ERROR_CTRL_CHAR) {  // https://github.com/reloxx13/TasmoAdmin/issues/78
						$output = preg_replace('/[[:cntrl:]]/', '', $output);
						$data   = json_decode($output);
					}
					
					if (json_last_error() !== JSON_ERROR_NONE) {
						$outputTmp = $this->fixJsonFormatv5100($output);
						$data      = json_decode($outputTmp);
					}
					if (json_last_error() !== JSON_ERROR_NONE) {
						$outputTmp = $this->fixJsonFormatv8500($output);
						$data      = json_decode($outputTmp);
					}
					
					if (json_last_error() !== JSON_ERROR_NONE) {
					}
					else {
						
						if (empty($data->ERROR)) {
							$data = $this->compatibility($data);
							$data = $this->stateTextsDetection($data);
						}
						$result[] = $data;
					}
					unset($outputTmp);
				}
				
				// start a new request (it's important to do this before removing the old one)
				if (sizeof($urls) >= $i + 1) {
					$ch                   = curl_init();
					$options[CURLOPT_URL] = $urlsClone[$i++];  // increment i
					
					
					curl_setopt_array($ch, $options);
					curl_multi_add_handle($master, $ch);
				}
				// remove the curl handle that just completed
				curl_multi_remove_handle($master, $done['handle']);
				curl_close($done["handle"]);
			}
		} while ($running);
		curl_multi_close($master);
		
		unset($urlsClone);
		unset($urls);
		
		
		ini_set("max_execution_time", "60");
		
		return $result;
	}
	
	public function decodeOptions($options) {
		if (empty($options)) {
			return FALSE;
		}
		$a_setoption = [
			//Tasmota\tools\decode-status.py
			"Save power state and use after restart",
			"Restrict button actions to single, double and hold",
			"Show value units in JSON messages",
			"MQTT enabled",
			"Respond as Command topic instead of RESULT",
			"MQTT retain on Power",
			"MQTT retain on Button",
			"MQTT retain on Switch",
			"Convert temperature to Fahrenheit",
			"MQTT retain on Sensor",
			"MQTT retained LWT to OFFLINE when topic changes",
			"Swap Single and Double press Button",
			"Do not use flash page rotate",
			"Button single press only",
			"Power interlock mode",
			"Do not allow PWM control",
			"Reverse clock",
			"Allow entry of decimal color values",
			"CO2 color to light signal",
			"HASS discovery",
			"Do not control Power with Dimmer",
			"Energy monitoring while powered off",
			"MQTT serial",
			"Rules",
			"Rules once mode",
			"KNX",
			"Use Power device index on single relay devices",
			"KNX enhancement",
			"",
			"",
			"",
			"",
		];
		
		if (is_array($options)) {
			$options = $options[0];
		}
		
		$decodedOptopns = new stdClass();
		
		$options = intval($options, 16);
		for ($i = 0; $i < count($a_setoption); $i++) {
			$optionV                           = ($options >> $i) & 1;
			$SetOPtion                         = "SetOption" . $i;
			$decodedOptopns->$SetOPtion        = new stdClass();
			$decodedOptopns->$SetOPtion->desc  = $a_setoption[$i];
			$decodedOptopns->$SetOPtion->value = $optionV;
			//                $decodedOptopns[ $i ] = [
			//                    "desc"  => $a_setoption[ $i ],
			//                    "value" => $optionV,
			//                ];
			//                debug( $a_setoption[ $i ]." => ".$optionV );
		}
		
		
		return $decodedOptopns;
	}
}


