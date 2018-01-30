<?php
	
	
	require_once _INCLUDESDIR_."Selfupdate.php";
	
	$msg        = "";
	$Selfupdate = new Selfupdate( $Config );
	
	if ( isset( $_POST[ "selfupdate" ] ) ) {
		$updateResult = $Selfupdate->update();
		$msg          = implode( "<br/>", $updateResult );
	}
	
	$newUpdate = $Selfupdate->checkForUpdate();


?>


<div class='center'>
	
	<p class='warning'>
		<?php echo __( "SELFUPDATE_WARNING", "SELFUPDATE" ); ?>
	</p>
	<br/>
	<br/>
	<?php if ( $msg != "" ): ?>
		<p>
			<?php echo $msg; ?>
		</p>
	<?php endif; ?>
	<br/>
	<br/>
	<?php if ( $newUpdate ): ?>
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
</div>