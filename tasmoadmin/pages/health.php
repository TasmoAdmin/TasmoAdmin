<?php

use Selective\Container\Container;
use TasmoAdmin\Health\HealthRepository;
use TasmoAdmin\Health\HealthState;
use TasmoAdmin\Health\HealthView;
use TasmoAdmin\Sonoff;

/** @var Container $container */
$repo = $container->get(HealthRepository::class);
$Sonoff = $container->get(Sonoff::class);

$healthRows = [];
foreach ($repo->all() as $row) {
    $healthRows[(string) $row['device_id']] = $row;
}

// Every configured device gets a row; devices the collector has not seen yet
// show as unknown, rows of removed devices are skipped.
$rows = [];
foreach ($Sonoff->getDevices() as $device) {
    $row = $healthRows[(string) $device->id] ?? ['device_id' => (string) $device->id, 'state' => HealthState::UNKNOWN];
    $row['device'] = $device;
    $row['name'] = $device->getName();
    $row['http_up_normalized'] = (bool) ($row['http_up'] ?? false);
    $row['mqtt_up_normalized'] = (bool) ($row['mqtt_up'] ?? false);
    $row['mqtt_expected_normalized'] = 0 !== (int) ($row['mqtt_expected'] ?? 1);
    $rows[] = $row;
}

$counts = array_fill_keys(HealthView::STATES, 0);
foreach ($rows as $row) {
    $state = $row['state'] ?? HealthState::UNKNOWN;
    if (isset($counts[$state])) {
        ++$counts[$state];
    }
}

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

        return strnatcasecmp((string) $left['name'], (string) $right['name']);
    }
);
$now = time();
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<div class='row justify-content-sm-center'>
    <div class='col col-12 devices-page health-page'>
        <?php if (empty($rows)) { ?>
            <div class="devices-panel devices-empty-state text-center">
                <?php echo __('NO_DATA', 'HEALTH'); ?>
            </div>
        <?php } else { ?>
            <div class="devices-panel devices-toolbar">
                <div class='row g-3 align-items-end devices-toolbar-row'>
                    <div class="col col-12 col-lg-5 devices-toolbar-search">
                        <div class="input-group device-search-group">
                            <input type="search"
                                   class='form-control device-search health-search'
                                   autocomplete="off"
                                   aria-label="<?php echo $e(__('SEARCH', 'HEALTH')); ?>"
                                   placeholder="<?php echo $e(__('SEARCH', 'HEALTH')); ?>"
                            >
                            <div class="input-group-text">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <?php if ('' !== $activeState) { ?>
                        <div class="col col-12 col-lg-auto ms-lg-auto">
                            <a class="btn btn-secondary" href="<?php echo _BASEURL_; ?>health">
                                <i class="fas fa-times" aria-hidden="true"></i>
                                <?php echo __('FILTER_CLEAR', 'HEALTH'); ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div id="health-content">
                <div class="row g-2 health-counts" id="health-counts">
                    <?php foreach (HealthView::STATES as $state) { ?>
                    <div class="col-6 col-md">
                        <a class="health-count-filter <?php echo $activeState === $state ? 'active' : ''; ?>"
                            href="<?php echo _BASEURL_; ?>health<?php echo $activeState === $state ? '' : '?state='.urlencode($state); ?>"
                            <?php echo $activeState === $state ? 'aria-current="true"' : ''; ?>>
                            <span class="health-count-number <?php echo HealthView::dotClass($state); ?>">
                                <?php echo (int) $counts[$state]; ?>
                            </span>
                            <span class="health-count-label"><?php echo __(HealthView::labelKey($state), 'HEALTH'); ?></span>
                        </a>
                    </div>
                    <?php } ?>
                </div>

                <div class="devices-panel devices-table-panel health-table-panel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle health-table">
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
                                    $device = $row['device'];
                                    $stateLabel = __(HealthView::labelKey($state), 'HEALTH');
                                    $topic = '' !== $device->mqttTopic ? $device->mqttTopic : (string) ($row['mqtt_topic'] ?? '');
                                    $httpUp = $row['http_up_normalized'];
                                    $mqttUp = $row['mqtt_up_normalized'];
                                    $mqttExpected = $row['mqtt_expected_normalized'];
                                    $rssi = null === ($row['rssi'] ?? null) ? null : (int) $row['rssi'];
                                    $lastSeen = empty($row['last_seen']) ? null : (int) $row['last_seen'];
                                    $keywords = implode(' ', [$row['name'], $device->ip, $topic, $stateLabel]);
                                    ?>
                                <tr class="health-row health-row-<?php echo $e($state); ?>"
                                    data-keywords="<?php echo $e($keywords); ?>">
                                    <td class="health-device" data-label="<?php echo $e(__('DEVICE', 'HEALTH')); ?>">
                                        <div class="device-primary-cell">
                                            <span class="health-state-dot <?php echo HealthView::dotClass($state); ?>" aria-hidden="true"></span>
                                            <div class="device-primary-copy">
                                                <a href="<?php echo $e($device->getUrlWithAuth()); ?>"
                                                   target="_blank"
                                                   rel="noopener"
                                                   title="<?php echo $e(__('LINK_OPEN_DEVICE_WEBUI', 'DEVICES')); ?>"
                                                ><?php echo $e((string) $row['name']); ?></a>
                                                <span class="device-primary-meta">
                                                    <?php echo $e($device->ip); ?><?php if ('' !== $topic) { ?> · <?php echo $e($topic); ?><?php } ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="health-state" data-label="<?php echo $e(__('STATE', 'HEALTH')); ?>">
                                        <span class="health-status-token <?php echo HealthView::dotClass($state); ?>">
                                            <?php echo $stateLabel; ?>
                                        </span>
                                    </td>
                                    <td data-label="<?php echo $e(__('HTTP', 'HEALTH')); ?>">
                                        <span class="health-channel-token <?php echo $httpUp ? 'channel-up' : 'channel-down'; ?>">
                                            <?php echo $httpUp ? __('CHANNEL_UP', 'HEALTH') : __('CHANNEL_DOWN', 'HEALTH'); ?>
                                        </span>
                                    </td>
                                    <td data-label="<?php echo $e(__('MQTT', 'HEALTH')); ?>">
                                        <?php if (!$mqttExpected) { ?>
                                            <span class="health-channel-token channel-off" title="<?php echo $e(__('CHANNEL_NOT_MONITORED_HINT', 'HEALTH')); ?>">
                                                <?php echo __('CHANNEL_NOT_MONITORED', 'HEALTH'); ?>
                                            </span>
                                        <?php } else { ?>
                                            <span class="health-channel-token <?php echo $mqttUp ? 'channel-up' : 'channel-down'; ?>">
                                                <?php echo $mqttUp ? __('CHANNEL_UP', 'HEALTH') : __('CHANNEL_DOWN', 'HEALTH'); ?>
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td data-label="<?php echo $e(__('CHANNELS', 'HEALTH')); ?>">
                                        <span class="badge health-channel-summary <?php echo HealthView::channelSummaryClass($httpUp, $mqttUp, $mqttExpected); ?>">
                                            <?php echo __(HealthView::channelSummaryKey($httpUp, $mqttUp, $mqttExpected), 'HEALTH'); ?>
                                        </span>
                                    </td>
                                    <td data-label="<?php echo $e(__('RSSI', 'HEALTH')); ?>">
                                        <span class="health-signal">
                                            <span class="signal-meter <?php echo HealthView::signalClass($rssi); ?>" aria-hidden="true">
                                                <span></span><span></span><span></span>
                                            </span>
                                            <span class="signal-value"><?php echo null === $rssi ? __('VALUE_EMPTY', 'HEALTH') : $rssi.'%'; ?></span>
                                        </span>
                                    </td>
                                    <td data-label="<?php echo $e(__('LAST_SEEN', 'HEALTH')); ?>"
                                        title="<?php echo null === $lastSeen ? '' : date('Y-m-d H:i:s', $lastSeen); ?>">
                                        <?php echo __(HealthView::relativeTime($lastSeen, $now), 'HEALTH', [HealthView::relativeTimeValue($lastSeen, $now)]); ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="health-no-match text-center text-muted mb-0 py-3"<?php echo empty($visibleRows) ? '' : ' hidden'; ?>>
                        <?php echo __('NO_MATCH', 'HEALTH'); ?>
                    </p>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<script src="<?php echo $urlHelper->js('compiled/health'); ?>"></script>
