<?php

namespace TasmoAdmin;

use ZipArchive;

class SelfUpdate
{
	private string $currentTag;
	private string $zipfile;
	private array $log = [];
	private Config $config;
	
	public function __construct(Config $config)
	{
		$this->config = $config;
		$this->currentTag = $this->config->read("current_git_tag");
		$this->zipfile = _DATADIR_ . "updates/tasmoadmin.zip";
		if (file_exists($this->zipfile)) {
			unlink($this->zipfile);
		}
	}
	
	public function update(string $releaseUrl, string $latestTag): array
	{
		if ($this->saveZip($releaseUrl)) {
			$this->log[] = __("SUCCESS_DOWNLOADED_ZIP_UPDATE", "SELFUPDATE");
			if ($this->install()) {
				$this->log[] = __("OLD_TAG_VERSION", "SELFUPDATE", [$this->currentTag]);
				$this->log[] = __("NEW_TAG_VERSION", "SELFUPDATE", [$latestTag]);
				$this->config->write("current_git_tag", $latestTag);
			}
		}
		else {
			$this->log[] = __("ERROR_COULD_NOT_DOWNLOAD_ZIP", "SELFUPDATE");
		}
		
		return $this->log;
	}
	
	private function saveZip(string $url)
    {
		ini_set("max_execution_time", "240");
		set_time_limit("240");
		$file = fopen($this->zipfile, 'w');
		$ch   = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		// set cURL options
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt(
			$ch,
			CURLOPT_USERAGENT,
			'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'
		);
		// set file handler option
		curl_setopt($ch, CURLOPT_FILE, $file);
		// execute cURL
		curl_exec($ch);
		// close cURL
		// close file
		if (curl_error($ch)) {
			$result = [
				"ERROR" => __("ERROR_CURL", "SELFUPDATE") . " - " . curl_errno($ch) . ": " . curl_error(
						$ch
					),
			];
			
			return FALSE;
		}
		curl_close($ch);
		
		fclose($file);
		ini_set("max_execution_time", 30);
		
		return (filesize($this->zipfile) > 0) ? TRUE : FALSE;
		
	}
	
	private function install() {
		$file = $this->zipfile;        // full path to zip file needing extracted
		$temp = _TMPDIR_;        // full path to temp dir to process extractions
		$path = _APPROOT_;       // full path to final destination to put the files (not the folder)
		
		$firstDir = NULL;       // holds the name of the first directory
		
		$zip = new ZipArchive();
		$res = $zip->open($file);
		if ($res === TRUE) {
			$firstDir = $zip->getNameIndex(0);
			$zip->extractTo($temp);
			$zip->close();
			$this->log[] = __(
				"SUCCESS_FILE_EXTRACTED_TO",
				"SELFUPDATE",
				[$file, $temp]
			);
		}
		else {
			$this->log[] = __("ERROR_FILE_EXTRACTED_TO", "SELFUPDATE", [$file, $temp]);
		}
		
		
		if (empty($firstDir)) {
			$this->log[] = __("ERROR_EMPTY_FIRST_DIR", "SELFUPDATE");
		}
		else {
			$firstDir    = realpath($temp . '/' . $firstDir);
			$this->log[] = __("FIRST_DIRECTORY", "SELFUPDATE", [$firstDir]);
			if (is_dir($firstDir)) {
				if ($this->config->read("update_channel") == "dev") {
					$this->log[] = __("CONTENT_COPY_SKIP_DEV", "SELFUPDATE");
					if ($this->removeDirectory($firstDir)) {
						$this->log[] = __("TEMP_DIR_DELETED", "SELFUPDATE");
					}
					else {
						echo 'Error deleting temp directory!<br />';
						$this->log[] = __("ERROR_COULD_NOT_DELETE_TEMP_DIR", "SELFUPDATE");
					}
				}
				else {
					if ($this->copyDirectoryContents($firstDir, $path)) {
						$this->log[] = __("CONTENT_COPY_DONE", "SELFUPDATE");
						
						if ($this->removeDirectory($firstDir)) {
							$this->log[] = __("TEMP_DIR_DELETED", "SELFUPDATE");
							$this->log[] = "<br/><strong>" . __("COPY_DONE", "SELFUPDATE") . "</strong>";
							
						}
						else {
							echo 'Error deleting temp directory!<br />';
							$this->log[] = __("ERROR_COULD_NOT_DELETE_TEMP_DIR", "SELFUPDATE");
						}
						
					}
					else {
						echo 'Error copying directory contents!<br />';
						$this->log[] = __("ERROR_COULD_NOT_COPY_UPDATE", "SELFUPDATE");
					}
				}
				
				
			}
			else {
				$this->log[] = __("ERROR_EMPTY_FIRST_DIR", "SELFUPDATE");
			}
		}
		
		return TRUE;
		
	}
	
	private function removeDirectory($directory, $options = []) {
		if (!isset($options['traverseSymlinks'])) {
			$options['traverseSymlinks'] = FALSE;
		}
		$files = array_diff(scandir($directory), ['.', '..']);
		foreach ($files as $file) {
			if (is_dir("$directory/$file")) {
				if (!$options['traverseSymlinks'] && is_link(rtrim($file, DIRECTORY_SEPARATOR))) {
					unlink("$directory/$file");
				}
				else {
					$this->removeDirectory("$directory/$file", $options);
				}
			}
			else {
				unlink("$directory/$file");
			}
		}
		
		return rmdir($directory);
	}
	
	private function copyDirectoryContents($source, $destination, $create = TRUE) {
		if (!is_dir($source)) {
			return FALSE;
		}
		
		if (!is_dir($destination) && $create === TRUE) {
			mkdir($destination);
		}
		
		if (is_dir($destination)) {
			$files = array_diff(scandir($source), ['.', '..']);
			foreach ($files as $file) {
				if (is_dir("$source/$file")) {
					$this->copyDirectoryContents("$source/$file", "$destination/$file");
				}
				else {
					copy("$source/$file", "$destination/$file");
				}
			}
			
			return TRUE;
		}
		
		return FALSE;
	}

	
	/**
	 * @return string
	 */
	public function getLatestTag() {
		return $this->latestTag;
	}
	

	
	/**
	 * @return string
	 */
	public function getCurrentTag() {
		return $this->currentTag;
	}
}
