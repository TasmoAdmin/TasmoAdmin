<?php

use TasmoAdmin\Helper\OtaHelper;
use TasmoAdmin\Helper\UrlHelper;

$otaHelper = new OtaHelper($Config, _BASEURL_);

if (!empty($_REQUEST['minimal_firmware_path'])) {
    $ota_minimal_firmware_url = $otaHelper->getFirmwareUrl($_REQUEST['minimal_firmware_path']);
}
if (!empty($_REQUEST['new_firmware_path'])) {
    $ota_new_firmware_url = $otaHelper->getFirmwareUrl($_REQUEST['new_firmware_path']);
}
if (!empty($_REQUEST['target_version'])) {
    $target_version = ($_REQUEST['target_version']);
}

$device_ids = $_REQUEST["device_ids"] ?? FALSE;
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
        <?php if (!$device_ids): ?>
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

        <input type='hidden' id='ota_minimal_firmware_url' value='<?php echo $ota_minimal_firmware_url ?? ""; ?>'>
        <input type='hidden' id='ota_new_firmware_url' value='<?php echo $ota_new_firmware_url ?? ""; ?>'>
        <input type='hidden' id='target_version' value='<?php echo $target_version ?? ""; ?>'>
            <script>
                const device_ids = '<?php echo json_encode($device_ids); ?>';
            </script>
            <script src="<?php echo $urlHelper->js("device_update"); ?>"></script>
        <?php endif; ?>
    </div>
</div>
