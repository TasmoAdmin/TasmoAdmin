<?php

$timersConfig = $status->StatusTIMERS ?? null;
$timers = $timersConfig->timers ?? [];

$formatTimerTimeValue = static function (int $minutes, string $prefix = ''): string {
    $hours = (int) floor($minutes / 60);
    $mins = $minutes % 60;

    return sprintf('%s%02d:%02d', $prefix, $hours, $mins);
};
$ensureTimerOption = static function (array $options, string $value): array {
    if ('' === $value || isset($options[$value])) {
        return $options;
    }

    $options[$value] = $value;
    ksort($options);

    return $options;
};
$normalizeTimerOffsetValue = static function (string $value): string {
    if (preg_match('/^[+-]\d{2}:\d{2}$/', $value)) {
        return $value;
    }

    if (preg_match('/^\d{2}:\d{2}$/', $value)) {
        return '+'.$value;
    }

    return '+00:00';
};

$timerEnableOptions = [
    0 => __('SWITCH_STATE_OFF', 'DEVICES'),
    1 => __('SWITCH_STATE_ON', 'DEVICES'),
];
$timerModeOptions = [
    0 => __('DEVICE_CONFIG_TIMER_MODE_FIXED_TIME', 'DEVICE_CONFIG'),
    1 => __('DEVICE_CONFIG_TIMER_MODE_SUNRISE', 'DEVICE_CONFIG'),
    2 => __('DEVICE_CONFIG_TIMER_MODE_SUNSET', 'DEVICE_CONFIG'),
];
$timerActionOptions = [
    0 => __('DEVICE_CONFIG_TIMER_ACTION_OFF', 'DEVICE_CONFIG'),
    1 => __('DEVICE_CONFIG_TIMER_ACTION_ON', 'DEVICE_CONFIG'),
    2 => __('DEVICE_CONFIG_TIMER_ACTION_TOGGLE', 'DEVICE_CONFIG'),
    3 => __('DEVICE_CONFIG_TIMER_ACTION_RULE', 'DEVICE_CONFIG'),
];
$timerRepeatOptions = [
    0 => __('SWITCH_STATE_OFF', 'DEVICES'),
    1 => __('SWITCH_STATE_ON', 'DEVICES'),
];
$timerOutputOptions = [];
foreach (range(1, 16) as $outputOption) {
    $timerOutputOptions[$outputOption] = __('DEVICE_CONFIG_TIMER_OUTPUT_OPTION', 'DEVICE_CONFIG', [$outputOption]);
}
$timerWindowOptions = [
    0 => __('DEVICE_CONFIG_TIMER_WINDOW_EXACT', 'DEVICE_CONFIG'),
];
foreach (range(1, 15) as $windowOption) {
    $timerWindowOptions[$windowOption] = __('DEVICE_CONFIG_TIMER_WINDOW_OFFSET', 'DEVICE_CONFIG', [$windowOption]);
}
$timerDayOptions = [
    ['mask' => 'S', 'label' => __('DEVICE_CONFIG_TIMER_DAY_SUNDAY', 'DEVICE_CONFIG')],
    ['mask' => 'M', 'label' => __('DEVICE_CONFIG_TIMER_DAY_MONDAY', 'DEVICE_CONFIG')],
    ['mask' => 'T', 'label' => __('DEVICE_CONFIG_TIMER_DAY_TUESDAY', 'DEVICE_CONFIG')],
    ['mask' => 'W', 'label' => __('DEVICE_CONFIG_TIMER_DAY_WEDNESDAY', 'DEVICE_CONFIG')],
    ['mask' => 'T', 'label' => __('DEVICE_CONFIG_TIMER_DAY_THURSDAY', 'DEVICE_CONFIG')],
    ['mask' => 'F', 'label' => __('DEVICE_CONFIG_TIMER_DAY_FRIDAY', 'DEVICE_CONFIG')],
    ['mask' => 'S', 'label' => __('DEVICE_CONFIG_TIMER_DAY_SATURDAY', 'DEVICE_CONFIG')],
];
$timerOffsetOptions = [];
foreach (range(-12 * 60, 12 * 60, 5) as $offsetMinutes) {
    $sign = $offsetMinutes < 0 ? '-' : '+';
    $formattedOffset = $formatTimerTimeValue(abs($offsetMinutes), $sign);
    $timerOffsetOptions[$formattedOffset] = $formattedOffset;
}
?>
<form class='center config-form' name='device_config_timers' method='post'>
	<input type='hidden' name='tab-index' value='3'>

	<div class="form-group col">
		<label for="Timers" class="form-label">
			<?php echo __('TAB_HL_TIMERS', 'DEVICE_CONFIG'); ?>
		</label>
		<select class="form-control form-select" id="Timers" name='Timers'>
			<option value='0' <?php echo isset($timersConfig->enabled) && 0 === (int) $timersConfig->enabled ? 'selected="selected"' : ''; ?>>
				<?php echo __('SWITCH_STATE_OFF', 'DEVICES'); ?>
			</option>
			<option value='1' <?php echo isset($timersConfig->enabled) && 1 === (int) $timersConfig->enabled ? 'selected="selected"' : ''; ?>>
				<?php echo __('SWITCH_STATE_ON', 'DEVICES'); ?>
			</option>
		</select>
		<small class="form-text text-muted">
			<?php echo __('DEVICE_CONFIG_TIMER_GLOBAL_HELP', 'DEVICE_CONFIG'); ?>
		</small>
	</div>

	<div class="row g-3 mt-1 device-config-card-grid device-config-timer-grid">
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
		    $isFixedTimeMode = 0 === (int) $timer->Mode;
		    $timerTimeValue = (string) $timer->Time;
		    $selectedTimerTime = preg_match('/^\d{2}:\d{2}$/', $timerTimeValue) ? $timerTimeValue : '00:00';
		    $selectedTimerOffset = $normalizeTimerOffsetValue($timerTimeValue);
		    $timerOffsetOptionsForSelect = $ensureTimerOption($timerOffsetOptions, $selectedTimerOffset);
		    $timerDays = str_pad(substr((string) $timer->Days, 0, 7), 7, '-', STR_PAD_RIGHT);
		    $normalizedTimerDays = '';
		    foreach ($timerDayOptions as $dayIndex => $dayOption) {
		        $dayValue = substr($timerDays, $dayIndex, 1);
		        $normalizedTimerDays .= ('-' !== $dayValue && '0' !== $dayValue && '' !== $dayValue)
		            ? $dayOption['mask']
		            : '-';
		    }
		    ?>
			<div class="col col-12">
				<div class="card device-config-card device-config-timer-card">
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
									<?php foreach ($timerEnableOptions as $option => $label) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Enable ? 'selected="selected"' : ''; ?>>
											<?php echo $label; ?>
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
								        name='<?php echo $timerKey; ?>[Mode]'
								        data-timer-mode>
									<?php foreach ($timerModeOptions as $option => $label) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Mode ? 'selected="selected"' : ''; ?>>
											<?php echo $label; ?>
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
									<?php foreach ($timerOutputOptions as $option => $label) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Output ? 'selected="selected"' : ''; ?>>
											<?php echo $label; ?>
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
									<?php foreach ($timerActionOptions as $option => $label) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Action ? 'selected="selected"' : ''; ?>>
											<?php echo $label; ?>
										</option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="row g-3 mt-1">
							<div class="form-group col col-12 col-md-3">
								<input type="hidden"
								       id="<?php echo $timerKey; ?>Time"
								       name='<?php echo $timerKey; ?>[Time]'
								       value='<?php echo htmlspecialchars($isFixedTimeMode ? $selectedTimerTime : $selectedTimerOffset, ENT_QUOTES, 'UTF-8'); ?>'
								       data-timer-time-value>
								<div data-timer-time-group class="<?php echo $isFixedTimeMode ? '' : 'hidden'; ?>">
									<label for="<?php echo $timerKey; ?>TimeSelect" class="form-label">
										<?php echo __('DEVICE_CONFIG_TIMER_TIME', 'DEVICE_CONFIG'); ?>
									</label>
									<div class="input-group device-config-time-picker-group">
										<span class="input-group-text" aria-hidden="true">
											<i class="fa-regular fa-clock"></i>
										</span>
										<input type="text"
										       class="form-control"
										       id="<?php echo $timerKey; ?>TimeSelect"
										       value="<?php echo htmlspecialchars($selectedTimerTime, ENT_QUOTES, 'UTF-8'); ?>"
										       inputmode="numeric"
										       autocomplete="off"
										       placeholder="HH:MM"
										       data-timer-time-select
										       data-timer-time-picker>
									</div>
								</div>
								<div data-timer-offset-group class="<?php echo $isFixedTimeMode ? 'hidden' : ''; ?>">
									<label for="<?php echo $timerKey; ?>OffsetSelect" class="form-label">
										<?php echo __('DEVICE_CONFIG_TIMER_OFFSET', 'DEVICE_CONFIG'); ?>
									</label>
									<select class="form-control form-select"
									        id="<?php echo $timerKey; ?>OffsetSelect"
									        data-timer-offset-select>
										<?php foreach ($timerOffsetOptionsForSelect as $option => $label) { ?>
											<option value='<?php echo $option; ?>' <?php echo $option === $selectedTimerOffset ? 'selected="selected"' : ''; ?>>
												<?php echo $label; ?>
											</option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group col col-12 col-md-3">
								<label for="<?php echo $timerKey; ?>Window" class="form-label">
									<?php echo __('DEVICE_CONFIG_TIMER_WINDOW', 'DEVICE_CONFIG'); ?>
								</label>
								<select class="form-control form-select"
								        id="<?php echo $timerKey; ?>Window"
								        name='<?php echo $timerKey; ?>[Window]'>
									<?php foreach ($timerWindowOptions as $option => $label) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Window ? 'selected="selected"' : ''; ?>>
											<?php echo $label; ?>
										</option>
									<?php } ?>
								</select>
							</div>
							<div class="form-group col col-12 col-md-3">
								<label for="<?php echo $timerKey; ?>Repeat" class="form-label">
									<?php echo __('DEVICE_CONFIG_TIMER_REPEAT', 'DEVICE_CONFIG'); ?>
								</label>
								<select class="form-control form-select"
								        id="<?php echo $timerKey; ?>Repeat"
								        name='<?php echo $timerKey; ?>[Repeat]'>
									<?php foreach ($timerRepeatOptions as $option => $label) { ?>
										<option value='<?php echo $option; ?>' <?php echo $option === (int) $timer->Repeat ? 'selected="selected"' : ''; ?>>
											<?php echo $label; ?>
										</option>
									<?php } ?>
								</select>
							</div>
							<div class="form-group col col-12 col-md-9">
								<label class="form-label" for="<?php echo $timerKey; ?>DaysValue">
									<?php echo __('DEVICE_CONFIG_TIMER_DAYS', 'DEVICE_CONFIG'); ?>
								</label>
								<div class="row g-2" data-timer-days-group>
									<input type="hidden"
									       id="<?php echo $timerKey; ?>DaysValue"
									       name='<?php echo $timerKey; ?>[Days]'
									       value='<?php echo htmlspecialchars($normalizedTimerDays, ENT_QUOTES, 'UTF-8'); ?>'
									       data-timer-days-value>
									<?php foreach ($timerDayOptions as $dayIndex => $dayOption) {
									    $dayValue = substr($normalizedTimerDays, $dayIndex, 1);
									    $dayInputId = $timerKey.'Day'.$dayIndex;
									    ?>
										<div class="col col-6 col-md-4 col-xl-3">
											<div class="form-check">
												<input type="checkbox"
												       class="form-check-input"
												       id="<?php echo $dayInputId; ?>"
												       value='<?php echo $dayOption['mask']; ?>'
												       data-timer-days-checkbox
												       data-days-label="<?php echo htmlspecialchars($dayOption['label'], ENT_QUOTES, 'UTF-8'); ?>"
												       <?php echo $dayValue === $dayOption['mask'] ? 'checked' : ''; ?>>
												<label class="form-check-label" for="<?php echo $dayInputId; ?>">
													<?php echo $dayOption['label']; ?>
												</label>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
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
