<?php
	$msg            = "";
	$error          = FALSE;
	$firmwarefolder = "data/firmwares/";
	
	$files = glob( $firmwarefolder.'*' ); // get all file names
	foreach ( $files as $file ) { // iterate files
		if ( is_file( $file ) && strpos( $file, ".empty" ) === FALSE ) {
			
			unlink( $file );
		} // delete file
	}
	
	
	if ( isset( $_POST[ "upload" ] ) ) {
		try {
			
			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if ( !isset( $_FILES[ 'minimal_firmware' ][ 'error' ] )
			     || is_array( $_FILES[ 'minimal_firmware' ][ 'error' ] ) ) {
				throw new RuntimeException( 'Invalid parameters.' );
			}
			
			// Check $_FILES['minimal_firmware']['error'] value.
			switch ( $_FILES[ 'minimal_firmware' ][ 'error' ] ) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					throw new RuntimeException( 'Server Error: Keine Datei übertragen' );
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException( 'Server Error: Dateigröße zu groß' );
				default:
					throw new RuntimeException( 'Server Error: Unbekannter Fehler' );
			}
			
			// You should also check filesize here.
			if ( $_FILES[ 'minimal_firmware' ][ 'size' ] > 502000 ) {
				throw new RuntimeException( 'Datei zu groß (max. 502kb)' );
			}
			
			if ( $_FILES[ 'minimal_firmware' ][ "type" ] == "application/octet-stream" ) {
				$ext = "bin";
			} else {
				throw new RuntimeException( 'Falsches Format! Bitte eine BIN Datei auswählen!' );
			}
			//		$finfo = new finfo( FILEINFO_MIME_TYPE );
			//		if ( FALSE === $ext = array_search(
			//				$finfo->file( $_FILES[ 'minimal_firmware' ][ 'tmp_name' ] ),
			//				array(
			//					'bin' => 'application/octet-stream',
			//				),
			//				TRUE
			//			) ) {
			//			throw new RuntimeException( 'Invalid file format.' );
			//		}
			
			$minimal_firmware_path = sprintf(
				$firmwarefolder.'%s-%s.%s',
				$_FILES[ 'minimal_firmware' ][ 'name' ],
				substr( sha1_file( $_FILES[ 'minimal_firmware' ][ 'tmp_name' ] ), 0, 6 ),
				$ext
			);
			
			if ( !move_uploaded_file(
				$_FILES[ 'minimal_firmware' ][ 'tmp_name' ],
				$minimal_firmware_path
			) ) {
				throw new RuntimeException( 'Konnte Datei nicht speichern!' );
			}
			
			$msg .= "Minimal Firmware: Erfolgreich hochgeladen!</br>";
			
		}
		catch ( RuntimeException $e ) {
			$error = TRUE;
			$msg   .= "Minimal Firmware: ".$e->getMessage()."!</br>";
			
		}
		
		try {
			
			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if ( !isset( $_FILES[ 'new_firmware' ][ 'error' ] )
			     || is_array( $_FILES[ 'new_firmware' ][ 'error' ] ) ) {
				throw new RuntimeException( 'Invalid parameters.' );
			}
			
			// Check $_FILES['new_firmware']['error'] value.
			switch ( $_FILES[ 'new_firmware' ][ 'error' ] ) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					throw new RuntimeException( 'Server Error: Keine Datei übertragen' );
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException( 'Server Error: Dateigröße zu groß' );
				default:
					throw new RuntimeException( 'Server Error: Unbekannter Fehler' );
			}
			
			// You should also check filesize here.
			if ( $_FILES[ 'new_firmware' ][ 'size' ] > 1000000 ) {
				throw new RuntimeException( 'Datei zu groß (max. 502kb)' );
			}
			
			if ( $_FILES[ 'new_firmware' ][ "type" ] == "application/octet-stream" ) {
				$ext = "bin";
			} else {
				throw new RuntimeException( 'Falsches Format! Bitte eine BIN Datei auswählen!' );
			}
			//		$finfo = new finfo( FILEINFO_MIME_TYPE );
			//		if ( FALSE === $ext = array_search(
			//				$finfo->file( $_FILES[ 'new_firmware' ][ 'tmp_name' ] ),
			//				array(
			//					'bin' => 'application/octet-stream',
			//				),
			//				TRUE
			//			) ) {
			//			throw new RuntimeException( 'Invalid file format.' );
			//		}
			
			$new_firmware_path = sprintf(
				$firmwarefolder.'%s-%s.%s',
				$_FILES[ 'new_firmware' ][ 'name' ],
				substr( sha1_file( $_FILES[ 'new_firmware' ][ 'tmp_name' ] ), 0, 6 ),
				$ext
			);
			if ( !move_uploaded_file(
				$_FILES[ 'new_firmware' ][ 'tmp_name' ],
				
				$new_firmware_path
			) ) {
				throw new RuntimeException( 'Konnte Datei nicht speichern!' );
			}
			
			$msg .= "Neue Firmware: Erfolgreich hochgeladen!</br>";
			
		}
		catch ( RuntimeException $e ) {
			$error = TRUE;
			$msg   .= "Neue Firmware: ".$e->getMessage()."!</br>";
			
		}
	} else if ( $_POST[ "auto" ] ) {
		//File to save the contents to
		
		
		$url = "https://api.github.com/repos/arendst/Sonoff-Tasmota/releases/latest";
		
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt(
			$ch,
			CURLOPT_USERAGENT,
			'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'
		);
		$result = curl_exec( $ch );
		curl_close( $ch );
		
		$data = json_decode( $result );
		
		
		foreach ( $data->assets as $binfileData ) {
			if ( $binfileData->name == "sonoff-DE.bin" ) {
				$fwUrl = $binfileData->browser_download_url;
			}
			if ( $binfileData->name == "sonoff-minimal.bin" ) {
				$fwMinimalUrl = $binfileData->browser_download_url;
			}
		}
		if ( isset( $fwUrl ) && isset( $fwMinimalUrl ) ) {
			$minimal_firmware_path = $firmwarefolder.'sonoff-minimal.bin';
			$new_firmware_path     = $firmwarefolder.'sonoff-DE.bin';
			$file                  = fopen( $minimal_firmware_path, 'w' );
			// cURL
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $fwMinimalUrl );
			// set cURL options
			curl_setopt( $ch, CURLOPT_FAILONERROR, TRUE );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			// set file handler option
			curl_setopt( $ch, CURLOPT_FILE, $file );
			// execute cURL
			curl_exec( $ch );
			// close cURL
			curl_close( $ch );
			// close file
			fclose( $file );
			
			$file = fopen( $new_firmware_path, 'w' );
			$ch   = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $fwUrl );
			// set cURL options
			curl_setopt( $ch, CURLOPT_FAILONERROR, TRUE );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			// set file handler option
			curl_setopt( $ch, CURLOPT_FILE, $file );
			// execute cURL
			curl_exec( $ch );
			// close cURL
			curl_close( $ch );
			// close file
			fclose( $file );
			$msg .= "Firmware erfolgreich von GitHUB geladen!<br/>";
			$msg .= "Version: ".$data->tag_name." | vom ".$data->published_at;
		} else {
			$error = TRUE;
			$msg   .= "Konnte Firmware nicht von GitHUB laden!";
		}
		
	} else {
		$error = TRUE;
		$msg   .= "Keine Firmware ausgewählt!</br>";
	}
	
	
	$ota_server_ip = $_POST[ "ota_server_ip" ];
	
	$Config->write( "ota_server_ip", $ota_server_ip );


?>


<?php if ( $error ): ?>
	<div class='center'>
		<p>
			<?php echo $msg; ?>
		</p>
	</div>

<?php else: ?>
	<?php
	$file = fopen( $filename, 'r' );
	while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
		//$line is an array of the csv elements
		$line[ 1 ] = explode( "|", $line[ 1 ] );
		$devices[] = $line;
	}
	fclose( $file );
	
	?>
	<div class='center'>
		<p>
			<?php echo $msg; ?>
		</p>
		<p>
			Wähle die Geräte zum Update aus:
		</p>
	</div>
	<form name='update_devices'
	      class='center'
	      id='update_devices'
	      method='post'
	      action='index.php?page=device_update'>
		<input type='hidden' name='minimal_firmware_path' value='<?php echo $minimal_firmware_path; ?>'>
		<input type='hidden' name='new_firmware_path' value='<?php echo $new_firmware_path; ?>'>
		<table id='device-list' class='center-table' border='0' cellspacing='0'>
			<thead>
			<tr>
				<td colspan='8'>
					<button type='submit' class='btn' name='submit' value='submit'>Starte Update</button>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<th>
					<input class='select_all' type='checkbox' name='select_all' value='select_all'
					       checked='checked'>
					<label for='select_all'>Alle</label>
				</th>
				<th>ID</th>
				<th>Name</th>
				<th>IP</th>
				<th>Status</th>
				<th>RSSI</th>
				<th>Version</th>
				<th>Laufzeit</th>
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
								<td>
									<?php if ( $key == 0 ): ?>
										<input type='checkbox'
										       name='device_ips[]'
										       value='<?php echo $device_group[ 2 ]; ?>'
										       checked='checked'
										       class='device_checkbox'
										>
									<?php endif; ?>
								</td>
								<td><?php echo $device_group[ 0 ]; ?></td>
								<td><?php echo $device; ?></td>
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
							</tr>
							<?php
							$odd = !$odd;
						endforeach;
					endforeach;
				endif; ?>
			</tbody>
			<tfoot>
			<tr class='bottom'>
				<th>
					<input class='select_all' type='checkbox' name='select_all' value='all'
					       checked='checked'>
					<label for='select_all'>Alle</label>
				</th>
				<th>ID</th>
				<th>Name</th>
				<th>IP</th>
				<th>Status</th>
				<th>RSSI</th>
				<th>Version</th>
				<th>Laufzeit</th>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan='8'>
					<button type='submit' class='btn' name='submit' value='submit'>Starte Update</button>
				</td>
			</tr>
			</tfoot>
		</table>
	</form>
	
	<script>
		$( document ).on( "ready", function () {
			//select all checkboxes
			$( ".select_all" ).change( function () {  //"select all" change
				var status = this.checked; // "select all" checked status
				$( '.device_checkbox' ).each( function () { //iterate all listed checkbox items
					this.checked = status; //change ".checkbox" checked status
				} );
				
				$( '.select_all' ).each( function () { //iterate all listed checkbox items
					this.checked = status; //change ".checkbox" checked status
				} );
				
			} );
			
			$( '.device_checkbox' ).change( function () { //".checkbox" change
				//uncheck "select all", if one of the listed checkbox item is unchecked
				if ( this.checked == false ) { //if this item is unchecked
					$( '.select_all' ).each( function () { //iterate all listed checkbox items
						this.checked = false; //change ".checkbox" checked status
					} );
				}
				
				//check "select all" if all checkbox items are checked
				if ( $( '.device_checkbox:checked' ).length == $( '.device_checkbox' ).length ) {
					$( '.select_all' ).each( function () { //iterate all listed checkbox items
						this.checked = true; //change ".checkbox" checked status
					} );
				}
			} );
			
		} );
	</script>
	<script type='text/javascript' src='/resources/js/devices.js'></script>
<?php endif; ?>


