<?php
	$file = fopen( $filename, 'r' );
	while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
		//$line is an array of the csv elements
		$line[ 1 ] = explode( "|", $line[ 1 ] );
		$devices[] = $line;
	}
	fclose( $file );
	
	
	//var_dump( $devices );
?>

<table id='device-list' class='center-table' border='0' cellspacing='0'>
	<thead>
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>IP</th>
		<th>Status</th>
		<th>RSSI</th>
		<th>Version</th>
		<th>Laufzeit</th>
		<th><a href='/index.php?page=device_action&action=add'>Neues Gerät</a></th>
	</tr>
	</thead>
	<tbody>
	<?php
		$odd = TRUE;
		if ( isset( $devices ) && !empty( $devices ) ):
			foreach ( $devices as $device_group ):
				foreach ( $device_group[ 1 ] as $key => $device ): ?>
					<tr class='<?php echo $odd ? "odd" : "even"; ?>'
					    data-device_id='<?php echo $device_group[ 0 ]; ?>'
					    data-device_group='<?php echo count( $device_group[ 1 ] ) > 1 ? "multi" : "single"; ?>'
					    data-device_ip='<?php echo $device_group[ 2 ]; ?>'
					    data-device_relais='<?php echo $key + 1; ?>'
					>
						<td><?php echo $device_group[ 0 ]; ?></td>
						<td><a href='http://<?php echo $device_group[ 2 ]; ?>/'
						       target='_blank'
						       title='Oberfläche aufrufen'><?php echo $device; ?></a>
						</td>
						<td><?php echo $device_group[ 2 ]; ?></td>
						<td class='status'>
							<label class="form-switch">
								<input type="checkbox">
								<i></i>
							</label>
						
						</td>
						<td class='rssi'>
							<div class='loader'><img
										src='/resources/img/loading.gif'
										alt='Lädt...'
										title='Lädt...'></div>
						</td>
						<td class='version'>
							<div class='loader'><img
										src='/resources/img/loading.gif'
										alt='Lädt...'
										title='Lädt...'></div>
						</td>
						<td class='runtime'>
							<div class='loader'><img
										src='/resources/img/loading.gif'
										alt='Lädt...'
										title='Lädt...'></div>
						</td>
						<td>
							<a href='/index.php?page=device_config&action=delete&device_id=<?php echo $device_group[ 0 ]; ?>'>Konfig</a>
							<a href='/index.php?page=device_action&action=edit&device_id=<?php echo $device_group[ 0 ]; ?>'>
								Bearbeiten</a>
							<a href='/index.php?page=device_action&action=delete&device_id=<?php echo $device_group[ 0 ]; ?>'>Löschen</a>
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
		<th>ID</th>
		<th>Name</th>
		<th>IP</th>
		<th>Status</th>
		<th>RSSI</th>
		<th>Version</th>
		<th>Laufzeit</th>
		<th><a href='/index.php?page=device_action&action=add'>Neues Gerät</a></th>
	</tr>
	</tfoot>
</table>


<script type='text/javascript' src='/resources/js/devices.js'></script>