<!doctype html>
<html lang="<?php echo $lang; ?>" xmlns="http://www.w3.org/1999/html">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="Cache-control" content="no-cache, must-revalidate"/>
	<meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT"/>
	<meta http-equiv="Pragma" content="no-cache"/>
	<meta name="mobile-web-app-capable" content="yes">
	<!--
	Always force latest IE rendering engine (even in intranet) & Chrome Frame
	Remove this if you use the .htaccess
	-->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	
	<link rel="apple-touch-icon" sizes="57x57" href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114"
	      href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120"
	      href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144"
	      href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152"
	      href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180"
	      href="<?php echo _RESOURCESURL_; ?>img/favicons/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"
	      href="<?php echo _RESOURCESURL_; ?>img/favicons/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo _RESOURCESURL_; ?>img/favicons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="<?php echo _RESOURCESURL_; ?>img/favicons/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo _RESOURCESURL_; ?>img/favicons/favicon-16x16.png">
	<link rel="manifest" href="<?php echo _RESOURCESURL_; ?>img/favicons/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="<?php echo _RESOURCESURL_; ?>img/favicons/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">
	
	
	<title><?php echo isset( $title ) ? $title." - " : ""; ?>SonWEB</title>
	<script type="application/javascript">
		var _BASEURL_      = "<?php echo _BASEURL_; ?>";
		var _RESOURCESURL_ = "<?php echo _RESOURCESURL_; ?>";
	</script>
	<script src="<?php echo _RESOURCESURL_; ?>js/jquery-ui/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/jquery-ui/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
	<script>
		/*** Handle jQuery plugin naming conflict between jQuery UI and Bootstrap ***/
		$.widget.bridge( 'uibutton', $.ui.button );
		$.widget.bridge( 'uitooltip', $.ui.tooltip );
	</script>
	<script src="<?php echo _RESOURCESURL_; ?>js/bootstrap/bootstrap.bundle.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/jquery.i18n.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/jquery.i18n.messagestore.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/jquery.i18n.fallbacks.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/jquery.i18n.parser.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/jquery.i18n.emitter.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/jquery.i18n.language.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/bs.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/dsb.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/fi.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/ga.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/he.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/hsb.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/hu.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/hy.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/la.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/ml.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/os.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/ru.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/sl.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/i18n/languages/uk.js"></script>
	
	<script src="<?php echo _RESOURCESURL_; ?>js/tablesaw/tablesaw.stackonly.jquery.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/tablesaw/tablesaw-init.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/js-cookie/js.cookie.js"></script>
	<script src="<?php echo _RESOURCESURL_; ?>js/bootstrap-waitingfor/waitingfor.js?<?php echo time(); ?>"></script>
	<!--	<script src="--><?php //echo _RESOURCESURL_; ?><!--js/moment-js/moment-with-locales.js"></script>-->
	
	<script type='text/javascript' src='<?php echo _RESOURCESURL_; ?>js/Sonoff.js?<?php echo time(); ?>'></script>
	
	<script type='text/javascript' src='<?php echo _RESOURCESURL_; ?>js/app.js?<?php echo time(); ?>'></script>
	
	<link href="<?php echo _RESOURCESURL_; ?>css/bootstrap/bootstrap.css" rel="stylesheet">
	<link href='<?php echo _RESOURCESURL_; ?>js/jquery-ui/jquery-ui-1.12.1.custom/jquery-ui.css'
	      rel='stylesheet'>
	
	<link href="<?php echo _RESOURCESURL_; ?>css/tablesaw/tablesaw.css" rel="stylesheet">
	<link href="<?php echo _RESOURCESURL_; ?>css/tablesaw/tablesaw.stackonly.css" rel="stylesheet">
	<link href="<?php echo _RESOURCESURL_; ?>css/fontawesome/css/fontawesome-all.css?<?php echo time(); ?>"
	      rel="stylesheet">
	<link href='<?php echo _RESOURCESURL_; ?>css/animate.css?<?php echo time(); ?>' rel='stylesheet'>
	<link href='<?php echo _RESOURCESURL_; ?>css/style.css?<?php echo time(); ?>' rel='stylesheet'>
	<link href='<?php echo _RESOURCESURL_; ?>css/mobile.css?<?php echo time(); ?>' rel='stylesheet'>
	<?php if ( @file_exists( _RESOURCESDIR_."css/custom.css" ) ): ?>
		<link href='<?php echo _RESOURCESURL_; ?>css/custom.css?<?php echo time(); ?>' rel='stylesheet'>
	<?php endif; ?>

</head>
<body>
<header>
	<nav class="navbar navbar-expand-sm navbar-dark bg-dark fixed-top">
		<?php //var_dump( $page ); ?>
		<a class="navbar-brand" href='<?php echo _BASEURL_; ?>start'>SonWEB</a>
		<button class="navbar-toggler"
		        type="button"
		        data-toggle="collapse"
		        data-target="#navbarSupportedContent"
		        aria-controls="navbarSupportedContent"
		        aria-expanded="false"
		        aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		
		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav mr-auto">
				<?php if ( $loggedin ): ?>
					<li class="nav-item <?php echo $page == "start" ? "active" : ""; ?>">
						<a class="nav-link" href="<?php echo _BASEURL_; ?>start"><?php echo __(
								"STARTPAGE",
								"NAVI"
							); ?></a>
					</li>
				<?php endif; ?>
				
				<?php if ( $loggedin ): ?>
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
							]
						) ? "active" : ""; ?>"
						   href="#"
						   id="devicesDropdown"
						   data-toggle="dropdown"
						   aria-haspopup="false"
						   aria-expanded="false">
							<?php echo __( "DEVICES", "NAVI" ); ?>
						</a>
						<div class="dropdown-menu bg-dark" aria-labelledby="devicesDropdown">
							<a class="dropdown-item nav-link <?php echo $page == "devices" ? "active" : ""; ?>"
							   href="<?php echo _BASEURL_; ?>devices">
								<?php echo __( "DEVICE_LIST", "NAVI" ); ?>
							</a>
							<a href='<?php echo _BASEURL_; ?>upload_form'
							   class='dropdown-item nav-link <?php echo in_array(
								   $page,
								   [
									   "upload_form",
									   "upload",
									   "device_update",
								   ]
							   ) ? "active" : ""; ?>'>
								<?php echo __( "UPDATE", "NAVI" ); ?>
							</a>
							
							<a class="dropdown-item nav-link <?php echo $page == "devices_autoscan" ? "active" : ""; ?>"
							   href="<?php echo _BASEURL_; ?>devices_autoscan">
								<?php echo __( "DEVICES_AUTOSCAN", "NAVI" ); ?>
							</a>
						</div>
					</li>
				<?php endif; ?>
				<?php if ( $loggedin ): ?>
					<li class="nav-item">
						<a class="nav-link <?php echo $page == "site_config" ? "active" : ""; ?>"
						   href='<?php echo _BASEURL_; ?>site_config'>
							<?php echo __( "SETTINGS", "NAVI" ); ?>
						</a>
					</li>
				<?php endif; ?>
				
				<?php if ( $loggedin ): ?>
					<li class="nav-item">
						<a class="nav-link <?php echo $page == "selfupdate" ? "active" : ""; ?>"
						   href='<?php echo _BASEURL_; ?>selfupdate'>
							<?php echo __( "SELFUPDATE", "NAVI" ); ?>
						</a>
					</li>
				<?php endif; ?>
			
			</ul>
			
			
			<div class='my-2 my-sm-0 language-switch-holder'>
				<select name='language-switch' id='language-switch' class='custom-select'>
					<option value='de' <?php echo $lang == "de" ? "selected=\"selected\"" : ""; ?>>DE</option>
					<option value='en' <?php echo $lang == "en" ? "selected=\"selected\"" : ""; ?>>EN</option>
					<option value='es' <?php echo $lang == "es" ? "selected=\"selected\"" : ""; ?>>ES</option>
					<option value='fr' <?php echo $lang == "fr" ? "selected=\"selected\"" : ""; ?>>FR</option>
					<option value='it' <?php echo $lang == "it" ? "selected=\"selected\"" : ""; ?>>IT</option>
					<option value='nl' <?php echo $lang == "nl" ? "selected=\"selected\"" : ""; ?>>NL</option>
					<option value='pl' <?php echo $lang == "pl" ? "selected=\"selected\"" : ""; ?>>PL</option>
					<option value='ru' <?php echo $lang == "ru" ? "selected=\"selected\"" : ""; ?>>RU</option>
				</select>
			</div>
			<?php if ( $loggedin ): ?>
				<?php if ( $Config->read( "login" ) == "1" ): ?>
					<div class="my-2 my-lg-0 ml-0 ml-sm-3 ">
						<a class=""
						   href='<?php echo _BASEURL_; ?>logout'
						   title='<?php echo __( "LOGOUT", "NAVI" ); ?>'>
							<i class='fas fa-sign-out-alt fa-lg'></i><span class='d-inline d-sm-none'>
							<?php echo __( "LOGOUT", "NAVI" ); ?>
						</span>
						</a>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</nav>
</header>
