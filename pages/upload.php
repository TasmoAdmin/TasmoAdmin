<?php
	$msg            = "";
	$error          = FALSE;
	$firmwarefolder = _DATADIR_."firmwares/";
	
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
				throw new RuntimeException( __( "UPLOAD_FIRMWARE_MINIMAL_INVALID_FILES", "DEVICE_UPDATE" ) );
			}
			
			// Check $_FILES['minimal_firmware']['error'] value.
			switch ( $_FILES[ 'minimal_firmware' ][ 'error' ] ) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					throw new RuntimeException( __( "UPLOAD_FIRMWARE_MINIMAL_ERR_NO_FILE", "DEVICE_UPDATE" ) );
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException( __( "UPLOAD_FIRMWARE_MINIMAL_ERR_FORM_SIZE", "DEVICE_UPDATE" ) );
				default:
					throw new RuntimeException( __( "UPLOAD_FIRMWARE_MINIMAL_UNKNOWN_ERROR", "DEVICE_UPDATE" ) );
			}
			
			// You should also check filesize here.
			if ( $_FILES[ 'minimal_firmware' ][ 'size' ] > 502000 ) {
				throw new RuntimeException(
					__( "UPLOAD_FIRMWARE_MINIMAL_TOO_BIG", "DEVICE_UPDATE", [ "maxsize" => "502kb" ] )
				);
			}
			
			if ( $_FILES[ 'minimal_firmware' ][ "type" ] == "application/octet-stream" ) {
				$ext = "bin";
			} else {
				throw new RuntimeException(
					__(
						"UPLOAD_FIRMWARE_MINIMAL_WRONG_FORMAT",
						"DEVICE_UPDATE",
						$_FILES[ 'minimal_firmware' ][ "type" ]
					)
				);
			}
			
			
			$minimal_firmware_path = $firmwarefolder."sonoff-minimal.bin";
			
			if ( !move_uploaded_file(
				$_FILES[ 'minimal_firmware' ][ 'tmp_name' ],
				$minimal_firmware_path
			) ) {
				throw new RuntimeException(
					__(
						"UPLOAD_FIRMWARE_MINIMAL_COULD_NOT_SAVE",
						"DEVICE_UPDATE",
						[ "FWPath" => $minimal_firmware_path ]
					)
				);
			}
			
			$msg .= __( "UPLOAD_FIRMWARE_MINIMAL_LABEL", "DEVICE_UPDATE" ).": ".__(
					"UPLOAD_FIRMWARE_MINIMAL_SUCCESSFULLY",
					"DEVICE_UPDATE"
				)."</br>";
			
		}
		catch ( RuntimeException $e ) {
			$error = TRUE;
			$msg   .= __( "UPLOAD_FIRMWARE_MINIMAL_LABEL", "DEVICE_UPDATE" ).": ".$e->getMessage()."!</br>";
			
		}
		
		try {
			
			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if ( !isset( $_FILES[ 'new_firmware' ][ 'error' ] )
			     || is_array( $_FILES[ 'new_firmware' ][ 'error' ] ) ) {
				throw new RuntimeException( __( "UPLOAD_FIRMWARE_FULL_INVALID_FILES", "DEVICE_UPDATE" ) );
			}
			
			// Check $_FILES['new_firmware']['error'] value.
			switch ( $_FILES[ 'new_firmware' ][ 'error' ] ) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					throw new RuntimeException( __( "UPLOAD_FIRMWARE_FULL_ERR_NO_FILE", "DEVICE_UPDATE" ) );
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException( __( "UPLOAD_FIRMWARE_FULL_ERR_FORM_SIZE", "DEVICE_UPDATE" ) );
				default:
					throw new RuntimeException( __( "UPLOAD_FIRMWARE_FULL_UNKNOWN_ERROR", "DEVICE_UPDATE" ) );
			}
			
			// You should also check filesize here.
			if ( $_FILES[ 'new_firmware' ][ 'size' ] > 1000000 ) {
				throw new RuntimeException( __( "UPLOAD_FIRMWARE_FULL_TOO_BIG", "DEVICE_UPDATE" ) );
			}
			
			if ( $_FILES[ 'new_firmware' ][ "type" ] == "application/octet-stream" ) {
				$ext = "bin";
			} else {
				throw new RuntimeException(
					__( "UPLOAD_FIRMWARE_FULL_WRONG_FORMAT", "DEVICE_UPDATE", $_FILES[ 'new_firmware' ][ "type" ] )
				);
			}
			
			$new_firmware_path = $firmwarefolder."sonoff-full.bin";
			
			if ( !move_uploaded_file(
				$_FILES[ 'new_firmware' ][ 'tmp_name' ],
				
				$new_firmware_path
			) ) {
				throw new RuntimeException( __( "UPLOAD_FIRMWARE_FULL_COULD_NOT_SAVE", "DEVICE_UPDATE" ) );
			}
			
			$msg .= __( "UPLOAD_FIRMWARE_FULL_LABEL", "DEVICE_UPDATE" ).": ".__(
					"UPLOAD_FIRMWARE_FULL_SUCCESSFULLY",
					"DEVICE_UPDATE"
				)."</br>";
			
		}
		catch ( RuntimeException $e ) {
			$error = TRUE;
			$msg   .= __( "UPLOAD_FIRMWARE_FULL_LABEL", "DEVICE_UPDATE" ).": ".$e->getMessage()."!</br>";
			
		}
	} else if ( isset( $_POST[ "auto" ] ) ) {
		//File to save the contents to
		$lCode = $Config->read( "update_automatic_lang" );
		if ( $lCode != "" ) {
			if ( $lCode != "EN" ) {
				$lCodeTasmota = "-".$lCode;
			} else {
				$lCodeTasmota = "";
			}
			
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
				if ( $binfileData->name == "sonoff-minimal.bin" ) {
					$fwMinimalUrl = $binfileData->browser_download_url;
				}
				if ( $binfileData->name == sprintf( "sonoff%s.bin", $lCodeTasmota ) ) {
					$fwUrl = $binfileData->browser_download_url;
				}
				
			}
			if ( isset( $fwUrl ) && isset( $fwMinimalUrl ) ) {
				$minimal_firmware_path = $firmwarefolder.'sonoff-minimal.bin';
				$new_firmware_path     = $firmwarefolder.'sonoff-full.bin';
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
				$msg .= __( "AUTO_SUCCESSFULL_DOWNLOADED", "DEVICE_UPDATE" )."<br/>";
				$msg .= __( "LANGUAGE", "DEVICE_UPDATE" ).": <strong>".$lCode."</strong> | ".__(
						"VERSION",
						"DEVICE_UPDATE"
					).": ".$data->tag_name." | ".__( "DATE", "DEVICE_UPDATE" )." ".$data->published_at;
			} else {
				$error = TRUE;
				$msg   .= __( "AUTO_ERROR_DOWNLOAD", "DEVICE_UPDATE" )."<br/>";
			}
		} else {
			$error = TRUE;
			$msg   = __( "MSG_SET_AUTOMATIC_LANG_FIRST", "DEVICE_UPDATE" );
		}
	} else {
		$error = TRUE;
		$msg   .= __( "UPLOAD_PLEASE_UPLOAD_FIRMWARE", "DEVICE_UPDATE" )."<br/>";
	}
	
	
	$ota_server_ip = isset( $_POST[ "ota_server_ip" ] ) ? $_POST[ "ota_server_ip" ] : "";
	
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
		<?php if ( isset( $_POST[ "auto" ] ) ) : ?>
			<p class='warning'>
				<?php echo __( "AUTO_WARNING_CFG_HOLDER", "DEVICE_UPDATE" ); ?>
			</p>
		<?php endif; ?>
		<p>
			<?php echo __( "CHOOSE_DEVICES_TO_UPDATE", "DEVICE_UPDATE" ); ?>:
		</p>
	</div>
	<form name='update_devices'
	      class='center'
	      id='update_devices'
	      method='post'
	      action='<?php echo _APPROOT_; ?>index.php?page=device_update'>
		<input type='hidden' name='minimal_firmware_path' value='<?php echo $minimal_firmware_path; ?>'>
		<input type='hidden' name='new_firmware_path' value='<?php echo $new_firmware_path; ?>'>
		<table id='device-list' class='center-table' border='0' cellspacing='0'>
			<thead>
			<tr>
				<td colspan='8'>
					<button type='submit' class='btn widget' name='submit' value='submit'><?php echo __(
							"BTN_START_UPDATE",
							"DEVICE_UPDATE"
						); ?></button>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<th>
					<input class='select_all' type='checkbox' name='select_all' value='select_all'>
					<label for='select_all'><?php echo __( "TABLE_HEAD_ALL", "DEVICES" ); ?></label>
				</th>
				<th><?php echo __( "TABLE_HEAD_ID", "DEVICES" ); ?></th>
				<th><?php echo __( "TABLE_HEAD_NAME", "DEVICES" ); ?></th>
				<th><?php echo __( "TABLE_HEAD_IP", "DEVICES" ); ?></th>
				<th><?php echo __( "TABLE_HEAD_STATE", "DEVICES" ); ?></th>
				<th><i class="fas fa-signal" title='<?php echo __( "TABLE_HEAD_RSSI", "DEVICES" ); ?>'></i></th>
				<th><?php echo __( "TABLE_HEAD_VERSION", "DEVICES" ); ?></th>
				<th><?php echo __( "TABLE_HEAD_RUNTIME", "DEVICES" ); ?></th>
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
					<input class='select_all' type='checkbox' name='select_all' value='select_all'>
					<label for='select_all'><?php echo __( "TABLE_HEAD_ALL", "DEVICES" ); ?></label>
				</th>
				<th><?php echo __( "TABLE_HEAD_ID", "DEVICES" ); ?></th>
				<th><?php echo __( "TABLE_HEAD_NAME", "DEVICES" ); ?></th>
				<th><?php echo __( "TABLE_HEAD_IP", "DEVICES" ); ?></th>
				<th><?php echo __( "TABLE_HEAD_STATE", "DEVICES" ); ?></th>
				<th><i class="fas fa-signal" title='<?php echo __( "TABLE_HEAD_RSSI", "DEVICES" ); ?>'></i></th>
				<th><?php echo __( "TABLE_HEAD_VERSION", "DEVICES" ); ?></th>
				<th><?php echo __( "TABLE_HEAD_RUNTIME", "DEVICES" ); ?></th>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan='8'>
					<button type='submit' class='btn widget' name='submit' value='submit'><?php echo __(
							"BTN_START_UPDATE",
							"DEVICE_UPDATE"
						); ?></button>
					</button>
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
	<script type='text/javascript' src='<?php echo _RESOURCESDIR_; ?>js/devices.js'></script>
<?php endif; ?>

