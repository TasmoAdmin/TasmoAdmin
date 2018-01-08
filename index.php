<?php
	include_once( "includes/Config.php" );
	
	$Config = new Config();
	
	$page = "start";
	if ( isset( $_GET ) ) {
		if ( isset( $_GET[ "page" ] ) ) {
			$page = $_GET[ "page" ];
			
			
		}
	}
	
	switch ( $page ) {
		case "upload_form":
			$title = "Upload new Firmware";
			break;
		default:
			$title = ucfirst( $page );
	}
	
	
	$filename = "data/devices.csv"; //csv file name

?>
<?php include_once( "includes/header.php" ); //always load header?>
	
	<div id="navi">
		<ul>
			<a href='/index.php?page=start' title='Startseite'>
				<li class=''>Start</li>
			</a>
			<hr/>
			<a href='/index.php?page=devices' title='Geräte'>
				<li class=''>Geräte</li>
			</a>
			<a href='/index.php?page=upload_form' title='Update'>
				<li class=''>Update</li>
			</a>
		
		</ul>
	</div>
	
	
	<div id="content-holder" class=''>
		<div id='content'>
			<?php include_once( "pages/".$page.".php" ); ?>
		</div>
	</div>
<?php include_once( "includes/footer.php" ); //always load header?>