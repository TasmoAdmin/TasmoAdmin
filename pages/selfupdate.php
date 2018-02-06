<?php
	
	
	require_once _INCLUDESDIR_."Selfupdate.php";
	require_once _LIBSDIR_."parsedown/Parsedown.php";
	
	$msg        = "";
	$Selfupdate = new Selfupdate( $Config );
	
	if ( isset( $_POST[ "selfupdate" ] ) ) {
		$updateResult = $Selfupdate->update();
		$msg          = implode( "<br/>", $updateResult );
	}
	
	$newUpdate = $Selfupdate->checkForUpdate();
	
	
	$changelog = @file_get_contents( "https://raw.githubusercontent.com/reloxx13/SonWEB/master/CHANGELOG.md" );
	//$changelog = file_get_contents( _APPROOT_."CHANGELOG.md" );
	if ( !$changelog ) {
		$changelog = "";
	}
	$mdParser  = new Parsedown();
	$changelog = $mdParser->parse( $changelog );

?>


<div class='center'>
	
	<!--	<p class='warning'>-->
	<!--		--><?php //echo __( "SELFUPDATE_WARNING", "SELFUPDATE" ); ?>
	<!--	</p>-->
	<!--	<br/>-->
	<!--	<br/>-->
	<?php if ( $msg != "" ): ?>
		<p>
			<?php echo $msg; ?>
		</p>
	<?php endif; ?>
	<?php if ( $newUpdate[ "error" ] ): ?>
		<p class='toastr error'>
			<?php echo $newUpdate[ "msg" ]; ?>
			<br/>
			<?php echo __( "ERROR_CHECK_CONNECTION", "SELFUPDATE" ); ?>
		</p>
	<?php endif; ?>
	<br/>
	<br/>
	<?php if ( $newUpdate[ "update" ] ): ?>
		<p>
			<?php echo __( "UPDATE_FOUND", "SELFUPDATE" ); ?>!<br/><br/>
			<?php echo __(
				"OLD_SHA_VERSION",
				"SELFUPDATE",
				[ $Selfupdate->getCurrentSha() ? $Selfupdate->getCurrentSha() : __( "UNKNOWN", "SELFUPDATE" ) ]
			); ?><br/>
			<?php echo __( "NEW_SHA_VERSION", "SELFUPDATE", [ $Selfupdate->getLatestSha() ] ); ?><br/>
			<br/>
		</p>
		<form name='selfupdateform' method='post'>
			<button type='submit' name='selfupdate' value='selfupdate' class='btn widget'>
				<?php echo __( "BTN_START_SELFUPDATE", "SELFUPDATE" ); ?>
			</button>
		</form>
	<?php else: ?>
		<p>
			<?php echo __( "NO_UPDATE_FOUND", "SELFUPDATE" ); ?>
		</p>
		<form name='selfupdateform' method='post'>
			<button type='submit' name='selfupdate' value='selfupdate' class='btn widget'>
				<?php echo __( "BTN_START_SELFUPDATE", "SELFUPDATE" ); ?>
			</button>
		</form>
	<?php endif; ?>
	<div class='changelog' style='margin-top: 50px;'>
		<?php echo $changelog; ?>
	</div>
</div>