<form class='center config-form' name='device_config_network' method='post'>
	<input type='hidden' name='tab-index' value='1'>
	<div class="form-group col">
		<label for="Hostname">
			<?php echo __( "CONFIG_HOSTNAME", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="Hostname"
		       name='Hostname'
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->StatusNET->Hostname )
		                         && !empty( $status->StatusNET->Hostname ) ? $status->StatusNET->Hostname : ""; ?>'
		>
		<small id="HostnameHelp" class="form-text text-muted">
			<?php echo __( "CONFIG_HOSTNAME_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group col">
		<label for="IPAddress1">
			<?php echo __( "CONFIG_IPADDRESS", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="IPAddress1"
		       name='IPAddress1'
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->StatusNET->IPAddress )
		                         && !empty( $status->StatusNET->IPAddress ) ? $status->StatusNET->IPAddress : ""; ?>'
		>
		<small id="IPAddress1Help" class="form-text text-muted">
			<?php echo __( "CONFIG_IPADDRESS_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group col">
		<label for="IPAddress2">
			<?php echo __( "CONFIG_GATEWAY", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="IPAddress2"
		       name='IPAddress2'
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->StatusNET->Gateway )
		                         && !empty( $status->StatusNET->Gateway ) ? $status->StatusNET->Gateway : ""; ?>'
		>
		<small id="IPAddress2Help" class="form-text text-muted">
			<?php echo __( "CONFIG_GATEWAY_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group col">
		<label for="IPAddress3">
			<?php echo __( "CONFIG_SUBNETMASK", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="IPAddress3"
		       name='IPAddress3'
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->StatusNET->Subnetmask )
		                         && !empty( $status->StatusNET->Subnetmask ) ? $status->StatusNET->Subnetmask : ""; ?>'
		>
		<small id="IPAddress3Help" class="form-text text-muted">
			<?php echo __( "CONFIG_SUBNETMASK_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group col">
		<label for="IPAddress4">
			<?php echo __( "CONFIG_DNSSERVER", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="IPAddress4"
		       name='IPAddress4'
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->StatusNET->DNSServer )
		                         && !empty( $status->StatusNET->DNSServer ) ? $status->StatusNET->DNSServer : ""; ?>'
		>
		<small id="IPAddress4Help" class="form-text text-muted">
			<?php echo __( "CONFIG_DNSSERVER_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group col">
		<label for="Mac">
			<?php echo __( "CONFIG_MACADDRESS", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control "
		       id="Mac"
		       name='' readonly disabled
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->StatusNET->Mac )
		                         && !empty( $status->StatusNET->Mac ) ? $status->StatusNET->Mac : ""; ?>'
		>
		<small id="MacHelp" class="form-text text-muted">
			<?php echo __( "CONFIG_MACADDRESS_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>

	<?php // TODO: Enable again if restart gets fixed https://github.com/arendst/Tasmota/issues/1819 ?>
	<div class="form-group mt-5">
		<label for="NtpServer1">
			<?php echo __( "CONFIG_NTPSERVER", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="NtpServer1"
		       name='NtpServer1'
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->statusNTP->NtpServer1 )
		                         && !empty( $status->statusNTP->NtpServer1 ) ? $status->statusNTP->NtpServer1 : ""; ?>'
		>
		<small id="NtpServer1Help" class="form-text text-muted">
			<?php echo __( "CONFIG_NTPSERVER_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group mt-5">
		<label for="AP" class='mb-0'>
			<?php echo __( "CONFIG_USE_AP", "DEVICE_CONFIG" ); ?>
		</label>
	</div>
	<div class="form-group mt-0">
		<div class="form-check form-check-inline custom-control-inline custom-radio">
			<input class="form-check-input custom-control-input" type="radio" name="AP" id="AP1" value="1"
				<?php echo isset( $status->StatusSTS->Wifi->AP )
				           && !empty( $status->StatusSTS->Wifi->AP )
				           && $status->StatusSTS->Wifi->AP == 1 ? "checked=\"checked\"" : ""; ?>
			>
			<label class="form-check-label custom-control-label" for="AP1">
				<?php echo __( "CONFIG_AP1", "DEVICE_CONFIG" ); ?>
			</label>
		</div>
		<div class="form-check form-check-inline custom-control-inline custom-radio">
			<input class="form-check-input custom-control-input" type="radio" name="AP" id="AP2" value="2"
				<?php echo isset( $status->StatusSTS->Wifi->AP )
				           && !empty( $status->StatusSTS->Wifi->AP )
				           && $status->StatusSTS->Wifi->AP == 2 ? "checked=\"checked\"" : ""; ?>
			>
			<label class="form-check-label custom-control-label" for="AP2">
				<?php echo __( "CONFIG_AP2", "DEVICE_CONFIG" ); ?>
			</label>
		</div>
		<small id="APHelp" class="form-text text-muted">
			<?php echo __( "CONFIG_USE_AP_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group col">
		<label for="SSId1">
			<?php echo __( "CONFIG_SSID1", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="SSId1"
		       name='SSId1'
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->StatusLOG->SSId1 )
		                         && !empty( $status->StatusLOG->SSId1 ) ? $status->StatusLOG->SSId1 : ""; ?>'
		>
		<small id="SSId1Help" class="form-text text-muted">
			<?php echo __( "CONFIG_SSID1_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group col">
		<label for="Password1">
			<?php echo __( "CONFIG_SSID1PW", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="Password1"
		       name='Password1'
		       placeholder=""
		       value=''
		>
		<small id="Password1Help" class="form-text text-muted">
			<?php echo __( "CONFIG_SSID1PW_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group col">
		<label for="SSId2">
			<?php echo __( "CONFIG_SSID2", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="SSId2"
		       name='SSId2'
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->StatusLOG->SSId2 )
		                         && !empty( $status->StatusLOG->SSId2 ) ? $status->StatusLOG->SSId2 : ""; ?>'
		>
		<small id="SSId2Help" class="form-text text-muted">
			<?php echo __( "CONFIG_SSID2_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group col">
		<label for="Password2">
			<?php echo __( "CONFIG_SSID2PW", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="Password2"
		       name='Password2'
		       placeholder=""
		       value=''
		>
		<small id="Password2Help" class="form-text text-muted">
			<?php echo __( "CONFIG_SSID2PW_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group col">
		<label for="WifiConfig">
			<?php echo __( "CONFIG_WIFICONFIG", "DEVICE_CONFIG" ); ?>
		</label>
		<select class="form-control custom-select" id="WifiConfig" name='WifiConfig'>
			<option value='0' <?php echo isset( $status->StatusNET->WifiConfig )
			                             && $status->StatusNET->WifiConfig == 0 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_WIFICONFIG_OPTION_0", "DEVICE_CONFIG" ); ?>
			</option>
			<option value='1' <?php echo isset( $status->StatusNET->WifiConfig )
			                             && $status->StatusNET->WifiConfig == 1 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_WIFICONFIG_OPTION_1", "DEVICE_CONFIG" ); ?>
			</option>
			<option value='2' <?php echo isset( $status->StatusNET->WifiConfig )
			                             && $status->StatusNET->WifiConfig == 2 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_WIFICONFIG_OPTION_2", "DEVICE_CONFIG" ); ?>
			</option>
			<option value='3' <?php echo isset( $status->StatusNET->WifiConfig )
			                             && $status->StatusNET->WifiConfig == 3 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_WIFICONFIG_OPTION_3", "DEVICE_CONFIG" ); ?>
			</option>
			<option value='4' <?php echo isset( $status->StatusNET->WifiConfig )
			                             && $status->StatusNET->WifiConfig == 4 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_WIFICONFIG_OPTION_4", "DEVICE_CONFIG" ); ?>
			</option>
			<option value='5' <?php echo isset( $status->StatusNET->WifiConfig )
			                             && $status->StatusNET->WifiConfig == 5 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_WIFICONFIG_OPTION_5", "DEVICE_CONFIG" ); ?>
			</option>
		</select>
		<small id="WifiConfigHelp" class="form-text text-muted">
			<?php echo __( "CONFIG_WIFICONFIG_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>

	<div class="row mt-5">
		<div class="col col-12">
			<div class="text-right">
				<button type='submit' class='btn btn-primary ' name='save' value='submit'>
					<?php echo __( "BTN_SAVE_DEVICE_CONFIG", "DEVICE_CONFIG" ); ?>
				</button>
			</div>
		</div>
	</div>
</form>