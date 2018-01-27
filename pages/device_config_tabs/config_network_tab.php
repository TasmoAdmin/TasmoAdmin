<form class='center' name='device_config_network' method='post'>
	<input type='hidden' name='tab-index' value='1'>
	
	<table border='0' cellspacing='0' class='center-table' style='text-align: left;'>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_HOSTNAME_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_HOSTNAME", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='Hostname'
				       value='<?php echo isset( $status->StatusNET->Hostname )
				                         && !empty( $status->StatusNET->Hostname ) ? $status->StatusNET->Hostname
					       : ""; ?>'><br/>
			</td>
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_IPADDRESS_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_IPADDRESS", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='IPAddress1'
				       value='<?php echo isset( $status->StatusNET->IPAddress )
				                         && !empty( $status->StatusNET->IPAddress ) ? $status->StatusNET->IPAddress
					       : ""; ?>'><br/>
			</td>
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_GATEWAY_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_GATEWAY", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='IPAddress2'
				       value='<?php echo isset( $status->StatusNET->Gateway )
				                         && !empty( $status->StatusNET->Gateway ) ? $status->StatusNET->Gateway
					       : ""; ?>'><br/>
			</td>
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_SUBNETMASK_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_SUBNETMASK", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='IPAddress3'
				       value='<?php echo isset( $status->StatusNET->Subnetmask )
				                         && !empty( $status->StatusNET->Subnetmask ) ? $status->StatusNET->Subnetmask
					       : ""; ?>'><br/>
			</td>
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_DNSSERVER_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_DNSSERVER", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='IPAddress4'
				       value='<?php echo isset( $status->StatusNET->DNSServer )
				                         && !empty( $status->StatusNET->DNSServer ) ? $status->StatusNET->DNSServer
					       : ""; ?>'><br/>
			</td>
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_MACADDRESS_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_MACADDRESS", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       readonly
				       value='<?php echo isset( $status->StatusNET->Mac )
				                         && !empty( $status->StatusNET->Mac ) ? $status->StatusNET->Mac : ""; ?>'><br/>
			</td>
		</tr>
		<tr>
			<td colspan='2'>
				<hr/>
			</td>
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_NTPSERVER_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_NTPSERVER", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='NtpServer1'
				       value='<?php echo isset( $status->statusNTP->NtpServer1 )
				                         && !empty( $status->statusNTP->NtpServer1 ) ? $status->statusNTP->NtpServer1
					       : ""; ?>'>
			</td>
		</tr>
		<tr>
			<td colspan='2'>
				<hr/>
			</td>
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_USE_AP_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_USE_AP", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='radio' name='AP' value='1' id='AP1'
					<?php echo isset( $status->StatusSTS->Wifi->AP )
					           && !empty( $status->StatusSTS->Wifi->AP )
					           && $status->StatusSTS->Wifi->AP == 1 ? "checked=\"checked\"" : ""; ?>
				>
				<label for='AP1'><?php echo __( "CONFIG_AP1", "DEVICE_CONFIG" ); ?></label>
				<span style='width: 10%;  display: inline-block;'>&nbsp;</span>
				<input type='radio' name='AP' value='2' id='AP2'
					<?php echo isset( $status->StatusSTS->Wifi->AP )
					           && !empty( $status->StatusSTS->Wifi->AP )
					           && $status->StatusSTS->Wifi->AP == 2 ? "checked=\"checked\"" : ""; ?>
				>
				<label for='AP2'> <?php echo __( "CONFIG_AP2", "DEVICE_CONFIG" ); ?></label>
			</td>
		
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_SSID1_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_SSID1", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='SSId1'
				       value='<?php echo isset( $status->StatusLOG->SSId1 )
				                         && !empty( $status->StatusLOG->SSId1 ) ? $status->StatusLOG->SSId1 : ""; ?>'>
			</td>
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_SSID1PW_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_SSID1PW", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='Password1'
				       value=''>
			</td>
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_SSID2_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_SSID2", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='SSId2'
				       value='<?php echo isset( $status->StatusLOG->SSId2 )
				                         && !empty( $status->StatusLOG->SSId2 ) ? $status->StatusLOG->SSId2 : ""; ?>'>
			</td>
		</tr>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_SSID2PW_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_SSID2PW", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
				</p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='Password2'
				       value=''>
			</td>
		</tr>
		<tr>
			<td colspan='2'>
				<hr/>
			</td>
		</tr>
		
		
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_WIFICONFIG_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_WIFICONFIG", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> : <br/>
				</p>
			</td>
			<td>
				<select name='WifiConfig' class='config select'>
					<option value='0' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 0 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_WIFICONFIG_OPTION_0", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='1' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 1 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_WIFICONFIG_OPTION_1", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='2' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 2 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_WIFICONFIG_OPTION_2", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='3' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 3 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_WIFICONFIG_OPTION_3", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='4' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 4 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_WIFICONFIG_OPTION_4", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='5' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 5 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_WIFICONFIG_OPTION_5", "DEVICE_CONFIG" ); ?>
					</option>
				</select><br/>
			</td>
		</tr>
		
		
		<tr>
			<td colspan='2'>&nbsp;</td>
		</tr>
		<tr>
			<td colspan='2'>
				<button type='submit' class='btn widget' name='save' value='submit'>
					<?php echo __( "BTN_SAVE_DEVICE_CONFIG", "DEVICE_CONFIG" ); ?>
				</button>
			</td>
		</tr>
	</table>
</form>