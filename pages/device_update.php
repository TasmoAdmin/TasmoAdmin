<?php
	var_dump( $_POST );
	$localIP = $Config->read( "ota_server_ip" );
	
	$otaServer = "http://".$localIP.":".$_SERVER[ "SERVER_PORT" ]."/data/firmwares/";
	
	var_dump( $otaServer );
?>