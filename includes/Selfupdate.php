<?php
	
	
	class Selfupdate {
		
		private $repoUrl    = "https://api.github.com/repos/reloxx13/SonWEB";
		private $latestSha  = "";
		private $currentSha = "";
		private $zipfile    = "";
		private $log        = array();
		private $Config     = NULL;
		
		
		public function __construct( $Config ) {
			
			$this->Config     = $Config;
			$this->currentSha = $this->Config->read( "current_git_sha" );
			$this->zipfile    = _DATADIR_."updates/sonoff.zip";
			if ( file_exists( $this->zipfile ) ) {
				unlink( $this->zipfile );
			}
		}
		
		public function checkForUpdate() {
			$action = "/commits";
			$result = [
				"update" => FALSE,
				"error"  => FALSE,
				"msg"    => "",
			];
			
			$commits = $this->doRequest( $action );
			if ( isset( $commits[ "ERROR" ] ) ) {
				$result[ "error" ] = TRUE;
				$result[ "msg" ]   = $commits[ "ERROR" ];
			} else {
				if ( isset( $commits[ 0 ]->sha ) ) {
					$this->latestSha = $commits[ 0 ]->sha;
					
					if ( $this->currentSha != $this->latestSha ) {
						$result[ "update" ] = TRUE;
					}
				}
			}
			
			return $result;
		}
		
		public function update() {
			$this->checkForUpdate();
			$action = "/zipball/master";
			if ( $this->saveZip( $action ) ) {
				$this->log[] = __( "SUCCESS_DOWNLOADED_ZIP_UPDATE", "SELFUPDATE" );
				if ( $this->install() ) {
					$this->log[] = __( "OLD_SHA_VERSION", "SELFUPDATE", [ $this->currentSha ] );
					$this->log[] = __( "NEW_SHA_VERSION", "SELFUPDATE", [ $this->latestSha ] );
					$this->Config->write( "current_git_sha", $this->latestSha );
					$this->currentSha = $this->latestSha;
				}
			} else {
				$this->log[] = __( "ERROR_COULD_NOT_DOWNLOAD_ZIP", "SELFUPDATE" );
			}
			
			return $this->log;
			
		}
		
		/**
		 * @return string
		 */
		public function getRepoUrl() {
			return $this->repoUrl;
		}
		
		/**
		 * @param string $repoUrl
		 */
		public function setRepoUrl( $repoUrl ) {
			$this->repoUrl = $repoUrl;
		}
		
		/**
		 * @return string
		 */
		public function getLatestSha() {
			return $this->latestSha;
		}
		
		/**
		 * @param string $latestSha
		 */
		public function setLatestSha( $latestSha ) {
			$this->latestSha = $latestSha;
		}
		
		/**
		 * @return string
		 */
		public function getCurrentSha() {
			return $this->currentSha;
		}
		
		/**
		 * @param string $currentSha
		 */
		public function setCurrentSha( $currentSha ) {
			$this->currentSha = $currentSha;
		}
		
		/**
		 * @return string
		 */
		public function getZipfile() {
			return $this->zipfile;
		}
		
		/**
		 * @param string $zipfile
		 */
		public function setZipfile( $zipfile ) {
			$this->zipfile = $zipfile;
		}
		
		/**
		 * @return array
		 */
		public function getLog() {
			return $this->log;
		}
		
		/**
		 * @param array $log
		 */
		public function setLog( $log ) {
			$this->log = $log;
		}
		
		/**
		 * @return null
		 */
		public function getConfig() {
			return $this->Config;
		}
		
		/**
		 * @param null $Config
		 */
		public function setConfig( $Config ) {
			$this->Config = $Config;
		}
		
		private function install() {
			$file = $this->zipfile;        // full path to zip file needing extracted
			$temp = _TMPDIR_;        // full path to temp dir to process extractions
			$path = _APPROOT_;       // full path to final destination to put the files (not the folder)
			
			$firstDir = NULL;       // holds the name of the first directory
			
			$zip = new ZipArchive;
			$res = $zip->open( $file );
			if ( $res === TRUE ) {
				$firstDir = $zip->getNameIndex( 0 );
				$zip->extractTo( $temp );
				$zip->close();
				$this->log[] = __(
					"SUCCESS_FILE_EXTRACTED_TO",
					"SELFUPDATE",
					[ $file, $temp ]
				);
			} else {
				$this->log[] = __( "ERROR_FILE_EXTRACTED_TO", "SELFUPDATE", [ $file, $temp ] );
			}
			
			
			if ( empty( $firstDir ) ) {
				$this->log[] = __( "ERROR_EMPTY_FIRST_DIR", "SELFUPDATE" );
			} else {
				$firstDir    = realpath( $temp.'/'.$firstDir );
				$this->log[] = __( "FIRST_DIRECTORY", "SELFUPDATE", [ $firstDir ] );
				if ( is_dir( $firstDir ) ) {
					if ( $this->copyDirectoryContents( $firstDir, $path ) ) {
						$this->log[] = __( "CONTENT_COPY_DONE", "SELFUPDATE" );
						
						if ( $this->removeDirectory( $firstDir ) ) {
							$this->log[] = __( "TEMP_DIR_DELETED", "SELFUPDATE" );
							$this->log[] = "<br/><strong>".__( "COPY_DONE", "SELFUPDATE" )."</strong>";
							
						} else {
							echo 'Error deleting temp directory!<br />';
							$this->log[] = __( "ERROR_COULD_NOT_DELETE_TEMP_DIR", "SELFUPDATE" );
						}
						
					} else {
						echo 'Error copying directory contents!<br />';
						$this->log[] = __( "ERROR_COULD_NOT_COPY_UPDATE", "SELFUPDATE" );
					}
					
				} else {
					$this->log[] = __( "ERROR_EMPTY_FIRST_DIR", "SELFUPDATE" );
				}
			}
			
			return TRUE;
			
		}
		
		
		private function copyDirectoryContents( $source, $destination, $create = TRUE ) {
			if ( !is_dir( $source ) ) {
				return FALSE;
			}
			
			if ( !is_dir( $destination ) && $create === TRUE ) {
				mkdir( $destination );
			}
			
			if ( is_dir( $destination ) ) {
				$files = array_diff( scandir( $source ), array( '.', '..' ) );
				foreach ( $files as $file ) {
					if ( is_dir( "$source/$file" ) ) {
						$this->copyDirectoryContents( "$source/$file", "$destination/$file" );
					} else {
						copy( "$source/$file", "$destination/$file" );
					}
				}
				
				return TRUE;
			}
			
			return FALSE;
		}
		
		private function removeDirectory( $directory, $options = array() ) {
			if ( !isset( $options[ 'traverseSymlinks' ] ) ) {
				$options[ 'traverseSymlinks' ] = FALSE;
			}
			$files = array_diff( scandir( $directory ), array( '.', '..' ) );
			foreach ( $files as $file ) {
				if ( is_dir( "$directory/$file" ) ) {
					if ( !$options[ 'traverseSymlinks' ] && is_link( rtrim( $file, DIRECTORY_SEPARATOR ) ) ) {
						unlink( "$directory/$file" );
					} else {
						$this->removeDirectory( "$directory/$file", $options );
					}
				} else {
					unlink( "$directory/$file" );
				}
			}
			
			return rmdir( $directory );
		}
		
		
		private function doRequest( $action = "" ) {
			ini_set( "max_execution_time", "240" );
			set_time_limit( "240" );
			
			$url = $this->repoUrl.$action;
			$ch  = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_FAILONERROR, TRUE );
			curl_setopt(
				$ch,
				CURLOPT_USERAGENT,
				'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'
			);
			$result = json_decode( curl_exec( $ch ) );
			if ( curl_error( $ch ) ) {
				$result = [
					"ERROR" => __( "ERROR_CURL", "SELFUPDATE" )." - ".curl_errno( $ch ).": ".curl_error(
							$ch
						),
				];
			}
			curl_close( $ch );
			
			ini_set( "max_execution_time", 30 );
			
			return $result;
		}
		
		private function saveZip( $action = "" ) {
			
			
			ini_set( "max_execution_time", "240" );
			set_time_limit( "240" );
			$url = $this->repoUrl.$action;
			//https://codeload.github.com/reloxx13/SonWEB/legacy.zip/master
			$file = fopen( $this->zipfile, 'w' );
			$ch   = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			// set cURL options
			curl_setopt( $ch, CURLOPT_FAILONERROR, TRUE );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt(
				$ch,
				CURLOPT_USERAGENT,
				'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'
			);
			// set file handler option
			curl_setopt( $ch, CURLOPT_FILE, $file );
			// execute cURL
			curl_exec( $ch );
			// close cURL
			// close file
			if ( curl_error( $ch ) ) {
				$result = [
					"ERROR" => __( "ERROR_CURL", "SELFUPDATE" )." - ".curl_errno( $ch ).": ".curl_error(
							$ch
						),
				];
				
				return FALSE;
			}
			curl_close( $ch );
			
			fclose( $file );
			ini_set( "max_execution_time", 30 );
			
			return ( filesize( $this->zipfile ) > 0 ) ? TRUE : FALSE;
			
		}
	}
