<?php
	
	$devices = $Sonoff->getDevices();
	
	
	//var_dump( $devices );
?>

<div class='btn-group'>
	<div style='text-align: right;'>
		<input type='checkbox' name='showmore' id='showmore' class='showmore'>
		<label for='showmore'><?php echo __( "SHOW_MORE", "DEVICES" ); ?></label>
	</div>
</div>
<table id='device-list'
       class='center-table tablesaw tablesaw-stack'
       data-tablesaw-mode="stack"
       border='0'
       cellspacing='0'>
	<thead>
	<tr>
		<th><?php echo __( "TABLE_HEAD_ID", "DEVICES" ); ?></th>
		<th><?php echo __( "TABLE_HEAD_NAME", "DEVICES" ); ?></th>
		<th><?php echo __( "TABLE_HEAD_IP", "DEVICES" ); ?></th>
		<th><?php echo __( "TABLE_HEAD_STATE", "DEVICES" ); ?></th>
		<th>
			<i class="fas fa-signal" title='<?php echo __( "TABLE_HEAD_RSSI", "DEVICES" ); ?>'></i>
		</th>
		<th><?php echo __( "TABLE_HEAD_VERSION", "DEVICES" ); ?></th>
		<th><?php echo __( "TABLE_HEAD_RUNTIME", "DEVICES" ); ?></th>
		<th class='temp hidden'><?php echo __( "TABLE_HEAD_TEMP", "DEVICES" ); ?></th>
		<th class='humidity hidden'><?php echo __( "TABLE_HEAD_HUMIDITY", "DEVICES" ); ?></th>
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
		if ( isset( $devices ) && !empty( $devices ) ):
			foreach ( $devices as $device_group ):
				foreach ( $device_group->names as $key => $devicename ): ?>
					<tr class='<?php echo $odd ? "odd" : "even"; ?>'
					    data-device_id='<?php echo $device_group->id; ?>'
					    data-device_group='<?php echo count( $device_group->names ) > 1 ? "multi" : "single"; ?>'
					    data-device_ip='<?php echo $device_group->ip; ?>'
					    data-device_relais='<?php echo $key + 1; ?>'
					>
						<td><?php echo $device_group->id; ?></td>
						<td><a href='http://<?php echo $device_group->ip; ?>/'
						       target='_blank'
						       title='<?php echo __(
							       "LINK_OPEN_DEVICE_WEBUI",
							       "DEVICES"
						       ); ?>'><?php echo $devicename; ?></a>
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
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='version'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='runtime'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
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
						
						
						<td class='more hostname dblcEdit' data-cmnd='Hostname'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='more mac'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='more mqtt dblcEdit' data-cmnd='Mqtt'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
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
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='more ledstate dblcEdit' data-cmnd='LedState'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='more savedata dblcEdit' data-cmnd='SaveData'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='more sleep dblcEdit' data-cmnd='Sleep'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='more bootcount'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='more savecount'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='more log'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='more wificonfig dblcEdit' data-cmnd='WifiConfig'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						<td class='more vcc'>
							<span>
								<div class='loader'>
									<img src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
									     alt='<?php echo __( "TEXT_LOADING" ); ?>'
									     title='<?php echo __( "TEXT_LOADING" ); ?>'>
								</div>
							</span>
						</td>
						
						
						<td class='actions'>
							<a href='<?php echo _BASEURL_; ?>device_config/<?php echo $device_group->id; ?>'>
								<i class="fas fa-cogs fa-lg"
								   title='<?php echo __( "LINK_DEVICE_CONFIG", "DEVICES" ); ?>'></i>
							</a>
							<a href='<?php echo _BASEURL_; ?>device_action/edit/<?php echo $device_group->id; ?>'>
								<i class="fas fa-edit fa-lg"
								   title='<?php echo __( "LINK_DEVICE_EDIT", "DEVICES" ); ?>'></i>
							</a>
							<a class="delete"
							   data-dialog-btn-cancel-text='<?php echo __( "CANCEL" ); ?>'
							   data-dialog-btn-ok-text='<?php echo __( "DELETE_DEVICE", "DEVICES" ); ?>'
							   data-dialog-title='<?php echo __( "DELETE_DEVICE_CONFIRM_TITLE", "DEVICES" ); ?>'
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
								   title='<?php echo __( "LINK_DEVICE_DELETE", "DEVICES" ); ?>'></i>
							</a>
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
		<th><?php echo __( "TABLE_HEAD_ID", "DEVICES" ); ?></th>
		<th><?php echo __( "TABLE_HEAD_NAME", "DEVICES" ); ?></th>
		<th><?php echo __( "TABLE_HEAD_IP", "DEVICES" ); ?></th>
		<th><?php echo __( "TABLE_HEAD_STATE", "DEVICES" ); ?></th>
		<th>
			<i class="fas fa-signal" title='<?php echo __( "TABLE_HEAD_RSSI", "DEVICES" ); ?>'></i>
		</th>
		<th><?php echo __( "TABLE_HEAD_VERSION", "DEVICES" ); ?></th>
		<th><?php echo __( "TABLE_HEAD_RUNTIME", "DEVICES" ); ?></th>
		<th class='temp hidden'><?php echo __( "TABLE_HEAD_TEMP", "DEVICES" ); ?></th>
		<th class='humidity hidden'><?php echo __( "TABLE_HEAD_HUMIDITY", "DEVICES" ); ?></th>
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
<div class='btn-group'>
	<div style='text-align: right;'>
		<input type='checkbox' name='showmore' id='showmore' class='showmore'>
		<label for='showmore'><?php echo __( "SHOW_MORE", "DEVICES" ); ?></label>
	</div>
</div>

<script type='text/javascript' src='<?php echo _RESOURCESDIR_; ?>js/devices.js?<?php echo time(); ?>'></script>