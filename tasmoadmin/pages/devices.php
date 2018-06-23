<?php

	$devices = $Sonoff->getDevices();


	//var_dump( $devices );
?>
<?php if( isset( $devices ) && !empty( $devices ) ): ?>
	<div class='row mb-1 mt-3'>
		<div class='col-12 col-sm-1 offset-0 offset-sm-1'>
			<div class="form-group">
				<input type="checkbox"
				       class="form-check-input showmore d-none"
				       id="showmore"
				       name='showmore'>
				<label class="form-check-label  btn btn-secondary" for="showmore">
					<?php echo __( "SHOW_MORE", "DEVICES" ); ?>
				</label>
			</div>
		</div>
	</div>
	<div class='row justify-content-center'>
		<div class=' table-responsive'>
			<table id='device-list'
			       class='table table-striped table-sm table-hover tablesaw tablesaw-stack'
			       data-tablesaw-mode="stack"
			       border='0'
			       cellspacing='0'>
				<thead>
				<tr>
					<th class='link cmd_cb d-none py-0'>
						<div class="form-check custom-control custom-checkbox">
							<input class="form-check-input custom-control-input select_all"
							       type="checkbox"
							       value='select_all'
							       id="select_all"
							       name='select_all'>
							<label class="form-check-label custom-control-label" for="select_all">
								<?php echo __( "TABLE_HEAD_ALL", "DEVICES" ); ?>
							</label>
						</div>
					</th>
					<th><?php echo __( "TABLE_HEAD_POSITION", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "TABLE_HEAD_ID", "DEVICES" ); ?></th>
					<th><?php echo __( "TABLE_HEAD_NAME", "DEVICES" ); ?></th>
					<th><?php echo __( "TABLE_HEAD_IP", "DEVICES" ); ?></th>
					<th><?php echo __( "TABLE_HEAD_STATE", "DEVICES" ); ?></th>
					<th>
						<i class="fas fa-signal" title='<?php echo __( "TABLE_HEAD_RSSI", "DEVICES" ); ?>'></i>
					</th>
					<th><?php echo __( "TABLE_HEAD_VERSION", "DEVICES" ); ?></th>
					<th><?php echo __( "TABLE_HEAD_RUNTIME", "DEVICES" ); ?></th>
					<th class='energyPower hidden'><?php echo __( "TABLE_HEAD_ENERGY", "DEVICES" ); ?></th>
					<th class='temp hidden'><?php echo __( "TABLE_HEAD_TEMP", "DEVICES" ); ?></th>
					<th class='humidity hidden'><?php echo __( "TABLE_HEAD_HUMIDITY", "DEVICES" ); ?></th>
					<th class='pressure hidden'><?php echo __( "TABLE_HEAD_PRESSURE", "DEVICES" ); ?></th>
					<th class='distance hidden'><?php echo __( "TABLE_HEAD_DISTANCE", "DEVICES" ); ?></th>
					<th class='gas hidden'><?php echo __( "TABLE_HEAD_GAS", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "HOSTNAME", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "MAC", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "MQTT", "DEVICES" ); ?></th>
					<th class='more idx hidden'><?php echo __( "TABLE_HEAD_IDX", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "POWERONSTATE", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "LEDSTATE", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "SAVEDATA", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "SLEEP", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "BOOTCOUNT", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "SAVECOUNT", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "LOGSTATES", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "WIFICONFIG", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "VCC", "DEVICES" ); ?></th>

					<th class='link'>
						<a href='<?php echo _BASEURL_; ?>device_action/add'>
							<i class="fas fa-plus add"
							   title='<?php echo __( "TABLE_HEAD_NEW_DEVICE", "DEVICES" ); ?>'></i>
							<?php echo __( "TABLE_HEAD_NEW_DEVICE", "DEVICES" ); ?>
						</a>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
					$odd = TRUE;
					if( isset( $devices ) && !empty( $devices ) ):
						foreach( $devices as $device_group ):
							foreach( $device_group->names as $key => $devicename ): ?>
								<tr class='<?php echo $odd ? "odd" : "even"; ?>'
								    data-device_id='<?php echo $device_group->id; ?>'
								    data-device_group='<?php echo count( $device_group->names ) > 1 ? "multi"
									    : "single"; ?>'
								    data-device_ip='<?php echo $device_group->ip; ?>'
								    data-device_relais='<?php echo $key+1; ?>'
								>
									<td class='cmd_cb d-none'>
										<?php if( $key == 0 ): ?>
											<div class="form-check custom-control custom-checkbox">
												<input class="form-check-input custom-control-input device_checkbox"
												       type="checkbox"
												       value='<?php echo $device_group->id; ?>'
												       id="cb_<?php echo $device_group->id; ?>"
												       name='device_ids[]'>
												<label class="form-check-label custom-control-label"
												       for="cb_<?php echo $device_group->id; ?>">
													<?php echo __( "CB_COMMAND", "DEVICES" ); ?>
												</label>
											</div>
										<?php endif; ?>
									</td>
									<td class='dblcEdit'
									    data-target='csv'
									    data-field='position'>
										<?php echo $device_group->position; ?>
									</td>
									<td class='more static'><?php echo $device_group->id; ?></td>
									<td class='device_name'><a href='http://<?php echo $device_group->ip; ?>/'
									                           target='_blank'
									                           title='<?php echo __(
										                           "LINK_OPEN_DEVICE_WEBUI",
										                           "DEVICES"
									                           ); ?>'><?php echo str_replace(
												" ",
												"&nbsp;",
												$devicename
											); ?></a>
									</td>
									<td><?php echo $device_group->ip; ?></td>
									<td class='status'>
										<label class="form-switch">
											<input type="checkbox">
											<i></i>
										</label>

									</td>
									<td class='rssi'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='version'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='runtime'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='energyPower hidden'>
									<span>
										-
									</span>
									</td>
									<td class='temp hidden'>
									<span>
										-
									</span>
									</td>
									<td class='humidity hidden'>
									<span>
										-
									</span>
									</td>
									<td class='pressure hidden'>
									<span>
										-
									</span>
									</td>
									<td class='distance hidden'>
									<span>
										-
									</span>
									</td>
									<td class='gas hidden'>
									<span>
										-
									</span>
									</td>
									<td class='more hostname dblcEdit' data-cmnd='Hostname'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more mac'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more mqtt dblcEdit' data-cmnd='SetOption3'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more idx hidden'>
									<span>
										-
									</span>
									</td>
									<td class='more poweronstate dblcEdit' data-cmnd='PowerOnState'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more ledstate dblcEdit' data-cmnd='LedState'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more savedata dblcEdit' data-cmnd='SaveData'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more sleep dblcEdit' data-cmnd='Sleep'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more bootcount'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more savecount'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more log'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more wificonfig dblcEdit' data-cmnd='WifiConfig'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>
									<td class='more vcc'>
									<span>
										<div class='loader'>
											<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
											     alt='<?php echo __( "TEXT_LOADING" ); ?>'
											     title='<?php echo __( "TEXT_LOADING" ); ?>'>
										</div>
									</span>
									</td>


									<td class='col actions'>
										<a href='<?php echo _BASEURL_; ?>device_config/<?php echo $device_group->id; ?>'>
											<i class="fas fa-cogs fa-lg"
											   title='<?php echo __(
												   "LINK_DEVICE_CONFIG",
												   "DEVICES"
											   ); ?>'></i></a>
										<a href='<?php echo _BASEURL_; ?>device_action/edit/<?php echo $device_group->id; ?>'>
											<i class="fas fa-edit fa-lg"
											   title='<?php echo __( "LINK_DEVICE_EDIT", "DEVICES" ); ?>'></i></a>
										<a class="delete" data-toggle="modal" data-target="#deleteDeviceModal"
										   data-dialog-text='<?php echo __(
											   "DELETE_DEVICE_CONFIRM_TEXT",
											   "DEVICES",
											   [
												   $devicename,
												   $device_group->ip,
											   ]
										   ); ?>'
										   href='<?php echo _BASEURL_; ?>device_action/delete/<?php echo $device_group->id; ?>'>
											<i class="fas fa-trash fa-lg"
											   title='<?php echo __( "LINK_DEVICE_DELETE", "DEVICES" ); ?>'></i></a>
										<a href='#' class='restart-device'>
											<i class="fas fa-sync fa-lg"
											   title='<?php echo __( "LINK_DEVICE_RESTART", "DEVICES" ); ?>'></i></a>
									</td>

								</tr>
								<?php
								$odd = !$odd;
							endforeach;
						endforeach;
					endif; ?>
				</tbody>
				<tfoot>
				<tr class='bottom'>
					<th class='link cmd_cb d-none'>
						<div class="form-check custom-control custom-checkbox">
							<input class="form-check-input custom-control-input select_all"
							       type="checkbox"
							       value='select_all'
							       id="select_all"
							       name='select_all'>
							<label class="form-check-label custom-control-label" for="select_all">
								<?php echo __( "TABLE_HEAD_ALL", "DEVICES" ); ?>
							</label>
						</div>
					</th>
					<th><?php echo __( "TABLE_HEAD_POSITION", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "TABLE_HEAD_ID", "DEVICES" ); ?></th>
					<th><?php echo __( "TABLE_HEAD_NAME", "DEVICES" ); ?></th>
					<th><?php echo __( "TABLE_HEAD_IP", "DEVICES" ); ?></th>
					<th><?php echo __( "TABLE_HEAD_STATE", "DEVICES" ); ?></th>
					<th>
						<i class="fas fa-signal" title='<?php echo __( "TABLE_HEAD_RSSI", "DEVICES" ); ?>'></i>
					</th>
					<th><?php echo __( "TABLE_HEAD_VERSION", "DEVICES" ); ?></th>
					<th><?php echo __( "TABLE_HEAD_RUNTIME", "DEVICES" ); ?></th>
					<th class='energyPower hidden'><?php echo __( "TABLE_HEAD_ENERGY", "DEVICES" ); ?></th>
					<th class='temp hidden'><?php echo __( "TABLE_HEAD_TEMP", "DEVICES" ); ?></th>
					<th class='humidity hidden'><?php echo __( "TABLE_HEAD_HUMIDITY", "DEVICES" ); ?></th>
					<th class='pressure hidden'><?php echo __( "TABLE_HEAD_PRESSURE", "DEVICES" ); ?></th>
					<th class='distance hidden'><?php echo __( "TABLE_HEAD_DISTANCE", "DEVICES" ); ?></th>
					<th class='gas hidden'><?php echo __( "TABLE_HEAD_GAS", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "HOSTNAME", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "MAC", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "MQTT", "DEVICES" ); ?></th>
					<th class='more idx hidden'><?php echo __( "TABLE_HEAD_IDX", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "POWERONSTATE", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "LEDSTATE", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "SAVEDATA", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "SLEEP", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "BOOTCOUNT", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "SAVECOUNT", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "LOGSTATES", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "WIFICONFIG", "DEVICES" ); ?></th>
					<th class='more'><?php echo __( "VCC", "DEVICES" ); ?></th>
					<th class='link'>
						<a href='<?php echo _BASEURL_; ?>device_action/add'>
							<i class="fas fa-plus add"
							   title='<?php echo __( "TABLE_HEAD_NEW_DEVICE", "DEVICES" ); ?>'></i>
							<?php echo __( "TABLE_HEAD_NEW_DEVICE", "DEVICES" ); ?>
						</a>
					</th>
				</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<div class='row mt-3'>
		<div class='col-auto offset-0 offset-sm-1'>
			<div class="form-group">
				<input type="checkbox"
				       class="form-check-input showmore d-none"
				       id="showmore"
				       name='showmore'>
				<label class="form-check-label  btn btn-secondary" for="showmore">
					<?php echo __( "SHOW_MORE", "DEVICES" ); ?>
				</label>
			</div>
		</div>
		<div class="col-auto">
			<button class='btn btn-secondary showCommandInput'>
				<?php echo __( "BTN_COMMAND", "DEVICES" ); ?>
			</button>
		</div>
	</div>
	<div class='cmdContainer row command-hidden d-none'>
		<div class="form-group col-12 col-sm-6 col-md-7 col-lg-8 offset-0 offset-sm-1 mb-1 mb-sm-0">
			<input type='text' name='command' class='form-control commandInput'>
		</div>
		<div class="form-group col-12 col-sm-4 col-md-3 col-lg-2 mb-0">
			<button type='submit' class='btn btn-primary sendCommand w-100' name='sendCommand'>
				<?php echo __( "SEND_COMMAND", "DEVICES" ); ?>
			</button>
		</div>
		<small id="commandInputError" class="form-text col-12 col-sm-11 offset-0 offset-sm-1 d-none">
		</small>
	</div>
<?php else: ?>
	<div class='row'>
		<div class='col-12 text-center'>
			<?php echo __( "NO_DEVICES_FOUND", "STARTPAGE" ); ?>
		</div>
	</div>
	<div class='row mt-5 justify-content-center text-center'>
		<div class='col-12 col-sm-2 '>
			<a class="btn btn-primary"
			   href="<?php echo _BASEURL_; ?>devices_autoscan">
				<?php echo __( "DEVICES_AUTOSCAN", "NAVI" ); ?>
			</a>
		</div>
		<div class='col-12 col-sm-2 '>
			<a href='<?php echo _BASEURL_; ?>device_action/add' class="btn btn-primary">
				<?php echo __( "TABLE_HEAD_NEW_DEVICE", "DEVICES" ); ?>
			</a>
		</div>
	</div>

<?php endif; ?>
<?php include "elements/modal_delete_device.php"; ?>

<script type='text/javascript' src='<?php echo _RESOURCESURL_; ?>js/devices.js?<?php echo time(); ?>'></script>
