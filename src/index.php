<?php
	/**
	 *
	 * funktion gibt preis in deutscher schreibweise zurück
	 * X.XXX,XX
	 *
	 * @param $number float
	 *
	 * @return string
	 *
	 */


?>
<?php include_once( "includes/header.php" ); //always load header?>
	
	
	<!-- Sichtbarer Dokumentinhalt im body -->
	<div id="navi">
		<ul>
			<a href='/pages/start.php' title='Startseite'>
				<li class=''>Start</li>
			</a>
			<hr/>
			<a href='/pages/devices.php' title='Geräte'>
				<li class=''>Geräte</li>
			</a>
		
		</ul>
	</div>
	
	
	<div id="content-holder" class='loading' data-page='start'>
		<div id='content'>
		
		</div>
	</div>

<?php include_once( "includes/footer.php" ); //always load header?>