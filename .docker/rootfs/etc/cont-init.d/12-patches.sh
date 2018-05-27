#!/usr/bin/with-contenv bash
# ==============================================================================
# Community Hass.io Add-ons: SonWEB
# Applies patch to remove SelfUpdate, since that is useless shit in Docker
# ==============================================================================
# shellcheck disable=SC1091
#source /usr/lib/hassio-addons/base.sh
#
#patch -F2 -R --ignore-whitespace /var/www/sonweb/includes/header.php <<'PATCH'
#--- header.php	2018-05-22 00:00:00.463304792 +0200
#+++ header.php	2018-05-22 00:00:00.291634513 +0200
#@@ -178,6 +178,15 @@
# 					</li>
# 				<?php endif; ?>
#
#+				<?php if ( $loggedin ): ?>
#+					<li class="nav-item">
#+						<a class="nav-link <?php echo $page == "selfupdate" ? "active" : ""; ?>"
#+						   href='<?php echo _BASEURL_; ?>selfupdate'>
#+							<?php echo __( "SELFUPDATE", "NAVI" ); ?>
#+						</a>
#+					</li>
#+				<?php endif; ?>
#+
# 			</ul>
#
#
#PATCH
#
## shellcheck disable=SC2181
#if [[ "$?" -ne 0 ]];
#then
#    hass.die 'Patching SonWEB SelfUpdate failed'
#fi
#hass.log.debug 'Applied SonWEB SelfUpdate fix'
#
#patch -F2 -R --ignore-whitespace \
#    /var/www/sonweb/pages/device_update.php << 'PATCH'
#--- device_update.php	2018-05-23 21:31:36.000000000 +0200
#+++ device_update.php	2018-05-23 21:30:19.000000000 +0200
#@@ -5,7 +5,7 @@
# 	$subdir  = str_replace( "\\", "/", $subdir );
# 	$subdir  = $subdir == "/" ? "" : $subdir;
#
#-	$otaServer = $_SERVER['REQUEST_SCHEME'] . "://".$localIP.":".$_SERVER[ "SERVER_PORT" ]._BASEURL_."";
#+	$otaServer = "http://".$localIP.":".$_SERVER[ "SERVER_PORT" ]._BASEURL_."";
#
# 	if ( isset( $_POST[ 'minimal_firmware_path' ] ) && !empty( $_POST[ 'minimal_firmware_path' ] ) ) {
# 		$ota_minimal_firmware_url = $otaServer."data/firmwares/sonoff-minimal.bin";
#PATCH
#
## shellcheck disable=SC2181
#if [[ "$?" -ne 0 ]];
#then
#    hass.die 'Patching SonWEB OTA over HTTPS failed'
#fi
#hass.log.debug 'Applied SonWEB OTA over HTTPS fix'
