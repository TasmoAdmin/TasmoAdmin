<!doctype html>
<?php
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
		
		<link rel="apple-touch-icon"
			  sizes="57x57"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-57x57.png"
		>
		<link rel="apple-touch-icon"
			  sizes="60x60"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-60x60.png"
		>
		<link rel="apple-touch-icon"
			  sizes="72x72"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-72x72.png"
		>
		<link rel="apple-touch-icon"
			  sizes="76x76"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-76x76.png"
		>
		<link rel="apple-touch-icon" sizes="114x114"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-114x114.png"
		>
		<link rel="apple-touch-icon" sizes="120x120"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-120x120.png"
		>
		<link rel="apple-touch-icon" sizes="144x144"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-144x144.png"
		>
		<link rel="apple-touch-icon" sizes="152x152"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-152x152.png"
		>
		<link rel="apple-touch-icon" sizes="180x180"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-180x180.png"
		>
		<link rel="icon" type="image/png" sizes="192x192"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/android-icon-192x192.png"
		>
		<link rel="icon"
			  type="image/png"
			  sizes="32x32"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/favicon-32x32.png"
		>
		<link rel="icon"
			  type="image/png"
			  sizes="96x96"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/favicon-96x96.png"
		>
		<link rel="icon"
			  type="image/png"
			  sizes="16x16"
			  href="<?php echo _RESOURCESURL_; ?>img/favicons/favicon-16x16.png"
		>
		<link rel="manifest" href="<?php echo _RESOURCESURL_; ?>img/favicons/manifest.json">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="<?php echo _RESOURCESURL_; ?>img/favicons/ms-icon-144x144.png">
		<meta name="theme-color" content="#ffffff">
		
		
		<title><?php echo isset($title) ? $title . " - " : ""; ?>TasmoAdmin</title>
		<script type="application/javascript">
            const config = {
                base_url: window.location.origin + '<?php echo _BASEURL_; ?>',
                resource_url: '<?php echo _RESOURCESURL_; ?>',
                nightmodeconfig: '<?php echo $Config->read("nightmode"); ?>',
                update_fe_check: <?php echo $Config->read("update_fe_check"); ?> === 1,
                force_upgrade: <?php echo $Config->read("force_upgrade"); ?> === 1,
            };
		</script>
		<script src="<?php echo $urlHelper->js("jquery", "node_modules/jquery/dist/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("bootstrap.bundle", "node_modules/bootstrap/dist/js/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("jquery.i18n", "node_modules/@wikimedia/jquery.i18n/src/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("jquery.i18n.messagestore", "node_modules/@wikimedia/jquery.i18n/src/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("jquery.i18n.fallbacks", "node_modules/@wikimedia/jquery.i18n/src/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("jquery.i18n.parser", "node_modules/@wikimedia/jquery.i18n/src/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("jquery.i18n.emitter", "node_modules/@wikimedia/jquery.i18n/src/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("jquery.i18n.language", "node_modules/@wikimedia/jquery.i18n/src/"); ?>"></script>
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/bs" ); ?><!--"></script>-->
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/dsb" ); ?><!--"></script>-->
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/fi" ); ?><!--"></script>-->
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/ga" ); ?><!--"></script>-->
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/he" ); ?><!--"></script>-->
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/hsb" ); ?><!--"></script>-->
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/hu" ); ?><!--"></script>-->
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/hy" ); ?><!--"></script>-->
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/la" ); ?><!--"></script>-->
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/ml" ); ?><!--"></script>-->
		<!--	<script src="--><?php //echo $urlHelper->js( "i18n/languages/os" ); ?><!--"></script>-->
		<script src="<?php echo $urlHelper->js("languages/ru", "node_modules/@wikimedia/jquery.i18n/src/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("languages/sl", "node_modules/@wikimedia/jquery.i18n/src/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("languages/uk", "node_modules/@wikimedia/jquery.i18n/src/"); ?>"></script>
		
		<script src="<?php echo $urlHelper->js("js.cookie", "node_modules/js-cookie/dist/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("tablesaw.jquery", "node_modules/tablesaw/dist/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("tablesaw-init", "node_modules/tablesaw/dist/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("bootstrap-waitingfor", "node_modules/bootstrap-waitingfor/build/"); ?>"></script>
		<script src="<?php echo $urlHelper->js("jquery.doubleScroll", "node_modules/jqdoublescroll/"); ?>"></script>

		<script src="<?php echo $urlHelper->js("Sonoff"); ?>"></script>
		<script src="<?php echo $urlHelper->js("app"); ?>"></script>

		<link href="<?php echo $urlHelper->style("bootstrap", "node_modules/bootstrap/dist/css/"); ?>" rel="stylesheet">
		<link href="<?php echo $urlHelper->style("all", "node_modules/@fortawesome/fontawesome-free/css/"); ?>" rel="stylesheet">
		<link href="<?php echo $urlHelper->style("tablesaw", "node_modules/tablesaw/dist/"); ?>" rel="stylesheet">
		<link href="<?php echo $urlHelper->style("tablesaw.stackonly", "node_modules/tablesaw/dist/stackonly/"); ?>" rel="stylesheet">
		
		<link href="<?php echo $urlHelper->style("all"); ?>" rel="stylesheet">
		
		<?php if (@file_exists(_RESOURCESDIR_ . "css/custom.css")): ?>
			<link href="<?php echo $urlHelper->style("custom"); ?>" rel="stylesheet">
		<?php endif; ?>
	
	</head>
	<body class='<?php echo $container->get(ViewHelper::class)->getNightMode(date('H')); ?>'>
		<header>
			<nav class="navbar navbar-expand-sm navbar-dark bg-dark fixed-top py-1">
				<?php //var_dump( $page ); ?>
				<a class="navbar-brand py-0 logo" href='<?php echo _BASEURL_ . $Config->read("homepage"); ?>'>
					<img src='<?php echo _RESOURCESURL_; ?>img/logo_small.png' height='50px'/>
				</a>
				<button class="navbar-toggler"
						type="button"
						data-toggle="collapse"
						data-target="#navbarSupportedContent"
						aria-controls="navbarSupportedContent"
						aria-expanded="false"
						aria-label="Toggle navigation"
				>
					<span class="navbar-toggler-icon"></span>
				</button>
				
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto">
						<?php if ($loggedin): ?>
							<li class="nav-item <?php echo $page == "start" ? "active" : ""; ?>">
								<a class="nav-link" href="<?php echo _BASEURL_; ?>start"><?php echo __(
										"STARTPAGE",
										"NAVI"
									); ?></a>
							</li>
						<?php endif; ?>
						
						<?php if ($loggedin): ?>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle <?php echo in_array(
									$page,
									[
										"upload_form",
										"upload",
										"device_update",
										"devices",
										"device_config",
										"device_action",
										"devices_autoscan",
                                        "backup"
									]
								) ? "active" : ""; ?>"
								   href="#"
								   id="devicesDropdown"
								   data-toggle="dropdown"
								   aria-haspopup="false"
								   aria-expanded="false"
								>
									<?php echo __("DEVICES", "NAVI"); ?>
								</a>
								<div class="dropdown-menu bg-dark" aria-labelledby="devicesDropdown">
									<a class="dropdown-item nav-link <?php echo $page == "devices" ? "active" : ""; ?>"
									   href="<?php echo _BASEURL_; ?>devices"
									>
										<?php echo __("DEVICE_LIST", "NAVI"); ?>
									</a>
									<a href='<?php echo _BASEURL_; ?>upload_form'
									   class='dropdown-item nav-link <?php echo in_array(
										   $page,
										   [
											   "upload_form",
											   "upload",
											   "device_update",
										   ]
									   ) ? "active" : ""; ?>'
									>
										<?php echo __("UPDATE", "NAVI"); ?>
									</a>

                                    <a class="dropdown-item nav-link <?php echo $page == "backup" ? "active" : ""; ?>"
                                       href="<?php echo _BASEURL_; ?>backup"
                                    ><?php echo __("DEVICES_BACKUP", "NAVI"); ?>
                                    </a>
									
									<a class="dropdown-item nav-link <?php echo $page == "devices_autoscan" ? "active"
										: ""; ?>"
									   href="<?php echo _BASEURL_; ?>devices_autoscan"
									>
										<?php echo __("DEVICES_AUTOSCAN", "NAVI"); ?>
									</a>
								</div>
							</li>
						<?php endif; ?>
						<?php if ($loggedin): ?>
							<li class="nav-item">
								<a class="nav-link <?php echo $page == "site_config" ? "active" : ""; ?>"
								   href='<?php echo _BASEURL_; ?>site_config'
								>
									<?php echo __("SETTINGS", "NAVI"); ?>
								</a>
							</li>
						<?php endif; ?>
						
						<?php if ($loggedin && !$docker): ?>
							<li class="nav-item">
								<a class="nav-link <?php echo $page == "selfupdate" ? "active" : ""; ?>"
								   href='<?php echo _BASEURL_; ?>selfupdate'
								>
									<?php echo __("SELFUPDATE", "NAVI"); ?>
								</a>
							</li>
						<?php endif; ?>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle <?php echo in_array(
								$page,
								[
									"chat",
									($docker ? "selfupdate" : NULL),
								]
							) ? "active" : ""; ?>"
							   href="#"
							   id="helpDropdown"
							   data-toggle="dropdown"
							   aria-haspopup="false"
							   aria-expanded="false"
							>
								<i class='fa fa-question-circle'></i>
							</a>
							<div class="dropdown-menu bg-dark" aria-labelledby="helpDropdown">
								<?php if ($docker): ?>
									<a class="dropdown-item nav-link <?php echo $page == "selfupdate" ? "active"
										: ""; ?>"
									   href="<?php echo _BASEURL_; ?>selfupdate"
									>
										<?php echo __("HELP_CHANGELOG", "NAVI"); ?>
									</a>
								<?php endif; ?>
								<a href='https://tasmota.github.io/docs/' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
									   $page,
									   []
								   ) ? "active" : ""; ?>'
								>
									<?php echo __("HELP_TASDOCS", "NAVI"); ?>
								</a>
								<a href='https://tasmota.github.io/docs/Commands/' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
									   $page,
									   []
								   ) ? "active" : ""; ?>'
								>
									<?php echo __("HELP_TASCOMMANDS", "NAVI"); ?>
								</a>
								<a href='https://templates.blakadder.com/' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
									   $page,
									   []
								   ) ? "active" : ""; ?>'
								>
									<?php echo __("HELP_TASTEMPLATES", "NAVI"); ?>
								</a>
								<a href='https://tasmota.github.io/docs/Troubleshooting/' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
									   $page,
									   []
								   ) ? "active" : ""; ?>'
								>
									<?php echo __("HELP_TASTROUBLESHOOTING", "NAVI"); ?>
								</a>
								
								<a href='https://discord.gg/Ks2Kzd4' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
									   $page,
									   []
								   ) ? "active" : ""; ?>'
								>
									<?php echo __("HELP_DISCORD_TASMOTA", "NAVI"); ?>
								</a>
								<a href='https://discord.gg/Q6zPX3C' target='_blank'
								   class='dropdown-item nav-link <?php echo in_array(
									   $page,
									   []
								   ) ? "active" : ""; ?>'
								>
									<?php echo __("HELP_DISCORD_TASMOADMIN", "NAVI"); ?>
								</a>
							
							</div>
						</li>
					</ul>
					
					
					<div class='my-2 my-sm-0 language-switch-holder'>
						<?php $tasmoAdminLanguages = [
							"cz",
							"de",
							"en",
							"es",
							"fr",
							"hu",
							"it",
							"nl",
							"pl",
							"it",
							"ru",
							"zh_TW",
						]; ?>
						<select name='language-switch' id='language-switch' class='custom-select'>
							<?php foreach ($tasmoAdminLanguages as $l): ?>
								<option value='<?php echo $l; ?>'
									<?php echo $lang === $l ? "selected=\"selected\"" : ""; ?>
								>
									<?php echo strtoupper($l); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php if ($loggedin): ?>
						<?php if ($Config->read("login") == "1"): ?>
							<div class="my-2 my-lg-0 ml-0 ml-sm-3 ">
								<a class="error"
								   href='<?php echo _BASEURL_; ?>logout'
								   title='<?php echo __("LOGOUT", "NAVI"); ?>'
								>
									<i class='fas fa-sign-out-alt fa-lg'></i>
									<span class='d-inline d-sm-none'>
										<?php echo __("LOGOUT", "NAVI"); ?>
									</span>
								</a>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</nav>
		</header>
        <main class='container-fluid' id='content' data-refreshtime='<?php echo $Config->read( "refreshtime" ); ?>'>
            <div id='content-holder'>
