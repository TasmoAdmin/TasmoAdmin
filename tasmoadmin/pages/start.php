<?php
$devices = $Sonoff->getDevices();
?>
<div class='container-fluid'>
	
	<?php if (isset($devices) && !empty($devices)):
		$nightmode = "";   //todo: make function
		$h = date('H');
		
		if ($Config->read("nightmode") === "disable") {
			$nightmode = "";
		}
		else {
			if ($Config->read("nightmode") === "auto") {
				if ($h >= 18 || $h <= 8) {
					$nightmode = "nightmode ";
				}
			}
			elseif ($Config->read("nightmode") === "always") {
				$nightmode = "nightmode ";
			}
		}
		
		$imgNight = "";
		if ($nightmode == "nightmode") {
			$imgNight = "night/";
		}
		?>
		<div class='row justify-content-center startpage'>
			<div class='card-holder col-6 col-sm-3 col-md-2 col-xl-1 col-xxl-1 mb-4'>
				<div class='card box_device position-relative' id='all_off' style=''>
					<div class=" rubberBand">
						<?php //col col-xs-6 col-4 col-sm-3 col-md-2 col-xl-1
						if (!empty($device_group)) {
							$type = $device_group->img;
						}
						else {
							$type = "bulb_1";
						}
						$img = _RESOURCESURL_ . "img/device_icons/" . $imgNight . $type . "_off.png";
						
						?>
						<img class='card-img-top'
							 src='<?php echo $img; ?>'
							 data-icon='<?php echo $type; ?>'
							 alt=''
						>
					</div>
					<div class='card-body'>
						<h5 class="card-title box_device_name">
							<?php echo __("ALL_OFF", "DEVICES"); ?>
						</h5>
					</div>
				</div>
			</div>
			
			<?php foreach ($devices as $device_group): ?>
				<?php foreach ($device_group->names as $key => $devicename): ?>
					<?php
					$img = _RESOURCESURL_ . "img/device_icons/" . $imgNight . $device_group->img . "_off.png";
					?>
					<div class='card-holder col-6 col-sm-3 col-md-2 col-xl-1 col-xxl-1 mb-4'>
						<div class='card box_device position-relative' style=''
							 data-device_id='<?php echo $device_group->id; ?>'
							 data-device_group='<?php echo count($device_group->names) > 1 ? "multi" : "single"; ?>'
							 data-device_ip='<?php echo $device_group->ip; ?>'
							 data-device_relais='<?php echo $key + 1; ?>'
							 data-device_state='none'
							 data-device_all_off='<?php echo $device_group->device_all_off; ?>'
							 data-device_protect_on='<?php echo $device_group->device_protect_on; ?>'
							 data-device_protect_off='<?php echo $device_group->device_protect_off; ?>'
						>
							<div class="animated rubberBand">
								<img class='card-img-top'
									 data-icon='<?php echo $device_group->img; ?>'
									 src='<?php echo $img; ?>'
									 alt=''
								>
							</div>
							<div class='card-body'>
								<h5 class="card-title box_device_name">
									<?php echo $devicename; ?>
								</h5>
								<div class='card-text info-holder'>
									<div class='info info-1 hidden'>
										<span>-</span>
									</div>
									<div class='info info-2 hidden'>
										<span>-</span>
									</div>
									<div class='info info-3 hidden'>
										<span>-</span>
									</div>
									<div class='info info-4 hidden'>
										<span>-</span>
									</div>
									<div class='info info-5 hidden'>
										<span>-</span>
									</div>
									<div class='info info-6 hidden'>
										<span>-</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach;
			endforeach; ?>
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

<script src="<?php echo UrlHelper::JS("start"); ?>"></script>