<?php

use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Helper\OtaHelper;

$otaHelper = new OtaHelper($Config, _BASEURL_);

$updateTargets = [];

if (!empty($_REQUEST['update_targets'])) {
    $updateTargets = json_decode($_REQUEST['update_targets'], true) ?? [];
}

if (empty($updateTargets) && !empty($_REQUEST['new_firmware_path'])) {
    $updateTargets['default'] = [
        'minimalOtaUrl' => !empty($_REQUEST['minimal_firmware_path'])
            ? $otaHelper->getFirmwareUrl($_REQUEST['minimal_firmware_path'])
            : '',
        'otaUrl' => $otaHelper->getFirmwareUrl($_REQUEST['new_firmware_path']),
        'targetVersion' => $_REQUEST['target_version'] ?? '',
        'source' => 'manual',
    ];
}

$deviceIds = $_REQUEST['device_ids'] ?? [];

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
            <div class='row g-4'>
                <div class='col col-12'>
                    <div class='card update-log-card'>
                        <div class='card-body'>
                            <div id='logGlobal' class='update-log-output'></div>
                        </div>
                    </div>
                </div>
                <div class='col col-12'>
                    <div class='card update-log-card'>
                        <div class='card-body'>
                            <div id='progressbox' class='update-progress-output'></div>
                        </div>
                    </div>
                </div>
            </div>

            <input type='hidden' id='update_targets' value='<?php echo htmlspecialchars(json_encode($updateTargets), ENT_QUOTES); ?>'>
            <script>
                const devices = <?php echo json_encode($devicesJson); ?>;
            </script>
            <script src="<?php echo $urlHelper->js('compiled/device_update'); ?>"></script>
        <?php } ?>
</div>
