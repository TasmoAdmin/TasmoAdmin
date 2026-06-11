<?php

use TasmoAdmin\Sonoff;

$Sonoff = $container->get(Sonoff::class);
$devices = $Sonoff->getDevices();
?>
<div class='row'>
	<div class='col col-12'>

		<?php if (!empty($devices)) { ?>
			<div class='row mb-1 mt-3'>
				<div class="col col-auto offset-0 offset-xl-1">
					<div class="dropdown" data-bs-auto-close="outside">
						<button class="btn btn-secondary dropdown-toggle"
								type="button"
								id="deviceColumnsMenuButton"
								data-bs-toggle="dropdown"
								aria-expanded="false"
						>
							<?php echo __('VISIBLE_COLUMNS', 'DEVICES'); ?>
						</button>
						<div class="dropdown-menu p-3 device-columns-menu"
							 aria-labelledby="deviceColumnsMenuButton"
						></div>
					</div>
				</div>
				<?php if (1 == $Config->read('show_search')) { ?>
					<div class="col col-auto">
						<div class="col">
							<div class="input-group">
								<input type="text"
									   name="searchterm"
									   class='form-control device-search has-clearer'
									   autocomplete="off"
									   placeholder="<?php echo __('FILTER', 'DEVICES'); // (Name, IP#123, ID#321, POS#1)?>"
								>
								<div class="input-group-text">
									<span class="input-group-text">
										<i class="fas fa-search"></i>
									</span>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class='row justify-content-center'>
				<div class='col col-12'>
					<div class='table-responsive double-scroll'>
                        <?php
                        $deviceLinks = true;
		    $deviceLinksDefaultHide = false;
		    $deviceLinkActionText = __('CB_COMMAND', 'DEVICES');

		    include 'elements/devices_table.php';
		    ?>
					</div>
				</div>
			</div>
			<div class='row mt-3 g-3 align-items-end'>
				<div class="col col-12 col-xl-5">
					<select class='form-select batchActionSelect' aria-label="<?php echo __('PLEASE_SELECT'); ?>">
						<option value=''><?php echo __('PLEASE_SELECT'); ?></option>
						<option value='command'><?php echo __('BTN_COMMAND', 'DEVICES'); ?></option>
						<option value='delete'><?php echo __('DELETE_SELECTED', 'DEVICES'); ?></option>
					</select>
				</div>
				<div class="col col-12 col-xl-4 batchActionCommandWrapper d-none">
					<input type='text'
						   name='command'
						   class='form-control batchActionCommandInput'
						   placeholder="<?php echo __('CB_COMMAND', 'DEVICES'); ?>"
					>
				</div>
				<div class="col col-12 col-sm-6 col-xl-2">
					<button type='button' class='btn btn-primary applyBatchAction w-100' disabled>
						<?php echo __('PLEASE_SELECT'); ?>
					</button>
				</div>
				<div class="col col-12 col-sm-6 col-xl-1">
					<div class="form-check pl-0">
						<input type="checkbox"
							   class="form-check-input ignoreProtections d-none"
							   id="ignoreProtections"
							   name='ignoreProtections'
						>
						<label class="form-check-label btn btn-secondary w-100"
							   title="<?php echo __('BTN_UNLOCK_TOOLTIP', 'DEVICES'); ?>"
							   for="ignoreProtections"
						>
							<i class="fas fa-lock" style="width: 18px;"></i>
						</label>
					</div>
				</div>
				<div class="col col-12">
					<small class="form-text batchActionFeedback d-none"></small>
				</div>
			</div>
		<?php } else { ?>
			<div class='row'>
				<div class='col col-12 text-center'>
					<?php echo __('NO_DEVICES_FOUND', 'STARTPAGE'); ?>
				</div>
			</div>
			<div class='row mt-5 justify-content-center text-center'>
				<div class='col col-12 col-sm-2 '>
					<a class="btn btn-primary"
					   href="<?php echo _BASEURL_; ?>devices_autoscan"
					>
						<?php echo __('DEVICES_AUTOSCAN', 'NAVI'); ?>
					</a>
				</div>
				<div class='col col-12 col-sm-2 '>
					<a href='<?php echo _BASEURL_; ?>device_action/add' class="btn btn-primary">
						<?php echo __('TABLE_HEAD_NEW_DEVICE', 'DEVICES'); ?>
					</a>
				</div>
			</div>

		<?php } ?>


	</div>
</div>
<?php include 'elements/modal_delete_device.php'; ?>

<script src="<?php echo $urlHelper->js('compiled/devices'); ?>"></script>
