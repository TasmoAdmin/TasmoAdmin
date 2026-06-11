<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;

class SiteConfigMqttDiscoveryPanelTest extends TestCase
{
    public function testPanelRendersMqttDiscoveryFields(): void
    {
        $config = [
            'mqtt_discovery_host' => 'broker.local',
            'mqtt_discovery_port' => '1883',
            'mqtt_discovery_username' => 'mqtt',
            'mqtt_discovery_password' => '',
            'mqtt_discovery_cmnd_prefix' => 'cmnd',
            'mqtt_discovery_stat_prefix' => 'stat',
            'mqtt_discovery_tele_prefix' => 'tele',
            'mqtt_discovery_subscriptions' => 'tele/+/LWT',
            'mqtt_discovery_timeout_seconds' => '5',
        ];
        $mqttDiscoveryPasswordStored = true;

        ob_start();

        include __DIR__.'/../../pages/elements/site_config_mqtt_discovery_panel.php';
        $output = (string) ob_get_clean();

        self::assertStringContainsString('USER_CONFIG_CONFIG_MQTT_DISCOVERY_TITLE', $output);
        self::assertStringContainsString('name=\'mqtt_discovery_host\'', $output);
        self::assertStringContainsString('name=\'mqtt_discovery_password\'', $output);
        self::assertStringContainsString('name=\'clear_mqtt_discovery_password\'', $output);
        self::assertStringContainsString('name=\'mqtt_discovery_subscriptions\'', $output);
    }
}
