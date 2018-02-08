<?php
	$changelogUrl = "https://raw.githubusercontent.com/arendst/Sonoff-Tasmota/development/sonoff/_releasenotes.ino?r="
	                .time();
	$ch           = curl_init();
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_URL, $changelogUrl );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$changelog = curl_exec( $ch );
	
	
	//$changelog = file_get_contents( _APPROOT_."CHANGELOG.md" );
	if ( !$changelog || curl_error( $ch ) != "" || $changelog == "" ) {
		$changelog = "";
	} else {
		$changelog = substr( str_replace( [ "*/", "/*", " *\n" ], [ "", " * ", "\n" ], $changelog ), 0, 5000 )."...";
		require_once _LIBSDIR_."parsedown/Parsedown.php";
		$mdParser  = new Parsedown();
		$changelog = $mdParser->parse( $changelog );
	}

?>
<div class='row justify-content-sm-center'>
    <div class='col-12 col-md-6 '>
        <h2 class='text-sm-center mb-5'>
			<?php echo $title; ?>
        </h2>
        <div class='text-center mb-5'>
			<?php echo __( "UPLOAD_DESCRIPTION", "DEVICE_UPDATE" ); ?><br/>
            <a href='https://github.com/arendst/Sonoff-Tasmota/releases' target='_blank'>Tasmota Releases</a>
        </div>


        <form class='' name='update_form' method='post' enctype='multipart/form-data'
              action='<?php echo _BASEURL_; ?>upload'>
            <div class="form-group">
                <label for="ota_server_ip">
					<?php echo __( "CONFIG_SERVER_IP", "USER_CONFIG" ); ?>
                </label>
                <input type="text"
                       class="form-control"
                       id="ota_server_ip"
                       name='ota_server_ip'
                       required
                       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
                       value='<?php echo $Config->read( "ota_server_ip" ); ?>'
                >
            </div>

            <div class="form-group">
                <label for="minimal_firmware">
					<?php echo __( "FORM_CHOOSE_MINIMAL_FIRMWARE", "DEVICE_UPDATE" ); ?>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="minimal_firmware" name='minimal_firmware'>
                    <label class="custom-file-label" for="minimal_firmware">

                    </label>
                </div>
            </div>
            <div class="form-group">
                <label for="new_firmware">
					<?php echo __( "UPLOAD_FIRMWARE_FULL_LABEL", "DEVICE_UPDATE" ); ?>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="new_firmware" name='new_firmware' required>
                    <label class="custom-file-label" for="new_firmware">

                    </label>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-12">
                    <div class="float-left">
                        <button type='submit' class='btn btn-primary' id="automatic" name='auto' value='submit'
                                title='<?php echo __( "BTN_UPLOAD_AUTOMATIC_HELP", "DEVICE_UPDATE" ); ?>'
                        >
							<?php echo __( "BTN_UPLOAD_AUTOMATIC", "DEVICE_UPDATE" ); ?>
                        </button>
                    </div>
                    <div class="float-right">
                        <button type='submit' class='btn btn-primary' name='upload' value='submit'>
							<?php echo __( "BTN_UPLOAD_NEXT", "DEVICE_UPDATE" ); ?>
                        </button>
                    </div>
                    <span class='clearfix'></span>
                </div>
            </div>


        </form>
        <hr class='my-5'>
        <div class='changelog'>
            <h2>Tasmota Changelog</h2>
			<?php echo $changelog; ?>
        </div>
    </div>
</div>

<script>
    $( "#automatic" ).on( "click", function( e ) {
        $( "#new_firmware" ).removeProp( "required" );
    } );
</script>