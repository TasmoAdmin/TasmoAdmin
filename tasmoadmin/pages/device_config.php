<?php
	$msg            = FALSE;
	$device         = NULL;
	$activeTabIndex = 0;

	if( isset( $_GET[ "device_id" ] ) ) {
		$device = $Sonoff->getDeviceById( $_GET[ "device_id" ] );
	} else {
		$msg = __( "ERROR_NO_DEVICE_SELECTED", "DEVICE_CONFIG" );
	}

	if( !empty( $_POST[ "save" ] ) ) {
		$activeTabIndex = $_POST[ "tab-index" ];
		if( isset( $_POST[ "save" ] ) ) {
			unset( $_POST[ "save" ] );
			unset( $_POST[ "tab-index" ] );
			$settings = $_POST;
			if( !isset( $_POST[ "Password1" ] ) || empty( $settings[ "Password1" ] )
			    || $settings[ "Password1" ] == "" ) {
				unset( $settings[ "Password1" ] );
			}
			if( !isset( $settings[ "Password2" ] ) || empty( $settings[ "Password2" ] )
			    || $settings[ "Password2" ] == "" ) {
				unset( $settings[ "Password2" ] );
			}
			if( isset( $settings[ "IPAddress1" ] ) && !empty( $settings[ "IPAddress1" ] )
			    && $settings[ "IPAddress1" ] != "" ) {
				if( $settings[ "IPAddress1" ] != "0.0.0.0" ) {
					if( $device->ip == $settings[ "IPAddress1" ] ) {
						unset( $settings[ "IPAddress1" ] );
					}
				}
			}

			$backlog = "Backlog ";
			foreach( $settings as $settingKey => $settingVal ) {
				$settingVal = trim( $settingVal );
				if( $settingVal == "" ) {
					continue;
				}
				$backlog .= $settingKey." ".$settingVal."; ";
			}
			$backlog = trim( $backlog );

			$result = $Sonoff->saveConfig( $device, $backlog );
			$msg    = __( "MSG_CONFIG_SAVED", "DEVICE_CONFIG" );
			$msg    .= "<br/> ".$backlog;
			sleep( count( $settings ) );
		}


	}

	$status = $Sonoff->getAllStatus( $device );

	if( empty( $status->ERROR ) ) {
		$status->statusNTP = $Sonoff->getNTPStatus( $device );
		if( empty( $status->StatusMQT ) ) {
			$status->StatusMQT = new stdClass();
		}
		$status->StatusMQT->FullTopic   = $Sonoff->getFullTopic( $device );
		$status->StatusMQT->SwitchTopic = $Sonoff->getSwitchTopic( $device );
		sleep( 1 );
		$status->StatusMQT->MqttRetry    = $Sonoff->getMqttRetry( $device );
		$status->StatusMQT->SensorRetain = $Sonoff->getSensorRetain( $device );
		sleep( 1 );
		$status->StatusMQT->TelePeriod = $Sonoff->getTelePeriod( $device );
		$status->StatusMQT->Prefixe    = $Sonoff->getPrefixe( $device );
		sleep( 1 );
		$status->StatusMQT->StateTexts = $Sonoff->getStateTexts( $device );
		sleep( 1 );
		$status->StatusMQT->MqttFingerprint = $Sonoff->getMqttFingerprint( $device );


		$status->StatusLOG->SetOptionDecoded = $Sonoff->decodeOptions( $status->StatusLOG->SetOption[ 0 ] );
	}


?>
<div class='row justify-content-sm-center'>
	<div class='col col-12 col-md-10 col-lg-10 col-xl-6'>
		<div class='row'>
			<div class='col col-12'>
				<h2 class='text-sm-center'>
					<?php echo __( "CONFIG_HL", "DEVICE_CONFIG" ); ?>: <?php echo implode( " | ", $device->names ); ?>

				</h2>
			</div>
		</div>
		<div class='row'>
			<div class='col col-12 mb-5'>
				<div class='text-center'>
					ID: <?php echo $device->id; ?>
					<a href='http://<?php echo $device->ip; ?>' target='_blank'><?php echo $device->ip; ?>  </a>
				</div>
			</div>
		</div>
		<div class='row'>
			<div class='col col-12'>
				<?php if( isset( $msg ) && $msg != "" ): ?>
					<div class="alert alert-success alert-dismissible fade show mb-5" data-dismiss="alert" role="alert">
						<?php echo $msg; ?>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				<?php endif; ?>

				<?php if( isset( $status->ERROR ) && !empty( $status->ERROR ) ): ?>
				<div class="alert alert-danger alert-dismissible fade show mb-5" role="alert">
					<?php echo __( "ERROR_COULD_NOT_GET_DATA", "DEVICE_CONFIG" ); ?><br/>
					<?php echo $status->ERROR; ?><br/><br/>
					<a href='#' class='reload'><?php echo __( "PAGE_RELOAD" ); ?></a>
					<a type="button" class="reload close" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</a>
				</div>

			</div>
		</div>
		<?php else: ?>
			<div class='row'>
				<div class='col col-12'>
					<ul class="nav nav-tabs" id="device_config" role="tablist">
						<li class="nav-item">
							<a class="nav-link <?php echo $activeTabIndex == 0 ? "active" : ""; ?>"
							   id="config_general_tab-tab"
							   data-toggle="tab"
							   href="#config_general_tab"
							   role="tab"
							   aria-controls="home"
							   aria-selected="true">
								<?php echo __( "TAB_HL_GENERAL", "DEVICE_CONFIG" ); ?>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link <?php echo $activeTabIndex == 1 ? "active" : ""; ?>"
							   id="config_network_tab-tab"
							   data-toggle="tab"
							   href="#config_network_tab"
							   role="tab"
							   aria-controls="profile"
							   aria-selected="false">
								<?php echo __( "TAB_HL_NETWORK", "DEVICE_CONFIG" ); ?>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link <?php echo $activeTabIndex == 2 ? "active" : ""; ?>"
							   id="config_mqtt_tab-tab"
							   data-toggle="tab"
							   href="#config_mqtt_tab"
							   role="tab"
							   aria-controls="profile"
							   aria-selected="false">
								<?php echo __( "TAB_HL_MQTT", "DEVICE_CONFIG" ); ?>
							</a>
						</li>

					</ul>
					<div class="tab-content mt-3" id="device_configContent">
						<div class="tab-pane fade <?php echo $activeTabIndex == 0 ? "show active" : ""; ?>"
						     id="config_general_tab"
						     role="tabpanel"
						     aria-labelledby="config_general_tab-tab">
							<?php include_once _PAGESDIR_."device_config_tabs/config_general_tab.php"; ?>
						</div>
						<div class="tab-pane fade <?php echo $activeTabIndex == 1 ? "show active" : ""; ?>"
						     id="config_network_tab"
						     role="tabpanel"
						     aria-labelledby="config_network_tab-tab">
							<?php include_once _PAGESDIR_."device_config_tabs/config_network_tab.php"; ?>
						</div>
						<div class="tab-pane fade <?php echo $activeTabIndex == 2 ? "show active" : ""; ?>"
							 id="config_mqtt_tab"
							 role="tabpanel"
							 aria-labelledby="config_mqtt_tab-tab">
							<?php include_once _PAGESDIR_ . "device_config_tabs/config_mqtt_tab.php"; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<script src="<?php echo UrlHelper::JS("device_config"); ?>"></script>