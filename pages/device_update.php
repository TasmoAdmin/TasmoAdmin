<?php
	//	var_dump( $_POST );
	$localIP = $Config->read( "ota_server_ip" );
	$subdir  = dirname( $_SERVER[ 'PHP_SELF' ] );
	$subdir  = str_replace( "\\", "/", $subdir );
	$subdir  = $subdir == "/" ? "" : $subdir;
	
	$otaServer = "http://".$localIP.":".$_SERVER[ "SERVER_PORT" ].$subdir."/";
	
	$ota_minimal_firmware_url = $otaServer."data/firmwares/sonoff-minimal.bin";
	$ota_new_firmware_url     = $otaServer."data/firmwares/sonoff-full.bin";
	
	$device_ids = isset( $_POST[ "device_ids" ] ) ? $_POST[ "device_ids" ] : FALSE;
?>
<div class='center'>
	<?php if ( !$device_ids ): ?>
	<br/>
	<br/>
		<p class='warning'>
			<?php echo __( "NO_DEVICES_SELECTED", "DEVICE_UPDATE" ); ?>
		</p>
	
	<?php else: ?>
		<div id='progressbox'>
		
		</div>
	
	<input type='hidden' id='ota_minimal_firmware_url' value='<?php echo $ota_minimal_firmware_url; ?>'>
	<input type='hidden' id='ota_new_firmware_url' value='<?php echo $ota_new_firmware_url; ?>'>
		
		
		<script>
			var device_ids = '<?php echo json_encode( $device_ids ); ?>';
		
		</script>
		
		
		<script type='text/javascript'
		        src='<?php echo _RESOURCESDIR_; ?>js/device_update.js?<?php echo time(); ?>'></script>
	<?php endif; ?>
</div>