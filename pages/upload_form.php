<?php


?>

<form class='center' name='update_form' method='post' enctype='multipart/form-data'
      action='<?php echo _APPROOT_; ?>index.php?page=upload'>
    <p>
		<?php echo __( "UPLOAD_DESCRIPTION", "DEVICE_UPDATE" ); ?><br/>
        <a href='https://github.com/arendst/Sonoff-Tasmota/releases' target='_blank'>Tasmota Releases</a>
    </p>
    <table border='0' cellspacing='0' class='center-table'>
        <tr>
            <td>
				<?php echo __( "CONFIG_SERVER_IP", "USER_CONFIG" ); ?>:
            </td>
        </tr>
        <tr>
            <td>
                <input type='text' name='ota_server_ip' value='<?php echo $Config->read( "ota_server_ip" ); ?>'>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
				<?php echo __( "FORM_CHOOSE_MINIMAL_FIRMWARE", "DEVICE_UPDATE" ); ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type='file' name='minimal_firmware'>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
				<?php echo __( "UPLOAD_FIRMWARE_FULL_LABEL", "DEVICE_UPDATE" ); ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type='file' name='new_firmware'>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <button type='submit' class='btn' name='auto' value='submit' style='margin-right: 20px;'><?php echo __(
						"BTN_UPLOAD_AUTOMATIC",
						"DEVICE_UPDATE"
					); ?>
                </button>
                <button type='submit' class='btn' name='upload' value='submit'><?php echo __(
						"BTN_UPLOAD_NEXT",
						"DEVICE_UPDATE"
					); ?></button>
            </td>
        </tr>
    </table>
</form>
