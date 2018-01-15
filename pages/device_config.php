<?php
	$msg    = FALSE;
	$device = NULL;
	
	if ( isset( $_GET[ "device_id" ] ) ) {
		$file = fopen( $filename, 'r' );
		while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
			//$line is an array of the csv elements
			//var_dump( $line );
			if ( $line[ 0 ] == $_GET[ "device_id" ] ) {
				$line[ 1 ] = explode( "|", $line[ 1 ] );
				$device    = $line;
				break;
			}
		}
		fclose( $file );
	} else {
		$msg = __( "ERROR_NO_DEVICE_SELECTED", "DEVICE_CONFIG" );
	}
	
	if ( isset( $_POST ) && !empty( $_POST ) ) {
		if ( isset( $_POST[ "save" ] ) ) {
			$settings = $_POST;
			unset( $settings[ "save" ] );
			
			$backlog = "Backlog ";
			foreach ( $settings as $settingKey => $settingVal ) {
				$backlog .= $settingKey." ".$settingVal.";";
			}
			$result = $Sonoff->saveConfig( $device[ 2 ], $backlog );
			$msg    = __( "MSG_CONFIG_SAVED", "DEVICE_CONFIG" );
			sleep( count( $settings ) );
		}
		
		
	}
	
	$status = $Sonoff->getAllStatus( $device[ 2 ] );


?>

<form class='center' name='web_config' method='post'>
	<p>
		<?php echo $msg ? $msg : ""; ?>
	</p>
	<table border='0' cellspacing='0' class='center-table' style='text-align: left;'>
		<tr>
			<td>
				<p title='<?php echo __( "CONFIG_FRIENDLYNAME_HELP", "DEVICE_CONFIG" ); ?>'><?php echo __(
						"CONFIG_FRIENDLYNAME",
						"DEVICE_CONFIG"
					); ?>:<br/></p>
			</td>
			<td>
				<input type='text'
				       class='config text'
				       name='FriendlyName'
				       value='<?php echo isset( $status->Status->FriendlyName )
				                         && !empty( $status->Status->FriendlyName ) ? $status->Status->FriendlyName
					       : ""; ?>'><br/>
			</td>
		</tr>
		
		<tr>
			<td>
				<p title='<?php echo __( "CONFIG_POWERONSTATE_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_POWERONSTATE", "DEVICE_CONFIG" ); ?>: <br/>
				</p>
			</td>
			<td>
				<select name='PowerOnState' class='config select'>
					<option value='0' <?php echo isset( $status->Status->PowerOnState )
					                             && $status->Status->PowerOnState == 0 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_POWERONSTATE_OPTION_0", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='1' <?php echo isset( $status->Status->PowerOnState )
					                             && $status->Status->PowerOnState == 1 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_POWERONSTATE_OPTION_1", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='2' <?php echo isset( $status->Status->PowerOnState )
					                             && $status->Status->PowerOnState == 2 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_POWERONSTATE_OPTION_2", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='3' <?php echo isset( $status->Status->PowerOnState )
					                             && $status->Status->PowerOnState == 3 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_POWERONSTATE_OPTION_3", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='4' <?php echo isset( $status->Status->PowerOnState )
					                             && $status->Status->PowerOnState == 4 ? "selected=\selected\""
						: ""; ?>>
						<?php echo __( "CONFIG_POWERONSTATE_OPTION_4", "DEVICE_CONFIG" ); ?>
					</option>
				
				</select><br/>
			</td>
		</tr>
		<tr>
			<td>
				<p title='<?php echo __( "CONFIG_LEDSTATE", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_LEDSTATE", "DEVICE_CONFIG" ); ?>: <br/>
				</p>
			</td>
			<td>
				<select name='LedState' class='config select'>
					<option value='0' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 0 ? "selected=\selected\"" : ""; ?>>
						<?php echo __( "CONFIG_LEDSTATE_OPTION_0", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='1' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 1 ? "selected=\selected\"" : ""; ?>>
						<?php echo __( "CONFIG_LEDSTATE_OPTION_1", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='2' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 2 ? "selected=\selected\"" : ""; ?>>
						<?php echo __( "CONFIG_LEDSTATE_OPTION_2", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='3' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 3 ? "selected=\selected\"" : ""; ?>>
						<?php echo __( "CONFIG_LEDSTATE_OPTION_3", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='4' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 4 ? "selected=\selected\"" : ""; ?>>
						<?php echo __( "CONFIG_LEDSTATE_OPTION_4", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='5' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 5 ? "selected=\selected\"" : ""; ?>>
						<?php echo __( "CONFIG_LEDSTATE_OPTION_5", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='6' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 6 ? "selected=\selected\"" : ""; ?>>
						<?php echo __( "CONFIG_LEDSTATE_OPTION_6", "DEVICE_CONFIG" ); ?>
					</option>
					<option value='7' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 7 ? "selected=\selected\"" : ""; ?>>
						<?php echo __( "CONFIG_LEDSTATE_OPTION_7", "DEVICE_CONFIG" ); ?>
					</option>
				</select><br/>
			</td>
		</tr>
		
		<tr>
			<td>
				<p title='<?php echo __( "CONFIG_SLEEP_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_SLEEP", "DEVICE_CONFIG" ); ?>: <br/>
				</p>
			</td>
			<td>
				<input type='number' step='25' max='250' min='0' pattern="\d{1,3}"
				       class='config text'
				       name='Sleep'
				       value='<?php echo isset( $status->StatusPRM->Sleep )
				                         && !empty( $status->StatusPRM->Sleep ) ? $status->StatusPRM->Sleep : ""; ?>'>
				<br/>
			</td>
		</tr>
		
		<tr>
			<td>
				<p title='<?php echo __( "CONFIG_WIFICONFIG_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_WIFICONFIG", "DEVICE_CONFIG" ); ?>: <br/>
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
				<button type='submit' class='btn' name='save' value='submit'>
					<?php echo __( "BTN_SAVE_DEVICE_CONFIG", "DEVICE_CONFIG" ); ?>
				</button>
			</td>
		</tr>
	</table>
</form>
