<?php

use TasmoAdmin\Device;
use TasmoAdmin\DeviceFactory;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Sonoff;

function normalizeDeviceNames($values): array
{
    if (!is_array($values)) {
        return [];
    }

    return array_values(
        array_filter(
            array_map(static fn ($value): string => trim((string) $value), $values),
            static fn (string $value): bool => '' !== $value
        )
    );
}

function hasReachableStatus($status): bool
{
    return $status instanceof stdClass && !empty((array) $status) && !isset($status->ERROR);
}

function hasStatusError($status): bool
{
    return $status instanceof stdClass && isset($status->ERROR) && '' !== $status->ERROR;
}

function getFriendlyNamesFromStatus($status): array
{
    if (!hasReachableStatus($status) || !isset($status->Status)) {
        return [];
    }

    $friendlyNameSource = $status->Status->FriendlyName ?? [];
    if (!is_array($friendlyNameSource)) {
        $friendlyNameSource = [$friendlyNameSource];
    }

    $friendlyNames = normalizeDeviceNames($friendlyNameSource);
    if ([] === $friendlyNames) {
        return [];
    }

    $channelCount = 0;
    if (isset($status->StatusSTS->POWER)) {
        $channelCount = 1;
    } else {
        for ($i = 1; isset($status->StatusSTS->{'POWER'.$i}); ++$i) {
            ++$channelCount;
        }
    }

    if (0 === $channelCount) {
        $channelCount = count($friendlyNames);
    }

    $resolvedFriendlyNames = [];
    for ($i = 0; $i < max($channelCount, 1); ++$i) {
        $resolvedFriendlyNames[] = $friendlyNames[$i] ?? trim(($friendlyNames[0] ?? '').' '.($i + 1));
    }

    return normalizeDeviceNames($resolvedFriendlyNames);
}

function getDeviceNameState(?Device $device, $status, array $request): array
{
    $deviceNames = normalizeDeviceNames($request['device_name'] ?? []);
    if ([] === $deviceNames && $device instanceof Device) {
        $deviceNames = normalizeDeviceNames($device->names);
    }

    $friendlyNames = normalizeDeviceNames($request['device_friendly_name'] ?? []);
    if ([] === $friendlyNames) {
        $friendlyNames = getFriendlyNamesFromStatus($status);
    }
    if ([] === $friendlyNames && $device instanceof Device) {
        $friendlyNames = normalizeDeviceNames($device->getFriendlyNames());
    }

    if ([] === $deviceNames) {
        $deviceNames = $friendlyNames;
    }
    if ([] === $friendlyNames) {
        $friendlyNames = $deviceNames;
    }

    $nameCount = max(count($deviceNames), count($friendlyNames), 1);
    for ($i = 0; $i < $nameCount; ++$i) {
        $deviceNames[$i] ??= $friendlyNames[$i] ?? '';
        $friendlyNames[$i] ??= $deviceNames[$i] ?? '';
    }

    return [array_values($deviceNames), array_values($friendlyNames)];
}

$status = null;
$device = null;
$msg = null;

$Sonoff = $container->get(Sonoff::class);
$deviceRepository = $container->get(DeviceRepository::class);

if ('edit' == $action) {
    $device = $deviceRepository->getDeviceById((int) $device_id);

    if ($device instanceof Device) {
        $status = $Sonoff->getAllStatus($device);
        if (hasStatusError($status)) {
            $msg = __('MSG_DEVICE_NOT_FOUND', 'DEVICE_ACTIONS').'<br/>';
            $msg .= $status->ERROR.'<br/>';
        }
    }
} elseif ('delete' == $action) {
    $deviceRepository->removeDevice((int) $device_id);
    $msg = __('MSG_DEVICE_DELETE_DONE', 'DEVICE_ACTIONS');
    $action = 'done';
}

if (!empty($_POST)) {
    if (isset($_REQUEST['search'])) {
        $deviceIp = trim((string) ($_REQUEST['device_ip'] ?? ''));

        if ('' === $deviceIp) {
            $msg = __('ERROR_PLEASE_ENTER_DEVICE_IP', 'DEVICE_ACTIONS');
        } else {
            if (!($device instanceof Device) && !empty($_REQUEST['device_id'])) {
                $device = $deviceRepository->getDeviceById((int) $_REQUEST['device_id']);
            }

            if (!$device instanceof Device) {
                $device = DeviceFactory::fakeDevice(
                    $deviceIp,
                    (int) ($_REQUEST['device_port'] ?? Device::DEFAULT_PORT),
                    (string) ($_REQUEST['device_username'] ?? ''),
                    (string) ($_REQUEST['device_password'] ?? '')
                );
            }

            $device->ip = $deviceIp;
            $device->port = (int) ($_REQUEST['device_port'] ?? Device::DEFAULT_PORT);
            $device->username = (string) ($_REQUEST['device_username'] ?? '');
            $device->password = (string) ($_REQUEST['device_password'] ?? '');

            $status = $Sonoff->getAllStatus($device);
            if (hasStatusError($status)) {
                $msg = __('MSG_DEVICE_NOT_FOUND', 'DEVICE_ACTIONS').'<br/>';
                $msg .= $status->ERROR.'<br/>';
            }
        }
    } elseif (!empty($_REQUEST['device_id'])) {
        $device = DeviceFactory::fromRequest($_REQUEST);
        $deviceRepository->updateDevice($device);
        $msg = __('MSG_DEVICE_EDIT_DONE', 'DEVICE_ACTIONS');
        $action = 'done';
    } else {
        $deviceRepository->addDevice($_REQUEST);
        $msg = __('MSG_DEVICE_ADD_DONE', 'DEVICE_ACTIONS');
        $action = 'done';
    }
}

[$deviceNames, $friendlyNames] = getDeviceNameState($device, $status, $_REQUEST);
$showDeviceFields = ('edit' == $action && $device instanceof Device) || hasReachableStatus($status);
$canSave = ('edit' == $action && $device instanceof Device) || hasReachableStatus($status);
?>
<div class='row justify-content-sm-center'>
	<div class='col col-12 col-md-8 col-xl-6'>
		<h2 class='text-sm-center mb-5'>
			<?php echo $title; ?>
		</h2>
		<?php if (hasStatusError($status)) { ?>
			<div class="alert alert-danger alert-dismissible fade show mb-5" data-bs-dismiss="alert" role="alert">
				<p><?php echo __('MSG_DEVICE_NOT_FOUND', 'DEVICE_ACTIONS'); ?></p>
				<p><?php echo $status->ERROR; ?></p>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php } elseif (null !== $msg && '' !== $msg && 'done' !== $action) { ?>
			<div class="alert alert-danger alert-dismissible fade show mb-5" data-bs-dismiss="alert" role="alert">
				<p><?php echo $msg; ?></p>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php } elseif (hasReachableStatus($status) && isset($status->WARNING) && '' !== $status->WARNING) { ?>
			<div class="alert alert-warning alert-dismissible fade show mb-5" data-bs-dismiss="alert" role="alert">
				<p><?php echo __('MSG_DEVICE_FOUND', 'DEVICE_ACTIONS'); ?></p>
				<p><?php echo $status->WARNING; ?></p>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php } elseif (hasReachableStatus($status)) { ?>
			<div class="alert alert-success alert-dismissible fade show my-5" data-bs-dismiss="alert" role="alert">
				<?php echo __('MSG_DEVICE_FOUND', 'DEVICE_ACTIONS'); ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php } ?>
		<?php if ('done' == $action) { ?>
			<div class="alert alert-success fade show mb-5" role="alert">
				<div class="col col-12 text-start">
					<?php echo $msg; ?>
				</div>
				<div class="col col-12 text-start mt-3">
					<a class="btn btn-secondary col-12 col-sm-auto" href='<?php echo _BASEURL_; ?>devices'>
						<?php echo __('BTN_BACK', 'DEVICE_ACTIONS'); ?>
					</a>
				</div>
			</div>
		<?php } ?>
		<?php if ('add' == $action || 'edit' == $action) { ?>
			<?php if (isset($device->id)) { ?>
				<h3 class='text-sm-center mb-5'>
					<?php echo __('DEVICE', 'DEVICE_CONFIG'); ?>:
					<?php echo implode(' | ', $device->names); ?>
					<small>( ID: <?php echo $device->id; ?> )</small>
				</h3>
			<?php } ?>

			<form class='form'
				  name='save_device'
				  method='post'
				  action='<?php echo _BASEURL_; ?>device_action/<?php echo $action; ?><?php echo isset($device->id) ? '/'.$device->id : ''; ?>'
			>
				<input type='hidden' name='device_id' value='<?php echo $device->id ?? ''; ?>'>

				<div class="form-row">
					<div class="form-group col col-12 col-sm-6">
						<label for="device_ip">
							<?php echo __('DEVICE_IP', 'DEVICE_ACTIONS'); ?>
						</label>
						<input type="text"
							   autofocus="autofocus"
							   class="form-control"
							   id="device_ip"
							   name='device_ip'
                               placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                               value='<?php echo isset($device->id) && !isset($_REQUEST['device_ip']) ? $device->ip : ($_REQUEST['device_ip'] ?? ''); ?>'
                               required
						>
						<small id="device_ipHelp" class="text-muted">
							<?php echo __('DEVICE_IP_HELP', 'DEVICE_ACTIONS'); ?>
						</small>
					</div>
                    <div class="form-group col col-12 col-sm-3">
                        <label for="device_port">
                            <?php echo __('DEVICE_PORT', 'DEVICE_ACTIONS'); ?>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="device_port"
                               name='device_port'
                               placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                               value='<?php echo isset($device->port) && !isset($_REQUEST['device_port']) ? $device->port : ($_REQUEST['device_port'] ?? Device::DEFAULT_PORT); ?>'
                               required
                        >
                        <small id="device_portHelp" class="text-muted">
							<?php echo __('DEVICE_PORT_HELP', 'DEVICE_ACTIONS'); ?>
						</small>
                    </div>
					<div class="form-group col col-12 col-sm-3">
						<label class="d-none d-sm-block">&nbsp;</label>
						<button type='submit'
								name='search'
								value='search'
								class='btn btn-primary col-12'
						>
							<?php echo __('BTN_SEARCH_DEVICE', 'DEVICE_ACTIONS'); ?>
						</button>
					</div>
				</div>
				<div class="form-group col">
					<label for="device_username">
						<?php echo __('DEVICE_USERNAME', 'DEVICE_ACTIONS'); ?>
					</label>
					<input id="username" style="display: none;" type="text" name="username"/>
					<input id="password" style="display: none;" type="password" name="password"/>

					<input type="text"
						   autocomplete='off'
						   autofill='off'
						   class="form-control"
						   id="device_username"
						   name='device_username'
						   value='<?php echo isset($device->id) && !isset($_REQUEST['device_username']) ? $device->username : ($_REQUEST['device_username'] ?? 'admin'); ?>'
					>
					<small id="device_usernameHelp" class="text-muted">
						<?php echo __('DEVICE_USERNAME_HELP', 'DEVICE_ACTIONS'); ?>
					</small>
				</div>
				<div class="form-group col">
					<label for="device_password">
						<?php echo __('DEVICE_PASSWORD', 'DEVICE_ACTIONS'); ?>
					</label>
					<input type="password"
						   autocomplete='off'
						   autofill='off'
						   class="form-control"
						   id="device_password"
						   name='device_password'
						   value='<?php echo isset($device->id) && !isset($_REQUEST['device_password']) ? $device->password : ($_REQUEST['device_password'] ?? ''); ?>'
					>
					<small id="device_passwordHelp" class="text-muted">
						<?php echo __('DEVICE_PASSWORD_HELP', 'DEVICE_ACTIONS'); ?>
					</small>
				</div>

				<?php if ($showDeviceFields) { ?>
					<div class="form-group col">
						<label for="device_position">
							<?php echo __('DEVICE_POSITION', 'DEVICE_ACTIONS'); ?>
						</label>
						<input type="text"
							   class="form-control"
							   id="device_position"
							   name='device_position'
							   value='<?php echo isset($device->position) && !isset($_REQUEST['device_position']) ? $device->position : ($_REQUEST['device_position'] ?? ''); ?>'
						>
						<small id="device_positionHelp" class="form-text text-muted">
							<?php echo __('DEVICE_POSITION_HELP', 'DEVICE_ACTIONS'); ?>
						</small>
					</div>

					<?php foreach ($deviceNames as $index => $deviceName) { ?>
						<?php
                        $friendlyName = $friendlyNames[$index] ?? '';
					    $nameLabel = __('LABEL_NAME', 'DEVICE_ACTIONS').(count($deviceNames) > 1 ? $index + 1 : '');
					    ?>
						<div class="form-row">
							<div class="form-group col col-12 col-sm-7">
								<label for="device_name_<?php echo $index; ?>">
									<?php echo $nameLabel; ?>
								</label>
								<input type="text"
									   class="form-control tasmoadmin-name-input"
									   id="device_name_<?php echo $index; ?>"
									   name='device_name[<?php echo $index; ?>]'
									   placeholder="<?php echo __('PLEASE_ENTER'); ?>"
									   value='<?php echo htmlspecialchars($deviceName, ENT_QUOTES); ?>'
									   required
								>
								<small class="form-text text-muted d-none d-sm-block">
									&nbsp;
								</small>
							</div>
							<div class="form-group col col-12 col-sm-5">
								<label for="device_friendly_name_<?php echo $index; ?>">
									<?php echo __('CONFIG_FRIENDLYNAME', 'DEVICE_CONFIG'); ?>
								</label>
								<div class="input-group">
									<input type="text"
										   class="form-control"
										   id="device_friendly_name_<?php echo $index; ?>"
										   value='<?php echo htmlspecialchars($friendlyName, ENT_QUOTES); ?>'
										   readonly
									>
									<input type='hidden'
										   name='device_friendly_name[<?php echo $index; ?>]'
										   value='<?php echo htmlspecialchars($friendlyName, ENT_QUOTES); ?>'
									>
									<button type="button"
											class="btn btn-outline-secondary default-name"
											data-default-name="<?php echo htmlspecialchars($friendlyName, ENT_QUOTES); ?>"
									>
										<?php echo __('DEVICE_NAME_TOOLTIP', 'DEVICE_ACTIONS'); ?>
									</button>
								</div>
								<small class="form-text text-muted">
									<?php echo __('DEVICE_NAME_TOOLTIP', 'DEVICE_ACTIONS'); ?>
								</small>
							</div>
						</div>
					<?php } ?>

					<div class="form-row">
						<div class="form-group col col-12 col-sm-3">
							<div class="form-check mb-5">
								<input type='hidden' name='device_all_off' value='0'>
								<input class="form-check-input"
									   type="checkbox"
									   value="1"
									   id="device_all_off"
									   name='device_all_off' <?php echo $device->deviceAllOff ? 'checked="checked"' : ''; ?>>
								<label class="form-check-label" for="device_all_off">
									<?php echo __('LABEL_ALL_OFF', 'DEVICE_ACTIONS'); ?>
								</label>
							</div>
						</div>
						<div class="form-group col col-12 col-sm-3">
							<div class="form-check mb-5">
								<input type='hidden' name='device_protect_on' value='0'>
								<input class="form-check-input"
									   type="checkbox"
									   value="1"
									   id="device_protect_on"
									   name='device_protect_on' <?php echo $device->deviceProtectionOn ? 'checked="checked"' : ''; ?>>
								<label class="form-check-label" for="device_protect_on">
									<?php echo __('LABEL_PROTECT_ON', 'DEVICE_ACTIONS'); ?>
								</label>
							</div>
						</div>
						<div class="form-group col col-12 col-sm-3">
							<div class="form-check mb-5">
								<input type='hidden' name='device_protect_off' value='0'>
								<input class="form-check-input"
									   type="checkbox"
									   value="1"
									   id="device_protect_off"
									   name='device_protect_off' <?php echo $device->deviceProtectionOff ? 'checked="checked"' : ''; ?>>
								<label class="form-check-label" for="device_protect_off">
									<?php echo __('LABEL_PROTECT_OFF', 'DEVICE_ACTIONS'); ?>
								</label>
							</div>
						</div>
                        <div class="form-group col col-12 col-sm-3">
                            <div class="form-check mb-5">
                                <input type='hidden' name='is_updatable' value='0'>
                                <input class="form-check-input"
                                       type="checkbox"
                                       value="1"
                                       id="is_updatable"
                                       name='is_updatable' <?php echo $device->isUpdatable ? 'checked="checked"' : ''; ?>>
                                <label class="form-check-label" for="is_updatable">
                                    <?php echo __('LABEL_IS_UPDATABLE', 'DEVICE_ACTIONS'); ?>
                                </label>
                            </div>
                        </div>
					</div>
				<?php } ?>

				<div class="row">
					<div class="col col-12 col-sm-6 text-start">
						<a class="btn btn-secondary col-12 col-sm-auto" href='<?php echo _BASEURL_; ?>devices'>
							<?php echo __('BTN_BACK', 'DEVICE_ACTIONS'); ?>
						</a>
					</div>
					<div class="col col-12 col-sm-6 text-end">
						<button type='submit'
								name='submit'
								value='<?php echo isset($device->id) ? 'edit' : 'add'; ?>'
								class='btn btn-primary col-12 col-sm-auto'
							<?php if (!$canSave) { ?>
								disabled
							<?php } ?>
						>
							<?php echo __('BTN_SAVE', 'DEVICE_ACTIONS'); ?>
						</button>
					</div>
				</div>
			</form>
		<?php } ?>
	</div>
</div>
<script>
    $(document).ready(function()
    {
        $(".default-name").on("click", function (e)
        {
            e.preventDefault();
            $(this)
                .closest(".form-row")
                .find(".tasmoadmin-name-input")
                .val($(this).data("default-name").trim());
        });
    });
</script>
