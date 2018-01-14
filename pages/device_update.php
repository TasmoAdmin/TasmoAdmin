<?php
	//	var_dump( $_POST );
	$localIP = $Config->read( "ota_server_ip" );
	
	$otaServer = "http://".$localIP.":".$_SERVER[ "SERVER_PORT" ]."/";
	
	$ota_minimal_firmware_url = $otaServer.$_POST[ "minimal_firmware_path" ];
	$ota_new_firmware_url     = $otaServer.$_POST[ "new_firmware_path" ];
	
	$device_ips = $_POST[ "device_ips" ];
?>
<div class='center'>
	<p>
		Update Prozess
	</p>
	<div id='progressbox'>
	
	</div>
	
	<input type='hidden' id='ota_minimal_firmware_url' value='<?php echo $ota_minimal_firmware_url; ?>'>
	<input type='hidden' id='ota_new_firmware_url' value='<?php echo $ota_new_firmware_url; ?>'>
</div>

<script>
	var device_ips = '<?php echo json_encode( $device_ips ); ?>';

</script>


<script type='text/javascript' src='/resources/js/device_update.js'></script>