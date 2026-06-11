<?php

namespace TasmoAdmin\Mqtt;

class MqttDiscoveryResult
{
    /**
     * @param array<int, array<string, mixed>>  $updatedDevices
     * @param array<int, \stdClass>             $newDevices
     * @param array<int, array<string, string>> $offlineTopics
     * @param array<int, array<string, string>> $conflicts
     */
    public function __construct(
        public array $updatedDevices = [],
        public array $newDevices = [],
        public array $offlineTopics = [],
        public array $conflicts = []
    ) {}
}
