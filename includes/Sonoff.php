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
		 * @param $ip
		 * @param $cmnd
		 *
		 * @return mixed
		 */
		private function doRequest( $ip, $cmnd ) {
			$url = $this->buildCmndUrl( $ip, $cmnd );
			
			
			if ( !$json = @file_get_contents( $url ) ) {
				$result = NULL;
			} else {
				$result = json_decode( $json );
			}
			
			return $result;
			
		}
	}