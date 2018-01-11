<?php
	$file = fopen( $filename, 'r' );
	while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
		//$line is an array of the csv elements
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
		<th><a href='/index.php?page=device_action&action=add'>Neues Gerät</a></th>
	</tr>
	</thead>
	<tbody>
	<?php
		$odd = TRUE;
		if ( isset( $devices ) && !empty( $devices ) ):
			foreach ( $devices as $device ): ?>
				<tr class='<?php echo $odd ? "odd" : "even"; ?>'
				    data-device_id='<?php echo $device[ 0 ]; ?>'
				    data-device_ip='<?php echo $device[ 2 ]; ?>'
				
				>
					<td><?php echo $device[ 0 ]; ?></td>
					<td><a href='http://<?php echo $device[ 2 ]; ?>/'
					       target='_blank'
					       title='Oberfläche aufrufen'><?php echo $device[ 1 ]; ?></a>
					</td>
					<td><?php echo $device[ 2 ]; ?></td>
					<td class='status'>Lädt...</td>
					<td>
						<a href='/index.php?page=device_action&action=edit&device_id=<?php echo $device[ 0 ]; ?>'>Bearbeiten</a>
						<a href='/index.php?page=device_action&action=delete&device_id=<?php echo $device[ 0 ]; ?>'>Löschen</a>
					</td>
				</tr>
				<?php
				$odd = !$odd;
			endforeach;
		endif; ?>
	</tbody>
	<tfoot>
	<tr class='bottom'>
		<th>ID</th>
		<th>Name</th>
		<th>IP</th>
		<th>Status</th>
		<th><a href='/index.php?page=device_action&action=add'>Neues Gerät</a></th>
	</tr>
	</tfoot>
</table>


<script type='text/javascript' src='/resources/js/devices.js'></script>