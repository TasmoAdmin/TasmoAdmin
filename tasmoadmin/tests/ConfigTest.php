<?php

namespace Tests\TasmoAdmin;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Config;

class ConfigTest extends TestCase
{
    private vfsStreamDirectory $root;

    private string $originalPath;

    private array $serverBackup = [];

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('config');
        $this->originalPath = getenv('PATH') ?: '';
        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        putenv('PATH='.$this->originalPath);
        putenv('BUILD_VERSION');
        putenv('TASMOADMIN_DEVICE_PASSWORD_KEY');
        $_SERVER = $this->serverBackup;
    }

    public function testConstructor(): void
    {
        $config = $this->getConfig();
        self::assertEquals('auto', $config->read('nightmode'));
        self::assertEquals('0', $config->read('confirm_device_toggles'));
        self::assertEquals('tele/+/LWT', $config->read('mqtt_discovery_subscriptions'));
        self::assertEquals('1883', $config->read('mqtt_discovery_port'));
    }

    public function testReadInvalidKey(): void
    {
        $config = $this->getConfig();
        self::assertNull($config->read('random_key'));
    }

    public function testReadValidKey(): void
    {
        $config = $this->getConfig();
        self::assertEquals('1', $config->read('hide_copyright'));
    }

    public function testReadEscaped(): void
    {
        $config = $this->getConfig();
        $config->write('escaped', "alert('XSS')");
        self::assertEquals('alert(&#039;XSS&#039;)', $config->read('escaped'));
    }

    public function testWrite(): void
    {
        $config = $this->getConfig();
        $config->write('hide_copyright', '0');
        self::assertEquals('0', $config->read('hide_copyright'));
    }

    public function testWriteRemove(): void
    {
        $config = $this->getConfig();
        $config->write('hide_copyright', null);
        self::assertNull($config->read('hide_copyright'));
    }

    public function testWriteDefault(): void
    {
        $config = $this->getConfig();
        $config->write('hide_copyright', '0');
        self::assertEquals('0', $config->read('hide_copyright'));
        $config->write('hide_copyright', 0);
        self::assertEquals('1', $config->read('hide_copyright'));
    }

    public function testWriteAll(): void
    {
        $config = $this->getConfig();
        $config->writeAll(['hide_copyright' => '0', 'homepage' => 'devices']);
        self::assertEquals('0', $config->read('hide_copyright'));
        self::assertEquals('devices', $config->read('homepage'));
    }

    public function testWriteAllEncryptsMqttDiscoveryPasswordAtRest(): void
    {
        putenv('TASMOADMIN_DEVICE_PASSWORD_KEY='.base64_encode(random_bytes(32)));
        $config = $this->getConfig();

        $config->writeAll(['mqtt_discovery_password' => 'broker-secret']);

        self::assertSame('broker-secret', $config->read('mqtt_discovery_password'));
        $stored = (string) file_get_contents($this->root->url().'/MyConfig.json');
        self::assertStringContainsString('enc:v1:', $stored);
        self::assertStringNotContainsString('broker-secret', $stored);
    }

    public function testReadAllReturnsRawMqttDiscoveryPasswordWithoutHtmlEscaping(): void
    {
        putenv('TASMOADMIN_DEVICE_PASSWORD_KEY='.base64_encode(random_bytes(32)));
        $config = $this->getConfig();
        $config->writeAll(['mqtt_discovery_password' => 'broker&"<>\'secret']);

        self::assertSame('broker&"<>\'secret', $config->readAll()['mqtt_discovery_password']);
        self::assertSame('broker&amp;&quot;&lt;&gt;&#039;secret', $config->read('mqtt_discovery_password'));
    }

    public function testWriteAllDoesNotReencryptRecognizedCiphertext(): void
    {
        putenv('TASMOADMIN_DEVICE_PASSWORD_KEY='.base64_encode(random_bytes(32)));
        $config = $this->getConfig();

        $config->writeAll(['mqtt_discovery_password' => 'broker-secret']);
        $storedConfig = json_decode((string) file_get_contents($this->root->url().'/MyConfig.json'), true, 512, JSON_THROW_ON_ERROR);
        $encryptedValue = $storedConfig['mqtt_discovery_password'];

        $config->writeAll(['mqtt_discovery_password' => $encryptedValue]);
        $rewrittenConfig = json_decode((string) file_get_contents($this->root->url().'/MyConfig.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($encryptedValue, $rewrittenConfig['mqtt_discovery_password']);
        self::assertSame('broker-secret', $config->read('mqtt_discovery_password'));
    }

    public function testWriteAllTrimsWhitespaceOnlyMqttPasswordToEmptyString(): void
    {
        putenv('TASMOADMIN_DEVICE_PASSWORD_KEY='.base64_encode(random_bytes(32)));
        $config = $this->getConfig();

        $config->writeAll(['mqtt_discovery_password' => " \n\t "]);
        $storedConfig = json_decode((string) file_get_contents($this->root->url().'/MyConfig.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('', $config->read('mqtt_discovery_password'));
        self::assertSame('', $storedConfig['mqtt_discovery_password']);
    }

    public function testClean(): void
    {
        $config = $this->getConfig();
        $config->write('random_key', '123');
        self::assertEquals('123', $config->read('random_key'));
        $config->clean();
        self::assertNull($config->read('random_key'));
    }

    public function testConstructorFallsBackToGitDescribeWhenVersionSourcesAreMissing(): void
    {
        putenv('BUILD_VERSION');

        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-'.bin2hex(random_bytes(6));
        $binDir = $appRoot.'/bin';
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($binDir);
        mkdir($dataDir);
        file_put_contents($binDir.'/git', "#!/bin/sh\nif [ \"$3\" = \"describe\" ]; then\n  echo test-dev-version\nelif [ \"$3\" = \"rev-parse\" ]; then\n  echo test-branch\nfi\n");
        chmod($binDir.'/git', 0o755);
        putenv('PATH='.$binDir.':'.$this->originalPath);

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertEquals('test-dev-version', $config->read('current_git_tag'));
            self::assertEquals('test-branch', $config->read('current_git_branch'));
        } finally {
            if (file_exists($dataDir.'/MyConfig.json')) {
                unlink($dataDir.'/MyConfig.json');
            }
            unlink($binDir.'/git');
            rmdir($binDir);
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorUsesVersionFileWhenPresent(): void
    {
        putenv('BUILD_VERSION=v9.9.9');

        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-version-'.bin2hex(random_bytes(6));
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($dataDir);
        file_put_contents($appRoot.'/.version', "v5.1.0\n");

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertSame('v5.1.0', $config->read('current_git_tag'));
            self::assertSame('', $config->read('current_git_branch'));
        } finally {
            if (file_exists($dataDir.'/MyConfig.json')) {
                unlink($dataDir.'/MyConfig.json');
            }
            unlink($appRoot.'/.version');
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorUsesBuildVersionWhenVersionFileIsMissing(): void
    {
        putenv('BUILD_VERSION=v5.1.0-build');

        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-build-'.bin2hex(random_bytes(6));
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($dataDir);

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertSame('v5.1.0-build', $config->read('current_git_tag'));
            self::assertSame('', $config->read('current_git_branch'));
        } finally {
            if (file_exists($dataDir.'/MyConfig.json')) {
                unlink($dataDir.'/MyConfig.json');
            }
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorTrimsBuildVersionWhenVersionFileIsMissing(): void
    {
        putenv("BUILD_VERSION= \tv5.1.0-build \n");

        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-build-trim-'.bin2hex(random_bytes(6));
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($dataDir);

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertSame('v5.1.0-build', $config->read('current_git_tag'));
            self::assertSame('', $config->read('current_git_branch'));
        } finally {
            if (file_exists($dataDir.'/MyConfig.json')) {
                unlink($dataDir.'/MyConfig.json');
            }
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorUsesDockerUpdateChannelWhenDockerenvExists(): void
    {
        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-docker-'.bin2hex(random_bytes(6));
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($dataDir);
        file_put_contents($appRoot.'/.dockerenv', '');

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertSame('docker', $config->read('update_channel'));
            self::assertSame('stable', $config->read('auto_update_channel'));
        } finally {
            if (file_exists($dataDir.'/MyConfig.json')) {
                unlink($dataDir.'/MyConfig.json');
            }
            unlink($appRoot.'/.dockerenv');
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorRemovesLegacyKeysFromExistingConfig(): void
    {
        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-legacy-'.bin2hex(random_bytes(6));
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($dataDir);
        file_put_contents($dataDir.'/MyConfig.json', json_encode([
            'page' => 'start',
            'use_gzip_package' => '1',
            'hide_copyright' => '0',
            'current_git_branch' => '',
            'current_git_tag' => '',
        ], JSON_PRETTY_PRINT));

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertArrayNotHasKey('page', $config->readAll());
            self::assertArrayNotHasKey('use_gzip_package', $config->readAll());
            self::assertSame('0', $config->read('hide_copyright'));
        } finally {
            unlink($dataDir.'/MyConfig.json');
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorMigratesLegacyMyConfigPhpIntoJsonConfig(): void
    {
        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-php-migration-'.bin2hex(random_bytes(6));
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($dataDir);
        file_put_contents($dataDir.'/MyConfig.php', <<<'PHP'
            <?php

            return [
                'hide_copyright' => '0',
                'homepage' => 'devices',
            ];
            PHP);
        file_put_contents($appRoot.'/.version', "v5.1.0\n");

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertSame('0', $config->read('hide_copyright'));
            self::assertSame('devices', $config->read('homepage'));
            self::assertSame('auto', $config->read('nightmode'));
            self::assertFileExists($dataDir.'/MyConfig.json');
        } finally {
            if (file_exists($dataDir.'/MyConfig.json')) {
                unlink($dataDir.'/MyConfig.json');
            }
            unlink($dataDir.'/MyConfig.php');
            unlink($appRoot.'/.version');
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorTreatsScalarLegacyMyConfigPhpAsEmptyConfig(): void
    {
        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-php-empty-'.bin2hex(random_bytes(6));
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($dataDir);
        file_put_contents($dataDir.'/MyConfig.php', "<?php\n\nreturn 1;\n");
        file_put_contents($appRoot.'/.version', "v5.1.0\n");

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertSame('1', $config->read('hide_copyright'));
            self::assertSame('auto', $config->read('nightmode'));
            self::assertSame('1883', $config->read('mqtt_discovery_port'));
        } finally {
            if (file_exists($dataDir.'/MyConfig.json')) {
                unlink($dataDir.'/MyConfig.json');
            }
            unlink($dataDir.'/MyConfig.php');
            unlink($appRoot.'/.version');
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorUsesServerDerivedDefaultsForOtaAndScanRange(): void
    {
        $_SERVER['SERVER_ADDR'] = '10.20.30.40';
        $_SERVER['SERVER_PORT'] = '8080';

        $config = $this->getConfig();

        self::assertSame('10.20.30.40', $config->read('ota_server_ip'));
        self::assertSame('8080', $config->read('ota_server_port'));
        self::assertSame('10.20.30.2', $config->read('scan_from_ip'));
        self::assertSame('10.20.30.254', $config->read('scan_to_ip'));
    }

    public function testConstructorBackfillsMissingDefaultsIntoExistingPartialJsonConfig(): void
    {
        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-backfill-'.bin2hex(random_bytes(6));
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($dataDir);
        file_put_contents($dataDir.'/MyConfig.json', json_encode([
            'hide_copyright' => '0',
            'current_git_tag' => 'existing-tag',
            'current_git_branch' => '',
        ], JSON_PRETTY_PRINT));
        file_put_contents($appRoot.'/.version', "v5.1.0\n");

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertSame('0', $config->read('hide_copyright'));
            self::assertSame('auto', $config->read('nightmode'));
            self::assertSame('1883', $config->read('mqtt_discovery_port'));
            self::assertSame('50', $config->read('request_concurrency'));
        } finally {
            unlink($dataDir.'/MyConfig.json');
            unlink($appRoot.'/.version');
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorClearsPersistedGitBranchWhenGitMetadataIsUnavailable(): void
    {
        putenv('BUILD_VERSION');

        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-branch-cleanup-'.bin2hex(random_bytes(6));
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($dataDir);
        file_put_contents($dataDir.'/MyConfig.json', json_encode([
            'current_git_branch' => 'feature/stale-branch',
            'current_git_tag' => 'existing-tag',
        ], JSON_PRETTY_PRINT));

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertSame('', $config->read('current_git_branch'));
            self::assertSame('existing-tag', $config->read('current_git_tag'));
        } finally {
            unlink($dataDir.'/MyConfig.json');
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorClearsPersistedGitBranchWhenGitReturnsHead(): void
    {
        putenv('BUILD_VERSION');

        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-detached-head-'.bin2hex(random_bytes(6));
        $binDir = $appRoot.'/bin';
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($binDir);
        mkdir($dataDir);
        file_put_contents($dataDir.'/MyConfig.json', json_encode([
            'current_git_branch' => 'feature/stale-branch',
            'current_git_tag' => '',
        ], JSON_PRETTY_PRINT));
        file_put_contents($binDir.'/git', "#!/bin/sh\nif [ \"$3\" = \"describe\" ]; then\n  echo test-dev-version\nelif [ \"$3\" = \"rev-parse\" ]; then\n  echo HEAD\nfi\n");
        chmod($binDir.'/git', 0o755);
        putenv('PATH='.$binDir.':'.$this->originalPath);

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertSame('test-dev-version', $config->read('current_git_tag'));
            self::assertSame('', $config->read('current_git_branch'));
        } finally {
            unlink($dataDir.'/MyConfig.json');
            unlink($binDir.'/git');
            rmdir($binDir);
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testConstructorWithBlankVersionFileKeepsExistingTagButClearsBranch(): void
    {
        putenv('BUILD_VERSION');

        $appRoot = sys_get_temp_dir().'/tasmoadmin-config-blank-version-'.bin2hex(random_bytes(6));
        $dataDir = $appRoot.'/data';
        mkdir($appRoot);
        mkdir($dataDir);
        file_put_contents($appRoot.'/.version', " \n");
        file_put_contents($dataDir.'/MyConfig.json', json_encode([
            'current_git_tag' => 'existing-tag',
            'current_git_branch' => 'feature/stale-branch',
        ], JSON_PRETTY_PRINT));

        try {
            $config = new Config($dataDir.'/', $appRoot.'/');

            self::assertSame('existing-tag', $config->read('current_git_tag'));
            self::assertSame('', $config->read('current_git_branch'));
        } finally {
            unlink($dataDir.'/MyConfig.json');
            unlink($appRoot.'/.version');
            rmdir($dataDir);
            rmdir($appRoot);
        }
    }

    public function testGetRequestConcurrencyReturnsConfiguredInteger(): void
    {
        $config = $this->getConfig();
        $config->write('request_concurrency', '75');

        self::assertSame(75, $config->getRequestConcurrency());
    }

    private function getConfig(): Config
    {
        return new Config($this->root->url().'/', $this->root->url().'/');
    }
}
