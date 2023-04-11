<?php

use Symfony\Component\BrowserKit\HttpBrowser;
use TasmoAdmin\Helper\GuzzleFactory;
use TasmoAdmin\Helper\TasmotaHelper;
use TasmoAdmin\Helper\TasmotaOtaScraper;

$tasmotaHelper = new TasmotaHelper(
    new Parsedown(),
    GuzzleFactory::getClient($Config),
    new TasmotaOtaScraper($Config->read('auto_update_channel'), new HttpBrowser()),
    $Config->read("auto_update_channel")
);
$releaseNotes = $tasmotaHelper->getReleaseNotes();
$changelog = $tasmotaHelper->getChangelog();
$releases = $tasmotaHelper->getReleases();

$fwAsset = $Config->read("update_automatic_lang");

?>
<div class='row justify-content-sm-center'>
	<div class='col col-12 col-md-8 col-xl-6'>
		<h2 class='text-sm-center mb-3'>
			<?php echo $title; ?>
		</h2>
		<div class='text-center mb-3'>
			<?php echo __("UPLOAD_DESCRIPTION", "DEVICE_UPDATE"); ?>
			<br/>
			<a href='https://github.com/arendst/Tasmota/releases' target='_blank'>Tasmota Releases</a>
		</div>
		
		
		<form class='' name='update_form' method='post' enctype='multipart/form-data'
			  action='<?php echo _BASEURL_; ?>upload'
		>
			<div class='form-row'>
				<div class="form-group col col-12 col-sm-3">
					<div class="form-check custom-control custom-checkbox mb-3" style='margin-top: 35px;'>
						<input class="form-check-input custom-control-input"
							   type="checkbox"
							   value="1"
							   autofocus="autofocus"
							   id="cb_ota_server_ssl"
							   name='ota_server_ssl' <?php echo $Config->read("ota_server_ssl") == "1"
							? "checked=\"checked\"" : ""; ?>>
						<label class="form-check-label custom-control-label" for="cb_ota_server_ssl" style='top:3px;'>
							<?php echo __("CONFIG_SERVER_SSL", "USER_CONFIG"); ?>
						</label>
					</div>
				</div>
				
				<div class="form-group col col-12 col-sm-6">
					<label for="ota_server_ip">
						<?php echo __("CONFIG_SERVER_IP", "USER_CONFIG"); ?>
					</label>
					<input type="text"
						   class="form-control"
						   id="ota_server_ip"
						   name='ota_server_ip'
						   required
						   placeholder="<?php echo __("PLEASE_ENTER"); ?>"
						   value='<?php echo $Config->read("ota_server_ip"); ?>'
					>
				</div>
				<div class="form-group col col-12 col-sm-3">
					<label for="ota_server_ip">
						<?php echo __("CONFIG_SERVER_PORT", "USER_CONFIG"); ?>
					</label>
					<input type="text"
						   class="form-control"
						   id="ota_server_port"
						   name='ota_server_port'
						   required
						   placeholder="<?php echo __("PLEASE_ENTER"); ?>"
						   value='<?php echo !empty($Config->read("ota_server_port")) ? $Config->read(
							   "ota_server_port"
						   ) : $_SERVER["SERVER_PORT"]; ?>'
					>
				</div>
			</div>
			
			<div class='form-row'>
				<div class="form-group col">
					<label for="minimal_firmware">
						<?php echo __("FORM_CHOOSE_MINIMAL_FIRMWARE", "DEVICE_UPDATE"); ?>
					</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="minimal_firmware" name='minimal_firmware'>
						<label class="custom-file-label" for="minimal_firmware">
						
						</label>
					</div>
				</div>
			</div>
			
			<div class='form-row'>
				
				<div class="form-group col">
					<label for="new_firmware">
						<?php echo __("UPLOAD_FIRMWARE_FULL_LABEL", "DEVICE_UPDATE"); ?>
					</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="new_firmware" name='new_firmware' required>
						<label class="custom-file-label" for="new_firmware">
						
						</label>
					</div>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col col-12 col-sm-6">
					<label for="update_automatic_lang">
						<?php echo __("CONFIG_AUTOMATIC_FW", "USER_CONFIG"); ?>
					</label>
					
					<select class="form-control custom-select" id="update_automatic_lang" name='update_automatic_lang'>
						<?php if ($fwAsset === ""): ?>
							<option><?php echo __("PLEASE_SELECT"); ?></option>
						<?php endif; ?>
						
						<?php foreach ($releases as $tr): ?>
							<option value='<?php echo $tr; ?>'
								<?php echo $fwAsset === $tr ? "selected=\selected\"" : ""; ?>
							>
								<?php echo $tr; ?>
							</option>
						<?php endforeach; ?>
					
					
					</select>
				</div>
			</div>
			<div class='form-row'>
				<div class="col col-12 col-sm-3">
					<button type='submit' class='btn btn-primary' id="automatic" name='auto' value='submit'
							title='<?php echo __("BTN_UPLOAD_AUTOMATIC_HELP", "DEVICE_UPDATE"); ?>'
					>
						<?php echo __("BTN_UPLOAD_AUTOMATIC", "DEVICE_UPDATE"); ?>
					</button>
				</div>
				
				<div class='col flex-column mb-3 mb-sm-0'></div>
				
				<div class='col col-12 col-sm-3 text-sm-right'>
					<button type='submit' class='btn btn-primary' name='upload' value='submit'>
						<?php echo __("BTN_UPLOAD_NEXT", "DEVICE_UPDATE"); ?>
					</button>
				</div>
			</div>
		
		
		</form>
	
	</div>
	
	<div class='col-12'>
		<hr class='my-5'>
		<div class='row'>
			<div class='col col-12 col-md-6'>
				<div class='changelog'>
					<?php echo $releaseNotes; ?>
				</div>
			</div>
			<div class='col col-12 col-md-6'>
				<div class='changelog'>
					<h1 class='text-uppercase'>
						<?php echo __("TASMOTA_CHANGELOG", "DEVICE_UPDATE"); ?>
					</h1>
					<?php echo $changelog; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelector('#automatic').addEventListener('click', () => {
            document.querySelector('#new_firmware').removeAttribute('required');
        })
    });
</script>
