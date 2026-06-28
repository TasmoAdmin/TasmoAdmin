<?php

use Selective\Container\Container;
use TasmoAdmin\Health\HealthRepository;
use TasmoAdmin\Sonoff;

/** @var Container $container */
$repo = $container->get(HealthRepository::class);
$Sonoff = $container->get(Sonoff::class);

$names = [];
foreach ($Sonoff->getDevices() as $device) {
    $names[(string) $device->id] = $device->getName();
}

$rows = $repo->all();
$counts = ['online' => 0, 'degraded_mqtt' => 0, 'degraded_http' => 0, 'offline' => 0, 'unknown' => 0];
$devices = [];
foreach ($rows as $row) {
    $state = $row['state'] ?? 'unknown';
    if (isset($counts[$state])) {
        ++$counts[$state];
    }
    $row['name'] = $names[$row['device_id']] ?? $row['device_id'];
    $row['channel_disagreement'] = (int) ($row['http_up'] ?? 0) !== (int) ($row['mqtt_up'] ?? 0) ? 1 : 0;
    $devices[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['counts' => $counts, 'devices' => $devices], JSON_PRETTY_PRINT);
