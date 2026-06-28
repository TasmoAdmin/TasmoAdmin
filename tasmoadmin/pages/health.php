<?php

use Selective\Container\Container;
use TasmoAdmin\Health\HealthRepository;
use TasmoAdmin\Health\HealthState;
use TasmoAdmin\Health\HealthView;
use TasmoAdmin\Sonoff;

/** @var Container $container */
$repo = $container->get(HealthRepository::class);
$Sonoff = $container->get(Sonoff::class);

$names = [];
foreach ($Sonoff->getDevices() as $device) {
    $names[(string) $device->id] = $device->getName();
}

$rows = $repo->all();
$counts = [
    HealthState::ONLINE => 0,
    HealthState::DEGRADED_MQTT => 0,
    HealthState::DEGRADED_HTTP => 0,
    HealthState::OFFLINE => 0,
    HealthState::UNKNOWN => 0,
];

foreach ($rows as &$row) {
    $state = $row['state'] ?? HealthState::UNKNOWN;
    if (isset($counts[$state])) {
        ++$counts[$state];
    }
    $row['name'] = $names[$row['device_id']] ?? $row['device_id'];
    $row['http_up_normalized'] = (int) ($row['http_up'] ?? 0);
    $row['mqtt_up_normalized'] = (int) ($row['mqtt_up'] ?? 0);
    $row['channel_disagreement'] = $row['http_up_normalized'] !== $row['mqtt_up_normalized'];
}
unset($row);

$activeState = $_GET['state'] ?? '';
if (!in_array($activeState, HealthView::STATES, true)) {
    $activeState = '';
}

$visibleRows = array_values(array_filter(
    $rows,
    static fn (array $row): bool => '' === $activeState || ($row['state'] ?? HealthState::UNKNOWN) === $activeState
));
usort(
    $visibleRows,
    static function (array $left, array $right): int {
        $rank = HealthView::severityRank($left['state'] ?? HealthState::UNKNOWN)
            <=> HealthView::severityRank($right['state'] ?? HealthState::UNKNOWN);

        if (0 !== $rank) {
            return $rank;
        }

        return strcmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
    }
);
$now = time();
?>
<div class="container-fluid health-page">
    <h1 class="mt-3"><?php echo __('HEALTH', 'PAGE_TITLES'); ?></h1>

    <div class="row my-3 g-2" id="health-counts">
        <?php foreach (HealthView::STATES as $state) { ?>
        <div class="col-6 col-md">
            <a class="health-count-filter <?php echo $activeState === $state ? 'active' : ''; ?>"
                href="<?php echo _BASEURL_; ?>health?state=<?php echo urlencode($state); ?>">
                <span class="health-count-number <?php echo HealthView::dotClass($state); ?>">
                    <?php echo (int) ($counts[$state] ?? 0); ?>
                </span>
                <span class="health-count-label"><?php echo __(HealthView::labelKey($state), 'HEALTH'); ?></span>
            </a>
        </div>
        <?php } ?>
        <?php if ('' !== $activeState) { ?>
            <div class="col-12 col-md-auto">
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo _BASEURL_; ?>health">
                    <?php echo __('FILTER_CLEAR', 'HEALTH'); ?>
                </a>
            </div>
        <?php } ?>
    </div>

    <?php if (empty($rows)) { ?>
        <div class="alert alert-info"><?php echo __('NO_DATA', 'HEALTH'); ?></div>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle health-table">
                <thead>
                    <tr>
                        <th><?php echo __('DEVICE', 'HEALTH'); ?></th>
                        <th><?php echo __('STATE', 'HEALTH'); ?></th>
                        <th><?php echo __('HTTP', 'HEALTH'); ?></th>
                        <th><?php echo __('MQTT', 'HEALTH'); ?></th>
                        <th><?php echo __('CHANNELS', 'HEALTH'); ?></th>
                        <th><?php echo __('RSSI', 'HEALTH'); ?></th>
                        <th><?php echo __('LAST_SEEN', 'HEALTH'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visibleRows as $row) {
                        $state = $row['state'] ?? HealthState::UNKNOWN;
                        $lastSeen = empty($row['last_seen']) ? null : (int) $row['last_seen'];
                        $relativeKey = HealthView::relativeTime($lastSeen, $now);
                        $relativeValue = HealthView::relativeTimeValue($lastSeen, $now);
                        ?>
                <tr class="health-row health-row-<?php echo htmlspecialchars($state, ENT_QUOTES, 'UTF-8'); ?>">
                    <td>
                        <div class="health-device-cell">
                            <span class="health-state-dot <?php echo HealthView::dotClass($state); ?>"></span>
                            <span><?php echo htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="health-status-token <?php echo HealthView::dotClass($state); ?>">
                            <?php echo __(HealthView::labelKey($state), 'HEALTH'); ?>
                        </span>
                    </td>
                    <td>
                        <span class="health-channel-token <?php echo $row['http_up_normalized'] ? 'channel-up' : 'channel-down'; ?>">
                            <?php echo $row['http_up_normalized'] ? __('CHANNEL_UP', 'HEALTH') : __('CHANNEL_DOWN', 'HEALTH'); ?>
                        </span>
                    </td>
                    <td>
                        <span class="health-channel-token <?php echo $row['mqtt_up_normalized'] ? 'channel-up' : 'channel-down'; ?>">
                            <?php echo $row['mqtt_up_normalized'] ? __('CHANNEL_UP', 'HEALTH') : __('CHANNEL_DOWN', 'HEALTH'); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge health-channel-summary <?php echo HealthView::channelSummaryClass((bool) $row['http_up_normalized'], (bool) $row['mqtt_up_normalized']); ?>">
                            <?php echo __(HealthView::channelSummaryKey((bool) $row['http_up_normalized'], (bool) $row['mqtt_up_normalized']), 'HEALTH'); ?>
                        </span>
                    </td>
                    <td>
                        <span class="signal-meter <?php echo HealthView::signalClass(null === $row['rssi'] ? null : (int) $row['rssi']); ?>">
                            <span></span><span></span><span></span>
                        </span>
                        <span class="signal-value"><?php echo null === $row['rssi'] ? __('VALUE_EMPTY', 'HEALTH') : (int) $row['rssi']; ?></span>
                    </td>
                            <td title="<?php echo null === $lastSeen ? '' : date('Y-m-d H:i:s', $lastSeen); ?>">
                                <?php echo __($relativeKey, 'HEALTH', [$relativeValue]); ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
