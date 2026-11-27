<?php

use TasmoAdmin\Helper\UrlHelper;

// Standalone page: the service worker caches it and shows it when the server is unreachable.
$urlHelper = $container->get(UrlHelper::class);
?>
<!doctype html>
<html lang="<?php echo $lang; ?>">
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex">
		<title><?php echo __('OFFLINE_TITLE', 'PWA'); ?> - TasmoAdmin</title>
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo _RESOURCESURL_; ?>img/favicons/favicon-32x32.png">
		<link href="<?php echo $urlHelper->style('compiled/all'); ?>" rel="stylesheet">
		<script>
			// Mirror the app theme without loading the full bundle.
			(function () {
				var override = localStorage.getItem('nightmode_override');
				var night = override ? override === 'night' : window.matchMedia('(prefers-color-scheme: dark)').matches;
				document.addEventListener('DOMContentLoaded', function () {
					document.body.classList.toggle('nightmode', night);
				});
			})();
		</script>
	</head>
	<body>
		<main class="container offline-page">
			<div class="card login-card text-center">
				<div class="card-body">
					<img src="<?php echo _RESOURCESURL_; ?>img/favicons/android-chrome-192x192.png" alt="" width="72" height="72" class="mb-3">
					<h1 class="h4"><?php echo __('OFFLINE_TITLE', 'PWA'); ?></h1>
					<p class="text-body-secondary"><?php echo __('OFFLINE_TEXT', 'PWA'); ?></p>
					<button type="button" class="btn btn-primary" onclick="window.location.reload()">
						<?php echo __('OFFLINE_RETRY', 'PWA'); ?>
					</button>
				</div>
			</div>
		</main>
	</body>
</html>
<?php
exit;
