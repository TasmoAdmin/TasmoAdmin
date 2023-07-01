<?php

use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Helper\OtaHelper;
use TasmoAdmin\Helper\UrlHelper;

$otaHelper = new OtaHelper($Config, _BASEURL_);

$ota_new_firmware_url = '';
$target_version = '';

if (!empty($_REQUEST['new_firmware_path'])) {
    $ota_new_firmware_url = $otaHelper->getFirmwareUrl($_REQUEST['new_firmware_path']);
}
if (!empty($_REQUEST['target_version'])) {
    $target_version = ($_REQUEST['target_version']);
}

$deviceIds = $_REQUEST["device_ids"] ?? [];

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
<div class='row justify-content-sm-center'>
    <div class='col col-12 col-md-8 '>
        <h2 class='text-sm-center mb-5'>
            <?php echo $title; ?>
        </h2>
    </div>
</div>
<div class='row justify-content-center'>
    <div class='col col-12 col-md-10'>
        <?php if (empty($deviceIds)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-5" data-dismiss="alert" role="alert">
                <?php echo __("NO_DEVICES_SELECTED", "DEVICE_UPDATE"); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php else: ?>
            <div id='logGlobal' class='mt-3 border p-3'>

            </div>

            <div id='progressbox' class='mt-3 border border-dark p-3'>

            </div>

        <input type='hidden' id='ota_new_firmware_url' value='<?php echo $ota_new_firmware_url; ?>'>
        <input type='hidden' id='target_version' value='<?php echo $target_version; ?>'>
            <script>
                const devices = <?php echo json_encode($devicesJson); ?>;
            </script>
            <script src="<?php echo $urlHelper->js("device_update"); ?>"></script>
        <?php endif; ?>
    </div>
</div>
