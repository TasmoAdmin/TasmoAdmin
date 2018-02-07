<?php
	include_once( "./includes/top.php" );
	
	
	if ( !isset ( $_SESSION[ "login" ] ) && $Config->read( "login" ) == "1" ) {
		header( "Location: "._BASEURL_."login" );
	}
	
	$page = "start";
	if ( isset( $_GET ) ) {
		if ( isset( $_GET[ "page" ] ) ) {
			$page = $_GET[ "page" ];
		}
	}
	
	switch ( $page ) {
		case "device_action":
			$title = __( "MANAGE_DEVICE", "PAGE_TITLES" );
			if ( isset( $_GET[ "action" ] ) && $_GET[ "action" ] == "add" ) {
				$title = __( "ADD_DEVICE", "PAGE_TITLES" );
			} else if ( isset( $_GET[ "action" ] ) && $_GET[ "action" ] == "edit" ) {
				$title = __( "EDIT_DEVICE", "PAGE_TITLES" );
			}
			break;
		case "devices":
			$title = __( "DEVICES", "PAGE_TITLES" );
			break;
		case "device_update":
		case "update_devices":
		case "upload_form":
		case "upload":
			$title = __( "DEVICE_UPDATE", "PAGE_TITLES" );
			break;
		case "device_config":
			$title = __( "DEVICE_CONFIG", "PAGE_TITLES" );
			break;
		case "site_config":
			$title = __( "SITE_CONFIG", "PAGE_TITLES" );
			break;
		case "selfupdate":
			$title = __( "SITE_SELFUPDATE", "PAGE_TITLES" );
			break;
		default:
			$title = __( strtoupper( str_replace( " ", "_", $page ) ), "PAGE_TITLES" );
	}


?>
<?php include_once( _INCLUDESDIR_."header.php" ); //always load header?>


<main class='container-fluid' id='content' data-refreshtime='<?php echo $Config->read( "refreshtime" ); ?>'>
	<?php include_once( _PAGESDIR_.$page.".php" ); ?>
</main>
<?php include_once( _INCLUDESDIR_."footer.php" ); //always load header?>

