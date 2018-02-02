<form class='center' name='device_config_general' method='post'>
	<input type='hidden' name='tab-index' value='0'>
	
	<table border='0' cellspacing='0' class='center-table' style='text-align: left;'>
		<tr>
			<td>
				<p class='label' title='<?php echo __( "CONFIG_FRIENDLYNAME_HELP", "DEVICE_CONFIG" ); ?>'><?php echo __(
						"CONFIG_FRIENDLYNAME",
						"DEVICE_CONFIG"
					); ?> <i class='fas fa-info-circle'></i> :</p>
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
				<p class='label' title='<?php echo __( "CONFIG_POWERONSTATE_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_POWERONSTATE", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> :
					<br/>
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
				<p class='label' title='<?php echo __( "CONFIG_LEDSTATE", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_LEDSTATE", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> : <br/>
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
				<p class='label' title='<?php echo __( "CONFIG_SLEEP_HELP", "DEVICE_CONFIG" ); ?>'>
					<?php echo __( "CONFIG_SLEEP", "DEVICE_CONFIG" ); ?> <i class='fas fa-info-circle'></i> : <br/>
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