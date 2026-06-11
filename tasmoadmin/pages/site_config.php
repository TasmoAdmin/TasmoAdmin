<?php

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\BrowserKit\HttpBrowser;
use TasmoAdmin\Helper\CacheCleanupHelper;
use TasmoAdmin\Helper\GuzzleFactory;
use TasmoAdmin\Helper\HtmlAttributeHelper;
use TasmoAdmin\Helper\LoginHelper;
use TasmoAdmin\Helper\TasmotaHelper;
use TasmoAdmin\Helper\TasmotaOtaScraper;

$msg = false;
$settings = [];

if (isset($_POST['clean_temp_cache'])) {
    CacheCleanupHelper::cleanTargets(_TMPDIR_, ['sessions', 'i18n']);
    $msg = __('MSG_CACHE_CLEARED', 'USER_CONFIG');
} elseif (isset($_POST['save'])) {
    $settings = $_POST;
    unset($settings['save']);

    if (!isset($settings['login'])) {
        $settings['login'] = '0';
    }

    if (!isset($settings['check_for_updates'])) {
        $settings['check_for_updates'] = '0';
    }

    if (!isset($settings['force_upgrade'])) {
        $settings['force_upgrade'] = '0';
    }

    if (!isset($settings['show_search'])) {
        $settings['show_search'] = '0';
    }

    if (!isset($settings['confirm_device_toggles'])) {
        $settings['confirm_device_toggles'] = '0';
    }

    if (!isset($settings['nightmode']) || 'auto' !== $settings['nightmode']) {
        $settings['nightmode'] = 'disable';
    }

    if (!isset($settings['update_fe_check'])) {
        $settings['update_fe_check'] = '0';
    }

    if (!isset($settings['update_be_check'])) {
        $settings['update_be_check'] = '0';
    }

    if (!isset($settings['update_newer_only'])) {
        $settings['update_newer_only'] = '0';
    }

    if (empty($settings['password'])) {
        unset($settings['password']);
    } else {
        $settings['password'] = LoginHelper::hashPassword($settings['password']);
    }

    if ('0' === $settings['login']) {
        unset($settings['password'], $settings['username']);
    }

    $Config->writeAll($settings);
    $msg = __('MSG_USER_CONFIG_SAVED', 'USER_CONFIG');
}

$config = array_merge($Config->readAll(), $settings);
unset($config['password']);

$tasmotaHelper = new TasmotaHelper(
    new GithubFlavoredMarkdownConverter(),
    GuzzleFactory::getClient($Config),
    new TasmotaOtaScraper($Config->read('auto_update_channel'), new HttpBrowser()),
    $Config->read('auto_update_channel')
);
$tasmotaEsp8266Releases = $tasmotaHelper->getEsp8266Releases();
$tasmotaEsp32Releases = $tasmotaHelper->getEsp32Releases();

$autoFirmwareChannels = ['stable', 'dev'];

?>


<div class='row justify-content-sm-center'>
	<div class='col col-12 col-md-9 col-xl-8 site-config-page'>
		<div class='row'>
			<div class='col col-12'>
				<h2 class='text-start text-sm-center settings-page-title'>
					<?php echo $title; ?>
				</h2>
			</div>
		</div>

		<?php if (isset($msg) && '' != $msg) { ?>
			<div class="alert alert-success alert-dismissible fade show settings-page-alert" data-bs-dismiss="alert" role="alert">
				<?php echo $msg; ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php } ?>
		<div class="settings-page">
		<form name='web_config' method='post' class="settings-form">
			<div class="settings-section">
			<div class="row g-4 settings-row">
				<div class="col col-12 col-md-6">
					<div class="settings-option">
					<div class="form-check mb-0">
						<input class="form-check-input"
							   autofocus="autofocus"
							   type="checkbox"
							   value="1"
							   id="cb_login"
							   name='login' <?php echo '1' == $config['login'] ? 'checked="checked"' : ''; ?>>
						<label class="form-check-label" for="cb_login">
							<?php echo __('CONFIG_LOGIN_ENABLE', 'USER_CONFIG'); ?>
						</label>
					</div>
					</div>
				</div>
				<div class="col col-12 col-md-6">
					<div class="settings-option">
					<div class="form-check mb-0">
						<input class="form-check-input"
							   type="checkbox"
							   value="1"
							   id="cb_check_for_updates"
							   name='check_for_updates' <?php echo '1' == $config['check_for_updates']
                            ? 'checked="checked"' : ''; ?>>
						<label class="form-check-label" for="cb_check_for_updates">
							<?php echo __('CONFIG_UPDATE_CHECK_ENABLE', 'USER_CONFIG'); ?>
						</label>
					</div>
					</div>
				</div>
				<?php if (empty($config['update_channel']) || 'docker' !== $config['update_channel']) { ?>
					<div class="col col-12 col-md-6">
						<label class="settings-label-spacer" for="update_channel" aria-hidden="true">&nbsp;</label>
						<select class="form-control form-select" id="update_channel" name='update_channel'>
							<option value='stable'
								<?php echo empty($config['update_channel']) || 'stable' == $config['update_channel'] ? 'selected="selected"' : ''; ?>
							>
								<?php echo __('CONFIG_UPDATE_CHANNEL_STABLE', 'USER_CONFIG'); ?>
							</option>
							<option value='beta'
								<?php echo 'beta' == $config['update_channel'] ? 'selected="selected"' : ''; ?>
							>
								<?php echo __('CONFIG_UPDATE_CHANNEL_BETA', 'USER_CONFIG'); ?>
							</option>
						</select>
					</div>
				<?php } ?>
			</div>
			<div class='row g-4 settings-row'>
				<div class="col col-12 col-sm-6">
					<label for="username">
						<?php echo __('CONFIG_USERNAME', 'USER_CONFIG'); ?>
					</label>
					<input type="text"
						   class="form-control"
						   id="username"
						   name='username'
						   placeholder="<?php echo __('PLEASE_ENTER'); ?>"
						   value='<?php echo $config['username']; ?>'
					>
				</div>
				<div class="col col-12 col-sm-6">
					<label for="password">
						<?php echo __('CONFIG_PASSWORD', 'USER_CONFIG'); ?>
					</label>
					<input type="password"
						   class="form-control"
						   id="password"
						   placeholder="<?php echo __('PLEASE_ENTER'); ?>"
						   name='password'
						   value=''
						autocomplete="off"
					>
				</div>
			</div>
			<div class='row g-4 settings-row'>
				<div class="col col-12 col-sm-6">
					<label for="homepage">
						<?php echo __('CONFIG_HOMEPAGE', 'USER_CONFIG'); ?>
					</label>
					<select class="form-control form-select" id="homepage" name='homepage'>
						<option value='start'
							<?php echo 'start' == $config['homepage'] ? 'selected="selected"' : ''; ?>
						>
							<?php echo __('CONFIG_HOMEPAGE_START', 'USER_CONFIG'); ?>
						</option>
						<option value='devices'
							<?php echo 'devices' == $config['homepage'] ? 'selected="selected"' : ''; ?>
						>
							<?php echo __('CONFIG_HOMEPAGE_DEVICES', 'USER_CONFIG'); ?>
						</option>

					</select>
				</div>
			</div>
			</div>


			<div class="settings-section">
			<div class="row g-4 settings-row">
				<div class="col col-12 col-sm-9">
					<label for="ota_server_ip">
						<?php echo __('CONFIG_SERVER_IP', 'USER_CONFIG'); ?>
					</label>
					<input type="text"
						   class="form-control"
						   id="ota_server_ip"
						   name='ota_server_ip'
						   placeholder="<?php echo __('PLEASE_ENTER'); ?>"
						   value='<?php echo $config['ota_server_ip']; ?>'
					>
					<small id="from_ipHelp" class="text-body-secondary">
						<?php echo __('CONFIG_SERVER_IP_HELP', 'USER_CONFIG'); ?>
					</small>
				</div>
				<div class="col col-12 col-sm-3">
					<label for="ota_server_port">
						<?php echo __('CONFIG_SERVER_PORT', 'USER_CONFIG'); ?>
					</label>
					<input type="text"
						   class="form-control"
						   id="ota_server_port"
						   name='ota_server_port'
						   placeholder="<?php echo __('PLEASE_ENTER'); ?>"
						   value='<?php echo !empty($config['ota_server_port']) ? $config['ota_server_port']
                               : $_SERVER['SERVER_PORT']; ?>'
					>
					<small id="from_ipHelp" class="text-body-secondary">
						<?php echo __('CONFIG_SERVER_PORT_HELP', 'USER_CONFIG'); ?>
					</small>
				</div>
			</div>
			</div>


			<div class="settings-section">
				<div class="row g-4 settings-row">
	                <div class="col col-12">
	                    <h2 class="settings-section-title"><?php echo __('CONFIG_AUTO_FIRMWARE_TITLE', 'USER_CONFIG'); ?></h2>
	                </div>
					<div class="col col-12 col-md-6">
						<label for="update_automatic_lang">
							<?php echo __('CONFIG_AUTOMATIC_FW_ESP8266', 'USER_CONFIG'); ?>
						</label>
					<select class="form-control form-select" id="update_automatic_lang" name='update_automatic_lang'>
						<?php if ('' == $config['update_automatic_lang']) { ?>
							<option><?php echo __('PLEASE_SELECT'); ?></option>
						<?php } ?>

						<?php foreach ($tasmotaEsp8266Releases as $tr) { ?>
							<option value='<?php echo $tr; ?>'
								<?php echo $config['update_automatic_lang'] == $tr ? 'selected="selected"' : ''; ?>
							>
								<?php echo $tr; ?>
							</option>
						<?php } ?>
					</select>
				</div>
					<div class="col col-12 col-md-6">
						<label for="update_automatic_lang_esp32">
							<?php echo __('CONFIG_AUTOMATIC_FW_ESP32', 'USER_CONFIG'); ?>
						</label>
					<select class="form-control form-select" id="update_automatic_lang_esp32" name='update_automatic_lang_esp32'>
						<?php if ('' == $config['update_automatic_lang_esp32']) { ?>
							<option><?php echo __('PLEASE_SELECT'); ?></option>
						<?php } ?>

						<?php foreach ($tasmotaEsp32Releases as $tr) { ?>
							<option value='<?php echo $tr; ?>'
								<?php echo $config['update_automatic_lang_esp32'] == $tr ? 'selected="selected"' : ''; ?>
							>
								<?php echo $tr; ?>
							</option>
						<?php } ?>
					</select>
				</div>
					<div class="col col-12 col-md-6">
	                    <label for="auto_update_channel">
	                        <?php echo __('CONFIG_AUTO_FIRMWARE_CHANNEL_HELP', 'USER_CONFIG'); ?>
	                    </label>
					<select class="form-control form-select" id="auto_update_channel" name='auto_update_channel'>
                        <?php foreach ($autoFirmwareChannels as $channel) { ?>
                            <option value="<?php echo $channel; ?>"
                                <?php echo $config['auto_update_channel'] === $channel ? 'selected="selected"' : ''; ?>
                            ><?php echo $channel; ?>
                            </option>
                        <?php } ?>
						</select>
	                </div>
				</div>
				<div class="row g-4 settings-row">
	                <div class="col col-12 col-md-6">
	                    <div class="settings-option">
	                    <div class="form-check mb-0">
	                        <input class="form-check-input"
	                               type="checkbox"
	                               value="1"
                               id="force_upgrade"
                               name='force_upgrade' <?php echo '1' == $config['force_upgrade']
                            ? 'checked="checked"' : ''; ?>>
	                        <label class="form-check-label" for="force_upgrade">
	                            <?php echo __('CONFIG_FORCE_UPGRADE', 'USER_CONFIG'); ?>
	                        </label>
	                        <small class="d-block text-body-secondary mt-2">
	                            <?php echo __('CONFIG_FORCE_UPGRADE_HELP', 'USER_CONFIG'); ?>
	                        </small>
	                    </div>
	                    </div>
	                </div>
	                <div class="col col-12 col-md-6">
	                    <div class="settings-option">
	                    <div class="form-check mb-0">
	                        <input class="form-check-input"
	                               type="checkbox"
	                               value="1"
                               id="update_newer_only"
                               name='update_newer_only' <?php echo '1' == $config['update_newer_only']
                            ? 'checked="checked"' : ''; ?>>
	                        <label class="form-check-label" for="update_newer_only">
	                            <?php echo __('CONFIG_UPDATE_NEWER_ONLY', 'USER_CONFIG'); ?>
	                        </label>
	                        <small class="d-block text-body-secondary mt-2">
	                            <?php echo __('CONFIG_UPDATE_NEWER_ONLY_HELP', 'USER_CONFIG'); ?>
	                        </small>
	                    </div>
	                    </div>
                </div>
			</div>
			</div>
			<div class="settings-section">
			<div class="row g-4 settings-row">
				<div class="col col-12 col-sm-6">
					<label for="refreshtime"><?php echo __('CONFIG_REFRESHTIME', 'USER_CONFIG'); ?></label>
					<select class="form-control form-select" id="refreshtime" name='refreshtime'>
						<option value='none' <?php echo HtmlAttributeHelper::selected('none' == $config['refreshtime']); ?>>
							<?php echo __('CONFIG_REFRESHTIME_NONE', 'USER_CONFIG'); ?>
						</option>
						<option value='1' <?php echo HtmlAttributeHelper::selected('1' == $config['refreshtime']); ?> >
							1 <?php echo __('CONFIG_REFRESHTIME_SECOND', 'USER_CONFIG'); ?>
						</option>
						<option value='2' <?php echo HtmlAttributeHelper::selected('2' == $config['refreshtime']); ?> >
							2 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
						<option value='3' <?php echo HtmlAttributeHelper::selected('3' == $config['refreshtime']); ?> >
							3 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
						<option value='4' <?php echo HtmlAttributeHelper::selected('4' == $config['refreshtime']); ?> >
							4 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
						<option value='5' <?php echo HtmlAttributeHelper::selected('5' == $config['refreshtime']); ?> >
							5 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
						<option value='8' <?php echo HtmlAttributeHelper::selected('8' == $config['refreshtime']); ?> >
							8 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
						<option value='10' <?php echo HtmlAttributeHelper::selected('10' == $config['refreshtime']); ?> >
							10 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
						<option value='15' <?php echo HtmlAttributeHelper::selected('15' == $config['refreshtime']); ?> >
							15 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
						<option value='30' <?php echo HtmlAttributeHelper::selected('30' == $config['refreshtime']); ?> >
							30 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
						<option value='60' <?php echo HtmlAttributeHelper::selected('60' == $config['refreshtime']); ?> >
							60 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
						<option value='120' <?php echo HtmlAttributeHelper::selected('120' == $config['refreshtime']); ?> >
							120 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
						<option value='300' <?php echo HtmlAttributeHelper::selected('300' == $config['refreshtime']); ?> >
							300 <?php echo __('CONFIG_REFRESHTIME_SECONDS', 'USER_CONFIG'); ?>
						</option>
					</select>
				</div>
				<div class="col col-12 col-sm-6">
					<label class="settings-label-spacer" for="cb_nightmode" aria-hidden="true">&nbsp;</label>
					<div class="settings-option settings-option-toggle">
						<div class="settings-option-copy">
							<span class="settings-option-title">
								<?php echo __('CONFIG_NIGHTMODE', 'USER_CONFIG'); ?>
							</span>
							<small class="d-block text-body-secondary">
								<?php echo __('CONFIG_NIGHTMODE_AUTO', 'USER_CONFIG'); ?> /
								<?php echo __('CONFIG_NIGHTMODE_DISABLE', 'USER_CONFIG'); ?>
							</small>
						</div>
						<label class="form-switch" for="cb_nightmode">
							<input type="checkbox"
								   value="auto"
								   id="cb_nightmode"
								   name='nightmode' <?php echo 'disable' !== $config['nightmode']
                                ? 'checked="checked"' : ''; ?>>
							<i aria-hidden="true"></i>
						</label>
					</div>
				</div>
			</div>

			<div class="row g-4 settings-row">
				<div class="col col-12 col-md-6">
					<div class="settings-option">
					<div class="form-check mb-0">
						<input class="form-check-input"
							   type="checkbox"
							   value="1"
							   id="cb_show_search"
							   name='show_search' <?php echo '1' == $config['show_search']
                            ? 'checked="checked"' : ''; ?>>
						<label class="form-check-label" for="cb_show_search">
							<?php echo __('CONFIG_SHOW_SEARCH', 'USER_CONFIG'); ?>
						</label>
					</div>
					</div>
				</div>
				<div class="col col-12 col-md-6">
					<div class="settings-option">
					<div class="form-check mb-0">
						<input class="form-check-input"
							   type="checkbox"
							   value="1"
							   id="cb_confirm_device_toggles"
							   name='confirm_device_toggles' <?php echo '1' == $config['confirm_device_toggles']
                            ? 'checked="checked"' : ''; ?>>
						<label class="form-check-label" for="cb_confirm_device_toggles">
							<?php echo __('CONFIG_CONFIRM_DEVICE_TOGGLES', 'USER_CONFIG'); ?>
						</label>
					</div>
					</div>
				</div>
			</div>
			</div>

            <div class="settings-section">
            <div class="row g-4 settings-row">
                <div class="col col-12">
                    <h2 class="settings-section-title"><?php echo __('CONFIG_UPDATE_CHECK', 'USER_CONFIG'); ?></h2>
                </div>
                <div class="col col-12 col-sm-6">
                    <div class="settings-option">
                    <div class="form-check mb-0">
                        <input class="form-check-input"
                               type="checkbox"
                               value="1"
                               id="cb_update_fe_check"
                               name='update_fe_check' <?php echo '1' == $config['update_fe_check']
                            ? 'checked="checked"' : ''; ?>>
                        <label class="form-check-label" for="cb_update_fe_check">
                            <?php echo __('CONFIG_UPDATE_FE_CHECK', 'USER_CONFIG'); ?>
                        </label>
                        <small class="d-block text-body-secondary mt-2">
                            <?php echo __('CONFIG_UPDATE_FE_CHECK_HELP', 'USER_CONFIG'); ?>
                        </small>
                    </div>
                    </div>
                </div>
                <div class="col col-12 col-sm-6">
                    <div class="settings-option">
                    <div class="form-check mb-0">
                        <input class="form-check-input"
                               type="checkbox"
                               value="1"
                               id="cb_update_be_check"
                               name='update_be_check' <?php echo '1' == $config['update_be_check']
                            ? 'checked="checked"' : ''; ?>>
                        <label class="form-check-label" for="cb_update_be_check">
                            <?php echo __('CONFIG_UPDATE_BE_CHECK', 'USER_CONFIG'); ?>
                        </label>
                        <small class="d-block text-body-secondary mt-2">
                            <?php echo __('CONFIG_UPDATE_BE_CHECK_HELP', 'USER_CONFIG'); ?>
                        </small>
                    </div>
                    </div>
                </div>
            </div>
            </div>


            <div class="settings-section">
            <div class="row g-4 settings-row">
                <div class="col col-12">
                    <h2 class="settings-section-title"><?php echo __('CONFIG_ADVANCED', 'USER_CONFIG'); ?></h2>
                </div>
                <div class="col col-12 col-md-6">
                    <label for="connect_timeout">
                        <?php echo __('CONFIG_CONNECT_TIMEOUT', 'USER_CONFIG'); ?>
                    </label>
                    <input type="number"
                           class="form-control"
                           id="connect_timeout"
                           name='connect_timeout'
                           placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                           value='<?php echo $config['connect_timeout']; ?>'
                    >
                    					<small id="connect_timeoutHelp" class="text-body-secondary">
						<?php echo __('CONFIG_CONNECT_TIMEOUT_HELP', 'USER_CONFIG'); ?>
					</small>
                </div>
                <div class="col col-12 col-md-6">
                    <label for="timeout">
                        <?php echo __('CONFIG_TIMEOUT', 'USER_CONFIG'); ?>
                    </label>
                    <input type="number"
                           class="form-control"
                           id="timeout"
                           name='timeout'
                           placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                           value='<?php echo $config['timeout']; ?>'
                    >
                    					<small id="timeoutHelp" class="text-body-secondary">
						<?php echo __('CONFIG_TIMEOUT_HELP', 'USER_CONFIG'); ?>
					</small>
                </div>
                <div class="col col-12 col-md-6">
                    <label for="request_concurrency">
                        <?php echo __('CONFIG_REQUEST_CONCURRENCY', 'USER_CONFIG'); ?>
                    </label>
                    <input type="number"
                           class="form-control"
                           id="request_concurrency"
                           name='request_concurrency'
                           placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                           value='<?php echo $config['request_concurrency']; ?>'
                    >
                    					<small id="requestConcurrencyHelp" class="text-body-secondary">
						<?php echo __('CONFIG_REQUEST_CONCURRENCY_HELP', 'USER_CONFIG'); ?>
					</small>
                </div>
            </div>
            </div>

			<div class="row settings-actions-row">
				<div class="col col-12">
					<div class="settings-actions">
						<button type='submit' class='btn btn-primary' name='save' value='submit'>
							<?php echo __('BTN_SAVE_USER_CONFIG', 'USER_CONFIG'); ?>
						</button>
					</div>
				</div>
			</div>
		</form>
		</div>
        <?php include __DIR__.'/elements/cache_cleanup_panel.php'; ?>
	</div>
</div>
