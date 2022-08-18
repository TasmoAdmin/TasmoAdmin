<?php

use TasmoAdmin\Helper\FirmwareFolderHelper;
use TasmoAdmin\Helper\GuzzleFactory;
use TasmoAdmin\Helper\OtaHelper;
use TasmoAdmin\Helper\TasmotaHelper;
use TasmoAdmin\Helper\UrlHelper;
use TasmoAdmin\Update\FirmwareChecker;
use TasmoAdmin\Update\FirmwareDownloader;

$msg                   = "";
$error                 = FALSE;
$firmwarefolder        = _DATADIR_ . "firmwares/";
$minimal_firmware_path = "";
$new_firmware_path     = "";

$use_gzip_package  = isset($_REQUEST["use_gzip_package"]) ? $_REQUEST["use_gzip_package"] : "0";
$Config->write("use_gzip_package", $use_gzip_package);

FirmwareFolderHelper::clean($firmwarefolder);

if (isset($_REQUEST["upload"])) {
	if ($_FILES['minimal_firmware']["name"] == "") {
		$msg .= __("UPLOAD_FIRMWARE_MINIMAL_LABEL", "DEVICE_UPDATE") . ": " . __(
				"UPLOAD_FIRMWARE_MINIMAL_SKIP",
				"DEVICE_UPDATE"
			) . "<br/>";
	}
	else {
		try {
			
			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if (!isset($_FILES['minimal_firmware']['error'])
				|| is_array($_FILES['minimal_firmware']['error'])) {
				throw new RuntimeException(__("UPLOAD_FIRMWARE_MINIMAL_INVALID_FILES", "DEVICE_UPDATE"));
			}
			
			// Check $_FILES['minimal_firmware']['error'] value.
			switch ($_FILES['minimal_firmware']['error']) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					throw new RuntimeException(__("UPLOAD_FIRMWARE_MINIMAL_ERR_NO_FILE", "DEVICE_UPDATE"));
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException(__("UPLOAD_FIRMWARE_MINIMAL_ERR_FORM_SIZE", "DEVICE_UPDATE"));
				default:
					throw new RuntimeException(__("UPLOAD_FIRMWARE_MINIMAL_UNKNOWN_ERROR", "DEVICE_UPDATE"));
			}
			
			// You should also check filesize here.
			if ($_FILES['minimal_firmware']['size'] > 502000) {
				throw new RuntimeException(
					__("UPLOAD_FIRMWARE_MINIMAL_TOO_BIG", "DEVICE_UPDATE", ["maxsize" => "502kb"])
				);
			}		
			if ($_FILES['minimal_firmware']["type"] == "application/gzip"
			 || $_FILES['minimal_firmware']["type"] == "application/x-gzip") {
					$ext = "bin.gz";
					$useGZIP = 1;
			}
			elseif ($_FILES['minimal_firmware']["type"] == "application/octet-stream"
					 || $_FILES['minimal_firmware']["type"] == "application/macbinary") {
					$ext = "bin";
					$useGZIP = 0;
			}
			else {
				throw new RuntimeException(
					__("UPLOAD_FIRMWARE_MINIMAL_WRONG_FORMAT", "DEVICE_UPDATE", $_FILES['minimal_firmware']["type"])
				);
			}

			$minimal_firmware_path = $firmwarefolder . "tasmota-minimal" . "." . $ext;
			
			if (!move_uploaded_file(
				$_FILES['minimal_firmware']['tmp_name'],
				$minimal_firmware_path
			)) {
				throw new RuntimeException(
					__(
						"UPLOAD_FIRMWARE_MINIMAL_COULD_NOT_SAVE",
						"DEVICE_UPDATE",
						["FWPath" => $minimal_firmware_path]
					)
				);
			}
			
			$msg .= __("UPLOAD_FIRMWARE_MINIMAL_LABEL", "DEVICE_UPDATE") . ": " . __(
					"UPLOAD_FIRMWARE_MINIMAL_SUCCESSFULLY",
					"DEVICE_UPDATE"
				) . "</br>";
			
		} catch (RuntimeException $e) {
			$error = TRUE;
			$msg   .= __("UPLOAD_FIRMWARE_MINIMAL_LABEL", "DEVICE_UPDATE") . ": " . $e->getMessage() . "!</br>";
			
		}
		
	}
	try {
		
		// Undefined | Multiple Files | $_FILES Corruption Attack
		// If this request falls under any of them, treat it invalid.
		if (!isset($_FILES['new_firmware']['error'])
			|| is_array($_FILES['new_firmware']['error'])) {
			throw new RuntimeException(__("UPLOAD_FIRMWARE_FULL_INVALID_FILES", "DEVICE_UPDATE"));
		}
		
		// Check $_FILES['new_firmware']['error'] value.
		switch ($_FILES['new_firmware']['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				throw new RuntimeException(__("UPLOAD_FIRMWARE_FULL_ERR_NO_FILE", "DEVICE_UPDATE"));
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new RuntimeException(__("UPLOAD_FIRMWARE_FULL_ERR_FORM_SIZE", "DEVICE_UPDATE"));
			default:
				throw new RuntimeException(__("UPLOAD_FIRMWARE_FULL_UNKNOWN_ERROR", "DEVICE_UPDATE"));
		}
		
		// You should also check filesize here.
		if ($_FILES['new_firmware']['size'] > 1000000) {
			throw new RuntimeException(__("UPLOAD_FIRMWARE_FULL_TOO_BIG", "DEVICE_UPDATE"));
		}
		if ($_FILES['new_firmware']["type"] == "application/gzip"
		 || $_FILES['new_firmware']["type"] == "application/x-gzip") {
				$ext = "bin.gz";
				$useGZIP = 1;
		}
		elseif ($_FILES['new_firmware']["type"] == "application/octet-stream"
				 || $_FILES['new_firmware']["type"] == "application/macbinary") {
				$ext = "bin";
				$useGZIP = 0;
		}
		else {
			throw new RuntimeException(
				__("UPLOAD_FIRMWARE_FULL_WRONG_FORMAT", "DEVICE_UPDATE", $_FILES['new_firmware']["type"])
			);
		}
		
		$new_firmware_path = $firmwarefolder . "tasmota" . "." . $ext;
		
		if (!move_uploaded_file(
			$_FILES['new_firmware']['tmp_name'],
			
			$new_firmware_path
		)) {
			throw new RuntimeException(__("UPLOAD_FIRMWARE_FULL_COULD_NOT_SAVE", "DEVICE_UPDATE"));
		}
		
		$msg .= __("UPLOAD_FIRMWARE_FULL_LABEL", "DEVICE_UPDATE") . ": " . __(
				"UPLOAD_FIRMWARE_FULL_SUCCESSFULLY",
				"DEVICE_UPDATE"
			) . "</br>";
		
	} catch (RuntimeException $e) {
		$error = TRUE;
		$msg   .= __("UPLOAD_FIRMWARE_FULL_LABEL", "DEVICE_UPDATE") . ": " . $e->getMessage() . "!</br>";
		
	}
}
elseif (isset($_REQUEST["auto"])) {
    $tasmotaHelper = new TasmotaHelper(new Parsedown(), GuzzleFactory::getClient($Config));

	$useGZIP  = $Config->read("use_gzip_package");
	if ($useGZIP === "1") {
		$ext = "bin.gz";
	} else {
		$ext = "bin";
	}

	//File to save the contents to
	if (!empty($_REQUEST["update_automatic_lang"])) {
		$Config->write("update_automatic_lang", $_REQUEST["update_automatic_lang"]);
	}
	$fwAsset = $Config->read("update_automatic_lang");
	
	if ($fwAsset !== "") {
        $firmwareDownloader = new FirmwareDownloader(GuzzleFactory::getClient($Config), $firmwarefolder);
        try {
            $result = $tasmotaHelper->getLatestFirmwares($ext, $fwAsset);
            $minimal_firmware_path= $firmwareDownloader->download($result->getMinimalFirmwareUrl());
            $new_firmware_path = $firmwareDownloader->download($result->getFirmwareUrl());

			$withGzip = $useGZIP ? "true" : "false";
			$msg .= __("AUTO_SUCCESSFULL_DOWNLOADED", "DEVICE_UPDATE") . "<br/>";
			$msg .= __("ASSET", "DEVICE_UPDATE") . ": " . $fwAsset . " | Gzip: " . $withGzip . " | " . __(
					"VERSION",
					"DEVICE_UPDATE"
				) . ": " . $result->getTagName();
		} catch (Throwable $e) {
			$error = TRUE;
			$msg   .= __("AUTO_ERROR_DOWNLOAD", "DEVICE_UPDATE") . "<br/>" . $e->getMessage();
		}
	}
	else {
		$error = TRUE;
		$msg   = __("MSG_SET_AUTOMATIC_LANG_FIRST", "DEVICE_UPDATE");
	}
}
else {
	$error = TRUE;
	$msg   .= __("UPLOAD_PLEASE_UPLOAD_FIRMWARE", "DEVICE_UPDATE") . "<br/>";
}

$ota_server_ssl  = isset($_REQUEST["ota_server_ssl"]) ? $_REQUEST["ota_server_ssl"] : "0";
$ota_server_ip   = isset($_REQUEST["ota_server_ip"]) ? $_REQUEST["ota_server_ip"] : "";
$ota_server_port = isset($_REQUEST["ota_server_port"]) ? $_REQUEST["ota_server_port"] : "";

$Config->write("ota_server_ssl", $ota_server_ssl);
$Config->write("ota_server_ip", $ota_server_ip);
$Config->write("ota_server_port", $ota_server_port);

$otaHelper = new OtaHelper($Config, _BASEURL_);

$firmwareChecker = new FirmwareChecker(GuzzleFactory::getClient($Config));

$checkForFirmware = $Config->read("update_be_check") === "1";

if ($checkForFirmware && !$firmwareChecker->isValid($otaHelper->getFirmwareUrl($minimal_firmware_path))) {
    $error = true;
    $msg = __("FIRMWARE_NOT_ACCESSIBLE", "DEVICE_UPDATE", [
        __("UPLOAD_FIRMWARE_MINIMAL_LABEL", "DEVICE_UPDATE"),
        $otaHelper->getFirmwareUrl($minimal_firmware_path)
        ]) . "<br>" .  __("FIRMWARE_NOT_ACCESSIBLE_HELP", "DEVICE_UPDATE");
}

if ($checkForFirmware &&  !$firmwareChecker->isValid($otaHelper->getFirmwareUrl($new_firmware_path))) {
    $error = true;
    $msg = __("FIRMWARE_NOT_ACCESSIBLE", "DEVICE_UPDATE",[
        __("UPLOAD_FIRMWARE_FULL_LABEL", "DEVICE_UPDATE"),
        $otaHelper->getFirmwareUrl($new_firmware_path)
    ]) . "<br>" .  __("FIRMWARE_NOT_ACCESSIBLE_HELP", "DEVICE_UPDATE");
}

?>

<div class='row justify-content-sm-center'>
	<div class='col col-12 col-md-6 '>
		<h2 class='text-sm-center mb-3'>
			<?php echo $title; ?>
		</h2>
	</div>
</div>
<?php if (isset($error) && $error): ?>
	<div class='row justify-content-sm-center'>
		<div class='col col-12 col-md-6 '>
			<div class="alert alert-danger fade show mb-3" role="alert">
				<?php echo $msg; ?>
			</div>
		</div>
	</div>
<?php else: ?>
	<?php if (isset($msg) && $msg != ""): ?>
		<div class='row justify-content-sm-center'>
			<div class='col col-12 col-md-6 '>
				<div class="alert alert-success fade show mb-3" role="alert">
					<?php echo $msg; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	
	<?php $devices = $Sonoff->getDevices(); ?>
	
	
	<?php if (isset($_REQUEST["auto"])) : ?>
		<div class='row justify-content-sm-center'>
			<div class='col col-12 col-md-6 '>
				<div class="alert alert-warning fade show mb-5" data-dismiss="alert" role="alert">
					<?php echo __("AUTO_WARNING_CFG_HOLDER", "DEVICE_UPDATE"); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<div class='row'>
		<div class='col col-12  '>
			<div class='mb-3 text-center '>
				<h3>
					<?php echo __("CHOOSE_DEVICES_TO_UPDATE", "DEVICE_UPDATE"); ?>
				</h3>
			</div>
			<form name='update_devices'
				  class=''
				  id='update_devices'
				  method='post'
				  action='<?php echo _BASEURL_; ?>device_update'
			>
				<input type='hidden' name='minimal_firmware_path' value='<?php echo $minimal_firmware_path; ?>'>
				<input type='hidden' name='new_firmware_path' value='<?php echo $new_firmware_path; ?>'>
				<input type='hidden' name='target_version' value='<?php echo $result->getTagName(); ?>'>

				<div class='form-row mb-3'>
					<div class='offset-1 col-auto col col-auto'>
						<button type='submit' class='btn btn-success' name='submit' value='submit'>
							<?php echo __("BTN_START_UPDATE", "DEVICE_UPDATE"); ?>
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
								<?php echo __("SHOW_MORE", "DEVICES"); ?>
							</label>
						</div>
					</div>
					<?php if ($Config->read("show_search") == 1): ?>
						<div class="col col-auto">
							<div class="form-group">
								<div class="input-group">
									<input type="text"
										   name="searchterm"
										   class='form-control device-search has-clearer'
										   placeholder="<?php echo __("FILTER", "DEVICES"); ?>"
									>
									<div class="input-group-append">
										<span class="input-group-text">
											<i class="fas fa-search"></i>
										</span>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<div class='row justify-content-center'>
					<div class='col'>
						<div class='table-responsive double-scroll'>
							<table id='device-list'
								   class='table table-striped table-sm table-hover tablesaw tablesaw-stack'
								   data-tablesaw-mode="stack"
								   border='0'
								   cellspacing='0'
							>
								<thead>
									<tr>
										<th class='link'>
											<div class="form-check custom-control custom-checkbox">
												<input class="form-check-input custom-control-input select_all"
													   type="checkbox"
													   value='select_all'
													   id="select_all"
													   name='select_all'
												>
												<label class="form-check-label custom-control-label" for="select_all">
													<?php echo __("TABLE_HEAD_ALL", "DEVICES"); ?>
												</label>
											</div>
										</th>
										<th><?php echo __("TABLE_HEAD_ID", "DEVICES"); ?></th>
										<th><?php echo __("TABLE_HEAD_NAME", "DEVICES"); ?></th>
										<th><?php echo __("TABLE_HEAD_IP", "DEVICES"); ?></th>
										<th><?php echo __("TABLE_HEAD_STATE", "DEVICES"); ?></th>
										<th>
											<i class="fas fa-signal"
											   title='<?php echo __("TABLE_HEAD_RSSI", "DEVICES"); ?>'
											></i>
										</th>
										<th><?php echo __("TABLE_HEAD_VERSION", "DEVICES"); ?></th>
										<th><?php echo __("TABLE_HEAD_RUNTIME", "DEVICES"); ?></th>
										<th class='temp hidden'><?php echo __("TABLE_HEAD_TEMP", "DEVICES"); ?></th>
										<th class='humidity hidden'><?php echo __(
												"TABLE_HEAD_HUMIDITY",
												"DEVICES"
											); ?></th>
										<th class='more'><?php echo __("HOSTNAME", "DEVICES"); ?></th>
										<th class='more'><?php echo __("MAC", "DEVICES"); ?></th>
										<th class='more'><?php echo __("MQTT", "DEVICES"); ?></th>
										<th class='more idx hidden'><?php echo __(
												"TABLE_HEAD_IDX",
												"DEVICES"
											); ?></th>
										<th class='more'><?php echo __("POWERONSTATE", "DEVICES"); ?></th>
										<th class='more'><?php echo __("LEDSTATE", "DEVICES"); ?></th>
										<th class='more'><?php echo __("SAVEDATA", "DEVICES"); ?></th>
										<th class='more'><?php echo __("SLEEP", "DEVICES"); ?></th>
										<th class='more'><?php echo __("BOOTCOUNT", "DEVICES"); ?></th>
										<th class='more'><?php echo __("SAVECOUNT", "DEVICES"); ?></th>
										<th class='more'><?php echo __("LOGSTATES", "DEVICES"); ?></th>
										<th class='more'><?php echo __("WIFICONFIG", "DEVICES"); ?></th>
										<th class='more'><?php echo __("VCC", "DEVICES"); ?></th>
										
										<th class='link text-sm-right'>
											<a href='<?php echo _BASEURL_; ?>device_action/add'>
												<i class="fas fa-plus add"
												   title='<?php echo __("TABLE_HEAD_NEW_DEVICE", "DEVICES"); ?>'
												></i>
												<?php echo __("TABLE_HEAD_NEW_DEVICE", "DEVICES"); ?>
											</a>
										</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$odd = TRUE;
									if (isset($devices) && !empty($devices)):
										foreach ($devices as $device_group):
											foreach ($device_group->names as $key => $devicename): ?>
												<?php
												$device_group->keywords[] = strtolower($devicename);
												?>
												<tr class='<?php echo $odd ? "odd" : "even"; ?>'
													data-device_id='<?php echo $device_group->id; ?>'
													data-device_group='<?php echo count($device_group->names) > 1
														? "multi" : "single"; ?>'
													data-device_ip='<?php echo $device_group->ip; ?>'
													data-device_relais='<?php echo $key + 1; ?>'
													data-keywords="<?php echo implode(" ", $device_group->keywords); ?>"
												>
													<td class='update_cb'>
														<?php if ($key == 0): ?>
															<div class="form-check custom-control custom-checkbox">
																<input class="form-check-input custom-control-input device_checkbox"
																	   type="checkbox"
																	   value='<?php echo $device_group->id; ?>'
																	   id="cb_<?php echo $device_group->id; ?>"
																	   name='device_ids[]'
																>
																<label class="form-check-label custom-control-label"
																	   for="cb_<?php echo $device_group->id; ?>"
																>
																	<?php echo __("UPDATE", "DEVICE_UPDATE"); ?>
																</label>
															</div>
														
														
														<?php endif; ?>
													</td>
													<td><?php echo $device_group->id; ?></td>
													<td>
														<a href='http://<?php echo $device_group->ip; ?>/'
														   target='_blank'
														   title='<?php echo __(
															   "LINK_OPEN_DEVICE_WEBUI",
															   "DEVICES"
														   ); ?>'
														><?php echo str_replace(
																" ",
																"&nbsp;",
																$devicename
															); ?></a>
													</td>
													<td><?php echo $device_group->ip; ?></td>
													<td class='status'>
														<label class="form-switch">
															<input type="checkbox">
															<i></i>
														</label>
													
													</td>
													<td class='rssi'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='version'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='runtime'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='temp hidden'>
														<span>
															-
														</span>
													</td>
													<td class='humidity hidden'>
														<span>
															-
														</span>
													</td>
													
													
													<td class='more hostname dblcEdit' data-cmnd='Hostname'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more mac'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more mqtt dblcEdit' data-cmnd='Mqtt'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more idx hidden'>
														<span>
															-
														</span>
													</td>
													<td class='more poweronstate dblcEdit' data-cmnd='PowerOnState'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more ledstate dblcEdit' data-cmnd='LedState'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more savedata dblcEdit' data-cmnd='SaveData'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more sleep dblcEdit' data-cmnd='Sleep'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more bootcount'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more savecount'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more log'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more wificonfig dblcEdit' data-cmnd='WifiConfig'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													<td class='more vcc'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
																	 alt='<?php echo __("TEXT_LOADING"); ?>'
																	 title='<?php echo __("TEXT_LOADING"); ?>'
																>
															</div>
														</span>
													</td>
													
													
													<td class='col actions text-sm-right'>
														<a href='<?php echo _BASEURL_; ?>device_config/<?php echo $device_group->id; ?>'>
															<i class="fas fa-cogs fa-lg"
															   title='<?php echo __(
																   "LINK_DEVICE_CONFIG",
																   "DEVICES"
															   ); ?>'
															></i></a>
														<a href='<?php echo _BASEURL_; ?>device_action/edit/<?php echo $device_group->id; ?>'>
															<i class="fas fa-edit fa-lg"
															   title='<?php echo __(
																   "LINK_DEVICE_EDIT",
																   "DEVICES"
															   ); ?>'
															></i></a>
														<a class="delete"
														   data-dialog-btn-cancel-text='<?php echo __(
															   "CANCEL"
														   ); ?>'
														   data-dialog-btn-ok-text='<?php echo __(
															   "DELETE_DEVICE",
															   "DEVICES"
														   ); ?>'
														   data-dialog-title='<?php echo __(
															   "DELETE_DEVICE_CONFIRM_TITLE",
															   "DEVICES"
														   ); ?>'
														   data-dialog-text='<?php echo __(
															   "DELETE_DEVICE_CONFIRM_TEXT",
															   "DEVICES",
															   [
																   $devicename,
																   $device_group->ip,
															   ]
														   ); ?>'
														   href='<?php echo _BASEURL_; ?>device_action/delete/<?php echo $device_group->id; ?>'
														>
															<i class="fas fa-trash fa-lg"
															   title='<?php echo __(
																   "LINK_DEVICE_DELETE",
																   "DEVICES"
															   ); ?>'
															></i></a>
														<a href='#' class='restart-device'>
															<i class="fas fa-sync fa-lg"
															   title='<?php echo __(
																   "LINK_DEVICE_RESTART",
																   "DEVICES"
															   ); ?>'
															></i></a>
													</td>
												
												</tr>
												<?php
												$odd = !$odd;
											endforeach;
										endforeach;
									endif; ?>
								</tbody>
								<tfoot>
									<tr class='bottom'>
										<th class='link'>
											<div class="form-check custom-control custom-checkbox">
												<input class="form-check-input custom-control-input select_all"
													   type="checkbox"
													   value='select_all'
													   id="select_all"
													   name='select_all'
												>
												<label class="form-check-label custom-control-label" for="select_all">
													<?php echo __("TABLE_HEAD_ALL", "DEVICES"); ?>
												</label>
											</div>
										</th>
										<th><?php echo __("TABLE_HEAD_ID", "DEVICES"); ?></th>
										<th><?php echo __("TABLE_HEAD_NAME", "DEVICES"); ?></th>
										<th><?php echo __("TABLE_HEAD_IP", "DEVICES"); ?></th>
										<th><?php echo __("TABLE_HEAD_STATE", "DEVICES"); ?></th>
										<th>
											<i class="fas fa-signal"
											   title='<?php echo __("TABLE_HEAD_RSSI", "DEVICES"); ?>'
											></i>
										</th>
										<th><?php echo __("TABLE_HEAD_VERSION", "DEVICES"); ?></th>
										<th><?php echo __("TABLE_HEAD_RUNTIME", "DEVICES"); ?></th>
										<th class='temp hidden'><?php echo __("TABLE_HEAD_TEMP", "DEVICES"); ?></th>
										<th class='humidity hidden'><?php echo __(
												"TABLE_HEAD_HUMIDITY",
												"DEVICES"
											); ?></th>
										<th class='more'><?php echo __("HOSTNAME", "DEVICES"); ?></th>
										<th class='more'><?php echo __("MAC", "DEVICES"); ?></th>
										<th class='more'><?php echo __("MQTT", "DEVICES"); ?></th>
										<th class='more idx hidden'><?php echo __(
												"TABLE_HEAD_IDX",
												"DEVICES"
											); ?></th>
										<th class='more'><?php echo __("POWERONSTATE", "DEVICES"); ?></th>
										<th class='more'><?php echo __("LEDSTATE", "DEVICES"); ?></th>
										<th class='more'><?php echo __("SAVEDATA", "DEVICES"); ?></th>
										<th class='more'><?php echo __("SLEEP", "DEVICES"); ?></th>
										<th class='more'><?php echo __("BOOTCOUNT", "DEVICES"); ?></th>
										<th class='more'><?php echo __("SAVECOUNT", "DEVICES"); ?></th>
										<th class='more'><?php echo __("LOGSTATES", "DEVICES"); ?></th>
										<th class='more'><?php echo __("WIFICONFIG", "DEVICES"); ?></th>
										<th class='more'><?php echo __("VCC", "DEVICES"); ?></th>
										<th class='link'>
											<a href='<?php echo _BASEURL_; ?>device_action/add'>
												<i class="fas fa-plus add"
												   title='<?php echo __("TABLE_HEAD_NEW_DEVICE", "DEVICES"); ?>'
												></i>
												<?php echo __("TABLE_HEAD_NEW_DEVICE", "DEVICES"); ?>
											</a>
										</th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
				
				<div class='form-row mt-3'>
					<div class='col col-auto offset-1'>
						<button type='submit' class='btn btn-success' name='submit' value='submit'>
							<?php echo __("BTN_START_UPDATE", "DEVICE_UPDATE"); ?>
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
								<?php echo __("SHOW_MORE", "DEVICES"); ?>
							</label>
						</div>
					</div>
				</div>
			</form>
		
		</div>
	</div>
	<script>
        $(document).ready(function()
        {
            //select all checkboxes
            $(".select_all").change(function ()
                                    {  //"select all" change
                                        var status = this.checked; // "select all" checked status
                                        $(".device_checkbox").each(function ()
                                                                   { //iterate all listed checkbox items
                                                                       this.checked = status; //change ".checkbox" checked status
                                                                   });

                                        $(".select_all").each(function ()
                                                              { //iterate all listed checkbox items
                                                                  this.checked = status; //change ".checkbox" checked status
                                                              });

                                    });

            $(".device_checkbox").change(function ()
                                         { //".checkbox" change
                                             //uncheck "select all", if one of the listed checkbox item is unchecked
                                             if (this.checked == false)
                                             { //if this item is unchecked
                                                 $(".select_all").each(function ()
                                                                       { //iterate all listed checkbox items
                                                                           this.checked = false; //change ".checkbox" checked status
                                                                       });
                                             }

                                             //check "select all" if all checkbox items are checked
                                             if ($(".device_checkbox:checked").length == $(".device_checkbox").length)
                                             {
                                                 $(".select_all").each(function ()
                                                                       { //iterate all listed checkbox items
                                                                           this.checked = true; //change ".checkbox" checked status
                                                                       });
                                             }
                                         });

        });
	</script>
	
	<script src="<?php echo $urlHelper->js("devices"); ?>"></script>
<?php endif; ?>

