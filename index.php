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


<div id="navi" class='open desktop'>
	<ul>
		<a href='<?php echo _BASEURL_; ?>start' title='<?php echo __( "STARTPAGE_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "STARTPAGE", "NAVI" ); ?></li>
		</a>
		<hr/>
		<a href='<?php echo _BASEURL_; ?>devices' title='<?php echo __( "DEVICES_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "DEVICES", "NAVI" ); ?></li>
		</a>
		<a href='<?php echo _BASEURL_; ?>upload_form' class=''
		   title='<?php echo __( "UPDATE_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "UPDATE", "NAVI" ); ?></li>
		</a>
		
		<!--			<hr/>-->
		<!--			<a href='<?php echo _APPROOT_; ?>index.php?page=chat' title='Chat'>-->
		<!--				<li class=''>Chat</li>-->
		<!--			</a>-->
		<hr/>
		<a href='<?php echo _BASEURL_; ?>site_config'
		   title='<?php echo __( "SETTINGS_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "SETTINGS", "NAVI" ); ?></li>
		</a>
		<a href='<?php echo _BASEURL_; ?>selfupdate'
		   title='<?php echo __( "SELFUPDATE_TOOLTIP", "NAVI" ); ?>'>
			<li class=''><?php echo __( "SELFUPDATE", "NAVI" ); ?></li>
		</a>
		<?php if ( $Config->read( "login" ) == "1" ): ?>
			<hr/>
			<a href='<?php echo _BASEURL_; ?>logout' title='<?php echo __( "LOGOUT_TOOLTIP", "NAVI" ); ?>'>
				<li class=''><?php echo __( "LOGOUT", "NAVI" ); ?></li>
			</a>
		<?php endif; ?>
	
	</ul>
</div>


<div id="content-holder" class='<?php echo $page == "start" ? "full-width" : ""; ?>'>
	<div id='content' data-refreshtime='<?php echo $Config->read( "refreshtime" ); ?>'>
		<?php include_once( _PAGESDIR_.$page.".php" ); ?>
	</div>
</div>
<?php include_once( _INCLUDESDIR_."footer.php" ); //always load header?>

