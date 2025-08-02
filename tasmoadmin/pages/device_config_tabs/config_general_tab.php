<form class='center config-form' name='device_config_general' method='post'>
	<input type='hidden' name='tab-index' value='0'>

	<?php if (is_array($status->Status->FriendlyName)) { // array since >= 5.12.0h?>
		<div class="row mt-5">
			<?php foreach ($status->Status->FriendlyName as $key => $friendlyName) { ?>
								<div class="col col-12 <?php echo (1 == count($status->Status->FriendlyName)) ? 'col-sm-12'
                    : 'col-sm-6'; ?>">
					<div class="col">
						<?php

			    ?>
						<label for="FriendlyName">
							<?php echo __('CONFIG_FRIENDLYNAME', 'DEVICE_CONFIG').' ('.($key + 1).')'; ?>
						</label>
						<input type="text"
						       class="form-control"
						       id="FriendlyName"
						       name='FriendlyName<?php echo $key + 1; ?>'
						       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
						       value='<?php echo $friendlyName; ?>'
						>
						<small id="FriendlyNameHelp" class="text-muted">
							<?php echo __('CONFIG_FRIENDLYNAME_HELP', 'DEVICE_CONFIG'); ?>
						</small>
					</div>
				</div>
			<?php } ?>
		</div>

	<?php } else { // only one friendlyname was editable v < 5.12.0h?>
		<div class="col">
			<?php
			    $friendlyName = is_array($status->Status->FriendlyName) // array since 5.12.0h
			        ? $status->Status->FriendlyName[0] : $status->Status->FriendlyName;
	    ?>
			<label for="FriendlyName">
				<?php echo __('CONFIG_FRIENDLYNAME', 'DEVICE_CONFIG'); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="FriendlyName"
			       name='FriendlyName'
			       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
			       value='<?php echo $friendlyName; ?>'
			>
			<small id="FriendlyNameHelp" class="text-muted">
				<?php echo __('CONFIG_FRIENDLYNAME_HELP', 'DEVICE_CONFIG'); ?>
			</small>
		</div>
	<?php } // END only one friendlyname was editable v < 5.12.0h?>

	<div class="col">
		<label for="PowerOnState" class="form-label">
			<?php echo __('CONFIG_POWERONSTATE', 'DEVICE_CONFIG'); ?>
		</label>
		<select class="form-control form-select" id="PowerOnState" name='PowerOnState'>
			<option value='0' <?php echo isset($status->Status->PowerOnState)
	                                 && 0 == $status->Status->PowerOnState ? 'selected="selected"' : ''; ?>>
				<?php echo __('CONFIG_POWERONSTATE_OPTION_0', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='1' <?php echo isset($status->Status->PowerOnState)
	                                 && 1 == $status->Status->PowerOnState ? 'selected="selected"' : ''; ?>>
				<?php echo __('CONFIG_POWERONSTATE_OPTION_1', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='2' <?php echo isset($status->Status->PowerOnState)
	                                 && 2 == $status->Status->PowerOnState ? 'selected="selected"' : ''; ?>>
				<?php echo __('CONFIG_POWERONSTATE_OPTION_2', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='3' <?php echo isset($status->Status->PowerOnState)
	                                 && 3 == $status->Status->PowerOnState ? 'selected="selected"' : ''; ?>>
				<?php echo __('CONFIG_POWERONSTATE_OPTION_3', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='4' <?php echo isset($status->Status->PowerOnState)
	                                 && 4 == $status->Status->PowerOnState ? 'selected="selected"' : ''; ?>>
				<?php echo __('CONFIG_POWERONSTATE_OPTION_4', 'DEVICE_CONFIG'); ?>
			</option>
		</select>
		<small id="PowerOnStateHelp" class="text-muted">
			<?php echo __('CONFIG_POWERONSTATE_HELP', 'DEVICE_CONFIG'); ?>
		</small>
	</div>
	<div class="col">
		<label for="LedState" class="form-label">
			<?php echo __('CONFIG_LEDSTATE', 'DEVICE_CONFIG'); ?>
		</label>
		<select class="form-control form-select" id="LedState" name='LedState'>
			<option value='0' <?php echo isset($status->Status->LedState)
	                                 && 0 == $status->Status->LedState ? 'selected=\selected"' : ''; ?>>
				<?php echo __('CONFIG_LEDSTATE_OPTION_0', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='1' <?php echo isset($status->Status->LedState)
	                                 && 1 == $status->Status->LedState ? 'selected=\selected"' : ''; ?>>
				<?php echo __('CONFIG_LEDSTATE_OPTION_1', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='2' <?php echo isset($status->Status->LedState)
	                                 && 2 == $status->Status->LedState ? 'selected=\selected"' : ''; ?>>
				<?php echo __('CONFIG_LEDSTATE_OPTION_2', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='3' <?php echo isset($status->Status->LedState)
	                                 && 3 == $status->Status->LedState ? 'selected=\selected"' : ''; ?>>
				<?php echo __('CONFIG_LEDSTATE_OPTION_3', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='4' <?php echo isset($status->Status->LedState)
	                                 && 4 == $status->Status->LedState ? 'selected=\selected"' : ''; ?>>
				<?php echo __('CONFIG_LEDSTATE_OPTION_4', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='5' <?php echo isset($status->Status->LedState)
	                                 && 5 == $status->Status->LedState ? 'selected=\selected"' : ''; ?>>
				<?php echo __('CONFIG_LEDSTATE_OPTION_5', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='6' <?php echo isset($status->Status->LedState)
	                                 && 6 == $status->Status->LedState ? 'selected=\selected"' : ''; ?>>
				<?php echo __('CONFIG_LEDSTATE_OPTION_6', 'DEVICE_CONFIG'); ?>
			</option>
			<option value='7' <?php echo isset($status->Status->LedState)
	                                 && 7 == $status->Status->LedState ? 'selected=\selected"' : ''; ?>>
				<?php echo __('CONFIG_LEDSTATE_OPTION_7', 'DEVICE_CONFIG'); ?>
			</option>
		</select>
		<small id="LedStateHelp" class="text-muted">
			<?php echo __('CONFIG_LEDSTATE', 'DEVICE_CONFIG'); ?>
		</small>
	</div>
	<div class="col">
		<label for="Sleep" class="form-label">
			<?php echo __('CONFIG_SLEEP', 'DEVICE_CONFIG'); ?>
		</label>
		<input type="number"
		       class="form-control"
		       id="Sleep"
		       name='Sleep'
		       step='1' max='250' min='0' pattern="\d{1,3}"
		       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
		       value='<?php echo isset($status->StatusPRM->Sleep)
	                         && !empty($status->StatusPRM->Sleep) ? $status->StatusPRM->Sleep : ''; ?>'
		>
		<small id="SleepHelp" class="text-muted">
			<?php echo __('CONFIG_SLEEP_HELP', 'DEVICE_CONFIG'); ?>
		</small>
	</div>

	<div class="row mt-5">
		<div class="col col-12">
			<div class="text-end">
				<button type='submit' class='btn btn-primary ' name='save' value='submit'>
					<?php echo __('BTN_SAVE_DEVICE_CONFIG', 'DEVICE_CONFIG'); ?>
				</button>
			</div>
		</div>
	</div>
</form>
