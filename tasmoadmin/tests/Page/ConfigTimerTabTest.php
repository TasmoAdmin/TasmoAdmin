<?php

declare(strict_types=1);

namespace Tests\TasmoAdmin\Page;

use PHPUnit\Framework\TestCase;

class ConfigTimerTabTest extends TestCase
{
    public function testTimerTabRendersTimerFieldsAndGlobalToggle(): void
    {
        $status = $this->getStatusWithTimers();

        ob_start();

        include __DIR__.'/../../pages/device_config_tabs/config_timer_tab.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertStringContainsString("name='Timers'", $output);
        self::assertStringContainsString("name='Timer1[Time]'", $output);
        self::assertStringContainsString('data-timer-time-picker', $output);
        self::assertStringContainsString("name='Timer16[Action]'", $output);
        self::assertStringContainsString("name='Timer16[Output]'", $output);
        self::assertStringContainsString("value='16'", $output);
        self::assertStringContainsString("name='Timer1[Days]'", $output);
        self::assertStringContainsString('data-timer-days-checkbox', $output);
        self::assertStringContainsString('TAB_HL_TIMERS', $output);
        self::assertStringContainsString('DEVICE_CONFIG_TIMER_DAYS', $output);
        self::assertStringContainsString('DEVICE_CONFIG_TIMER_MODE_SUNRISE', $output);
        self::assertStringContainsString('DEVICE_CONFIG_TIMER_ACTION_TOGGLE', $output);
        self::assertStringContainsString('DEVICE_CONFIG_TIMER_WINDOW_OFFSET', $output);
    }

    public function testTimerTabMarksSelectedTimerValues(): void
    {
        $status = $this->getStatusWithTimers();

        ob_start();

        include __DIR__.'/../../pages/device_config_tabs/config_timer_tab.php';
        $output = ob_get_clean();

        self::assertIsString($output);
        self::assertMatchesRegularExpression('/name=\'Timers\'.+value=\'1\'.+selected="selected"/s', $output);
        self::assertMatchesRegularExpression('/name=\'Timer1\[Enable\]\'.+value=\'1\'.+selected="selected"/s', $output);
        self::assertMatchesRegularExpression('/name=\'Timer1\[Mode\]\'.+value=\'2\'.+selected="selected"/s', $output);
        self::assertMatchesRegularExpression('/name=\'Timer1\[Output\]\'.+value=\'2\'.+selected="selected"/s', $output);
        self::assertMatchesRegularExpression('/name=\'Timer1\[Action\]\'.+value=\'3\'.+selected="selected"/s', $output);
        self::assertMatchesRegularExpression('/name=\'Timer1\[Window\]\'.+value=\'15\'.+selected="selected"/s', $output);
        self::assertStringContainsString('id="Timer1TimeSelect"', $output);
        self::assertStringContainsString('value="06:30"', $output);
        self::assertStringContainsString("name='Timer1[Time]'", $output);
        self::assertStringContainsString("value='+06:30'", $output);
        self::assertStringContainsString("value='--TWT--'", $output);
        self::assertMatchesRegularExpression('/id="Timer1Day2"[^>]+checked/s', $output);
        self::assertMatchesRegularExpression('/id="Timer1Day4"[^>]+checked/s', $output);
    }

    private function getStatusWithTimers(): \stdClass
    {
        $status = new \stdClass();
        $status->StatusTIMERS = (object) [
            'enabled' => 1,
            'timers' => [],
        ];

        foreach (range(1, 16) as $index) {
            $status->StatusTIMERS->timers[$index] = (object) [
                'Enable' => 1 === $index ? 1 : 0,
                'Mode' => 1 === $index ? 2 : 0,
                'Time' => 1 === $index ? '06:30' : '00:00',
                'Window' => 1 === $index ? 15 : 0,
                'Days' => 1 === $index ? '--TWT--' : '-------',
                'Repeat' => 1 === $index ? 1 : 0,
                'Output' => 1 === $index ? 2 : 1,
                'Action' => 1 === $index ? 3 : 0,
            ];
        }

        return $status;
    }
}
