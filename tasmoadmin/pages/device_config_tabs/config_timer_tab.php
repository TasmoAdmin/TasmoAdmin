<?php

$timersConfig = $status->StatusTIMERS ?? null;
$timers = $timersConfig->timers ?? [];

$timerModeOptions = [0, 1, 2];
$timerActionOptions = [0, 1, 2, 3];
$timerEnableOptions = [0, 1];
$timerRepeatOptions = [0, 1];
$timerOutputOptions = range(1, 16);
?>
<form class='center config-form' name='device_config_timers' method='post'>
	<input type='hidden' name='tab-index' value='3'>

	<div class="form-group col">
		<label for="Timers" class="form-label">
			<?php echo __('TAB_HL_TIMERS', 'DEVICE_CONFIG'); ?>
		</label>
		<select class="form-control form-select" id="Timers" name='Timers'>
			<option value='0' <?php echo isset($timersConfig->enabled) && 0 === (int) $timersConfig->enabled ? 'selected="selected"' : ''; ?>>
				<?php echo __('OFF'); ?>
			</option>
			<option value='1' <?php echo isset($timersConfig->enabled) && 1 === (int) $timersConfig->enabled ? 'selected="selected"' : ''; ?>>
				<?php echo __('ON'); ?>
			</option>
		</select>
		<small class="form-text text-muted">
			<?php echo __('DEVICE_CONFIG_TIMER_GLOBAL_HELP', 'DEVICE_CONFIG'); ?>
		</small>
	</div>

	<div class="row g-3 mt-1">
		<?php foreach (range(1, 16) as $timerIndex) {
		    $timerKey = 'Timer'.$timerIndex;
		    $timer = $timers[$timerIndex] ?? (object) [
		        'Enable' => 0,
		        'Mode' => 0,
		        'Time' => '00:00',
		        'Window' => 0,
		        'Days' => '-------',
		        'Repeat' => 0,
		        'Output' => 1,
		        'Action' => 0,
		    ];
		    ?>
			<div class="col col-12">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title mb-3"><?php echo __('DEVICE_CONFIG_TIMER_TITLE', 'DEVICE_CONFIG').' '.$timerIndex; ?></h5>
						<div class="row g-3">
							<div class="form-group col col-12 col-md-3">
								<label for="<?php echo $timerKey; ?>Enable" class="form-label">
									<?php echo __('DEVICE_CONFIG_TIMER_ENABLED', 'DEVICE_CONFIG'); ?>
								</label>
								<select class="form-control form-select"
								        id="<?php echo $timerKey; ?>Enable"
								        name='<?php echo $timerKey; ?>[Enable]'>
									<?php foreach ($timerEnableOptions as $option) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Enable ? 'selected="selected"' : ''; ?>>
											<?php echo 1 === $option ? __('ON') : __('OFF'); ?>
										</option>
									<?php } ?>
								</select>
							</div>
							<div class="form-group col col-12 col-md-3">
								<label for="<?php echo $timerKey; ?>Mode" class="form-label">
									<?php echo __('DEVICE_CONFIG_TIMER_MODE', 'DEVICE_CONFIG'); ?>
								</label>
								<select class="form-control form-select"
								        id="<?php echo $timerKey; ?>Mode"
								        name='<?php echo $timerKey; ?>[Mode]'>
									<?php foreach ($timerModeOptions as $option) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Mode ? 'selected="selected"' : ''; ?>>
											<?php echo $option; ?>
										</option>
									<?php } ?>
								</select>
							</div>
							<div class="form-group col col-12 col-md-3">
								<label for="<?php echo $timerKey; ?>Output" class="form-label">
									<?php echo __('DEVICE_CONFIG_TIMER_OUTPUT', 'DEVICE_CONFIG'); ?>
								</label>
								<select class="form-control form-select"
								        id="<?php echo $timerKey; ?>Output"
								        name='<?php echo $timerKey; ?>[Output]'>
									<?php foreach ($timerOutputOptions as $option) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Output ? 'selected="selected"' : ''; ?>>
											<?php echo $option; ?>
										</option>
									<?php } ?>
								</select>
							</div>
							<div class="form-group col col-12 col-md-3">
								<label for="<?php echo $timerKey; ?>Action" class="form-label">
									<?php echo __('DEVICE_CONFIG_TIMER_ACTION', 'DEVICE_CONFIG'); ?>
								</label>
								<select class="form-control form-select"
								        id="<?php echo $timerKey; ?>Action"
								        name='<?php echo $timerKey; ?>[Action]'>
									<?php foreach ($timerActionOptions as $option) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Action ? 'selected="selected"' : ''; ?>>
											<?php echo $option; ?>
										</option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="row g-3 mt-1">
							<div class="form-group col col-12 col-md-3">
								<label for="<?php echo $timerKey; ?>Time" class="form-label">
									<?php echo __('DEVICE_CONFIG_TIMER_TIME', 'DEVICE_CONFIG'); ?>
								</label>
								<input type="text"
								       class="form-control"
								       id="<?php echo $timerKey; ?>Time"
								       name='<?php echo $timerKey; ?>[Time]'
								       value='<?php echo htmlspecialchars((string) $timer->Time, ENT_QUOTES, 'UTF-8'); ?>'>
							</div>
							<div class="form-group col col-12 col-md-3">
								<label for="<?php echo $timerKey; ?>Window" class="form-label">
									<?php echo __('DEVICE_CONFIG_TIMER_WINDOW', 'DEVICE_CONFIG'); ?>
								</label>
								<input type="number"
								       class="form-control"
								       id="<?php echo $timerKey; ?>Window"
								       name='<?php echo $timerKey; ?>[Window]'
								       min='0'
								       max='15'
								       value='<?php echo (int) $timer->Window; ?>'>
							</div>
							<div class="form-group col col-12 col-md-3">
								<label for="<?php echo $timerKey; ?>Repeat" class="form-label">
									<?php echo __('DEVICE_CONFIG_TIMER_REPEAT', 'DEVICE_CONFIG'); ?>
								</label>
								<select class="form-control form-select"
								        id="<?php echo $timerKey; ?>Repeat"
								        name='<?php echo $timerKey; ?>[Repeat]'>
									<?php foreach ($timerRepeatOptions as $option) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Repeat ? 'selected="selected"' : ''; ?>>
											<?php echo 1 === $option ? __('ON') : __('OFF'); ?>
										</option>
									<?php } ?>
								</select>
							</div>
							<div class="form-group col col-12 col-md-3">
								<label for="<?php echo $timerKey; ?>Days" class="form-label">
									<?php echo __('DEVICE_CONFIG_TIMER_DAYS', 'DEVICE_CONFIG'); ?>
								</label>
								<input type="text"
								       class="form-control"
								       id="<?php echo $timerKey; ?>Days"
								       name='<?php echo $timerKey; ?>[Days]'
								       maxlength='7'
								       value='<?php echo htmlspecialchars((string) $timer->Days, ENT_QUOTES, 'UTF-8'); ?>'>
							</div>
						</div>

						<small class="form-text text-muted d-block mt-2">
							<?php echo __('DEVICE_CONFIG_TIMER_HELP', 'DEVICE_CONFIG'); ?>
						</small>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="row mt-5">
		<div class="col col-12">
			<div class="text-end">
				<button type='submit' class='btn btn-primary' name='save' value='submit'>
					<?php echo __('BTN_SAVE_DEVICE_CONFIG', 'DEVICE_CONFIG'); ?>
				</button>
			</div>
		</div>
	</div>
</form>
