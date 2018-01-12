<?php
	
	
	/**
	 * Class Sonoff
	 */
	class Sonoff {
		
		
		/**
		 * @param $ip
		 *
		 * @return mixed
		 */
		public function getAllStatus( $ip ) {
			$cmnd = "Status 0";
			
			$status = $this->doRequest( $ip, $cmnd );
			
			return $status;
		}
		
		
		/**
		 * @param     $ip
		 * @param int $level
		 * @param int $try
		 *
		 * @return mixed
		 */
		public function setWebLog( $ip, $level = 2, $try = 1 ) {
			$cmnd = "Weblog ".$level;
			
			$weblog = $this->doRequest( $ip, $cmnd, $try );
			
			return $weblog;
		}
		
		
		/**
		 * @param $ip
		 * @param $cmnd
		 *
		 * @return mixed|string
		 */
		private function buildCmndUrl( $ip, $cmnd ) {
			$url = "http://".$ip."/cm?cmnd=".$cmnd;
			$url = str_replace( " ", "%20", $url );
			
			return $url;
		}
		
		/**
		 * @param     $ip
		 * @param     $cmnd
		 * @param int $try
		 *
		 * @return mixed
		 */
		private function doRequest( $ip, $cmnd, $try = 1 ) {
			$url = $this->buildCmndUrl( $ip, $cmnd );
			
			$result = NULL;
			
			if ( !$json = @file_get_contents( $url ) ) {
				$result = NULL;
			} else {
				$result = json_decode( $json );
			}
			
			if ( isset( $result->WARNING ) && !empty( $result->WARNING ) && $try == 1 ) {
				$try++;
				//set web log level 2 and try again
				$webLog = $this->setWebLog( $ip, 2, $try );
				if ( !isset( $webLog->WARNING ) && empty( $webLog->WARNING ) ) {
					$result = $this->doRequest( $ip, $cmnd, $try );
				}
			}
			
			
			return $result;
		}
	}