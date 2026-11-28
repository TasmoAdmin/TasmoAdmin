<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;

class ConfigNetworkTabTest extends TestCase
{
    public function testNetworkTabUsesSharedCardLayout(): void
    {
        $output = $this->renderNetworkTab((object) [
            'Hostname' => 'test-device',
            'IPAddress' => '192.168.1.10',
            'Gateway' => '192.168.1.1',
            'Subnetmask' => '255.255.255.0',
            'DNSServer1' => '192.168.1.2',
            'Mac' => '00:11:22:33:44:55',
            'WifiConfig' => 4,
        ]);

        self::assertStringContainsString('device-config-card', $output);
        self::assertStringContainsString('DEVICE_CONFIG_TAB_HL_NETWORK', $output);
        self::assertStringContainsString("name='WifiConfig'", $output);
    }

    public function testNetworkTabDisplaysDnsServer1(): void
    {
        $output = $this->renderNetworkTab((object) [
            'DNSServer1' => '192.168.1.3',
            'DNSServer2' => '0.0.0.0',
        ]);

        self::assertStringContainsString("value='192.168.1.3'", $output);
    }

    public function testNetworkTabFallsBackToLegacyDnsServer(): void
    {
        $output = $this->renderNetworkTab((object) [
            'DNSServer' => '192.168.1.2',
        ]);

        self::assertStringContainsString("value='192.168.1.2'", $output);
    }

    public function testNetworkTabDisplaysSecondaryDnsServer(): void
    {
        $output = $this->renderNetworkTab((object) [
            'DNSServer1' => '192.168.1.3',
            'DNSServer2' => '192.168.1.4',
        ]);

        self::assertStringContainsString("name='IPAddress5'", $output);
        self::assertStringContainsString("value='192.168.1.4'", $output);
    }

    public function testNetworkTabHidesSecondaryDnsServerWhenUnsupported(): void
    {
        $output = $this->renderNetworkTab((object) [
            'DNSServer' => '192.168.1.2',
        ]);

        self::assertStringNotContainsString("name='IPAddress5'", $output);
    }

    public function testNetworkTabSuppressesUnsetDnsServerSentinel(): void
    {
        $output = $this->renderNetworkTab((object) [
            'DNSServer1' => '192.168.1.3',
            'DNSServer2' => '0.0.0.0',
        ]);

        self::assertStringNotContainsString("value='0.0.0.0'", $output);
    }

    private function renderNetworkTab(object $statusNet): string
    {
        $status = new \stdClass();
        $status->StatusNET = $statusNet;
        $status->statusNTP = (object) [
            'NtpServer1' => 'pool.ntp.org',
        ];
        $status->StatusSTS = (object) [
            'Wifi' => (object) [
                'AP' => 2,
            ],
        ];
        $status->StatusLOG = (object) [
            'SSId1' => 'wifi-main',
            'SSId2' => 'wifi-backup',
        ];

        ob_start();

        include __DIR__.'/../../pages/device_config_tabs/config_network_tab.php';

        return (string) ob_get_clean();
    }
}
