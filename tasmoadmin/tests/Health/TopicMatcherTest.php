<?php

namespace Tests\TasmoAdmin\Health;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Device;
use TasmoAdmin\Health\TopicMatcher;

final class TopicMatcherTest extends TestCase
{
    public function testIsLwtTopic(): void
    {
        self::assertTrue(TopicMatcher::isLwtTopic('tele/kitchen/LWT'));
        self::assertTrue(TopicMatcher::isLwtTopic('groundfloor/kitchen/lwt'));
        self::assertFalse(TopicMatcher::isLwtTopic('tele/kitchen/STATE'));
    }

    public function testIsStateTopic(): void
    {
        self::assertTrue(TopicMatcher::isStateTopic('tele/kitchen/STATE'));
        self::assertTrue(TopicMatcher::isStateTopic('groundfloor/kitchen/state'));
        self::assertFalse(TopicMatcher::isStateTopic('tele/kitchen/LWT'));
    }

    public function testLwtOnline(): void
    {
        self::assertTrue(TopicMatcher::lwtOnline('Online'));
        self::assertTrue(TopicMatcher::lwtOnline(" online\n"));
        self::assertFalse(TopicMatcher::lwtOnline('Offline'));
        self::assertFalse(TopicMatcher::lwtOnline(''));
    }

    public function testMatchesDefaultPrefixTopic(): void
    {
        $devices = [$this->device(1, 'kitchen'), $this->device(2, 'bedroom')];
        $match = TopicMatcher::matchDevice('tele/kitchen/LWT', $devices);
        self::assertNotNull($match);
        self::assertSame(1, $match->id);
    }

    public function testMatchesCustomFullTopic(): void
    {
        $devices = [$this->device(7, 'kitchen')];
        $match = TopicMatcher::matchDevice('groundfloor/kitchen/LWT', $devices);
        self::assertNotNull($match);
        self::assertSame(7, $match->id);
    }

    public function testSkipsEmptyMqttTopicDevices(): void
    {
        $devices = [$this->device(1, ''), $this->device(2, 'bedroom')];
        self::assertNull(TopicMatcher::matchDevice('tele/anything/LWT', $devices));
    }

    public function testNoFalsePartialMatch(): void
    {
        // 'kitchen' must not match 'kitchenette'
        $devices = [$this->device(1, 'kitchen')];
        self::assertNull(TopicMatcher::matchDevice('tele/kitchenette/LWT', $devices));
    }

    public function testFallsBackToReportedTopicWhenNoneConfigured(): void
    {
        $devices = [$this->device(1, ''), $this->device(2, 'bedroom')];

        $match = TopicMatcher::matchDevice('tele/tasmota_ABC123/STATE', $devices, ['1' => 'tasmota_ABC123']);

        self::assertNotNull($match);
        self::assertSame(1, $match->id);
    }

    public function testConfiguredTopicWinsOverReportedTopic(): void
    {
        $devices = [$this->device(1, 'kitchen')];

        self::assertNull(TopicMatcher::matchDevice('tele/other/LWT', $devices, ['1' => 'other']));
    }

    private function device(int $id, string $mqttTopic): Device
    {
        return new Device($id, ['dev'.$id], '192.168.1.'.$id, '', '', Device::DEFAULT_IMAGE, 1, true, false, false, [], true, 80, [], false, $mqttTopic);
    }
}
