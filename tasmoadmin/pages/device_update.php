<?php
	//	var_dump( $_REQUEST );
	$localIP   = $Config->read( "ota_server_ip" );
	$localPort = $Config->read( "ota_server_port" );
	if( empty( $localPort ) ) {
		$localPort = $_SERVER[ "SERVER_PORT" ];
	}
	$subdir = dirname( $_SERVER[ 'PHP_SELF' ] );
	$subdir = str_replace( "\\", "/", $subdir );
	$subdir = $subdir == "/" ? "" : $subdir;

	$otaServer = $_SERVER[ 'REQUEST_SCHEME' ]."://".$localIP.":".$localPort._BASEURL_."";

	if( isset( $_REQUEST[ 'minimal_firmware_path' ] ) && !empty( $_REQUEST[ 'minimal_firmware_path' ] ) ) {
		$ota_minimal_firmware_url = $otaServer."data/firmwares/sonoff-minimal.bin";
	}
	$ota_new_firmware_url = $otaServer."data/firmwares/sonoff-full.bin";

	$device_ids = isset( $_REQUEST[ "device_ids" ] ) ? $_REQUEST[ "device_ids" ] : FALSE;
?>
<div class='row justify-content-sm-center'>
	<div class='col-12 col-md-8 '>
		<h2 class='text-sm-center mb-5'>
			<?php echo $title; ?>
		</h2>
	</div>
</div>
<div class='row justify-content-center'>
	<div class='col-12 col-md-10'>
		<?php if( !$device_ids ): ?>
			<div class="alert alert-danger alert-dismissible fade show mb-5" data-dismiss="alert" role="alert">
				<?php echo __( "NO_DEVICES_SELECTED", "DEVICE_UPDATE" ); ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		<?php else: ?>
			<div id='progressbox' class='mt-3 border border-dark p-3 pre-scrollable'>

			</div>

		<input type='hidden' id='ota_minimal_firmware_url'
		       value='<?php echo isset( $ota_minimal_firmware_url ) ? $ota_minimal_firmware_url : ""; ?>'>
		<input type='hidden'
		       id='ota_new_firmware_url'
		       value='<?php echo isset( $ota_new_firmware_url ) ? $ota_new_firmware_url : ""; ?>'>


			<script>
				var device_ids = '<?php echo json_encode( $device_ids ); ?>';

			</script>


			<script type='text/javascript'
			        src='<?php echo _RESOURCESURL_; ?>js/device_update.js?<?php echo time(); ?>'></script>
		<?php endif; ?>
	</div>
</div>
