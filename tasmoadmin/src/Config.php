<?php

namespace TasmoAdmin;

use Symfony\Component\Filesystem\Filesystem;

class Config
{
    private bool $debug = false;

    private string $dataDir;

    private string $appRoot;

    private string $cfgFile;

    private Filesystem $filesystem;

    private array $defaults = [
        "ota_server_ip"         => "",
        "ota_server_port"       => "",
        "username"              => "",
        "password"              => "",
        "refreshtime"           => "8",
        "current_git_tag"       => "",
        "update_automatic_lang" => "tasmota-sensors",
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
        "update_fe_check"      => "0",
        "update_be_check"      => "1",
        "update_newer_only"    => "1",
        "auto_update_channel"  => "stable",
        "force_upgrade"  => "0",
    ];

    public function __construct(string $dataDir, string $appRoot)
    {
        $this->dataDir = $dataDir;
        $this->appRoot = $appRoot;
        $this->cfgFile = $this->dataDir . "MyConfig.json";
        $cfgFile140 = $this->dataDir . "MyConfig.php";       //for tag 1.4.0 migration
        $this->filesystem = new Filesystem();

        //init default values
        $this->defaults["ota_server_ip"]   = !empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : "";
        $this->defaults["ota_server_port"] = !empty($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : "";

        if (!empty($_SERVER["SERVER_ADDR"])) {
            $ipBlocks                             = explode(".", $_SERVER["SERVER_ADDR"]);
            $ipBlocks[3]                          = 2;
            $this->defaults["scan_from_ip"] = implode(".", $ipBlocks);
            $ipBlocks[3]                          = 254;
            $this->defaults["scan_to_ip"]   = implode(".", $ipBlocks);
        }

        if (file_exists($this->appRoot . ".dockerenv")) {
            $this->defaults["update_channel"] = "docker";
        }

        //init default values end


        if (!is_dir($this->dataDir)) {
            var_dump(debug_backtrace());
            die($this->dataDir . " is NO DIR! | __construct()");
        }
        if (!is_writable($this->dataDir)) {
            var_dump(debug_backtrace());
            die($this->dataDir . " is NOT WRITEABLE! | __construct()");
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
            if (file_exists($cfgFile140)) {
                $config = include $cfgFile140;
                if ($config === 1) { //its empty
                    $config = [];
                }
            }

            $config     = array_merge($this->defaults, $config);
            $configJSON = json_encode($config, JSON_PRETTY_PRINT);
            if (!fwrite($fh, $configJSON)) {
                die("COULD NOT CREATE OR WRITE IN CONFIG FILE");
            }
            fclose($fh);
        }

        $config = $this->cleanConfig();
        foreach ($this->defaults as $configName => $configValue) {
            if (!array_key_exists($configName, $config)) {
                $this->write($configName, $configValue);
            }
        }

        if (file_exists($this->appRoot . ".version")) {
            $version = trim(file_get_contents($this->appRoot . ".version"));
            if ($config["current_git_tag"] !== $version) {
                $this->write("current_git_tag", $version);
            }
        } elseif (!empty(getenv("BUILD_VERSION"))
            && ($config["current_git_tag"] != getenv(
                "BUILD_VERSION"
            ))) {
            $this->write("current_git_tag", getenv("BUILD_VERSION"));
        }
    }

    private function cleanConfig(): array
    {
        $config = $this->readAll(true);

        $modified = false;
        if (!empty($config["page"])) {
            unset($config["page"]);
            $modified = true;
        }

        if (!empty($config["use_gzip_package"])) {
            unset($config["use_gzip_package"]);
            $modified = true;
        }

        if ($modified) {
            $this->writeFile($config);
        }

        return $config;
    }

    public function read(string $key)
    {
        $this->logDebug("PERFORM READ (" . $key . ")");
        $configJSON = file_get_contents($this->cfgFile);
        if ($configJSON === false) {
            var_dump(debug_backtrace());
            die("could not read MyConfig.json in read");
        }

        $config = json_decode($configJSON, true);
        if (json_last_error() != 0) {
            var_dump($configJSON);
            die("JSON CONFIG ERROR in read: " . json_last_error() . " => " . json_last_error_msg());
        }

        return $config[$key] ?? null;
    }

    public function write(string $key, $value): void
    {
        $this->writeAll([$key => $value]);
    }

    public function writeAll(array $updates): void
    {
        $this->logDebug("PERFORM READ FOR WRITE");
        $configJSON = file_get_contents($this->cfgFile);
        if ($configJSON === false) {
            var_dump(debug_backtrace());
            die("could not read MyConfig.json in write");
        }
        $config = json_decode($configJSON, true);
        foreach ($updates as $key => $value) {
            if ($value === 0 && array_key_exists($key, $this->defaults)) {
                $value = $this->defaults[$key];
            }

            if ($value === null) {
                unset($config[$key]);
            } else {
                $value = trim($value);
                $config[$key] = $value;
            }

            $this->logDebug("PERFORM WRITE ({$key} => {$value})");
        }

        $this->writeFile($config);
    }

    public function readAll($inclPassword = false)
    {
        $this->logDebug("PERFORM READALL");
        $configJSON = file_get_contents($this->cfgFile);
        if ($configJSON === false) {
            var_dump(debug_backtrace());
            die("could not read MyConfig.json in readAll");
        } else {
            $config = json_decode($configJSON, true);
        }
        if (json_last_error() !== 0) {
            die("JSON CONFIG ERROR in readAll: " . json_last_error() . " => " . json_last_error_msg());
        }

        if (!$inclPassword) {
            unset($config["password"]);
        }

        return $config;
    }

    public function clean(): void
    {
        $config = $this->readAll(true);

        foreach ($config as $key => $value) {
            if (!isset($this->defaults[$key])) {
                $config[$key] = null;
            }
        }

        $this->writeAll($config);
    }

    private function logDebug($message): void
    {
        if ($this->debug) {
            debug($message);
        }
    }

    private function writeFile(array $config): void
    {
        $configJSON  = json_encode($config, JSON_PRETTY_PRINT);
        if (!is_dir($this->dataDir)) {
            var_dump(debug_backtrace());
            die($this->dataDir . " is NO DIR! | write()");
        }
        if (!is_writable($this->dataDir)) {
            var_dump(debug_backtrace());
            die($this->dataDir . " is NOT WRITEABLE! | write()");
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

        $tempFile = $this->filesystem->tempnam($this->dataDir, 'config');
        $this->filesystem->dumpFile($tempFile, $configJSON);
        $this->filesystem->rename($tempFile, $this->cfgFile, true);
    }
}
