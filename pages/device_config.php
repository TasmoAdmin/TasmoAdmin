<?php
	$msg            = FALSE;
	$device         = null;
	$activeTabIndex = 0;
	
	if ( isset( $_GET[ "device_id" ] ) ) {
		$device = $Sonoff->getDeviceById( $_GET[ "device_id" ] );
	} else {
		$msg = __( "ERROR_NO_DEVICE_SELECTED", "DEVICE_CONFIG" );
	}
	
	if ( isset( $_POST ) && !empty( $_POST ) ) {
		$activeTabIndex = $_POST[ "tab-index" ];
		if ( isset( $_POST[ "save" ] ) ) {
			$settings = $_POST;
			unset( $settings[ "save" ] );
			unset( $settings[ "tab-index" ] );
			if ( !isset( $settings[ "Password1" ] ) || empty( $settings[ "Password1" ] )
			     || $settings[ "Password1" ] == "" ) {
				unset( $settings[ "Password1" ] );
			}
			if ( !isset( $settings[ "Password2" ] ) || empty( $settings[ "Password2" ] )
			     || $settings[ "Password2" ] == "" ) {
				unset( $settings[ "Password2" ] );
			}
			if ( isset( $settings[ "IPAddress1" ] ) && !empty( $settings[ "IPAddress1" ] )
			     && $settings[ "IPAddress1" ] != "" ) {
				if ( $settings[ "IPAddress1" ] != "0.0.0.0" ) {
					if ( $device->ip == $settings[ "IPAddress1" ] ) {
						unset( $settings[ "IPAddress1" ] );
					}
				}
			}
			
			$backlog = "Backlog ";
			foreach ( $settings as $settingKey => $settingVal ) {
				if ( $settingVal == "" ) {
					continue;
				}
				$backlog .= $settingKey." ".$settingVal.";";
			}
			$result = $Sonoff->saveConfig( $device, $backlog );
			$msg    = __( "MSG_CONFIG_SAVED", "DEVICE_CONFIG" );
			$msg    .= "<br/> ".$backlog;
			sleep( count( $settings ) );
		}
		
		
	}
	
	$status            = $Sonoff->getAllStatus( $device );
	$status->statusNTP = $Sonoff->getNTPStatus( $device );


?>
<h2 class="center">
	<?php echo __( "DEVICE", "DEVICE_CONFIG" ); ?>:
	<?php echo implode( " | ", $device->names ); ?>
    <small>( ID: <?php echo $device->id; ?> )</small>
</h2>
<?php if ( isset( $msg ) && !empty( $msg ) ): ?>
    <p class='toastr success'>
		<?php echo $msg ? $msg : ""; ?>
    </p>
<?php endif; ?>

<?php if ( isset( $status->ERROR ) && !empty( $status->ERROR ) ): ?>
    <p class='toastr error'>
		<?php echo __( "ERROR_COULD_NOT_GET_DATA", "DEVICE_CONFIG" ); ?><br/><br/>
		<?php echo $status->ERROR; ?><br/><br/>
        <a href='#' class='reload'><?php echo __( "PAGE_RELOAD" ); ?></a>
    </p>

<?php else: ?>
    <div id="config_tabs">
        <ul>
            <li data-tab-index='0' class='<?php echo $activeTabIndex == 0 ? "active" : ""; ?>'>
                <a href="#config_general_tab">
					<?php echo __( "TAB_HL_GENERAL", "DEVICE_CONFIG" ); ?>
                </a>
            </li>
            <li data-tab-index='1' class='<?php echo $activeTabIndex == 1 ? "active" : ""; ?>'>
                <a href="#config_network_tab">
					<?php echo __( "TAB_HL_NETWORK", "DEVICE_CONFIG" ); ?>
                </a>
            </li>
        </ul>
        <div id="config_general_tab">
			<?php include_once _PAGESDIR_."device_config_tabs/config_general_tab.php"; ?>
        </div>
        <div id="config_network_tab">
			<?php include_once _PAGESDIR_."device_config_tabs/config_network_tab.php"; ?>
        </div>

    </div>

<?php endif; ?>

<script type='text/javascript' src='<?php echo _RESOURCESDIR_; ?>js/device_config.js?<?php echo time(); ?>'></script>