<?php
	$devices = $Sonoff->getDevices();

	$imgNight = "";
	if( $nightmode == "nightmode" ) {
		$imgNight = "night/";
	}

	//var_dump( $devices );
?>
<?php if( isset( $devices ) && !empty( $devices ) ): ?>

	<div class='row devices details justify-content-center' id='device_details'>
		<div class='col col-12 col-lg-10'>
			<div class="accordion" id="devices-accordion">
				<?php foreach( $devices as $device_group ):
					foreach( $device_group->names as $key => $devicename ): ?>
						<?php //debug( $device_group ); ?>
						<?php //debug( $devicename ); ?>
						<div class="card"
						     data-device_id='<?php echo $device_group->id; ?>'
						     data-device_group='<?php echo count( $device_group->names ) > 1 ? "multi" : "single"; ?>'
						     data-device_ip='<?php echo $device_group->ip; ?>'
						     data-device_relais='<?php echo $key+1; ?>'
						>
							<div class="card-header" id="device-<?php echo $device_group->id; ?>"
							     data-toggle="collapse"
							     data-target="#row-device-<?php echo $device_group->id; ?>"
							     aria-expanded="true"
							     aria-controls="row-device-<?php echo $device_group->id; ?>"
							>
								<div class='row'>
									<div class='col col-auto d-flex align-items-center '>
										<?php //col col-xs-6 col-4 col-sm-3 col-md-2 col-xl-1
											$img = _RESOURCESURL_
											       ."img/device_icons/"
											       .$imgNight
											       .$device_group->img
											       ."_off.png";
										?>
										<div class='row'>
											<div class='col col-6  pr-1 d-flex align-items-center'>
												<div class='img-container devices-switch-container'>
													<img class='card-img-top'
													     src='<?php echo $img; ?>'
													     data-icon='<?php echo $device_group->img; ?>'
													     alt=''>

												</div>
											</div>
											<div class='col col-6 pl-1 d-flex align-items-center'>
												<div class='device-rssi'
												     data-toggle='tooltip'
												     title='Searching...'
												>
													<img src="<?php echo _RESOURCESURL_
													                     ."css/fontawesome/signal-solid.svg"; ?>"
													     alt="%" class='svg-inject searching '
													     width='30px'
													     height='30px'
													>
												</div>
											</div>
										</div>


									</div>
									<div class='col col-auto text-left'>
										<div class='row '>
											<div class='col col-12'>
												<a href='http://<?php echo $device_group->ip; ?>/'
												   target='_blank'
												   title='<?php echo __(
													   "LINK_OPEN_DEVICE_WEBUI",
													   "DEVICES"
												   ); ?>'><?php echo str_replace(
														" ",
														"&nbsp;",
														$devicename
													); ?></a>
											</div>
										</div>
										<div class='row'>
											<div class='col col-12 '>
												<span class=''>
													<?php echo $device_group->ip; ?>
												</span>
											</div>
										</div>


									</div>
									<div class='col col-auto text-left'>
										<div class='row '>
											<div class='col col-12 '>
												<span class='device-data version hidden'>
												</span>
											</div>
										</div>
										<div class='row'>
											<div class='col col-12 '>
												<span class='device-data runtime hidden'>
												</span>
											</div>
										</div>
									</div>
									<div class='col col-auto text-left'>
										<div class='row '>
											<div class='col col-12 '>
												<span class='device-data humidity hidden'>
												</span>
											</div>
										</div>
										<div class='row'>
											<div class='col col-12 '>
												<span class='device-data temp hidden'>
												</span>
											</div>
										</div>
									</div>
								</div>

							</div>

							<div id="row-device-<?php echo $device_group->id; ?>"
							     class="collapse "
							     aria-labelledby="headingOne"
							     data-parent="#devices-accordion">
								<div class="card-body">
									<div class='row'>
										<div class='col col-12 text-black-50'>
											<?php echo $device_group->ip; ?>
											<?php debug( $device_group ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class='row mt-3'>
		<div class="col-auto ">
			<button class='btn btn-secondary showCommandInput'>
				<?php echo __( "BTN_COMMAND", "DEVICES" ); ?>
			</button>
		</div>
	</div>
	<div class='cmdContainer row command-hidden d-none my-3'>
		<div class="form-group col col-12 col-sm-6 col-md-7 col-lg-8 offset-0 offset-sm-1 mb-1 mb-sm-0  px-0 pl-0 pl-xl-3">
			<input type='text' name='command' class='form-control commandInput'>
		</div>
		<div class="form-group col col-12 col-sm-4 col-md-3 col-lg-2 mb-0 px-0 pl-3">
			<button type='submit' class='btn btn-primary sendCommand w-100' name='sendCommand'>
				<?php echo __( "SEND_COMMAND", "DEVICES" ); ?>
			</button>
		</div>
		<small id="commandInputError"
		       class="form-text col-12 col-sm-11 offset-0 offset-sm-1 d-none  px-0">
		</small>
	</div>
<?php else: ?>
	<div class='row'>
		<div class='col col-12 text-center'>
			<?php echo __( "NO_DEVICES_FOUND", "STARTPAGE" ); ?>
		</div>
	</div>
	<div class='row mt-5 justify-content-center text-center'>
		<div class='col col-12 col-sm-2 '>
			<a class="btn btn-primary"
			   href="<?php echo _BASEURL_; ?>devices_autoscan">
				<?php echo __( "DEVICES_AUTOSCAN", "NAVI" ); ?>
			</a>
		</div>
		<div class='col col-12 col-sm-2 '>
			<a href='<?php echo _BASEURL_; ?>device_action/add' class="btn btn-primary">
				<?php echo __("TABLE_HEAD_NEW_DEVICE", "DEVICES"); ?>
			</a>
		</div>
	</div>

<?php endif; ?>
<?php include "elements/modal_delete_device.php"; ?>

<script src="<?php echo UrlHelper::JS("devices_details"); ?>"></script>
