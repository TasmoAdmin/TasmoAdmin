<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;
use Tests\TasmoAdmin\TestUtils;

class ConfigMqttTabTest extends TestCase
{
    public function testMqttTabRendersDiscoveryToggle(): void
    {
        $status = $this->getStatusWithDiscoveryOption(0);

        ob_start();

        include __DIR__.'/../../pages/device_config_tabs/config_mqtt_tab.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString("name='SetOption19'", $output);
        self::assertStringContainsString('DEVICE_CONFIG_MQTT_DISCOVERY', $output);
    }

    public function testMqttTabMarksDiscoveryToggleAsCheckedWhenEnabled(): void
    {
        $status = $this->getStatusWithDiscoveryOption(1);

        ob_start();

        include __DIR__.'/../../pages/device_config_tabs/config_mqtt_tab.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertMatchesRegularExpression(
            '/name=\'SetOption19\'.+checked=\"checked\"/s',
            $output
        );
    }

    private function getStatusWithDiscoveryOption(int $value): \stdClass
    {
        $status = json_decode(TestUtils::loadFixture('response-valid.json'));
        $status->StatusLOG->SetOptionDecoded = new \stdClass();

        foreach ([2, 3, 4, 10, 19] as $option) {
            $setOption = 'SetOption'.$option;
            $status->StatusLOG->SetOptionDecoded->{$setOption} = (object) [
                'value' => 19 === $option ? $value : 0,
                'desc' => '',
            ];
        }

        $status->StatusMQT->SwitchTopic = '0';
        $status->StatusMQT->SensorRetain = 'OFF';

        return $status;
    }
}
