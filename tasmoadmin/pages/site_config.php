<?php

use Goutte\Client;
use TasmoAdmin\Helper\GuzzleFactory;
use TasmoAdmin\Helper\TasmotaHelper;
use TasmoAdmin\Helper\TasmotaOtaScraper;

$msg      = FALSE;
$settings = [];

if (isset($_POST) && !empty($_POST)) {
	if (isset($_POST["save"])) {
		$settings = $_POST;
		unset($settings["save"]);
		
		if (!isset($settings["login"])) {
			$settings["login"] = "0";
		}
		
		if (!isset($settings["check_for_updates"])) {
			$settings["check_for_updates"] = "0";
		}
		if (!isset($settings["ota_server_ssl"])) {
			$settings["ota_server_ssl"] = "0";
		}
		if (!isset($settings["use_gzip_package"])) {
			$settings["use_gzip_package"] = "0";
		}
		if (!isset($settings["show_search"])) {
			$settings["show_search"] = "0";
		}

        if (!isset($settings["update_fe_check"])) {
            $settings["update_fe_check"] = "0";
        }

        if (!isset($settings["update_be_check"])) {
            $settings["update_be_check"] = "0";
        }

		if (!isset($settings["password"]) || empty($settings["password"])
			|| $settings["password"] == "") {
			unset($settings["password"]);
		}
		else {
			$settings["password"] = md5($settings["password"]);
		}
		if ($settings["login"] == "0") {
			unset($settings["password"]);
			unset($settings["username"]);
		}
		
		
		foreach ($settings as $settingKey => $settingVal) {
			$Config->write($settingKey, $settingVal);
		}
		//header( "Refresh:0" ); //fix for not updated config cuz of buffer
		$msg = __("MSG_USER_CONFIG_SAVED", "USER_CONFIG");
	}
}

$config = array_merge($Config->readAll(), $settings);

$tasmotaHelper = new TasmotaHelper(
        new Parsedown(),
        GuzzleFactory::getClient($Config),
        new TasmotaOtaScraper($Config->read('auto_update_channel'), new Client())
);
$tasmotaReleases = $tasmotaHelper->getReleases();


$autoFirmwareChannels = ['stable', 'dev'];

?>


<div class='row justify-content-sm-center'>
	<div class='col col-12 col-md-8 col-xl-6'>
		<div class='row'>
			<div class='col col-12'>
				<h2 class='text-left text-sm-center mb-5'>
					<?php echo $title; ?>
				</h2>
			</div>
		</div>
		
		<?php if (isset($msg) && $msg != ""): ?>
			<div class="alert alert-success alert-dismissible fade show mb-5" data-dismiss="alert" role="alert">
				<?php echo $msg; ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		<?php endif; ?>
		<form name='web_config' method='post'>
			<div class="form-row">
				<div class="form-group col col-12 col-sm-3">
					<div class="form-check custom-control custom-checkbox mb-5">
						<input class="form-check-input custom-control-input"
							   autofocus="autofocus"
							   type="checkbox"
							   value="1"
							   id="cb_login"
							   name='login' <?php echo $config["login"] == "1" ? "checked=\"checked\"" : ""; ?>>
						<label class="form-check-label custom-control-label" for="cb_login">
							<?php echo __("CONFIG_LOGIN_ENABLE", "USER_CONFIG"); ?>
						</label>
					</div>
				</div>
				<div class="form-group col col-12 col-sm-4">
					<div class="form-check custom-control custom-checkbox mb-5">
						<input class="form-check-input custom-control-input"
							   type="checkbox"
							   value="1"
							   id="cb_check_for_updates"
							   name='check_for_updates' <?php echo $config["check_for_updates"] == "1"
							? "checked=\"checked\"" : ""; ?>>
						<label class="form-check-label custom-control-label" for="cb_check_for_updates">
							<?php echo __("CONFIG_UPDATE_CHECK_ENABLE", "USER_CONFIG"); ?>
						</label>
					</div>
				</div>
				<?php if (empty($config["update_channel"]) || $config["update_channel"] !== "docker"): ?>
					<div class="form-group col col-12 col-sm-2">
						<select class="form-control custom-select" id="update_channel" name='update_channel'>
							<option value='stable'
								<?php echo empty($config["update_channel"]) || $config["update_channel"] == "stable" ? "selected=\"selected\"" : ""; ?>
							>
								<?php echo __("CONFIG_UPDATE_CHANNEL_STABLE", "USER_CONFIG"); ?>
							</option>
							<option value='beta'
								<?php echo $config["update_channel"] == "beta" ? "selected=\"selected\"" : ""; ?>
							>
								<?php echo __("CONFIG_UPDATE_CHANNEL_BETA", "USER_CONFIG"); ?>
							</option>
						</select>
					</div>
				<?php endif; ?>
			</div>
			<div class='form-row'>
				<div class="form-group col col-12 col-sm-6">
					<label for="username">
						<?php echo __("CONFIG_USERNAME", "USER_CONFIG"); ?>
					</label>
					<input type="text"
						   class="form-control"
						   id="username"
						   name='username'
						   placeholder="<?php echo __("PLEASE_ENTER"); ?>"
						   value='<?php echo $config["username"]; ?>'
					>
				</div>
				<div class="form-group col col-12 col-sm-6">
					<label for="password">
						<?php echo __("CONFIG_PASSWORD", "USER_CONFIG"); ?>
					</label>
					<input type="password"
						   class="form-control"
						   id="password"
						   placeholder="<?php echo __("PLEASE_ENTER"); ?>"
						   name='password'
						   value=''
						   autocomplete="off"
					>
				</div>
			</div>
			<div class='form-row'>
				<div class="form-group col col-12 col-sm-6">
					<label for="homepage">
						<?php echo __("CONFIG_HOMEPAGE", "USER_CONFIG"); ?>
					</label>
					<select class="form-control custom-select" id="homepage" name='homepage'>
						<option value='start'
							<?php echo $config["homepage"] == "start" ? "selected=\"selected\"" : ""; ?>
						>
							<?php echo __("CONFIG_HOMEPAGE_START", "USER_CONFIG"); ?>
						</option>
						<option value='devices'
							<?php echo $config["homepage"] == "devices" ? "selected=\"selected\"" : ""; ?>
						>
							<?php echo __("CONFIG_HOMEPAGE_DEVICES", "USER_CONFIG"); ?>
						</option>
					
					</select>
				</div>
			</div>
			
			
			<div class="form-row  mt-5">
				<div class="form-group col col-12 col-sm-3">
					<label>&nbsp;</label>
					<div class="form-check custom-control custom-checkbox mb-5">
						<input class="form-check-input custom-control-input"
							   type="checkbox"
							   value="1"
							   id="cb_ota_server_ssl"
							   name='ota_server_ssl' <?php echo $config["ota_server_ssl"] == "1"
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
						   placeholder="<?php echo __("PLEASE_ENTER"); ?>"
						   value='<?php echo $config["ota_server_ip"]; ?>'
					>
					<small id="from_ipHelp" class="form-text text-muted">
						<?php echo __("CONFIG_SERVER_IP_HELP", "USER_CONFIG"); ?>
					</small>
				</div>
				<div class="form-group col col-12 col-sm-3">
					<label for="ota_server_port">
						<?php echo __("CONFIG_SERVER_PORT", "USER_CONFIG"); ?>
					</label>
					<input type="text"
						   class="form-control"
						   id="ota_server_port"
						   name='ota_server_port'
						   placeholder="<?php echo __("PLEASE_ENTER"); ?>"
						   value='<?php echo !empty($config["ota_server_port"]) ? $config["ota_server_port"]
							   : $_SERVER["SERVER_PORT"]; ?>'
					>
					<small id="from_ipHelp" class="form-text text-muted">
						<?php echo __("CONFIG_SERVER_PORT_HELP", "USER_CONFIG"); ?>
					</small>
				</div>
			</div>
			
			
			<div class="form-row">
                <div class="form-group col col-12">
                    <h2><?php echo __("CONFIG_AUTO_FIRMWARE_TITLE", "USER_CONFIG"); ?></h2>
                </div>
				<div class="form-group col col-12 col-sm-6">
					<label for="update_automatic_lang">
						<?php echo __("CONFIG_AUTOMATIC_FW", "USER_CONFIG"); ?>
					</label>
					<select class="form-control custom-select" id="update_automatic_lang" name='update_automatic_lang'>
						<?php if ($config["update_automatic_lang"] == ""): ?>
							<option><?php echo __("PLEASE_SELECT"); ?></option>
						<?php endif; ?>
						
						<?php foreach ($tasmotaReleases as $tr): ?>
							<option value='<?php echo $tr; ?>'
								<?php echo $config["update_automatic_lang"] == $tr ? "selected=\"selected\"" : ""; ?>
							>
								<?php echo $tr ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
                <div class="form-group col col-12 col-sm-3">
                    <label for="auto_update_channel">
                        <?php echo __("CONFIG_AUTO_FIRMWARE_CHANNEL_HELP", "USER_CONFIG"); ?>
                    </label>
                    <select class="form-control custom-select" id="auto_update_channel" name='auto_update_channel'>
                        <?php foreach ($autoFirmwareChannels as $channel): ?>
                            <option value="<?php echo $channel ?>"
                                <?php echo $config["auto_update_channel"] === $channel ? "selected=\"selected\"" : ""; ?>
                            ><?php echo $channel ?>
                            </option>
                        <?php endforeach; ?>
                        </option>
                    </select>
                </div>
				<div class="form-group col col-12 col-sm-3">
					<label>&nbsp;</label>
					<div class="form-check custom-control custom-checkbox mb-5">
						<input class="form-check-input custom-control-input"
							   type="checkbox"
							   value="1"
							   id="use_gzip_package"
							   name='use_gzip_package' <?php echo $config["use_gzip_package"] == "1"
							? "checked=\"checked\"" : ""; ?>>
						<label class="form-check-label custom-control-label" for="use_gzip_package" style='top:3px;'>
							<?php echo __("CONFIG_GZIP_PACKAGE", "USER_CONFIG"); ?>
						</label>
					</div>
				</div>
			</div>
			<div class="form-row mt-5">
				<div class="form-group col col-12 col-sm-6">
					<label for="refreshtime"><?php echo __("CONFIG_REFRESHTIME", "USER_CONFIG"); ?></label>
					<select class="form-control custom-select" id="refreshtime" name='refreshtime'>
						<option value='none' <?php echo $config["refreshtime"] == "none" ? "selected=\selected\"" : ""; ?>>
							<?php echo __("CONFIG_REFRESHTIME_NONE", "USER_CONFIG"); ?>
						</option>
						<option value='1' <?php echo $config["refreshtime"] == "1" ? "selected=\selected\"" : ""; ?> >
							1 <?php echo __("CONFIG_REFRESHTIME_SECOND", "USER_CONFIG"); ?>
						</option>
						<option value='2' <?php echo $config["refreshtime"] == "2" ? "selected=\selected\"" : ""; ?> >
							2 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
						<option value='3' <?php echo $config["refreshtime"] == "3" ? "selected=\selected\"" : ""; ?> >
							3 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
						<option value='4' <?php echo $config["refreshtime"] == "4" ? "selected=\selected\"" : ""; ?> >
							4 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
						<option value='5' <?php echo $config["refreshtime"] == "5" ? "selected=\selected\"" : ""; ?> >
							5 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
						<option value='8' <?php echo $config["refreshtime"] == "8" ? "selected=\selected\"" : ""; ?> >
							8 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
						<option value='10' <?php echo $config["refreshtime"] == "10" ? "selected=\selected\""
							: ""; ?> >
							10 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
						<option value='15' <?php echo $config["refreshtime"] == "15" ? "selected=\selected\""
							: ""; ?> >
							15 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
						<option value='30' <?php echo $config["refreshtime"] == "30" ? "selected=\selected\""
							: ""; ?> >
							30 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
						<option value='60' <?php echo $config["refreshtime"] == "60" ? "selected=\selected\""
							: ""; ?> >
							60 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
						<option value='120' <?php echo $config["refreshtime"] == "120" ? "selected=\selected\""
							: ""; ?> >
							120 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
						<option value='300' <?php echo $config["refreshtime"] == "300" ? "selected=\selected\""
							: ""; ?> >
							300 <?php echo __("CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG"); ?>
						</option>
					</select>
				</div>
				<div class="form-group col col-12 col-sm-6">
					<label for="nightmode">
						<?php echo __("CONFIG_NIGHTMODE", "USER_CONFIG"); ?>
					</label>
					<select class="form-control custom-select" id="nightmode" name='nightmode'>
						<option value='disable' <?php echo $config["nightmode"] == "disable" ? "selected=\"selected\""
							: ""; ?>><?php echo __(
								"CONFIG_NIGHTMODE_DISABLE",
								"USER_CONFIG"
							); ?>
						</option>
						<option value='always' <?php echo $config["nightmode"] == "always" ? "selected=\"selected\""
							: ""; ?> >
							<?php echo __("CONFIG_NIGHTMODE_ALWAYS", "USER_CONFIG"); ?>
						</option>
						<option value='auto' <?php echo $config["nightmode"] == "auto" ? "selected=\"selected\""
							: ""; ?> >
							<?php echo __("CONFIG_NIGHTMODE_AUTO", "USER_CONFIG"); ?>
						</option>
					</select>
				</div>
			</div>
			
			<div class="form-row">
				<div class="form-group col col-12 col-sm-4">
					<div class="form-check custom-control custom-checkbox mb-5">
						<input class="form-check-input custom-control-input"
							   type="checkbox"
							   value="1"
							   id="cb_show_search"
							   name='show_search' <?php echo $config["show_search"] == "1"
							? "checked=\"checked\"" : ""; ?>>
						<label class="form-check-label custom-control-label" for="cb_show_search">
							<?php echo __("CONFIG_SHOW_SEARCH", "USER_CONFIG"); ?>
						</label>
					</div>
				</div>
			</div>

            <div class="form-row">
                <div class="form-group col col-12">
                    <h2><?php echo __("CONFIG_UPDATE_CHECK", "USER_CONFIG"); ?></h2>
                </div>
                <div class="form-check custom-control custom-checkbox mb-5"">
                    <input class="form-check-input custom-control-input"
                           type="checkbox"
                           value="1"
                           id="cb_update_fe_check"
                           name='update_fe_check' <?php echo $config["update_fe_check"] == "1"
                        ? "checked=\"checked\"" : ""; ?>>
                    <label class="form-check-label custom-control-label" for="cb_update_fe_check">
                        <?php echo __("CONFIG_UPDATE_FE_CHECK", "USER_CONFIG"); ?>
                    </label>
                    <p class="small">
                        <?php echo __("CONFIG_UPDATE_FE_CHECK_HELP", "USER_CONFIG"); ?>
                    </p>
                </div>
                <div class="form-check custom-control custom-checkbox mb-5"">
                    <input class="form-check-input custom-control-input"
                           type="checkbox"
                           value="1"
                           id="cb_update_be_check"
                           name='update_be_check' <?php echo $config["update_be_check"] == "1"
                        ? "checked=\"checked\"" : ""; ?>>
                    <label class="form-check-label custom-control-label" for="cb_update_be_check">
                        <?php echo __("CONFIG_UPDATE_BE_CHECK", "USER_CONFIG"); ?>
                    </label>
                    <p class="small">
                        <?php echo __("CONFIG_UPDATE_BE_CHECK_HELP", "USER_CONFIG"); ?>
                    </p>
                </div>
            </div>
			
			<div class="form-row  mt-5">
				<div class='d-none d-sm-inline-flex col flex-column'></div>
				<div class="col col-12 col-sm-6 text-sm-right">
					<div class="text-right">
						<button type='submit' class='btn btn-primary ' name='save' value='submit'>
							<?php echo __("BTN_SAVE_USER_CONFIG", "USER_CONFIG"); ?>
						</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
