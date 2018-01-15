<?php


?>

<form class='center' name='update_form' method='post' enctype='multipart/form-data'
      action='<?php echo _APPROOT_; ?>index.php?page=upload'>
    <p>
        Bitte wähle eine MINIMAL und dann die normale Firmware Version aus!<br/>
        Der Automatik Modus benutzt die neuste Release Version aus dem Tasmota GIT<br/>
        <a href='https://github.com/arendst/Sonoff-Tasmota/releases' target='_blank'>Tasmota Releases</a>
    </p>
    <table border='0' cellspacing='0' class='center-table'>
        <tr>
            <td>
                SERVER IP:
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
                MINIMAL Firmware:
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
                Neue Firmware:
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
                <button type='submit' class='btn' name='auto' value='submit' style='margin-right: 20px;'>Automatik
                </button>
                <button type='submit' class='btn' name='upload' value='submit'>Weiter</button>
            </td>
        </tr>
    </table>
</form>
