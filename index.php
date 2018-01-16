<?php
	include_once( "./includes/top.php" );
	
	
	if ( !isset ( $_SESSION[ "login" ] ) ) {
		header( "Location: "._APPROOT_."login.php" );
	}
	
	$page = "start";
	if ( isset( $_GET ) ) {
		if ( isset( $_GET[ "page" ] ) ) {
			$page = $_GET[ "page" ];
		}
	}
	
	switch ( $page ) {
		case "upload_form":
			$title = __( "UPLOAD_NEW_FIRMWARE", "PAGE_TITLES" );
			break;
		case "device_action":
			$title = __( "MANAGE_DEVICE", "PAGE_TITLES" );
			if ( isset( $_GET[ "action" ] ) && $_GET[ "action" ] == "add" ) {
				$title = __( "ADD_DEVICE", "PAGE_TITLES" );
			} else if ( isset( $_GET[ "action" ] ) && $_GET[ "action" ] == "edit" ) {
				$title = __( "EDIT_DEVICE", "PAGE_TITLES" );
				break;
			}
		case "devices":
			$title = __( "DEVICES", "PAGE_TITLES" );
			break;
		case "device_update":
			$title = __( "DEVICE_UPDATE", "PAGE_TITLES" );
			break;
		case "device_config":
			$title = __( "DEVICE_CONFIG", "PAGE_TITLES" );
			break;
		case "site_config":
			$title = __( "SITE_CONFIG", "PAGE_TITLES" );
			break;
		default:
			$title = ucfirst( $page );
	}
	
	
	$filename = _DATADIR_."devices.csv"; //csv file name
	if ( !file_exists( $filename ) ) {
		fopen( $filename, 'w' ) or die( "Can't create file" );
	}


?>
<?php include_once( _INCLUDESDIR_."header.php" ); //always load header?>


<div id="navi" class='open desktop'>
	<ul>
		<a href='<?php echo _APPROOT_; ?>index.php?page=start' title='<?php echo __( "STARTPAGE_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "STARTPAGE", "NAVI" ); ?></li>
		</a>
		<hr/>
		<a href='<?php echo _APPROOT_; ?>index.php?page=devices' title='<?php echo __( "DEVICES_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "DEVICES", "NAVI" ); ?></li>
		</a>
		<a href='<?php echo _APPROOT_; ?>index.php?page=upload_form' class='hide-mobile'
		   title='<?php echo __( "UPDATE_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "UPDATE", "NAVI" ); ?></li>
		</a>
		
		<!--			<hr/>-->
		<!--			<a href='<?php echo _APPROOT_; ?>index.php?page=chat' title='Chat'>-->
		<!--				<li class=''>Chat</li>-->
		<!--			</a>-->
		<hr/>
		<a href='<?php echo _APPROOT_; ?>index.php?page=site_config'
		   title='<?php echo __( "SETTINGS_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "SETTINGS", "NAVI" ); ?></li>
		</a>
		<a href='<?php echo _APPROOT_; ?>index.php?page=selfupdate'
		   title='<?php echo __( "SELFUPDATE_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "SELFUPDATE", "NAVI" ); ?></li>
		</a>
		<hr/>
		<a href='<?php echo _APPROOT_; ?>login.php?logout' title='<?php echo __( "LOGOUT_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "LOGOUT", "NAVI" ); ?></li>
		</a>
	
	</ul>
</div>


<div id="content-holder" class='<?php echo $page == "start" ? "full-width" : ""; ?>'>
	<div id='content' data-refreshtime='<?php echo $Config->read( "refreshtime" ); ?>'>
		<?php include_once( _PAGESDIR_.$page.".php" ); ?>
	</div>
</div>
<?php include_once( _INCLUDESDIR_."footer.php" ); //always load header?>

