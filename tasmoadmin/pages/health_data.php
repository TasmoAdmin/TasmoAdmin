<?php

use TasmoAdmin\Health\HealthRepository;
use TasmoAdmin\Sonoff;

/** @var \Selective\Container\Container $container */
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
    $row['channel_disagreement'] = (null !== $row['http_up'] && null !== $row['mqtt_up']
        && (int) $row['http_up'] !== (int) $row['mqtt_up']) ? 1 : 0;
    $devices[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['counts' => $counts, 'devices' => $devices], JSON_PRETTY_PRINT);
