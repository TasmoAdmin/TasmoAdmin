<?php
	$msg = FALSE;
	if( isset( $_REQUEST ) && !empty( $_REQUEST ) ) {
		if( isset( $_REQUEST[ "save" ] ) ) {
			$settings = $_REQUEST;
			unset( $settings[ "save" ] );

			if( !isset( $settings[ "login" ] ) ) {
				$settings[ "login" ] = "0";
			}

			if( !isset( $settings[ "password" ] ) || empty( $settings[ "password" ] )
			    || $settings[ "password" ] == ""
			    || $settings[ "login" ] == "0" ) {
				unset( $settings[ "password" ] );
				unset( $settings[ "username" ] );
			} else {
				$settings[ "password" ] = md5( $settings[ "password" ] );
			}


			foreach( $settings as $settingKey => $settingVal ) {
				$Config->write( $settingKey, $settingVal );
			}
			$msg = __( "MSG_USER_CONFIG_SAVED", "USER_CONFIG" );
		}
	}

	$config = $Config->readAll();

?>


<div class='row justify-content-sm-center'>
	<div class='col-12 col-md-8 col-xl-6'>
		<h2 class='text-sm-center mb-5'>
			<?php echo $title; ?>
		</h2>
		<?php if( isset( $msg ) && $msg != "" ): ?>
			<div class="alert alert-success alert-dismissible fade show mb-5" data-dismiss="alert" role="alert">
				<?php echo $msg; ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		<?php endif; ?>
		<form name='web_config' method='post'>
			<div class="form-check custom-control custom-checkbox mb-3">
				<input class="form-check-input custom-control-input"
				       type="checkbox"
				       value="1"
				       id="cb_login"
				       name='login' <?php echo $config[ "login" ] == "1" ? "checked=\"checked\"" : ""; ?>>
				<label class="form-check-label custom-control-label" for="cb_login">
					<?php echo __( "CONFIG_LOGIN_ENABLE", "USER_CONFIG" ); ?>
				</label>
			</div>
			<div class="form-group">
				<label for="username">
					<?php echo __( "CONFIG_USERNAME", "USER_CONFIG" ); ?>
				</label>
				<input type="text"
				       class="form-control"
				       id="username"
				       name='username'
				       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
				       value='<?php echo $config[ "username" ]; ?>'
				>
			</div>
			<div class="form-group">
				<label for="password">
					<?php echo __( "CONFIG_PASSWORD", "USER_CONFIG" ); ?>
				</label>
				<input type="password"
				       class="form-control"
				       id="password"
				       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
				       name='password'
				       value=''
				       autocomplete="off">
			</div>
			<div class="form-group">
				<label for="homepage">
					<?php echo __( "CONFIG_HOMEPAGE", "USER_CONFIG" ); ?>
				</label>
				<select class="form-control custom-select" id="homepage" name='homepage'>
					<option value='start'
						<?php echo $config[ "homepage" ] == "start" ? "selected=\selected\"" : ""; ?>
					>
						<?php echo __( "CONFIG_HOMEPAGE_START", "USER_CONFIG" ); ?>
					</option>
					<option value='devices'
						<?php echo $config[ "homepage" ] == "devices" ? "selected=\selected\"" : ""; ?>
					>
						<?php echo __( "CONFIG_HOMEPAGE_DEVICES", "USER_CONFIG" ); ?>
					</option>

				</select>
			</div>


			<div class="form-row  mt-5">
				<div class="form-group col-12 col-sm-8">
					<label for="ota_server_ip">
						<?php echo __( "CONFIG_SERVER_IP", "USER_CONFIG" ); ?>
					</label>
					<input type="text"
					       class="form-control"
					       id="ota_server_ip"
					       name='ota_server_ip'
					       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
					       value='<?php echo $config[ "ota_server_ip" ]; ?>'
					>
					<small id="from_ipHelp" class="form-text text-muted">
						<?php echo __( "CONFIG_SERVER_IP_HELP", "USER_CONFIG" ); ?>
					</small>
				</div>
				<div class="form-group col-12 col-sm-4">
					<label for="ota_server_port">
						<?php echo __( "CONFIG_SERVER_PORT", "USER_CONFIG" ); ?>
					</label>
					<input type="text"
					       class="form-control"
					       id="ota_server_port"
					       name='ota_server_port'
					       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
					       value='<?php echo !empty( $config[ "ota_server_port" ] ) ? $config[ "ota_server_port" ]
						       : $_SERVER[ "SERVER_PORT" ]; ?>'
					>
					<small id="from_ipHelp" class="form-text text-muted">
						<?php echo __( "CONFIG_SERVER_PORT_HELP", "USER_CONFIG" ); ?>
					</small>
				</div>
			</div>


			<div class="form-group">
				<label for="update_automatic_lang">
					<?php echo __( "CONFIG_AUTOMATIC_LANG", "USER_CONFIG" ); ?>
				</label>
				<select class="form-control custom-select" id="update_automatic_lang" name='update_automatic_lang'>
					<?php if( $config[ "update_automatic_lang" ] == "" ): ?>
						<option><?php echo __( "PLEASE_SELECT" ); ?></option>
					<?php endif; ?>
					<option value='CN' <?php echo $config[ "update_automatic_lang" ] == "CN" ? "selected=\selected\""
						: ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_CN",
							"USER_CONFIG"
						); ?>
					</option>
					<option value='DE' <?php echo $config[ "update_automatic_lang" ] == "DE" ? "selected=\selected\""
						: ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_DE",
							"USER_CONFIG"
						); ?>
					</option>
					<option value='ds18x20' <?php echo $config[ "update_automatic_lang" ] == "ds18x20"
						? "selected=\selected\"" : ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_DS18X20",
							"USER_CONFIG"
						); ?>
					</option>
					<option value='EN' <?php echo $config[ "update_automatic_lang" ] == "EN" ? "selected=\selected\""
						: ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_EN",
							"USER_CONFIG"
						); ?>
					</option>
					<option value='ES' <?php echo $config[ "update_automatic_lang" ] == "ES" ? "selected=\selected\""
						: ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_ES",
							"USER_CONFIG"
						); ?>
					</option>
					<option value='FR' <?php echo $config[ "update_automatic_lang" ] == "FR" ? "selected=\selected\""
						: ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_FR",
							"USER_CONFIG"
						); ?>
					</option>
					<option value='HU' <?php echo $config[ "update_automatic_lang" ] == "HU" ? "selected=\selected\""
						: ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_HU",
							"USER_CONFIG"
						); ?> (next release version!)
					</option>
					<option value='IT' <?php echo $config[ "update_automatic_lang" ] == "IT" ? "selected=\selected\""
						: ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_IT",
							"USER_CONFIG"
						); ?>
					</option>
					<option value='NL' <?php echo $config[ "update_automatic_lang" ] == "NL" ? "selected=\selected\""
						: ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_NL",
							"USER_CONFIG"
						); ?>
					</option>
					<option value='PL' <?php echo $config[ "update_automatic_lang" ] == "PL" ? "selected=\selected\""
						: ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_PL",
							"USER_CONFIG"
						); ?>
					</option>
					<option value='RU' <?php echo $config[ "update_automatic_lang" ] == "PL" ? "selected=\selected\""
						: ""; ?>><?php echo __(
							"CONFIG_AUTOMATIC_LANGAUGE_RU",
							"USER_CONFIG"
						); ?> (next release version!)
					</option>
				</select>
			</div>
			<div class="form-group mt-5">
				<label for="refreshtime"><?php echo __( "CONFIG_REFRESHTIME", "USER_CONFIG" ); ?></label>
				<select class="form-control custom-select" id="refreshtime" name='refreshtime'>
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
					<option value='8' <?php echo $config[ "refreshtime" ] == "8" ? "selected=\selected\"" : ""; ?> >
						8 <?php echo __( "CONFIG_REFRESHTIME_SECONDS", "USER_CONFIG" ); ?>
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
				</select>
			</div>
			<div class="form-group">
				<label for="nightmode">
					<?php echo __( "CONFIG_NIGHTMODE", "USER_CONFIG" ); ?>
				</label>
				<select class="form-control custom-select" id="nightmode" name='nightmode'>
					<option value='disable' <?php echo $config[ "nightmode" ] == "disable" ? "selected=\"selected\""
						: ""; ?>><?php echo __(
							"CONFIG_NIGHTMODE_DISABLE",
							"USER_CONFIG"
						); ?>
					</option>
					<option value='always' <?php echo $config[ "nightmode" ] == "always" ? "selected=\"selected\""
						: ""; ?> >
						<?php echo __( "CONFIG_NIGHTMODE_ALWAYS", "USER_CONFIG" ); ?>
					</option>
					<option value='auto' <?php echo $config[ "nightmode" ] == "auto" ? "selected=\"selected\""
						: ""; ?> >
						<?php echo __( "CONFIG_NIGHTMODE_AUTO", "USER_CONFIG" ); ?>
					</option>
				</select>
			</div>
			<div class="row mt-5">
				<div class="col-12">
					<div class="text-right">
						<button type='submit' class='btn btn-primary ' name='save' value='submit'>
							<?php echo __( "BTN_SAVE_USER_CONFIG", "USER_CONFIG" ); ?>
						</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
