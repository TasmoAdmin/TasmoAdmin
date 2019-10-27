<?php
	$changelogUrl = "https://raw.githubusercontent.com/arendst/Tasmota/development/RELEASENOTES.md?r=".time();
	$ch           = curl_init();
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_URL, $changelogUrl );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$changelog = curl_exec( $ch );


	$fchangelog = "";
	//$changelog = file_get_contents( _APPROOT_."CHANGELOG.md" );
	if( !$changelog || curl_error( $ch ) != "" || $changelog == "" ) {
		$changelog = "";
	} else {
		$changelog = str_replace( [ "*/", "/*", " *\n" ], [ "", "", "" ], $changelog );
		if( strlen( $changelog ) > 99999 ) {
			$changelog = substr( $changelog, 0, 5000 )."...";
		}

		$changelog = substr(
			$changelog,
			0,
			strpos( $changelog, "Available Features and Sensors" )-5
		);        //.substr($changelog,strpos( $changelog, "Changelog" )-4)

		require_once _LIBSDIR_."parsedown/Parsedown.php";
		$mdParser  = new Parsedown();
		$changelog = $mdParser->parse( $changelog );

		$tasmotaIssueUrl = "https://github.com/arendst/Tasmota/issues/";
		$changelog       = preg_replace(
			"/\B#([\d]+)/",
			"<a href='$tasmotaIssueUrl$1' target='_blank'>#$1</a>",
			$changelog
		);
		//$changelog       = str_replace( "\n", "<br/>", $changelog );
	}
	$fchangelog .= $changelog;
	$fchangelog .= "<br/><br/>";


	$changelogUrl = "https://raw.githubusercontent.com/arendst/Tasmota/development/sonoff/_changelog.ino?r=".time();
	$ch           = curl_init();
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_URL, $changelogUrl );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$changelog = curl_exec( $ch );


	//$changelog = file_get_contents( _APPROOT_."CHANGELOG.md" );
	if( !$changelog || curl_error( $ch ) != "" || $changelog == "" ) {
		$changelog = "";
	} else {
		$changelog = substr( str_replace( [ "*/", "/*", " *\n" ], [ "", " * ", "\n" ], $changelog ), 0, 99999 )."...";

		$changelog = substr( $changelog, 0, strpos( $changelog, " 2018", 1000 )-10 );

		require_once _LIBSDIR_."parsedown/Parsedown.php";
		$mdParser  = new Parsedown();
		$changelog = $mdParser->parse( $changelog );

		$tasmotaIssueUrl = "https://github.com/arendst/Sonoff-Tasmota/issues/";
		$changelog       = preg_replace(
			"/\B#([\d]+)/",
			"<a href='$tasmotaIssueUrl$1' target='_blank'>#$1</a>",
			$changelog
		);
		//		$changelog       = "<h2>Developer Changelog</h2>".$changelog;
	}
	$fchangelog = $changelog.$fchangelog;

?>
<div class='row justify-content-sm-center'>
	<div class='col col-12 col-md-8 col-xl-6'>
		<h2 class='text-sm-center mb-3'>
			<?php echo $title; ?>
		</h2>
		<div class='text-center mb-3'>
			<?php echo __( "UPLOAD_DESCRIPTION", "DEVICE_UPDATE" ); ?>
			<br/>
			<a href='https://github.com/arendst/Sonoff-Tasmota/releases' target='_blank'>Tasmota Releases</a>
		</div>


		<form class='' name='update_form' method='post' enctype='multipart/form-data'
		      action='<?php echo _BASEURL_; ?>upload'>
			<div class='form-row'>
				<div class="form-group col col-12 col-sm-3">
					<div class="form-check custom-control custom-checkbox mb-3" style='margin-top: 35px;'>
						<input class="form-check-input custom-control-input"
						       type="checkbox"
						       value="1"
						       id="cb_ota_server_ssl"
						       name='ota_server_ssl' <?php echo $Config->read( "ota_server_ssl" ) == "1"
							? "checked=\"checked\"" : ""; ?>>
						<label class="form-check-label custom-control-label" for="cb_ota_server_ssl" style='top:3px;'>
							<?php echo __( "CONFIG_SERVER_SSL", "USER_CONFIG" ); ?>
						</label>
					</div>
				</div>

				<div class="form-group col col-12 col-sm-6">
					<label for="ota_server_ip">
						<?php echo __( "CONFIG_SERVER_IP", "USER_CONFIG" ); ?>
					</label>
					<input type="text"
					       class="form-control"
					       id="ota_server_ip"
					       name='ota_server_ip'
					       required
					       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
					       value='<?php echo $Config->read( "ota_server_ip" ); ?>'
					>
				</div>
				<div class="form-group col col-12 col-sm-3">
					<label for="ota_server_ip">
						<?php echo __( "CONFIG_SERVER_PORT", "USER_CONFIG" ); ?>
					</label>
					<input type="text"
					       class="form-control"
					       id="ota_server_port"
					       name='ota_server_port'
					       required
					       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
					       value='<?php echo !empty( $Config->read( "ota_server_port" ) ) ? $Config->read(
						       "ota_server_port"
					       ) : $_SERVER[ "SERVER_PORT" ]; ?>'
					>
				</div>
			</div>

			<div class='form-row'>
				<div class="form-group col">
					<label for="minimal_firmware">
						<?php echo __( "FORM_CHOOSE_MINIMAL_FIRMWARE", "DEVICE_UPDATE" ); ?>
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
						<?php echo __( "UPLOAD_FIRMWARE_FULL_LABEL", "DEVICE_UPDATE" ); ?>
					</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="new_firmware" name='new_firmware' required>
						<label class="custom-file-label" for="new_firmware">

						</label>
					</div>
				</div>
			</div>

			<div class='form-row'>
				<div class="col col-12 col-sm-3">
					<button type='submit' class='btn btn-primary' id="automatic" name='auto' value='submit'
					        title='<?php echo __( "BTN_UPLOAD_AUTOMATIC_HELP", "DEVICE_UPDATE" ); ?>'
					>
						<?php echo __( "BTN_UPLOAD_AUTOMATIC", "DEVICE_UPDATE" ); ?>
					</button>
				</div>

				<div class='col flex-column mb-3 mb-sm-0'></div>

				<div class='col col-12 col-sm-3 text-sm-right'>
					<button type='submit' class='btn btn-primary' name='upload' value='submit'>
						<?php echo __( "BTN_UPLOAD_NEXT", "DEVICE_UPDATE" ); ?>
					</button>
				</div>
			</div>


		</form>
		<hr class='my-5'>
		<div class='changelog'>
			<h2>Tasmota Changelog</h2>
			<?php echo $fchangelog; ?>
		</div>
	</div>
</div>

<script>
	$( "#automatic" ).on( "click", function ( e ) {
		$( "#new_firmware" ).removeProp( "required" );
	} );
</script>
