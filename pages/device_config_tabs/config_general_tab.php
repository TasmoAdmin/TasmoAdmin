<form class='center' name='device_config_general' method='post'>
	<input type='hidden' name='tab-index' value='0'>
	<div class="form-group">
		<label for="FriendlyName">
			<?php echo __( "CONFIG_FRIENDLYNAME", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="text"
		       class="form-control"
		       id="FriendlyName"
		       name='FriendlyName'
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->Status->FriendlyName )
		                         && !empty( $status->Status->FriendlyName ) ? $status->Status->FriendlyName : ""; ?>'
		>
		<small id="FriendlyNameHelp" class="form-text text-muted">
			<?php echo __( "CONFIG_FRIENDLYNAME_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group">
		<label for="PowerOnState">
			<?php echo __( "CONFIG_POWERONSTATE", "DEVICE_CONFIG" ); ?>
		</label>
		<select class="form-control custom-select" id="PowerOnState" name='PowerOnState'>
			<option value='0' <?php echo isset( $status->Status->PowerOnState )
			                             && $status->Status->PowerOnState == 0 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_POWERONSTATE_OPTION_0", "DEVICE_CONFIG" ); ?>
			</option>
			<option value='1' <?php echo isset( $status->Status->PowerOnState )
			                             && $status->Status->PowerOnState == 1 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_POWERONSTATE_OPTION_1", "DEVICE_CONFIG" ); ?>
			</option>
			<option value='2' <?php echo isset( $status->Status->PowerOnState )
			                             && $status->Status->PowerOnState == 2 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_POWERONSTATE_OPTION_2", "DEVICE_CONFIG" ); ?>
			</option>
			<option value='3' <?php echo isset( $status->Status->PowerOnState )
			                             && $status->Status->PowerOnState == 3 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_POWERONSTATE_OPTION_3", "DEVICE_CONFIG" ); ?>
			</option>
			<option value='4' <?php echo isset( $status->Status->PowerOnState )
			                             && $status->Status->PowerOnState == 4 ? "selected=\selected\"" : ""; ?>>
				<?php echo __( "CONFIG_POWERONSTATE_OPTION_4", "DEVICE_CONFIG" ); ?>
			</option>
		</select>
		<small id="PowerOnStateHelp" class="form-text text-muted">
			<?php echo __( "CONFIG_POWERONSTATE_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group">
		<label for="LedState">
			<?php echo __( "CONFIG_LEDSTATE", "DEVICE_CONFIG" ); ?>
		</label>
		<select class="form-control custom-select" id="LedState" name='LedState'>
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
		</select>
		<small id="LedStateHelp" class="form-text text-muted">
			<?php echo __( "CONFIG_LEDSTATE", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	<div class="form-group">
		<label for="Sleep">
			<?php echo __( "CONFIG_SLEEP", "DEVICE_CONFIG" ); ?>
		</label>
		<input type="number"
		       class="form-control"
		       id="Sleep"
		       name='Sleep'
		       step='25' max='250' min='0' pattern="\d{1,3}"
		       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
		       value='<?php echo isset( $status->StatusPRM->Sleep )
		                         && !empty( $status->StatusPRM->Sleep ) ? $status->StatusPRM->Sleep : ""; ?>'
		>
		<small id="SleepHelp" class="form-text text-muted">
			<?php echo __( "CONFIG_SLEEP_HELP", "DEVICE_CONFIG" ); ?>
		</small>
	</div>
	
	<div class="row mt-5">
		<div class="col-12">
			<div class="text-right">
				<button type='submit' class='btn btn-primary ' name='save' value='submit'>
					<?php echo __( "BTN_SAVE_DEVICE_CONFIG", "DEVICE_CONFIG" ); ?>
				</button>
			</div>
		</div>
	</div>
</form>