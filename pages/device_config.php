<?php
$msg    = FALSE;
$device = null;

if( isset( $_GET[ "device_id" ] ) ) {
	$file = fopen( $filename, 'r' );
	while( ( $line = fgetcsv( $file ) ) !== FALSE ) {
		//$line is an array of the csv elements
		//var_dump( $line );
		if( $line[ 0 ] == $_GET[ "device_id" ] ) {
			$line[ 1 ] = explode( "|", $line[ 1 ] );
			$device    = $line;
			break;
		}
	}
	fclose( $file );
} else {
	$msg = "Kein Device ausgewählt";
}

if( isset( $_POST ) && !empty( $_POST ) ) {
	if( isset( $_POST[ "save" ] ) ) {
		$settings = $_POST;
		unset( $settings[ "save" ] );
		
		$backlog = "Backlog ";
		foreach( $settings as $settingKey => $settingVal ) {
			$backlog .= $settingKey . " " . $settingVal . ";";
		}
		$result = $Sonoff->saveConfig( $device[ 2 ], $backlog );
		$msg    = "Einstellungen gespeichert";
		sleep( count( $settings ) );
	}
	
	
}

$status = $Sonoff->getAllStatus( $device[ 2 ] );


?>

<form class='center' name='web_config' method='post'>
    <p>
		<?php echo $msg ? $msg : ""; ?>
    </p>
    <table border='0' cellspacing='0' class='center-table' style='text-align: left;'>
        <tr>
            <td>
                <p title='Name den Alexa findet'> FriendlyName:<br/></p>
            </td>
            <td>
                <input type='text'
                       class='config text'
                       name='FriendlyName'
                       value='<?php echo isset( $status->Status->FriendlyName )
				                         && !empty( $status->Status->FriendlyName ) ? $status->Status->FriendlyName
					       : ""; ?>'><br/>
            </td>
        </tr>

        <tr>
            <td>
                <p title='Status nach Stromausfall'> PowerOnState: <br/></p>
            </td>
            <td>
                <select name='PowerOnState' class='config select'>
                    <option value='0' <?php echo isset( $status->Status->PowerOnState )
					                             && $status->Status->PowerOnState == 0 ? "selected=\selected\""
						: ""; ?>>
                        Bleib ausgeschaltet
                    </option>
                    <option value='1' <?php echo isset( $status->Status->PowerOnState )
					                             && $status->Status->PowerOnState == 1 ? "selected=\selected\""
						: ""; ?>>
                        Einschalten
                    </option>
                    <option value='2' <?php echo isset( $status->Status->PowerOnState )
					                             && $status->Status->PowerOnState == 2 ? "selected=\selected\""
						: ""; ?>>
                        Gegenteil vom letzten Schaltzustand
                    </option>
                    <option value='3' <?php echo isset( $status->Status->PowerOnState )
					                             && $status->Status->PowerOnState == 3 ? "selected=\selected\""
						: ""; ?>>
                        Letzter Schaltzustand
                    </option>
                    <option value='4' <?php echo isset( $status->Status->PowerOnState )
					                             && $status->Status->PowerOnState == 4 ? "selected=\selected\""
						: ""; ?>>
                        Einschalten und Schalter deaktivieren
                    </option>

                </select><br/>
            </td>
        </tr>
        <tr>
            <td>
                <p title='LED Verhalten'> LedState: <br/></p>
            </td>
            <td>
                <select name='LedState' class='config select'>
                    <option value='0' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 0 ? "selected=\selected\"" : ""; ?>>
                        Aus
                    </option>
                    <option value='1' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 1 ? "selected=\selected\"" : ""; ?>>
                        Wie Schaltzustand (umgekehrt für Sonoff Touch)
                    </option>
                    <option value='2' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 2 ? "selected=\selected\"" : ""; ?>>
                        Blinken für MQTT Subscriptions
                    </option>
                    <option value='3' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 3 ? "selected=\selected\"" : ""; ?>>
                        Wie Schaltzustand und blinken für MQTT Subscriptions
                    </option>
                    <option value='4' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 4 ? "selected=\selected\"" : ""; ?>>
                        Blinken für MQTT Publications
                    </option>
                    <option value='5' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 5 ? "selected=\selected\"" : ""; ?>>
                        Wie Schaltzustand und blinken für MQTT Publications
                    </option>
                    <option value='6' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 6 ? "selected=\selected\"" : ""; ?>>
                        Blinken für alle MQTT Messages
                    </option>
                    <option value='7' <?php echo isset( $status->Status->LedState )
					                             && $status->Status->LedState == 7 ? "selected=\selected\"" : ""; ?>>
                        Wie Schaltzustand und blinken für alle MQTT Messages
                    </option>
                </select><br/>
            </td>
        </tr>

        <tr>
            <td>
                <p title='Pause zwischen Ablauf zum Energiesparen'> Sleep (0-250ms): <br/></p>
            </td>
            <td>
                <input type='number' step='25' max='250' min='0' pattern="\d{1,3}"
                       class='config text'
                       name='Sleep'
                       value='<?php echo isset( $status->StatusPRM->Sleep )
				                         && !empty( $status->StatusPRM->Sleep ) ? $status->StatusPRM->Sleep : ""; ?>'>
                <br/>
            </td>
        </tr>

        <tr>
            <td>
                <p title='Was beim Wifi Verbindungsfehler passieren soll'> WifiConfig: <br/></p>
            </td>
            <td>
                <select name='WifiConfig' class='config select'>
                    <option value='0' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 0 ? "selected=\selected\""
						: ""; ?>>
                        Weiter Versuchen mit Neustart um Speicher suaber zu halten (WIFI_RESTART)
                    </option>
                    <option value='1' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 1 ? "selected=\selected\""
						: ""; ?>>
                        Starte SmartConfig Tool für eine Minute (WIFI_SMARTCONFIG)
                    </option>
                    <option value='2' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 2 ? "selected=\selected\""
						: ""; ?>>
                        Starte Wifi Manager (mit Sonoff Wlan verbinden und dann 192.168.4.1 aufrufen) (WIFI_MANAGER)
                    </option>
                    <option value='3' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 3 ? "selected=\selected\""
						: ""; ?>>
                        Starte WPS Konfiguration für 1 Minute (WIFI_WPSCONFIG)
                    </option>
                    <option value='4' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 4 ? "selected=\selected\""
						: ""; ?>>
                        Abwechselnd zwischen WIFI 1 und WIFI 2 versuchen ohne Neustart (WIFI_RETRY)
                    </option>
                    <option value='5' <?php echo isset( $status->StatusNET->WifiConfig )
					                             && $status->StatusNET->WifiConfig == 5 ? "selected=\selected\""
						: ""; ?>>
                        Nochmal versuchen ohne Neustart und Flash änderungen (WIFI_WAIT)
                    </option>
                </select><br/>
            </td>
        </tr>


        <tr>
            <td colspan='2'>&nbsp;</td>
        </tr>
        <tr>
            <td colspan='2'>
                <button type='submit' class='btn' name='save' value='submit'>Speichern</button>
            </td>
        </tr>
    </table>
</form>
