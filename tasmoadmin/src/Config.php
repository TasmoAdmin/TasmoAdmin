<?php

namespace TasmoAdmin;

use Symfony\Component\Filesystem\Filesystem;

class Config
{
    private const GIT_DESCRIBE_COMMAND = 'git -C %s describe --tags --always --dirty 2>/dev/null';
    private const GIT_BRANCH_COMMAND = 'git -C %s rev-parse --abbrev-ref HEAD 2>/dev/null';

    private string $dataDir;

    private string $appRoot;

    private string $cfgFile;

    private Filesystem $filesystem;

    private ?DevicePasswordCipher $configPasswordCipher = null;

    private array $defaults = [
        'ota_server_ip' => '',
        'ota_server_port' => '',
        'username' => '',
        'password' => '',
        'refreshtime' => '8',
        'current_git_tag' => '',
        'current_git_branch' => '',
        'update_automatic_lang' => 'tasmota-sensors',
        'update_automatic_lang_esp32' => 'tasmota32',
        'nightmode' => 'auto',
        'login' => '1',
        'scan_from_ip' => '192.168.178.2',
        'scan_to_ip' => '192.168.178.254',
        'additional_scan_ranges' => '',
        'port' => '80',
        'homepage' => 'start',
        'check_for_updates' => '3',
        'minimize_resources' => '1',
        'update_channel' => 'stable',
        'hide_copyright' => '1',
        'show_search' => '1',
        'confirm_device_toggles' => '0',
        'update_fe_check' => '0',
        'update_be_check' => '1',
        'update_newer_only' => '1',
        'auto_update_channel' => 'stable',
        'force_upgrade' => '0',
        'connect_timeout' => '5',
        'timeout' => '5',
        'request_concurrency' => '50',
        'mqtt_discovery_host' => '',
        'mqtt_discovery_port' => '1883',
        'mqtt_discovery_username' => '',
        'mqtt_discovery_password' => '',
        'mqtt_discovery_cmnd_prefix' => 'cmnd',
        'mqtt_discovery_stat_prefix' => 'stat',
        'mqtt_discovery_tele_prefix' => 'tele',
        'mqtt_discovery_subscriptions' => 'tele/+/LWT',
        'mqtt_discovery_timeout_seconds' => '5',
    ];

    private array $cachedConfig = [];

    public function __construct(string $dataDir, string $appRoot)
    {
        $this->dataDir = $dataDir;
        $this->appRoot = $appRoot;
        $this->cfgFile = $this->dataDir.'MyConfig.json';
        $cfgFile140 = $this->dataDir.'MyConfig.php';       // for tag 1.4.0 migration
        $this->filesystem = new Filesystem();

        // init default values
        $this->defaults['ota_server_ip'] = !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $this->defaults['ota_server_port'] = !empty($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '';

        if (!empty($_SERVER['SERVER_ADDR'])) {
            $ipBlocks = explode('.', $_SERVER['SERVER_ADDR']);
            $ipBlocks[3] = 2;
            $this->defaults['scan_from_ip'] = implode('.', $ipBlocks);
            $ipBlocks[3] = 254;
            $this->defaults['scan_to_ip'] = implode('.', $ipBlocks);
        }

        if (file_exists($this->appRoot.'.dockerenv')) {
            $this->defaults['update_channel'] = 'docker';
        }

        // init default values end

        if (!is_dir($this->dataDir)) {
            var_dump(debug_backtrace());

            exit($this->dataDir.' is NO DIR! | __construct()');
        }
        if (!is_writable($this->dataDir)) {
            var_dump(debug_backtrace());

            exit($this->dataDir.' is NOT WRITEABLE! | __construct()');
        }

        if (!file_exists($this->cfgFile)) { // create file if not exists
            $config = [];
            /*
             * MIGRATE FROM MyConfig.php tp MyConfig.json
             * Read old data and save in new json format
             * Tag 1.4.0
             */
            if (file_exists($cfgFile140)) {
                $config = include $cfgFile140;
                if (1 === $config) { // its empty
                    $config = [];
                }
            }

            $config = array_merge($this->defaults, $config);
            $this->writeFile($config);
        }

        $config = $this->cleanConfig();
        foreach ($this->defaults as $configName => $configValue) {
            if (!array_key_exists($configName, $config)) {
                $this->write($configName, $configValue);
            }
        }

        $currentGitTag = $this->resolveCurrentGitTag();
        if (null !== $currentGitTag && $config['current_git_tag'] !== $currentGitTag) {
            $this->write('current_git_tag', $currentGitTag);
        }

        $currentGitBranch = $this->resolveCurrentGitBranch();
        if (null !== $currentGitBranch && $config['current_git_branch'] !== $currentGitBranch) {
            $this->write('current_git_branch', $currentGitBranch);
        } elseif (null === $currentGitBranch && '' !== $config['current_git_branch']) {
            $this->write('current_git_branch', '');
        }
    }

    public function read(string $key): ?string
    {
        $config = $this->readAll();

        if (!array_key_exists($key, $config)) {
            return null;
        }

        return htmlspecialchars($config[$key]);
    }

    public function write(string $key, $value): void
    {
        $this->writeAll([$key => $value]);
    }

    public function writeAll(array $updates): void
    {
        $config = $this->readAll();
        foreach ($updates as $key => $value) {
            if (0 === $value && array_key_exists($key, $this->defaults)) {
                $value = $this->defaults[$key];
            }

            if (null === $value) {
                unset($config[$key]);
            } else {
                $value = trim($value);
                $config[$key] = $value;
            }
        }

        $this->writeFile($config);
    }

    public function readAll()
    {
        if (!empty($this->cachedConfig)) {
            return $this->cachedConfig;
        }

        $configJSON = file_get_contents($this->cfgFile);
        if (false === $configJSON) {
            var_dump(debug_backtrace());

            exit('could not read MyConfig.json');
        }

        $config = json_decode($configJSON, true);
        if (0 !== json_last_error()) {
            exit('JSON CONFIG ERROR: '.json_last_error().' => '.json_last_error_msg());
        }

        $this->cachedConfig = $this->decryptSensitiveValues($config);

        return $this->cachedConfig;
    }

    public function clean(): void
    {
        $config = $this->readAll();

        foreach ($config as $key => $value) {
            if (!isset($this->defaults[$key])) {
                $config[$key] = null;
            }
        }

        $this->writeAll($config);
    }

    public function getConnectTimeout(): int
    {
        return (int) $this->read('connect_timeout');
    }

    public function getTimeout(): int
    {
        return (int) $this->read('timeout');
    }

    public function getRequestConcurrency(): int
    {
        return (int) $this->read('request_concurrency');
    }

    private function cleanConfig(): array
    {
        $config = $this->readAll();

        $modified = false;

        $configKeys = [
            'page',
            'use_gzip_package',
        ];

        foreach ($configKeys as $key) {
            if (!empty($config[$key])) {
                unset($config[$key]);
                $modified = true;
            }
        }
        if ($modified) {
            $this->writeFile($config);
        }

        return $config;
    }

    private function resolveCurrentGitTag(): ?string
    {
        if (file_exists($this->appRoot.'.version')) {
            $version = trim(file_get_contents($this->appRoot.'.version'));

            return '' !== $version ? $version : null;
        }

        $buildVersion = getenv('BUILD_VERSION');
        if (false !== $buildVersion && '' !== trim($buildVersion)) {
            return trim($buildVersion);
        }

        $gitVersion = $this->runGitCommand(self::GIT_DESCRIBE_COMMAND);
        if (null === $gitVersion) {
            return null;
        }

        return $gitVersion;
    }

    private function resolveCurrentGitBranch(): ?string
    {
        if (file_exists($this->appRoot.'.version')) {
            return null;
        }

        $buildVersion = getenv('BUILD_VERSION');
        if (false !== $buildVersion && '' !== trim($buildVersion)) {
            return null;
        }

        $gitBranch = $this->runGitCommand(self::GIT_BRANCH_COMMAND);
        if (null === $gitBranch || 'HEAD' === $gitBranch) {
            return null;
        }

        return $gitBranch;
    }

    private function runGitCommand(string $commandTemplate): ?string
    {
        $command = sprintf($commandTemplate, escapeshellarg(rtrim($this->appRoot, '/')));
        $output = shell_exec($command);
        if (null === $output) {
            return null;
        }

        $output = trim($output);

        return '' !== $output ? $output : null;
    }

    private function writeFile(array $config): void
    {
        $encryptedConfig = $this->encryptSensitiveValues($config);
        $this->cachedConfig = $this->decryptSensitiveValues($encryptedConfig);

        $configJson = json_encode($encryptedConfig, JSON_PRETTY_PRINT);
        if (!is_dir($this->dataDir)) {
            var_dump(debug_backtrace());

            exit($this->dataDir.' is NO DIR! | write()');
        }
        if (!is_writable($this->dataDir)) {
            var_dump(debug_backtrace());

            exit($this->dataDir.' is NOT WRITEABLE! | write()');
        }
        if (!file_exists($this->cfgFile)) {
            $this->filesystem->touch($this->cfgFile);
        }
        if (!is_writable($this->cfgFile)) {
            var_dump(debug_backtrace());

            exit($this->cfgFile.' is NOT WRITEABLE! | write()');
        }

        if (empty($configJson)) {
            var_dump($configJson);
            var_dump(debug_backtrace());

            exit('configJSON IS EMPTY! | write()');
        }

        $tempFile = $this->filesystem->tempnam($this->dataDir, 'config');
        $this->filesystem->dumpFile($tempFile, $configJson);
        $this->filesystem->rename($tempFile, $this->cfgFile, true);
    }

    private function encryptSensitiveValues(array $config): array
    {
        foreach ($this->getEncryptedConfigKeys() as $key) {
            if (!array_key_exists($key, $config)) {
                continue;
            }

            $value = trim((string) $config[$key]);
            if ('' === $value || $this->getConfigPasswordCipher()->isRecognizedEncryptedPayload($value)) {
                $config[$key] = $value;

                continue;
            }

            $config[$key] = $this->getConfigPasswordCipher()->encrypt($value);
        }

        return $config;
    }

    private function decryptSensitiveValues(array $config): array
    {
        foreach ($this->getEncryptedConfigKeys() as $key) {
            if (!array_key_exists($key, $config)) {
                continue;
            }

            $config[$key] = $this->getConfigPasswordCipher()->decrypt((string) $config[$key]);
        }

        return $config;
    }

    private function getEncryptedConfigKeys(): array
    {
        return ['mqtt_discovery_password'];
    }

    private function getConfigPasswordCipher(): DevicePasswordCipher
    {
        if (null === $this->configPasswordCipher) {
            $this->configPasswordCipher = new DevicePasswordCipher(new DevicePasswordKeyProvider($this->dataDir));
        }

        return $this->configPasswordCipher;
    }
}
