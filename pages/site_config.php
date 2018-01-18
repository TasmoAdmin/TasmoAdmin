<?php
	$msg = FALSE;
	if ( isset( $_POST ) && !empty( $_POST ) ) {
		if ( isset( $_POST[ "save" ] ) ) {
			$settings = $_POST;
			unset( $settings[ "save" ] );
			
			if ( !isset( $settings[ "password" ] ) || empty( $settings[ "password" ] )
			     || $settings[ "password" ] == "" ) {
				unset( $settings[ "password" ] );
				unset( $settings[ "username" ] );
			} else {
				$settings[ "password" ] = md5( $settings[ "password" ] );
			}
			foreach ( $settings as $settingKey => $settingVal ) {
				$Config->write( $settingKey, $settingVal );
			}
			$msg = __( "MSG_USER_CONFIG_SAVED", "USER_CONFIG" );
		}
	}
	
	$config = $Config->readAll();
	unset( $config[ "password" ] )

?>

<form class='center' name='web_config' method='post'>
	<p>
		<?php echo $msg ? $msg : ""; ?>
	</p>
	<table border='0' cellspacing='0' class='center-table'>
		<tr>
			<td>
				<?php echo __( "CONFIG_USERNAME", "USER_CONFIG" ); ?>:<br/><br/>
			</td>
			<td>
				<input type='text' name='username' value='<?php echo $config[ "username" ]; ?>'><br/><br/>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __( "CONFIG_PASSWORD", "USER_CONFIG" ); ?>:<br/><br/>
			</td>
			<td>
				<input type='password' name='password' value='' autocomplete="off"><br/><br/>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __( "CONFIG_SERVER_IP", "USER_CONFIG" ); ?>:<br/><br/>
			</td>
			<td>
				<input type='text' name='ota_server_ip' value='<?php echo $config[ "ota_server_ip" ]; ?>'><br/><br/>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __( "CONFIG_AUTOMATIC_LANG", "USER_CONFIG" ); ?>:<br/><br/>
			</td>
			<td>
				<select name='update_automatic_lang'>
					<?php if ( $config[ "update_automatic_lang" ] == "" ): ?>
						<option><?php echo __( "PLEASE_SELECT" ); ?></option>
					<?php endif; ?>
					
					<option value='EN' <?php echo $config[ "update_automatic_lang" ] == "EN" ? "selected=\selected\""
						: ""; ?>><?php echo __( "CONFIG_AUTOMATIC_LANGAUGE_EN", "USER_CONFIG" ); ?>
					</option>
					<option value='DE' <?php echo $config[ "update_automatic_lang" ] == "DE" ? "selected=\selected\""
						: ""; ?>><?php echo __( "CONFIG_AUTOMATIC_LANGAUGE_DE", "USER_CONFIG" ); ?>
					</option>
					<option value='IT' <?php echo $config[ "update_automatic_lang" ] == "IT" ? "selected=\selected\""
						: ""; ?>><?php echo __( "CONFIG_AUTOMATIC_LANGAUGE_IT", "USER_CONFIG" ); ?>
					</option>
					<option value='NL' <?php echo $config[ "update_automatic_lang" ] == "NL" ? "selected=\selected\""
						: ""; ?>><?php echo __( "CONFIG_AUTOMATIC_LANGAUGE_NL", "USER_CONFIG" ); ?>
					</option>
					<option value='PL' <?php echo $config[ "update_automatic_lang" ] == "PL" ? "selected=\selected\""
						: ""; ?>><?php echo __( "CONFIG_AUTOMATIC_LANGAUGE_PL", "USER_CONFIG" ); ?>
					</option>
				
				</select><br/><br/>
			</td>
		</tr>
		<tr>
			<td>&nbsp;
				<?php echo __( "CONFIG_REFRESHTIME", "USER_CONFIG" ); ?>:<br/><br/>
			</td>
			<td>
				<select name='refreshtime'>
					<option value='none' <?php echo $config[ "refreshtime" ] == "none" ? "selected=\selected\""
						: ""; ?>><?php echo __( "CONFIG_REFRESHTIME_NONE", "USER_CONFIG" ); ?>
					</option>
					<option value='1' <?php echo $config[ "refreshtime" ] == "1" ? "selected=\selected\"" : ""; ?> >
						1 <?php echo __( "CONFIG_REFRESHTIME_SECOND", "USER_CONFIG" ); ?>
					</option>
					<option value='2' <?php echo $config[ "refreshtime" ] == "2" ? "selected=\selected\"" : ""; ?> >
						2 <?php echo __( "CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG" ); ?>
					</option>
					<option value='3' <?php echo $config[ "refreshtime" ] == "3" ? "selected=\selected\"" : ""; ?> >
						3 <?php echo __( "CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG" ); ?>
					</option>
					<option value='4' <?php echo $config[ "refreshtime" ] == "4" ? "selected=\selected\"" : ""; ?> >
						4 <?php echo __( "CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG" ); ?>
					</option>
					<option value='5' <?php echo $config[ "refreshtime" ] == "5" ? "selected=\selected\"" : ""; ?> >
						5 <?php echo __( "CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG" ); ?>
					</option>
					<option value='10' <?php echo $config[ "refreshtime" ] == "10" ? "selected=\selected\"" : ""; ?> >
						10 <?php echo __( "CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG" ); ?>
					</option>
					<option value='15' <?php echo $config[ "refreshtime" ] == "15" ? "selected=\selected\"" : ""; ?> >
						15 <?php echo __( "CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG" ); ?>
					</option>
					<option value='30' <?php echo $config[ "refreshtime" ] == "30" ? "selected=\selected\"" : ""; ?> >
						30 <?php echo __( "CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG" ); ?>
					</option>
					<option value='60' <?php echo $config[ "refreshtime" ] == "60" ? "selected=\selected\"" : ""; ?> >
						60 <?php echo __( "CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG" ); ?>
					</option>
				</select><br/><br/>
			</td>
		</tr>
		<tr>
			<td colspan='2'>&nbsp;</td>
		</tr>
		<tr>
			<td colspan='2'>
				<button type='submit' class='btn' name='save' value='submit'><?php echo __(
						"BTN_SAVE_USER_CONFIG",
						"USER_CONFIG"
					); ?></button>
			</td>
		</tr>
	</table>
</form>
