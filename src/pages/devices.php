<?php
	$filename = "../data/devices.csv";
	$file     = fopen( $filename, 'r' );
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
		<th><a href='/pages/device_action.php?action=add'>Neues Gerät</a></th>
	</tr>
	</thead>
	<tbody>
	<?php
		$odd = TRUE;
		foreach ( $devices as $device ): ?>
			<tr class='<?php echo $odd ? "odd" : "even"; ?>'
			    data-device_id='<?php echo $device[ 0 ]; ?>'
			    data-device_ip='<?php echo $device[ 2 ]; ?>'
			
			>
				<td><?php echo $device[ 0 ]; ?></td>
				<td><?php echo $device[ 1 ]; ?></td>
				<td><?php echo $device[ 2 ]; ?></td>
				<td class='status'>Lädt...</td>
				<td>
					<a href='/pages/device_action.php?action=update&device_id=<?php echo $device[ 0 ]; ?>'>Update</a>
					<a href='/pages/device_action.php?action=edit&device_id=<?php echo $device[ 0 ]; ?>'>Bearbeiten</a>
					<a href='/pages/device_action.php?action=delete&device_id=<?php echo $device[ 0 ]; ?>'>Löschen</a>
				</td>
			</tr>
			<?php
			$odd = !$odd;
		endforeach; ?>
	</tbody>
	<tfoot>
	<tr class='bottom'>
		<th>ID</th>
		<th>Name</th>
		<th>IP</th>
		<th>Status</th>
		<th><a href='/pages/device_action.php?action=add'>Neues Gerät</a></th>
	</tr>
	</tfoot>
</table>

<script>
	$( document ).on( "ready" );
	{
	
	
	}
</script>