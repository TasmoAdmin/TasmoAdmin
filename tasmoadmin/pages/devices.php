<?php


use TasmoAdmin\Sonoff;

$Sonoff = $container->get(Sonoff::class);
$devices = $Sonoff->getDevices();
?>
<div class='row'>
	<div class='col col-12'>
		
		<?php if (!empty($devices)): ?>
			<div class='row mb-1 mt-3'>
				<div class="col col-auto offset-0 offset-xl-1">
					<div class="form-check pl-0">
						<input type="checkbox"
							   class="form-check-input showmore d-none"
							   id="showmore"
							   name='showmore'
						>
						<label class="form-check-label  btn btn-secondary" for="showmore">
							<?php echo __("SHOW_MORE", "DEVICES"); ?>
						</label>
					</div>
				</div>
				<?php if ($Config->read("show_search") == 1): ?>
					<div class="col col-auto">
						<div class="form-group">
							<div class="input-group">
								<input type="text"
									   name="searchterm"
									   class='form-control device-search has-clearer'
									   autocomplete="off"
									   placeholder="<?php echo __("FILTER", "DEVICES"); //(Name, IP#123, ID#321, POS#1)?>"
								>
								<div class="input-group-append">
									<span class="input-group-text">
										<i class="fas fa-search"></i>
									</span>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<div class='row justify-content-center'>
				<div class='col col-12'>
					<div class='table-responsive double-scroll'>
                        <?php
                        $deviceLinks = true;
		    $deviceLinksDefaultHide = true;
		    $deviceLinkActionText = __("CB_COMMAND", "DEVICES");
		    include "elements/devices_table.php";
		    ?>
					</div>
				</div>
			</div>
			<div class='row mt-3'>
				<div class="col col-auto offset-0 offset-xl-1">
					<div class="form-check pl-0">
						<input type="checkbox"
							   class="form-check-input showmore d-none"
							   id="showmore"
							   name='showmore'
						>
						<label class="form-check-label  btn btn-secondary" for="showmore">
							<?php echo __("SHOW_MORE", "DEVICES"); ?>
						</label>
					</div>
				</div>
				<div class="col col-auto ">
					<button class='btn btn-secondary showCommandInput'>
						<?php echo __("BTN_COMMAND", "DEVICES"); ?>
					</button>
				</div>
				<div class="col col-auto ">
					<button class='btn btn-secondary showDelete'>
						<?php echo __("BTN_DELETE", "DEVICES"); ?>
					</button>
				</div>
				<div class="col col-auto ">
					<div class="form-check pl-0">
						<input type="checkbox"
							   class="form-check-input ignoreProtections d-none"
							   id="ignoreProtections"
							   name='ignoreProtections'
						>
						<label class="form-check-label  btn btn-secondary"
							   title="<?php echo __("BTN_UNLOCK_TOOLTIP", "DEVICES"); ?>"
							   for="ignoreProtections"
						>
							<i class="fas fa-lock" style="width: 18px;"></i>
						</label>
					</div>
				</div>
			</div>
			<div class='cmdContainer row command-hidden d-none my-3'>
				<div class="form-group col col-12 col-sm-6 col-md-7 col-lg-8 offset-0 offset-sm-1 mb-3 mb-sm-0">
					<input type='text' name='command' class='form-control commandInput'>
				</div>
				<div class="form-group col col-12 col-sm-4 col-md-3 col-lg-2 mb-0">
					<button type='submit' class='btn btn-primary sendCommand w-100' name='sendCommand'>
						<?php echo __("SEND_COMMAND", "DEVICES"); ?>
					</button>
				</div>
				<small id="commandInputError" class="form-text col-12 col-sm-11 offset-0 offset-sm-1 d-none  px-0">
				</small>
			</div>
			<div class='deleteContainer row delete-hidden d-none my-3'>
				<div class="form-group col col-12 col-sm-4 col-md-3 col-lg-2 mb-0">
					<button type='submit' class='btn btn-primary sendDelete w-100' name='sendDelete'>
						<?php echo __("DELETE_SELECTED", "DEVICES"); ?>
					</button>
				</div>
			</div>
		<?php else: ?>
			<div class='row'>
				<div class='col col-12 text-center'>
					<?php echo __("NO_DEVICES_FOUND", "STARTPAGE"); ?>
				</div>
			</div>
			<div class='row mt-5 justify-content-center text-center'>
				<div class='col col-12 col-sm-2 '>
					<a class="btn btn-primary"
					   href="<?php echo _BASEURL_; ?>devices_autoscan"
					>
						<?php echo __("DEVICES_AUTOSCAN", "NAVI"); ?>
					</a>
				</div>
				<div class='col col-12 col-sm-2 '>
					<a href='<?php echo _BASEURL_; ?>device_action/add' class="btn btn-primary">
						<?php echo __("TABLE_HEAD_NEW_DEVICE", "DEVICES"); ?>
					</a>
				</div>
			</div>
		
		<?php endif; ?>
	
	
	</div>
</div>
<?php include "elements/modal_delete_device.php"; ?>

<script src="<?php echo $urlHelper->js("compiled/devices"); ?>"></script>
