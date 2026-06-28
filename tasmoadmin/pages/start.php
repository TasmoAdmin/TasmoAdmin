<?php

use TasmoAdmin\Health\HealthRepository;
use TasmoAdmin\Health\HealthState;
use TasmoAdmin\Health\HealthView;
use TasmoAdmin\Sonoff;

$Sonoff = $container->get(Sonoff::class);

$devices = array_values(array_filter(
    $Sonoff->getDevices(),
    static fn ($deviceGroup): bool => !$deviceGroup->deviceHideFromStartpage
));
$healthRowsByDeviceId = [];

try {
    $healthRepo = $container->get(HealthRepository::class);
    foreach ($healthRepo->all() as $healthRow) {
        $healthRowsByDeviceId[(string) $healthRow['device_id']] = $healthRow;
    }
} catch (Throwable) {
    $healthRowsByDeviceId = [];
}

$dashboardCounts = [
    'total' => count($devices),
    HealthState::ONLINE => 0,
    HealthState::DEGRADED_MQTT => 0,
    HealthState::DEGRADED_HTTP => 0,
    HealthState::OFFLINE => 0,
    HealthState::UNKNOWN => 0,
];

foreach ($devices as $deviceGroup) {
    $state = $healthRowsByDeviceId[(string) $deviceGroup->id]['state'] ?? HealthState::UNKNOWN;
    if (isset($dashboardCounts[$state])) {
        ++$dashboardCounts[$state];
    }
}

usort(
    $devices,
    static function ($left, $right) use ($healthRowsByDeviceId): int {
        $leftState = $healthRowsByDeviceId[(string) $left->id]['state'] ?? HealthState::UNKNOWN;
        $rightState = $healthRowsByDeviceId[(string) $right->id]['state'] ?? HealthState::UNKNOWN;
        $rank = HealthView::severityRank($leftState) <=> HealthView::severityRank($rightState);

        if (0 !== $rank) {
            return $rank;
        }

        return strcmp((string) $left->getName(), (string) $right->getName());
    }
);

?>
<div class='container-fluid start-dashboard-page'>

	<?php if (!empty($devices)) {
	    $nightmode = '';   // todo: make function
	    $h = date('H');

	    if ('disable' === $Config->read('nightmode')) {
	        $nightmode = '';
	    } else {
	        if ('auto' === $Config->read('nightmode')) {
	            if ($h >= 18 || $h <= 8) {
	                $nightmode = 'nightmode ';
	            }
	        } elseif ('always' === $Config->read('nightmode')) {
	            $nightmode = 'nightmode ';
	        }
	    }

	    $imgNight = '';
    if ('nightmode' === $nightmode) {
        $imgNight = 'night/';
    }
    ?>
    <div class="dashboard-summary row g-2 my-3">
        <div class="col-6 col-md">
            <div class="dashboard-summary-card">
                <span class="dashboard-summary-label"><?php echo __('DASHBOARD_TOTAL', 'STARTPAGE'); ?></span>
                <strong><?php echo (int) $dashboardCounts['total']; ?></strong>
            </div>
        </div>
        <?php foreach ([HealthState::ONLINE, HealthState::DEGRADED_MQTT, HealthState::DEGRADED_HTTP, HealthState::OFFLINE, HealthState::UNKNOWN] as $state) { ?>
            <div class="col-6 col-md">
                <a class="dashboard-summary-card dashboard-summary-link" href="<?php echo _BASEURL_; ?>health?state=<?php echo urlencode($state); ?>">
                    <span class="dashboard-summary-label"><?php echo __(HealthView::labelKey($state), 'HEALTH'); ?></span>
                    <strong class="<?php echo HealthView::dotClass($state); ?>"><?php echo (int) ($dashboardCounts[$state] ?? 0); ?></strong>
                </a>
            </div>
        <?php } ?>
    </div>

    <div class='row justify-content-center startpage'>
			<div class='card-holder col-6 col-sm-3 col-md-2 col-xl-1 col-xxl-1 mb-4'>
        <div class='box_device position-relative dashboard-device-tile dashboard-action-tile' id='all_off' style='' aria-pressed="false">
            <span class="all-off-lock-indicator"
                  data-bs-toggle="tooltip"
                  data-bs-title="<?php echo __('ALL_OFF_LOCKED', 'STARTPAGE'); ?>"
                  aria-label="<?php echo __('ALL_OFF_LOCKED', 'STARTPAGE'); ?>">
                <i class="fas fa-lock" aria-hidden="true"></i>
            </span>
            <div class=" rubberBand">
						<?php // col col-xs-6 col-4 col-sm-3 col-md-2 col-xl-1
	                    if (!empty($device_group)) {
	                        $type = $device_group->img;
	                    } else {
	                        $type = 'bulb_1';
	                    }
	    $img = _RESOURCESURL_.'img/device_icons/'.$imgNight.$type.'_off.png';

	    ?>
						<img class='box_device_image'
							 src='<?php echo $img; ?>'
							 data-icon='<?php echo $type; ?>'
							 alt=''
						>
					</div>
					<div class='box_device_body'>
						<h5 class="box_device_name">
							<?php echo __('ALL_OFF', 'DEVICES'); ?>
						</h5>
					</div>
				</div>
			</div>

			<?php foreach ($devices as $device_group) { ?>
				<?php foreach ($device_group->names as $key => $devicename) { ?>
            <?php
            $img = _RESOURCESURL_.'img/device_icons/'.$imgNight.$device_group->img.'_off.png';
            $healthRow = $healthRowsByDeviceId[(string) $device_group->id] ?? null;
            $healthState = $healthRow['state'] ?? HealthState::UNKNOWN;
            ?>
            <div class='card-holder col-6 col-sm-3 col-md-2 col-xl-1 col-xxl-1 mb-4'>
                <div class='box_device position-relative dashboard-device-tile dashboard-device-<?php echo htmlspecialchars($healthState, ENT_QUOTES, 'UTF-8'); ?>' style=''
                    data-device_id='<?php echo $device_group->id; ?>'
                    data-device_group='<?php echo count($device_group->names) > 1 ? 'multi' : 'single'; ?>'
                    data-device_ip='<?php echo $device_group->ip; ?>'
							 data-device_relais='<?php echo $key + 1; ?>'
							 data-device_state='none'
							 data-device_all_off='<?php echo $device_group->deviceAllOff; ?>'
							 data-device_protect_on='<?php echo $device_group->deviceProtectionOn; ?>'
							 data-device_protect_off='<?php echo $device_group->deviceProtectionOff; ?>'
						data-device_confirm_toggle='<?php echo $device_group->deviceConfirmToggle ? '1' : '0'; ?>'
						>
                    <?php include __DIR__.'/elements/health_badge.php'; ?>
							<div class="animated rubberBand">
								<img class='box_device_image'
									 data-icon='<?php echo $device_group->img; ?>'
									 src='<?php echo $img; ?>'
									 alt=''
								>
							</div>
							<div class='box_device_body'>
                        <h5 class="box_device_name">
                            <?php echo $devicename; ?>
                        </h5>
                        <div class="box_device_meta"><?php echo $device_group->ip; ?></div>
								<div class='info-holder'>
									<div class='info info-1 hidden'>
										<span>-</span>
									</div>
									<div class='info info-2 hidden'>
										<span>-</span>
									</div>
									<div class='info info-3 hidden'>
										<span>-</span>
									</div>
									<div class='info info-4 hidden'>
										<span>-</span>
									</div>
									<div class='info info-5 hidden'>
										<span>-</span>
									</div>
									<div class='info info-6 hidden'>
										<span>-</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php }
				} ?>
		</div>
	<?php } else { ?>
		<div class='row'>
			<div class='col col-12 text-center'>
				<?php echo __('NO_DEVICES_FOUND', 'STARTPAGE'); ?>
			</div>
		</div>
		<div class='row mt-5 justify-content-center text-center'>
			<div class='col col-12 col-sm-2 '>
				<a class="btn btn-primary"
				   href="<?php echo _BASEURL_; ?>devices_autoscan"
				>
					<?php echo __('DEVICES_AUTOSCAN', 'NAVI'); ?>
				</a>
			</div>
			<div class='col col-12 col-sm-2 '>
				<a href='<?php echo _BASEURL_; ?>device_action/add' class="btn btn-primary js-add-device">
					<?php echo __('TABLE_HEAD_NEW_DEVICE', 'DEVICES'); ?>
				</a>
			</div>
		</div>

	<?php } ?>
</div>

<?php include 'elements/modal_add_device.php'; ?>

<script src="<?php echo $urlHelper->js('compiled/start'); ?>"></script>
