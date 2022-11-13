<?php

namespace TasmoAdmin;

use GuzzleHttp\Client;
use Throwable;
use ZipArchive;

class SelfUpdate
{
    private string $currentTag;
    private string $zipfile;
    private array $log = [];
    private Config $config;
    private Client $client;

    public function __construct(Config $config, Client $client)
    {
        $this->config = $config;
        $this->client = $client;
        $this->currentTag = $this->config->read("current_git_tag");
        $this->zipfile = _DATADIR_ . "updates/tasmoadmin.zip";
        if (file_exists($this->zipfile)) {
            unlink($this->zipfile);
        }
    }

    public function update(string $releaseUrl, string $latestTag): array
    {
        if (!$this->saveZip($releaseUrl)) {
            $this->log[] = __("ERROR_COULD_NOT_DOWNLOAD_ZIP", "SELFUPDATE");

            return $this->log;
        }

        $this->log[] = __("SUCCESS_DOWNLOADED_ZIP_UPDATE", "SELFUPDATE");
        if ($this->install()) {
            $this->log[] = __("OLD_TAG_VERSION", "SELFUPDATE", [$this->currentTag]);
            $this->log[] = __("NEW_TAG_VERSION", "SELFUPDATE", [$latestTag]);
            $this->config->write("current_git_tag", $latestTag);
        }

        return $this->log;
    }

    private function saveZip(string $url): bool
    {
        ini_set("max_execution_time", "240");
        set_time_limit("240");
        $this->client->get($url, ['sink' => $this->zipfile]);
        ini_set("max_execution_time", 30);

        return filesize($this->zipfile) > 0;
    }

    private function install(): bool
    {
        $file = $this->zipfile;        // full path to zip file needing extracted
        $temp = _TMPDIR_;        // full path to temp dir to process extractions
        $path = _APPROOT_;       // full path to final destination to put the files (not the folder)

        $zip = new ZipArchive();
        $res = $zip->open($file);
        if (!$res) {
            $this->log[] = __("ERROR_FILE_EXTRACTED_TO", "SELFUPDATE", [$file, $temp]);
            return false;
        }

        $firstDir = $zip->getNameIndex(0); // holds the name of the first directory
        $zip->extractTo($temp);
        $zip->close();
        $this->log[] = __(
            "SUCCESS_FILE_EXTRACTED_TO",
            "SELFUPDATE",
            [$file, $temp]
        );

        if (!$this->preInstallChecks($temp)) {
            return false;
        }

        if (empty($firstDir)) {
            $this->log[] = __("ERROR_EMPTY_FIRST_DIR", "SELFUPDATE");
        } else {
            $firstDir = realpath($temp . '/' . $firstDir);
            $this->log[] = __("FIRST_DIRECTORY", "SELFUPDATE", [$firstDir]);
            if (is_dir($firstDir)) {
                if ($this->config->read("update_channel") === "dev") {
                    $this->log[] = __("CONTENT_COPY_SKIP_DEV", "SELFUPDATE");
                    if ($this->removeDirectory($firstDir)) {
                        $this->log[] = __("TEMP_DIR_DELETED", "SELFUPDATE");
                    } else {
                        echo 'Error deleting temp directory!<br />';
                        $this->log[] = __("ERROR_COULD_NOT_DELETE_TEMP_DIR", "SELFUPDATE");
                    }
                } elseif ($this->copyDirectoryContents($firstDir, $path)) {
                    $this->log[] = __("CONTENT_COPY_DONE", "SELFUPDATE");

                    if ($this->removeDirectory($firstDir)) {
                        $this->log[] = __("TEMP_DIR_DELETED", "SELFUPDATE");
                        $this->log[] = "<br/><strong>" . __("COPY_DONE", "SELFUPDATE") . "</strong>";
                    } else {
                        echo 'Error deleting temp directory!<br />';
                        $this->log[] = __("ERROR_COULD_NOT_DELETE_TEMP_DIR", "SELFUPDATE");
                    }
                } else {
                    echo 'Error copying directory contents!<br />';
                    $this->log[] = __("ERROR_COULD_NOT_COPY_UPDATE", "SELFUPDATE");
                }
            } else {
                $this->log[] = __("ERROR_EMPTY_FIRST_DIR", "SELFUPDATE");
            }
        }

        return true;
    }

    private function removeDirectory($directory, $options = []): bool
    {
        if (!isset($options['traverseSymlinks'])) {
            $options['traverseSymlinks'] = false;
        }
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            if (is_dir("$directory/$file")) {
                if (!$options['traverseSymlinks'] && is_link(rtrim($file, DIRECTORY_SEPARATOR))) {
                    unlink("$directory/$file");
                } else {
                    $this->removeDirectory("$directory/$file", $options);
                }
            } else {
                unlink("$directory/$file");
            }
        }

        return rmdir($directory);
    }

    private function copyDirectoryContents($source, $destination): bool
    {
        if (!is_dir($source)) {
            return false;
        }

        if (!is_dir($destination)) {
            mkdir($destination);
        }

        if (is_dir($destination)) {
            $files = array_diff(scandir($source), ['.', '..']);
            foreach ($files as $file) {
                if (is_dir("$source/$file")) {
                    $this->copyDirectoryContents("$source/$file", "$destination/$file");
                } else {
                    copy("$source/$file", "$destination/$file");
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Run some pre-install checks by looking for a file n the next
     * version that performs some sanity checks.
     *
     * @param string $tempDir
     * @return bool
     */
    private function preInstallChecks(string $tempDir): bool
    {
        try {
            $preInstallCheckFile = $tempDir . '/tasmoadmin/includes/preinstallchecks.php';
            if (!file_exists($preInstallCheckFile)) {
                return true;
            }

            $check = require $preInstallCheckFile;

            $result = $check->run();

            if (!$result->isValid()) {
                foreach ($result->getErrors() as $error) {
                    $this->log[] = $error;
                }
            }

            return $result->isValid();
        } catch (Throwable $e) {
            $this->log[] = 'Failed to perform pre-install checks';
            $this->log[] = $e->getMessage();
            return true;
        }
    }
}
