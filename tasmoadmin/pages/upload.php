<?php

use Symfony\Component\BrowserKit\HttpBrowser;
use TasmoAdmin\Device;
use TasmoAdmin\Helper\FirmwareFolderHelper;
use TasmoAdmin\Helper\GuzzleFactory;
use TasmoAdmin\Helper\OtaHelper;
use TasmoAdmin\Helper\TasmotaHelper;
use TasmoAdmin\Helper\TasmotaOtaScraper;
use TasmoAdmin\Sonoff;
use TasmoAdmin\Update\FirmwareChecker;
use TasmoAdmin\Update\FirmwareDownloader;

$Sonoff = $container->get(Sonoff::class);

$errors = [];
$messages = [];
$firmwarefolder = _DATADIR_.'firmwares/';
$minimal_firmware_path = '';
$new_firmware_path = '';
$targetVersion = '';

$maxMb = 5;
$maxFileSize = $maxMb * 1024 * 1024;

FirmwareFolderHelper::clean($firmwarefolder);

if (isset($_REQUEST['upload'])) {
    if ('' == $_FILES['minimal_firmware']['name']) {
        $errors[] = __('UPLOAD_FIRMWARE_MINIMAL_LABEL', 'DEVICE_UPDATE').': '.__(
            'UPLOAD_FIRMWARE_MINIMAL_SKIP',
            'DEVICE_UPDATE'
        ).'<br/>';
    } else {
        try {
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (!isset($_FILES['minimal_firmware']['error'])
                || is_array($_FILES['minimal_firmware']['error'])) {
                throw new RuntimeException(__('UPLOAD_FIRMWARE_MINIMAL_INVALID_FILES', 'DEVICE_UPDATE'));
            }

            // Check $_FILES['minimal_firmware']['error'] value.
            switch ($_FILES['minimal_firmware']['error']) {
                case UPLOAD_ERR_OK:
                    break;

                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException(__('UPLOAD_FIRMWARE_MINIMAL_ERR_NO_FILE', 'DEVICE_UPDATE'));

                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException(__('UPLOAD_FIRMWARE_MINIMAL_ERR_FORM_SIZE', 'DEVICE_UPDATE'));

                default:
                    throw new RuntimeException(__('UPLOAD_FIRMWARE_MINIMAL_UNKNOWN_ERROR', 'DEVICE_UPDATE'));
            }

            // You should also check filesize here.
            if ($_FILES['minimal_firmware']['size'] > 502000) {
                throw new RuntimeException(
                    __('UPLOAD_FIRMWARE_MINIMAL_TOO_BIG', 'DEVICE_UPDATE', ['maxsize' => '502kb'])
                );
            }
            if (in_array($_FILES['minimal_firmware']['type'], ['application/gzip', 'application/x-gzip'])) {
                $ext = 'bin.gz';
            } elseif (in_array($_FILES['minimal_firmware']['type'], ['application/octet-stream', 'application/macbinary'])) {
                $ext = 'bin';
            } else {
                throw new RuntimeException(
                    __('UPLOAD_FIRMWARE_MINIMAL_WRONG_FORMAT', 'DEVICE_UPDATE', $_FILES['minimal_firmware']['type'])
                );
            }

            $minimal_firmware_path = $firmwarefolder.'tasmota-minimal.'.$ext;

            if (!move_uploaded_file(
                $_FILES['minimal_firmware']['tmp_name'],
                $minimal_firmware_path
            )) {
                throw new RuntimeException(
                    __(
                        'UPLOAD_FIRMWARE_MINIMAL_COULD_NOT_SAVE',
                        'DEVICE_UPDATE',
                        ['FWPath' => $minimal_firmware_path]
                    )
                );
            }

            $messages[] = __('UPLOAD_FIRMWARE_MINIMAL_LABEL', 'DEVICE_UPDATE').': '.__(
                'UPLOAD_FIRMWARE_MINIMAL_SUCCESSFULLY',
                'DEVICE_UPDATE'
            );
        } catch (RuntimeException $e) {
            $errors[] = __('UPLOAD_FIRMWARE_MINIMAL_LABEL', 'DEVICE_UPDATE').': '.$e->getMessage().'!';
        }
    }

    try {
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (!isset($_FILES['new_firmware']['error'])
            || is_array($_FILES['new_firmware']['error'])) {
            throw new RuntimeException(__('UPLOAD_FIRMWARE_FULL_INVALID_FILES', 'DEVICE_UPDATE'));
        }

        // Check $_FILES['new_firmware']['error'] value.
        switch ($_FILES['new_firmware']['error']) {
            case UPLOAD_ERR_OK:
                break;

            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException(__('UPLOAD_FIRMWARE_FULL_ERR_NO_FILE', 'DEVICE_UPDATE'));

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException(__('UPLOAD_FIRMWARE_FULL_ERR_FORM_SIZE', 'DEVICE_UPDATE'));

            default:
                throw new RuntimeException(__('UPLOAD_FIRMWARE_FULL_UNKNOWN_ERROR', 'DEVICE_UPDATE'));
        }

        // You should also check filesize here.
        if ($_FILES['new_firmware']['size'] > $maxFileSize) {
            throw new RuntimeException(__('UPLOAD_FIRMWARE_FULL_TOO_BIG', 'DEVICE_UPDATE', [sprintf('%sMB', $maxMb)]));
        }
        if (in_array($_FILES['new_firmware']['type'], ['application/gzip', 'application/x-gzip'])) {
            $ext = 'bin.gz';
        } elseif (in_array($_FILES['new_firmware']['type'], ['application/octet-stream', 'application/macbinary'])) {
            $ext = 'bin';
        } else {
            throw new RuntimeException(
                __('UPLOAD_FIRMWARE_FULL_WRONG_FORMAT', 'DEVICE_UPDATE', [$_FILES['new_firmware']['type']])
            );
        }

        $new_firmware_path = $firmwarefolder.'tasmota.'.$ext;

        if (!move_uploaded_file(
            $_FILES['new_firmware']['tmp_name'],
            $new_firmware_path
        )) {
            throw new RuntimeException(__('UPLOAD_FIRMWARE_FULL_COULD_NOT_SAVE', 'DEVICE_UPDATE'));
        }

        $messages[] = __('UPLOAD_FIRMWARE_FULL_LABEL', 'DEVICE_UPDATE').': '.__(
            'UPLOAD_FIRMWARE_FULL_SUCCESSFULLY',
            'DEVICE_UPDATE'
        );
    } catch (RuntimeException $e) {
        $errors[] = __('UPLOAD_FIRMWARE_FULL_LABEL', 'DEVICE_UPDATE').': '.$e->getMessage().'!';
    }
} elseif (isset($_REQUEST['auto'])) {
    $client = GuzzleFactory::getClient($Config);
    $tasmotaHelper = new TasmotaHelper(
        new Parsedown(),
        $client,
        new TasmotaOtaScraper($Config->read('auto_update_channel'), new HttpBrowser()),
        $Config->read('auto_update_channel')
    );

    // File to save the contents to
    if (!empty($_REQUEST['update_automatic_lang'])) {
        $Config->write('update_automatic_lang', $_REQUEST['update_automatic_lang']);
    }
    $fwAsset = $Config->read('update_automatic_lang');

    if ('' !== $fwAsset) {
        $firmwareDownloader = new FirmwareDownloader(GuzzleFactory::getClient($Config), $firmwarefolder);

        try {
            $result = $tasmotaHelper->getLatestFirmwares($fwAsset);

            if ($result->hasMinimalFirmware()) {
                // We need minimal firmware downloaded for upgrade to work for esp8266
                $minimal_firmware_path = $firmwareDownloader->download($result->getMinimalFirmwareUrl());
            }
            $new_firmware_path = $firmwareDownloader->download($result->getFirmwareUrl());
            $targetVersion = $result->getTagName();
            $messages[] = __('AUTO_SUCCESSFULL_DOWNLOADED', 'DEVICE_UPDATE').'<br/>';
            $messages[] = __('ASSET', 'DEVICE_UPDATE').': '.$fwAsset.' | '.__(
                'VERSION',
                'DEVICE_UPDATE'
            ).': '.$result->getTagName().' | '.__('DATE', 'DEVICE_UPDATE').' '.$result->getPublishedAt()->format('Y-m-d');
        } catch (Throwable $e) {
            $errors[] = __('AUTO_ERROR_DOWNLOAD', 'DEVICE_UPDATE').'<br/>'.$e->getMessage();
        }
    } else {
        $errors[] = __('MSG_SET_AUTOMATIC_LANG_FIRST', 'DEVICE_UPDATE');
    }
} else {
    $errors[] = __('UPLOAD_PLEASE_UPLOAD_FIRMWARE', 'DEVICE_UPDATE').'<br/>';
}

$ota_server_ip = $_REQUEST['ota_server_ip'] ?? '';
$ota_server_port = $_REQUEST['ota_server_port'] ?? '';

$Config->write('ota_server_ip', $ota_server_ip);
$Config->write('ota_server_port', $ota_server_port);

$otaHelper = new OtaHelper($Config, _BASEURL_);

$firmwareChecker = new FirmwareChecker(GuzzleFactory::getClient($Config));

$checkForFirmware = '1' === $Config->read('update_be_check');

if ($checkForFirmware && !empty($minimal_firmware_path) && !$firmwareChecker->isValid($otaHelper->getFirmwareUrl($minimal_firmware_path))) {
    $errors[] = __('FIRMWARE_NOT_ACCESSIBLE', 'DEVICE_UPDATE', [
        __('UPLOAD_FIRMWARE_MINIMAL_LABEL', 'DEVICE_UPDATE'),
        $otaHelper->getFirmwareUrl($minimal_firmware_path),
    ]).'<br>'.__('FIRMWARE_NOT_ACCESSIBLE_HELP', 'DEVICE_UPDATE');
}

if ($checkForFirmware && !$firmwareChecker->isValid($otaHelper->getFirmwareUrl($new_firmware_path))) {
    $errors[] = __('FIRMWARE_NOT_ACCESSIBLE', 'DEVICE_UPDATE', [
        __('UPLOAD_FIRMWARE_FULL_LABEL', 'DEVICE_UPDATE'),
        $otaHelper->getFirmwareUrl($new_firmware_path),
    ]).'<br>'.__('FIRMWARE_NOT_ACCESSIBLE_HELP', 'DEVICE_UPDATE');
}

?>

<div class='row justify-content-sm-center'>
	<div class='col col-12 col-md-6 '>
		<h2 class='text-sm-center mb-3'>
			<?php echo $title; ?>
		</h2>
	</div>
</div>
<?php if (!empty($errors)) { ?>
<div class='row justify-content-sm-center'>
	<div class='col col-12 col-md-6 '>
		<div class="alert alert-danger fade show mb-3" role="alert">
			<?php echo implode('<br/>', $errors); ?>
		</div>
	</div>
</div>
<?php } else { ?>
    <?php if (!empty($messages)) { ?>
    <div class='row justify-content-sm-center'>
        <div class='col col-12 col-md-6 '>
            <div class="alert alert-success fade show mb-3" role="alert">
                <?php echo implode('<br/>', $messages); ?>
            </div>
        </div>
    </div>
    <?php } ?>
	
	<?php

    $devices = $Sonoff->getDevices();
    $disabledDeviceIds = array_map(function (Device $device) {
        return $device->id;
    }, array_filter($devices, function (Device $device) {
        return !$device->isUpdatable;
    }));

    ?>
	
	
	<?php if (isset($_REQUEST['auto'])) { ?>
		<div class='row justify-content-sm-center'>
			<div class='col col-12 col-md-6 '>
				<div class="alert alert-warning fade show mb-5" data-dismiss="alert" role="alert">
					<?php echo __('AUTO_WARNING_CFG_HOLDER', 'DEVICE_UPDATE'); ?>
				</div>
			</div>
		</div>
	<?php } ?>
	<div class='row'>
		<div class='col col-12'>
			<div class='mb-3 text-center '>
				<h3>
					<?php echo __('CHOOSE_DEVICES_TO_UPDATE', 'DEVICE_UPDATE'); ?>
				</h3>
			</div>
			<form name='update_devices'
				  class=''
				  id='update_devices'
				  method='post'
				  action='<?php echo _BASEURL_; ?>device_update'
			>
				<input type='hidden' name='new_firmware_path' value='<?php echo $new_firmware_path; ?>'>
				<input type='hidden' name='target_version' value='<?php echo $targetVersion; ?>'>

				<div class='form-row mb-3'>
					<div class='offset-1 col-auto col col-auto'>
						<button type='submit' class='btn btn-success' name='submit' value='submit'>
							<?php echo __('BTN_START_UPDATE', 'DEVICE_UPDATE'); ?>
						</button>
					</div>
					<div class='col col-auto'>
						<div class="form-check pl-0">
							<input type="checkbox"
								   class="form-check-input showmore d-none"
								   id="showmore"
								   name='showmore'
							>
							<label class="form-check-label  btn btn-secondary" for="showmore">
								<?php echo __('SHOW_MORE', 'DEVICES'); ?>
							</label>
						</div>
					</div>
					<?php if (1 == $Config->read('show_search')) { ?>
						<div class="col col-auto">
							<div class="form-group">
								<div class="input-group">
									<input type="text"
										   name="searchterm"
										   class='form-control device-search has-clearer'
										   placeholder="<?php echo __('FILTER', 'DEVICES'); ?>"
									>
									<div class="input-group-append">
										<span class="input-group-text">
											<i class="fas fa-search"></i>
										</span>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
				<div class='row justify-content-center'>
					<div class='col'>
						<div class='table-responsive double-scroll'>
                            <?php
                            $deviceLinks = true;
    $deviceLinkActionText = __('UPDATE', 'DEVICE_UPDATE');

    include 'elements/devices_table.php';
    ?>
						</div>
					</div>
				</div>
				
				<div class='form-row mt-3'>
					<div class='col col-auto offset-1'>
						<button type='submit' class='btn btn-success' name='submit' value='submit'>
							<?php echo __('BTN_START_UPDATE', 'DEVICE_UPDATE'); ?>
						</button>
					</div>
					<div class='col col-auto'>
						<div class="form-check pl-0">
							<input type="checkbox"
								   class="form-check-input showmore d-none"
								   id="showmore"
								   name='showmore'
							>
							<label class="form-check-label  btn btn-secondary" for="showmore">
								<?php echo __('SHOW_MORE', 'DEVICES'); ?>
							</label>
						</div>
					</div>
				</div>
			</form>
		
		</div>
	</div>
	<script src="<?php echo $urlHelper->js('compiled/devices'); ?>"></script>
<?php } ?>

