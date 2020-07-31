<?php

require_once _LIBSDIR_ . "parsedown/Parsedown.php";

$mdParser = new Parsedown();
$ch       = curl_init();
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$releaselogUrl = "https://raw.githubusercontent.com/arendst/Tasmota/development/RELEASENOTES.md?r=" . time();

curl_setopt($ch, CURLOPT_URL, $releaselogUrl);
$releaselog = curl_exec($ch);


$fchangelog = "";
//$changelog = file_get_contents( _APPROOT_."CHANGELOG.md" );
if (!$releaselog || curl_error($ch) != "" || $releaselog == "") {
	$releaselog = "";
}
else {
	$releaselog = str_replace(["*/", "/*", " *\n"], ["", "", ""], $releaselog);
	//		if( strlen( $releaselog ) > 99999 ) {
	//			$releaselog = substr( $releaselog, 0, 5000 )."...";
	//		}
	
	//		$changelog = substr(
	//			$releaselog,
	//			0,
	//			strpos( $releaselog, "Available Features and Sensors" )-5
	//		);        //.substr($releaselog,strpos( $releaselog, "Changelog" )-4)
	
	$releaselog = str_replace("https://github.com/arendst/Tasmota/blob/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
		"https://raw.githubusercontent.com/arendst/Tasmota/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
		$releaselog);
	$releaselog = $mdParser->parse($releaselog);
	
	$tasmotaIssueUrl = "https://github.com/arendst/Tasmota/issues/";
	$releaselog      = preg_replace(
		"/\B#([\d]+)/",
		"<a href='$tasmotaIssueUrl$1' target='_blank'>#$1</a>",
		$releaselog
	);
	$releaselog      = str_replace("https://github.com/arendst/Tasmota/blob/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
		"https://raw.githubusercontent.com/arendst/Tasmota/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
		$releaselog);
	//$releaselog       = str_replace( "\n", "<br/>", $releaselog );
}


$changelogUrl = "https://raw.githubusercontent.com/arendst/Tasmota/development/tasmota/CHANGELOG.md?r=" . time();
curl_setopt($ch, CURLOPT_URL, $changelogUrl);
$changelog = curl_exec($ch);


//$changelog = file_get_contents( _APPROOT_."CHANGELOG.md" );
if (!$changelog || curl_error($ch) != "" || $changelog == "" || empty($changelog)) {
	$changelog = "";
}
else {
	//		$changelog = substr( str_replace( [ "*/", "/*", " *\n" ], [ "", " * ", "\n" ], $changelog ), 0, 99999 )."...";
	//		$changelog = str_replace( "****\\", "", $changelog );
	//		$changelog = substr( $changelog, 0, strpos( $changelog, " 2018", 1000 )-10 );
	
	$changelog = $mdParser->parse($changelog);
	
	$tasmotaIssueUrl = "https://github.com/arendst/Tasmota/issues/";
	$changelog       = preg_replace(
		"/\B#([\d]+)/",
		"<a href='$tasmotaIssueUrl$1' target='_blank'>#$1</a>",
		$changelog
	);
	//		$changelog       = "<h2>Developer Changelog</h2>".$changelog;
}
//	$fchangelog = $fchangelog.$changelog;


$releaselogUrlDocs = "https://raw.githubusercontent.com/tasmota/docs/master/docs/changelog.md?r=" . time();
curl_setopt($ch, CURLOPT_URL, $releaselogUrlDocs);
$releaselogDocs = curl_exec($ch);

if (!$releaselogDocs || curl_error($ch) != "" || $releaselogDocs == "" || empty($releaselogDocs)) {
	$releaselogDocs = "";
}
else {
	//		$changelog = substr( str_replace( [ "*/", "/*", " *\n" ], [ "", " * ", "\n" ], $changelog ), 0, 99999 )."...";
	//		$changelog = str_replace( "****\\", "", $changelog );
	//		$changelog = substr( $changelog, 0, strpos( $changelog, " 2018", 1000 )-10 );
	
	$releaselogDocs = $mdParser->parse($releaselogDocs);
	
	$tasmotaIssueUrl = "https://github.com/arendst/Tasmota/issues/";
	$releaselogDocs  = preg_replace(
		"/\B#([\d]+)/",
		"<a href='$tasmotaIssueUrl$1' target='_blank'>#$1</a>",
		$releaselogDocs
	);
	
	$releaselogDocs = str_replace(
		":rotating_light:",
		//		"<i class=\"error red fas fa-exclamation-triangle\" style='color: red;'></i>",
		"<img alt=\"🚨\" class=\"emojione\" src=\"https://cdnjs.cloudflare.com/ajax/libs/emojione/2.2.7/assets/png/1f6a8.png\" title=\":rotating_light:\">",
		$releaselogDocs
	);
	//		$changelog       = "<h2>Developer Changelog</h2>".$changelog;
}


$tasmotaReleases       = [];
$tasmotaRepoReleaseUrl = "https://api.github.com/repos/arendst/Tasmota/releases/latest";
curl_setopt($ch, CURLOPT_URL, $tasmotaRepoReleaseUrl);
$release = json_decode(curl_exec($ch));
if (curl_error($ch)) {
	$result = [
		"ERROR" => __("ERROR_CURL", "SELFUPDATE") . " - " . curl_errno($ch) . ": " . curl_error(
				$ch
			),
	];
}
curl_close($ch);

if (!empty($release) && !empty($release->assets)) {
	foreach ($release->assets as $asset) {
		if (strpos($asset->name, ".bin.gz") !== FALSE
			|| strpos($asset->name, "-minimal.bin") !== FALSE) {
			continue;
		}
		$tasmotaReleases[] = $asset->name;
	}
	//echo "\$tasmotaReleases=[\"" . implode("\",\"", $tasmotaReleases) . "\"];";
}
else {
	$tasmotaReleases =
		[
			"tasmota-BG.bin", "tasmota-BR.bin", "tasmota-CN.bin", "tasmota-CZ.bin", "tasmota-DE.bin",
			"tasmota-display.bin", "tasmota-ES.bin", "tasmota-FR.bin", "tasmota-GR.bin", "tasmota-HE.bin",
			"tasmota-HU.bin", "tasmota-ir.bin", "tasmota-ircustom.bin", "tasmota-IT.bin", "tasmota-knx.bin",
			"tasmota-KO.bin", "tasmota-lite.bin", "tasmota-NL.bin", "tasmota-PL.bin", "tasmota-PT.bin",
			"tasmota-RO.bin", "tasmota-RU.bin", "tasmota-SE.bin", "tasmota-sensors.bin", "tasmota-SK.bin",
			"tasmota-TR.bin", "tasmota-TW.bin", "tasmota-UK.bin", "tasmota-zbbridge.bin", "tasmota.bin",
			"tasmota32-BG.bin", "tasmota32-BR.bin", "tasmota32-CN.bin", "tasmota32-CZ.bin", "tasmota32-DE.bin",
			"tasmota32-display.bin", "tasmota32-ES.bin", "tasmota32-FR.bin", "tasmota32-GR.bin", "tasmota32-HE.bin",
			"tasmota32-ir.bin", "tasmota32-ircustom.bin", "tasmota32-knx.bin", "tasmota32-lite.bin",
			"tasmota32-PL.bin", "tasmota32-PT.bin", "tasmota32-RO.bin", "tasmota32-RU.bin", "tasmota32-SE.bin",
			"tasmota32-sensors.bin", "tasmota32-SK.bin", "tasmota32-TR.bin", "tasmota32-TW.bin", "tasmota32-UK.bin",
			"tasmota32-webcam.bin", "tasmota32.bin",
		];
}
asort($tasmotaReleases);
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
						<?php if ($Config->read("update_automatic_lang") == ""): ?>
							<option><?php echo __("PLEASE_SELECT"); ?></option>
						<?php endif; ?>
						
						<?php foreach ($tasmotaReleases as $tr): ?>
							<option value='<?php echo $tr; ?>'
								<?php echo $Config->read("update_automatic_lang") == $tr ? "selected=\selected\"" : ""; ?>
							>
								<?php echo substr($tr, 0, stripos($tr, ".")); ?>
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
					<?php echo $releaselog; ?>
				</div>
				<div class='changelog'>
					<?php echo $changelog; ?>
				</div>
			</div>
			<div class='col col-12 col-md-6'>
				<div class='changelog'>
					<h1 class='text-uppercase'>
						<?php echo __("TASMOTA_CHANGELOG", "DEVICE_UPDATE"); ?>
					</h1>
					<?php echo $releaselogDocs; ?>
				</div>
			</div>
		</div>
	</div>

</div>

<script>
    $("#automatic").on("click", function (e)
    {
        $("#new_firmware").removeProp("required");
    });
</script>
