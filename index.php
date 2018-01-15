<?php
ini_set( 'session.gc_maxlifetime', 2678400 );
session_start();
if( !isset ( $_SESSION[ "login" ] ) ) {
	header( "Location: " . _APPROOT_ . "login.php" );
}
define( "_VERSION_", "1.0.0b" );

define( "_APPROOT_", "./" );
define( "_RESOURCESDIR_", _APPROOT_ . "resources/" );
define( "_INCLUDESDIR_", _APPROOT_ . "includes/" );
define( "_PAGESDIR_", _APPROOT_ . "pages/" );
define( "_DATADIR_", _APPROOT_ . "data/" );

/**
 * @property Sonoff Sonoff
 */
include_once( _INCLUDESDIR_ . "Config.php" );
include_once( _INCLUDESDIR_ . "Sonoff.php" );

$Config = new Config();

$page = "start";
if( isset( $_GET ) ) {
	if( isset( $_GET[ "page" ] ) ) {
		$page = $_GET[ "page" ];
		
		
	}
}

switch( $page ) {
	case "upload_form":
		$title = "Upload new Firmware";
		break;
	case "device_action":
		$title = "Gerät verwalten";
		if( isset( $_GET[ "action" ] ) && $_GET[ "action" ] == "add" ) {
			$title = "Gerät hinzufügen";
		} else if( isset( $_GET[ "action" ] ) && $_GET[ "action" ] == "edit" ) {
			$title = "Gerät bearbeiten";
			break;
		}
	case "devices":
		$title = "Geräte";
		break;
	default:
		$title = ucfirst( $page );
}


$filename = _DATADIR_ . "devices.csv"; //csv file name
if( !file_exists( $filename ) ) {
	fopen( $filename, 'w' ) or die( "Can't create file" );
}


?>
<?php include_once( _INCLUDESDIR_ . "header.php" ); //always load header?>


<div id="navi" class='open desktop'>
    <ul>
        <a href='<?php echo _APPROOT_; ?>index.php?page=start' title='Startseite'>
            <li class=''>Start</li>
        </a>
        <hr/>
        <a href='<?php echo _APPROOT_; ?>index.php?page=devices' title='Geräte'>
            <li class=''>Geräte</li>
        </a>
        <a href='<?php echo _APPROOT_; ?>index.php?page=upload_form' class='hide-mobile' title='Update'>
            <li class=''>Update</li>
        </a>


        <!--			<hr/>-->
        <!--			<a href='<?php echo _APPROOT_; ?>index.php?page=chat' title='Chat'>-->
        <!--				<li class=''>Chat</li>-->
        <!--			</a>-->
        <hr/>
        <a href='<?php echo _APPROOT_; ?>index.php?page=site_config' title='Web Einstellungen'>
            <li class=''>Einstellungen</li>
        </a>

        <a href='/login.php?logout' title='Ausloggen'>
            <li class=''>Logout</li>
        </a>

    </ul>
</div>


<div id="content-holder" class=''>
    <div id='content' data-refreshtime='<?php echo $Config->read( "refreshtime" ); ?>'>
		<?php include_once( _PAGESDIR_ . $page . ".php" ); ?>
    </div>
</div>
<?php include_once( _INCLUDESDIR_ . "footer.php" ); //always load header?>

