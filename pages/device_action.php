<?php
	
	//	var_dump( $_GET );
	$action = $_GET[ "action" ];
	
	$device = NULL;
	$msg    = NULL;
	if ( $action == "edit" ) {
		$file = fopen( $filename, 'r' );
		while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
			//$line is an array of the csv elements
			//var_dump( $line );
			if ( $line[ 0 ] == $_GET[ "device_id" ] ) {
				$device = $line;
				break;
			}
		}
		fclose( $file );
	} else if ( $action == "delete" ) {
		$device[ 0 ] = $_GET[ "device_id" ];
		$tempfile    = @tempnam( "../data/", "tmp" ); // produce a temporary file name, in the current directory
		
		if ( !$input = fopen( $filename, 'r' ) ) {
			die( 'could not open existing csv file' );
		}
		if ( !$output = fopen( $tempfile, 'w' ) ) {
			die( 'could not open temporary output file' );
		}
		
		while ( ( $data = fgetcsv( $input ) ) !== FALSE ) {
			if ( $data[ 0 ] == $device[ 0 ] ) {
				continue;
			}
			fputcsv( $output, $data );
		}
		
		fclose( $input );
		fclose( $output );
		
		unlink( $filename );
		rename( $tempfile, $filename );
		
		$msg    = "Gerät entfernt";
		$action = "done";
	}
	
	if ( isset( $_POST ) && !empty( $_POST ) ) {
		//		var_dump( $_POST );
		if ( !empty( $_POST[ 'device_id' ] ) ) {//update
			$device[ 0 ] = $_POST[ "device_id" ];
			$device[ 1 ] = $_POST[ "device_name" ];
			$device[ 2 ] = $_POST[ "device_ip" ];
			
			$tempfile = @tempnam( "../data/", "tmp" ); // produce a temporary file name, in the current directory
			
			if ( !$input = fopen( $filename, 'r' ) ) {
				die( 'could not open existing csv file' );
			}
			if ( !$output = fopen( $tempfile, 'w' ) ) {
				die( 'could not open temporary output file' );
			}
			
			while ( ( $data = fgetcsv( $input ) ) !== FALSE ) {
				if ( $data[ 0 ] == $device[ 0 ] ) {
					$data = $device;
				}
				fputcsv( $output, $data );
			}
			
			fclose( $input );
			fclose( $output );
			
			unlink( $filename );
			rename( $tempfile, $filename );
			
			$msg    = "Gerät aktualisiert";
			$action = "done";
			
		} else { //add
			$fp          = file( $filename );
			$device[ 0 ] = count( $fp ) + 1;
			$device[ 1 ] = $_POST[ "device_name" ];
			$device[ 2 ] = $_POST[ "device_ip" ];
			
			$handle = fopen( $filename, "a" );
			fputcsv( $handle, $device );
			fclose( $handle );
			
			$msg    = "Gerät hinzugefügt";
			$action = "done";
		}
		
	}

?>

<?php if ( $action == "add" || $action == "edit" ): ?>
	<form class='form' name='save_device' method='post' action='/index.php?page=device_action&action=add'>
		<input type='hidden' name='device_id' value='<?php echo isset( $device ) ? $device[ 0 ] : ""; ?>'>
		<table class='center-table' border='0' cellspacing='0'>
			<tr>
				<td>Name:</td>
				<td><input type='text'
				           id="device_name"
				           name='device_name'
				           required
				           value='<?php echo isset( $device ) ? $device[ 1 ] : ""; ?>'></td>
			</tr>
			<tr>
				<td>IP:</td>
				<td><input type='text'
				           id="device_ip"
				           name='device_ip'
				           required
				           value='<?php echo isset( $device ) ? $device[ 2 ] : ""; ?>'></td>
			</tr>
			<tr>
				<td>
					&nbsp;
				</td>
				<td style='text-align: right'>
					<button type='submit'
					        name='submit'
					        value='<?php echo isset( $device ) ? "edit" : "add"; ?>'
					        class='btn'
					>
						Speichern
					</button>
				</td>
			</tr>
		
		</table>
	</form>

<?php elseif ( $action == "done" ): ?>
	<div class='center'>
		<p><?php echo $msg; ?></p>
		<a href='/index.php?page=devices'>Zurück</a>
	</div>
<?php endif; ?>
