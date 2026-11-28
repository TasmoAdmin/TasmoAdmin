<?php

use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Helper\OtaHelper;

$otaHelper = new OtaHelper($Config, _BASEURL_);

$updateTargets = [];

if (!empty($_POST['update_targets'])) {
    $updateTargets = json_decode($_POST['update_targets'], true) ?? [];
}

if (empty($updateTargets) && !empty($_POST['new_firmware_path'])) {
    $updateTargets['default'] = [
        'minimalOtaUrl' => !empty($_POST['minimal_firmware_path'])
            ? $otaHelper->getFirmwareUrl($_POST['minimal_firmware_path'])
            : '',
        'otaUrl' => $otaHelper->getFirmwareUrl($_POST['new_firmware_path']),
        'targetVersion' => $_POST['target_version'] ?? '',
        'source' => 'manual',
    ];
}

$deviceIds = $_POST['device_ids'] ?? [];

$deviceRepository = $container->get(DeviceRepository::class);
$devices = $deviceRepository->getDevicesByIds($deviceIds);

$devicesJson = [];
foreach ($devices as $device) {
    $devicesJson[] = [
        'id' => $device->id,
        'name' => $device->getName(),
    ];
}
?>
<div class='row justify-content-sm-center update-page device-update-page'>
    <div class='col col-12 col-xl-10'>
        <h2 class='text-sm-center mb-4'>
            <?php echo $title; ?>
        </h2>
        <?php if (empty($deviceIds)) { ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" data-bs-dismiss="alert" role="alert">
                <?php echo __('NO_DEVICES_SELECTED', 'DEVICE_UPDATE'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } else { ?>
            <div class='update-summary card update-log-card mb-4'>
                <div class='card-body'>
                    <div class='update-summary-header'>
                        <span id='updateSummaryTitle' class='update-summary-title'></span>
                        <span id='updateSummaryElapsed' class='update-summary-elapsed'></span>
                    </div>
                    <div class='progress update-summary-progress'>
                        <div id='updateSummaryBar' class='progress-bar' role='progressbar' style='width: 0%' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'></div>
                    </div>
                    <div class='update-summary-counts'>
                        <span class='update-count update-count-success'><i class='fas fa-check-circle'></i> <span id='updateCountSuccess'>0</span></span>
                        <span class='update-count update-count-error'><i class='fas fa-times-circle'></i> <span id='updateCountError'>0</span></span>
                        <span class='update-count update-count-active'><i class='fas fa-circle-notch fa-spin'></i> <span id='updateCountActive'>0</span></span>
                    </div>
                    <div id='logGlobal' class='update-log-output'></div>
                </div>
            </div>
            <div id='progressbox' class='update-device-list'></div>

            <input type='hidden' id='update_targets' value='<?php echo htmlspecialchars(json_encode($updateTargets), ENT_QUOTES); ?>'>
            <script>
                const devices = <?php echo json_encode($devicesJson); ?>;
            </script>
            <script src="<?php echo $urlHelper->js('compiled/device_update'); ?>"></script>
        <?php } ?>
    </div>
</div>
