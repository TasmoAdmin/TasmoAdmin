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
	<?php if ( $msg != "" ): ?>
		<p>
			<?php echo $msg; ?>
		</p>
	<?php endif; ?>
	<p class='warning'>
		<?php echo __( "SELFUPDATE_WARNING", "SELFUPDATE" ); ?>
	</p>
	<br/>
	<br/>
	<br/>
	<?php if ( $newUpdate ): ?>
		<form name='selfupdateform' method='post'>
			<button type='submit' name='selfupdate' value='selfupdate' class='btn'>
				<?php echo __( "BTN_START_SELFUPDATE", "SELFUPDATE" ); ?>
			</button>
		</form>
	<?php else: ?>
		<p>
			<?php echo __( "NO_UPDATE_FOUND", "SELFUPDATE" ); ?>
		</p>
	<?php endif; ?>
</div>