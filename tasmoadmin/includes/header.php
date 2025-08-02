<!doctype html>
<?php

use TasmoAdmin\Helper\SupportedLanguageHelper;
use TasmoAdmin\Helper\UrlHelper;
use TasmoAdmin\Helper\ViewHelper;

$urlHelper = $container->get(UrlHelper::class);

?>
<html lang="<?php echo $lang; ?>" xmlns="http://www.w3.org/1999/html">
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="Cache-control" content="no-cache, must-revalidate"/>
		<meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="robots" content="noindex">
		<!--
		Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess
		-->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>

        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo _RESOURCESURL_; ?>img/favicons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo _RESOURCESURL_; ?>img/favicons/favicon-16x16.png">
        <link rel="manifest" href="<?php echo _RESOURCESURL_; ?>img/favicons/site.webmanifest">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="<?php echo _RESOURCESURL_; ?>img/favicons/ms-icon-144x144.png">
		<meta name="theme-color" content="#ffffff">


		<title><?php echo isset($title) ? $title.' - ' : ''; ?>TasmoAdmin</title>
		<script type="application/javascript">
            const config = {
                base_url: '<?php echo _BASEURL_; ?>',
                resource_url: '<?php echo _RESOURCESURL_; ?>',
                nightmodeconfig: '<?php echo $Config->read('nightmode'); ?>',
                update_fe_check: <?php echo $Config->read('update_fe_check'); ?> === 1,
                force_upgrade: <?php echo $Config->read('force_upgrade'); ?> === 1,
                update_newer_only: <?php echo $Config->read('update_newer_only'); ?> === 1,
                request_concurrency: <?php echo $Config->getRequestConcurrency(); ?>,
            };
		</script>
		<script src="<?php echo $urlHelper->js('compiled/vendor'); ?>"></script>
		<script src="<?php echo $urlHelper->js('compiled/Sonoff'); ?>"></script>
		<script src="<?php echo $urlHelper->js('compiled/app'); ?>"></script>


		<link href="<?php echo $urlHelper->style('compiled/all'); ?>" rel="stylesheet">

		<?php if (@file_exists(_RESOURCESDIR_.'css/custom.css')) { ?>
			<link href="<?php echo $urlHelper->style('custom'); ?>" rel="stylesheet">
		<?php } ?>

	</head>
	<body class='<?php echo $container->get(ViewHelper::class)->getNightMode(date('H')); ?>'>
		<header>
			<nav class="navbar navbar-expand-sm navbar-dark bg-dark fixed-top py-1">
				<div class="container-fluid">
					<?php // var_dump( $page );?>
					<a class="navbar-brand py-0 logo" href='<?php echo _BASEURL_.$Config->read('homepage'); ?>'>
						<img src='<?php echo _RESOURCESURL_; ?>img/logo.svg' height='50px'/>
					</a>
					<button class="navbar-toggler"
							type="button"
							data-bs-toggle="collapse"
							data-bs-target="#navbarSupportedContent"
							aria-controls="navbarSupportedContent"
							aria-expanded="false"
							aria-label="Toggle navigation"
					>
						<span class="navbar-toggler-icon"></span>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav me-auto">
						<?php if ($loggedin) { ?>
							<li class="nav-item <?php echo 'start' == $page ? 'active' : ''; ?>">
								<a class="nav-link" href="<?php echo _BASEURL_; ?>start"><?php echo __(
								    'STARTPAGE',
								    'NAVI'
								); ?></a>
							</li>
						<?php } ?>

						<?php if ($loggedin) { ?>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle <?php echo in_array(
								    $page,
								    [
								        'upload_form',
								        'upload',
								        'device_update',
								        'devices',
								        'device_config',
								        'device_action',
								        'devices_autoscan',
								        'backup',
								    ]
								) ? 'active' : ''; ?>"
								   href="#"
								   id="devicesDropdown"
								   data-bs-toggle="dropdown"
								   aria-haspopup="false"
								   aria-expanded="false"
								>
									<?php echo __('DEVICES', 'NAVI'); ?>
								</a>
								<div class="dropdown-menu bg-dark" aria-labelledby="devicesDropdown">
									<a class="dropdown-item nav-link <?php echo 'devices' == $page ? 'active' : ''; ?>"
									   href="<?php echo _BASEURL_; ?>devices"
									>
										<?php echo __('DEVICE_LIST', 'NAVI'); ?>
									</a>
									<a href='<?php echo _BASEURL_; ?>upload_form'
									   class='dropdown-item nav-link <?php echo in_array($page, ['upload_form', 'upload', 'device_update']) ? 'active' : ''; ?>'
									>
										<?php echo __('UPDATE', 'NAVI'); ?>
									</a>

                                    <a class="dropdown-item nav-link <?php echo 'backup' == $page ? 'active' : ''; ?>"
                                       href="<?php echo _BASEURL_; ?>backup"
                                    ><?php echo __('DEVICES_BACKUP', 'NAVI'); ?>
                                    </a>

									<a class="dropdown-item nav-link <?php echo 'devices_autoscan' == $page ? 'active' : ''; ?>"
									   href="<?php echo _BASEURL_; ?>devices_autoscan"
									>
										<?php echo __('DEVICES_AUTOSCAN', 'NAVI'); ?>
									</a>
								</div>
							</li>
						<?php } ?>
						<?php if ($loggedin) { ?>
							<li class="nav-item">
								<a class="nav-link <?php echo 'site_config' == $page ? 'active' : ''; ?>"
								   href='<?php echo _BASEURL_; ?>site_config'
								>
									<?php echo __('SETTINGS', 'NAVI'); ?>
								</a>
							</li>
						<?php } ?>

						<?php if ($loggedin && !$docker) { ?>
							<li class="nav-item">
								<a class="nav-link <?php echo 'selfupdate' == $page ? 'active' : ''; ?>"
								   href='<?php echo _BASEURL_; ?>selfupdate'
								>
									<?php echo __('SELFUPDATE', 'NAVI'); ?>
								</a>
							</li>
						<?php } ?>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle <?php echo in_array(
							    $page,
							    [
							        'chat',
							        $docker ? 'selfupdate' : null,
							    ]
							) ? 'active' : ''; ?>"
							   href="#"
							   id="helpDropdown"
							   data-bs-toggle="dropdown"
							   aria-haspopup="false"
							   aria-expanded="false"
							>
								<i class='fa fa-question-circle'></i>
							</a>
							<div class="dropdown-menu bg-dark" aria-labelledby="helpDropdown">
								<?php if ($docker) { ?>
									<a class="dropdown-item nav-link <?php echo 'selfupdate' == $page ? 'active'
							: ''; ?>"
									   href="<?php echo _BASEURL_; ?>selfupdate"
									>
										<?php echo __('HELP_CHANGELOG', 'NAVI'); ?>
									</a>
								<?php } ?>
								<a href='https://tasmota.github.io/docs/' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
								       $page,
								       []
								   ) ? 'active' : ''; ?>'
								>
									<?php echo __('HELP_TASDOCS', 'NAVI'); ?>
								</a>
								<a href='https://tasmota.github.io/docs/Commands/' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
								       $page,
								       []
								   ) ? 'active' : ''; ?>'
								>
									<?php echo __('HELP_TASCOMMANDS', 'NAVI'); ?>
								</a>
								<a href='https://templates.blakadder.com/' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
								       $page,
								       []
								   ) ? 'active' : ''; ?>'
								>
									<?php echo __('HELP_TASTEMPLATES', 'NAVI'); ?>
								</a>
								<a href='https://tasmota.github.io/docs/Troubleshooting/' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
								       $page,
								       []
								   ) ? 'active' : ''; ?>'
								>
									<?php echo __('HELP_TASTROUBLESHOOTING', 'NAVI'); ?>
								</a>

								<a href='https://discord.gg/Ks2Kzd4' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
								       $page,
								       []
								   ) ? 'active' : ''; ?>'
								>
									<?php echo __('HELP_DISCORD_TASMOTA', 'NAVI'); ?>
								</a>
								<a href='https://discord.gg/Q6zPX3C' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
								       $page,
								       []
								   ) ? 'active' : ''; ?>'
								>
									<?php echo __('HELP_DISCORD_TASMOADMIN', 'NAVI'); ?>
								</a>

							</div>
						</li>
					</ul>
					<div class='my-2 my-sm-0 language-switch-holder ms-auto'>
						<select name='language-switch' id='language-switch' class='form-select'>
							<?php foreach (SupportedLanguageHelper::getSupportedLanguages() as $l => $name) { ?>
								<option value='<?php echo $l; ?>'
									<?php echo $lang === $l ? 'selected="selected"' : ''; ?>
								>
									<?php echo $name; ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<?php if ($loggedin) { ?>
						<?php if ('1' == $Config->read('login')) { ?>
							<div class="my-2 my-lg-0 ms-3">
								<a class="error"
								   href='<?php echo _BASEURL_; ?>logout'
								   title='<?php echo __('LOGOUT', 'NAVI'); ?>'
								>
									<i class='fas fa-sign-out-alt fa-lg'></i>
									<span class='d-inline d-sm-none'>
										<?php echo __('LOGOUT', 'NAVI'); ?>
									</span>
								</a>
							</div>
						<?php } ?>
					<?php } ?>
				</div>
				</div>
			</nav>
		</header>
        <main class='container-fluid' id='content' data-refreshtime='<?php echo $Config->read('refreshtime'); ?>'>
            <div id='content-holder'>
