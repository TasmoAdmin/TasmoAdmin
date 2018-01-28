<?php
	
	$devices = $Sonoff->getDevices();
	
	
	//var_dump( $devices );
?>

<table id='device-list' class='center-table' border='0' cellspacing='0'>
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
		<th>
			<a href='<?php echo _APPROOT_; ?>index.php?page=device_action&action=add'>
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
							<div class='loader'><img
										src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
										alt='<?php echo __( "TEXT_LOADING" ); ?>'
										title='<?php echo __( "TEXT_LOADING" ); ?>'></div>
						</td>
						<td class='version'>
							<div class='loader'><img
										src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
										alt='<?php echo __( "TEXT_LOADING" ); ?>'
										title='<?php echo __( "TEXT_LOADING" ); ?>'></div>
						</td>
						<td class='runtime'>
							<div class='loader'><img
										src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
										alt='<?php echo __( "TEXT_LOADING" ); ?>'
										title='<?php echo __( "TEXT_LOADING" ); ?>'></div>
						</td>
						<td class='actions'>
							<a href='<?php echo _APPROOT_; ?>index.php?page=device_config&device_id=<?php echo $device_group->id; ?>'>
								<i class="fas fa-cogs fa-lg"
								   title='<?php echo __( "LINK_DEVICE_CONFIG", "DEVICES" ); ?>'></i>
							</a>
							<a href='<?php echo _APPROOT_; ?>index.php?page=device_action&action=edit&device_id=<?php echo $device_group->id; ?>'>
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
							
							   href='<?php echo _APPROOT_; ?>index.php?page=device_action&action=delete&device_id=<?php echo $device_group->id; ?>'>
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
		<th>
			<a href='<?php echo _APPROOT_; ?>index.php?page=device_action&action=add'>
				<i class="fas fa-plus add"
				   title='<?php echo __( "TABLE_HEAD_NEW_DEVICE", "DEVICES" ); ?>'></i>
				<?php echo __( "TABLE_HEAD_NEW_DEVICE", "DEVICES" ); ?>
			</a>
		</th>
	</tr>
	</tfoot>
</table>


<script type='text/javascript' src='<?php echo _RESOURCESDIR_; ?>js/devices.js'></script>