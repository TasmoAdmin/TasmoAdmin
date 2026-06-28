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
foreach ($rows as $row) {
    $state = $row['state'] ?? 'unknown';
    if (isset($counts[$state])) {
        ++$counts[$state];
    }
}

$badgeClass = static function (string $state): string {
    return match ($state) {
        'online' => 'bg-success',
        'degraded_mqtt', 'degraded_http' => 'bg-warning text-dark',
        'offline' => 'bg-danger',
        default => 'bg-secondary',
    };
};
?>
<div class="container-fluid">
	<h1 class="mt-3"><?php echo __('HEALTH', 'PAGE_TITLES'); ?></h1>

	<div class="row my-3" id="health-counts">
		<div class="col"><span class="badge bg-success"><?php echo (int) $counts['online']; ?></span> Online</div>
		<div class="col"><span class="badge bg-warning text-dark"><?php echo (int) ($counts['degraded_mqtt'] + $counts['degraded_http']); ?></span> Degraded</div>
		<div class="col"><span class="badge bg-danger"><?php echo (int) $counts['offline']; ?></span> Offline</div>
		<div class="col"><span class="badge bg-secondary"><?php echo (int) $counts['unknown']; ?></span> Unknown</div>
	</div>

	<?php if (empty($rows)) { ?>
		<div class="alert alert-info">No health data yet. The collector populates this page once it runs.</div>
	<?php } else { ?>
		<table class="table table-sm table-striped">
			<thead>
				<tr>
					<th>Device</th><th>State</th><th>HTTP</th><th>MQTT</th><th>RSSI</th><th>Last seen</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($rows as $row) {
			    $state = $row['state'] ?? 'unknown';
			    $disagree = (null !== $row['http_up'] && null !== $row['mqtt_up']
			        && (int) $row['http_up'] !== (int) $row['mqtt_up']);
			    ?>
				<tr>
					<td><?php echo htmlspecialchars($names[$row['device_id']] ?? $row['device_id']); ?></td>
					<td>
						<span class="badge <?php echo $badgeClass($state); ?>"><?php echo htmlspecialchars($state); ?></span>
						<?php if ($disagree) { ?><span class="badge bg-info text-dark" title="HTTP and MQTT disagree">HTTP↔MQTT</span><?php } ?>
					</td>
					<td><?php echo null === $row['http_up'] ? '—' : ((int) $row['http_up'] ? '✓' : '✗'); ?></td>
					<td><?php echo null === $row['mqtt_up'] ? '—' : ((int) $row['mqtt_up'] ? '✓' : '✗'); ?></td>
					<td><?php echo null === $row['rssi'] ? '—' : (int) $row['rssi']; ?></td>
					<td><?php echo empty($row['last_seen']) ? '—' : date('Y-m-d H:i:s', (int) $row['last_seen']); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } ?>
</div>
