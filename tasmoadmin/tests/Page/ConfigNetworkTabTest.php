<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;

class ConfigNetworkTabTest extends TestCase
{
    public function testNetworkTabUsesSharedCardLayout(): void
    {
        $status = new \stdClass();
        $status->StatusNET = (object) [
            'Hostname' => 'test-device',
            'IPAddress' => '192.168.1.10',
            'Gateway' => '192.168.1.1',
            'Subnetmask' => '255.255.255.0',
            'DNSServer' => '192.168.1.2',
            'Mac' => '00:11:22:33:44:55',
            'WifiConfig' => 4,
        ];
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
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString('device-config-card', $output);
        self::assertStringContainsString('DEVICE_CONFIG_TAB_HL_NETWORK', $output);
        self::assertStringContainsString("name='WifiConfig'", $output);
    }
}
