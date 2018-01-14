<?php
	$msg = FALSE;
	if ( isset( $_POST ) && !empty( $_POST ) ) {
		if ( isset( $_POST[ "save" ] ) ) {
			$settings = $_POST;
			unset( $settings[ "save" ] );
			foreach ( $settings as $settingKey => $settingVal ) {
				$Config->write( $settingKey, $settingVal );
			}
			$msg = "Einstellungen gespeichert";
		}
	}
	$config = $Config->readAll();

?>

<form class='center' name='web_config' method='post'>
	<p>
		<?php echo $msg ? $msg : ""; ?>
	</p>
	<table border='0' cellspacing='0' class='center-table'>
		<tr>
			<td>
				SERVER IP:<br/><br/>
			</td>
			<td>
				<input type='text' name='ota_server_ip' value='<?php echo $config[ "ota_server_ip" ]; ?>'><br/><br/>
			</td>
		</tr>
		
		<tr>
			<td>&nbsp;
			    Aktualisierungsrate:<br/><br/>
			</td>
			<td>
				<select name='refreshtime'>
					<option value='none' <?php echo $config[ "refreshtime" ] == "none" ? "selected=\selected\""
						: ""; ?>>Nicht aktualisieren
					</option>
					<option value='1' <?php echo $config[ "refreshtime" ] == "1" ? "selected=\selected\"" : ""; ?> >
						1 Sekunde
					</option>
					<option value='2' <?php echo $config[ "refreshtime" ] == "2" ? "selected=\selected\"" : ""; ?> >2
					                                                                                                Sekunden
					</option>
					<option value='3' <?php echo $config[ "refreshtime" ] == "3" ? "selected=\selected\"" : ""; ?> >3
					                                                                                                Sekunden
					</option>
					<option value='4' <?php echo $config[ "refreshtime" ] == "4" ? "selected=\selected\"" : ""; ?> >4
					                                                                                                Sekunden
					</option>
					<option value='5' <?php echo $config[ "refreshtime" ] == "5" ? "selected=\selected\"" : ""; ?> >5
					                                                                                                Sekunden
					</option>
					<option value='10' <?php echo $config[ "refreshtime" ] == "10" ? "selected=\selected\"" : ""; ?> >10
					                                                                                                  Sekunden
					</option>
					<option value='15' <?php echo $config[ "refreshtime" ] == "15" ? "selected=\selected\"" : ""; ?> >15
					                                                                                                  Sekunden
					</option>
					<option value='30' <?php echo $config[ "refreshtime" ] == "30" ? "selected=\selected\"" : ""; ?> >30
					                                                                                                  Sekunden
					</option>
					<option value='60' <?php echo $config[ "refreshtime" ] == "60" ? "selected=\selected\"" : ""; ?> >60
					                                                                                                  Sekunden
					</option>
				</select><br/><br/>
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
