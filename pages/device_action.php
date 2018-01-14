<?php
	
	//	var_dump( $_GET );
	$action = $_GET[ "action" ];
	$status = FALSE;
	$device = NULL;
	$msg    = NULL;
	if ( $action == "edit" ) {
		$file = fopen( $filename, 'r' );
		while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
			//$line is an array of the csv elements
			//var_dump( $line );
			if ( $line[ 0 ] == $_GET[ "device_id" ] ) {
				$line[ 1 ] = explode( "|", $line[ 1 ] );
				$device    = $line;
				break;
			}
		}
		fclose( $file );
		
		$status = $Sonoff->getAllStatus( $device[ 2 ] );
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
		
		if ( isset( $_POST[ "search" ] ) ) {
			if ( isset( $_POST[ 'device_ip' ] ) ) {
				$status = $Sonoff->getAllStatus( $_POST[ 'device_ip' ] );
			} else {
				die( "Bitte IP eingeben" );
			}
		} else if ( !empty( $_POST[ 'device_id' ] ) ) {//update
			$device[ 0 ] = $_POST[ "device_id" ];
			$device[ 1 ] = implode( "|", $_POST[ "device_name" ] );
			$device[ 2 ] = $_POST[ "device_ip" ];
			
			$tempfile = @tempnam( "data/", "tmp" ); // produce a temporary file name, in the current directory
			
			
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
			
			if ( isset( $_POST[ "search" ] ) ) {
				if ( isset( $_POST[ 'device_ip' ] ) ) {
					$status = $Sonoff->getAllStatus( $_POST[ 'device_ip' ] );
				} else {
					die( "Bitte IP eingeben" );
				}
			} else {
				$fp          = file( $filename );
				$device[ 0 ] = count( $fp ) + 1;
				$device[ 1 ] = implode( "|", isset( $_POST[ "device_name" ] ) ? $_POST[ "device_name" ] : array() );
				$device[ 2 ] = isset( $_POST[ "device_ip" ] ) ? $_POST[ "device_ip" ] : "";
				
				
				$handle = fopen( $filename, "a" );
				fputcsv( $handle, $device );
				fclose( $handle );
				
				$msg    = "Gerät hinzugefügt";
				$action = "done";
			}
			
		}
		
	}

?>

<?php if ( $action == "add" || $action == "edit" ): ?>
	<form class='form'
	      name='save_device'
	      method='post'
	      action='/index.php?page=device_action&action=<?php echo $action ?><?php echo isset( $device ) ? "&device_id="
	                                                                                                      .$device[ 0 ]
		      : "" ?>'>
		<input type='hidden' name='device_id' value='<?php echo isset( $device ) ? $device[ 0 ] : ""; ?>'>
		<table class='center-table' border='0' cellspacing='0'>
			<tr>
				<td>IP vom Sonoff:</td>
				<td><input type='text'
				           id="device_ip"
				           name='device_ip'
				           required
				           value='<?php echo( isset( $device ) && !isset( $_POST[ 'device_ip' ] ) ? $device[ 2 ]
					           : ( isset( $_POST[ 'device_ip' ] ) ? $_POST[ 'device_ip' ] : "" ) ); ?>'></td>
				<td>
					<button type='submit'
					        name='search'
					        value='search'
					        class='btn'
					>
						Suchen
					</button>
				</td>
			</tr>
			
			
			<?php if ( isset( $status ) && !empty( $status ) ): ?>
				<?php if ( isset( $status->WARNING ) && !empty( $status->WARNING ) ): ?>
					<tr>
						<td colspan='3' style='text-align: center; margin-top: 20px; '>
							<p> Gerät gefunden!</p>
							<p class='error' style='color: red;'>FEHLER: <?php echo $status->WARNING; ?></p>
						</td>
					</tr>
				<?php else: ?>
					<tr>
						<td colspan='3' style='text-align: center; margin-top: 20px;'>
							<br/><br/>Gerät gefunden!<br/><br/>
						</td>
					</tr>
					<?php if ( isset( $status->StatusSTS->POWER ) ): ?>
						<tr>
							<td>Name:</td>
							<td><input type='text'
							           id="device_name"
							           name='device_name[1]'
							           required
							           value='<?php echo isset( $device )
								           ? $device[ 1 ][ 0 ]
								           : ( isset( $_POST[ 'device_name' ][ 1 ] ) ? $_POST[ 'device_name' ][ 1 ]
									           : $status->Status->FriendlyName ); ?>'></td>
							<td class='default-value'>( <a href='#' title='Übernehmen'
							                               class='default-name'><?php echo $status->Status->FriendlyName; ?></a>
							                          )
							</td>
						</tr>
					<?php endif; ?>
					
					
					<?php
					$i     = 1;
					$power = "POWER".$i;
					while ( isset( $status->StatusSTS->$power ) )  : ?>
						<tr>
							<td>Name <?php echo $i; ?>:</td>
							<td><input type='text'
							           id="device_name"
							           name='device_name[<?php echo $i; ?>]'
							           required
							           value='<?php echo isset( $device[ 1 ][ $i - 1 ] )
							                             && !empty(
							           $device[ 1 ][ $i - 1 ]
							           )
								           ? $device[ 1 ][ $i - 1 ]
								           : ( isset( $_POST[ 'device_name' ][ $i ] ) ? $_POST[ 'device_name' ][ $i ]
									           : $status->Status->FriendlyName." ".$i ); ?>'></td>
							<td class='default-value'>( <a href='#' title='Übernehmen'
							                               class='default-name'><?php echo $status->Status->FriendlyName
							                                                               ." "
							                                                               .$i; ?></a> )
							</td>
						</tr>
						
						
						<?php
						
						$i++;
						$power = "POWER".$i;
						?>
					
					<?php endwhile; ?>
					<tr>
						<td style='text-align: right' colspan='3'>
							<br/><br/>
							<button type='submit'
							        name='submit'
							        value='<?php echo isset( $device ) ? "edit" : "add"; ?>'
							        class='btn'
							>
								Speichern
							</button>
						</td>
					</tr>
				<?php endif; ?>
			
			<?php elseif ( isset( $status ) && $status == FALSE ): ?>
				<div class='center'>
					<p><?php echo "Gerät konnte nicht gefunden werden => ".print_r( $status, TRUE ); ?></p>
				
				</div>
			<?php endif; ?>
		</table>
	</form>


<?php elseif ( $action == "done" ): ?>
	<div class='center'>
		<p><?php echo $msg; ?></p>
		<a href='/index.php?page=devices'>Zurück</a>
	</div>
<?php endif; ?>

<script>
	$( document ).on( "ready", function () {
		$( ".default-name" ).on( "click", function ( e ) {
			e.preventDefault();
			console.log( $( this ).parent().parent().find( "input" ) );
			$( this ).parent().parent().find( "input" ).val( $( this ).html() );
		} );
	} );
</script>
