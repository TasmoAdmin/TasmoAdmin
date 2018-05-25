<?php

	require_once _INCLUDESDIR_."Selfupdate.php";

	$msg        = "";
	$Selfupdate = new Selfupdate( $Config );

	if( isset( $_POST[ "selfupdate" ] ) || isset( $_GET[ "selfupdate" ] ) ) {
		$updateResult = $Selfupdate->update();
		$msg          = implode( "<br/>", $updateResult );
	}

	$newUpdate = $Selfupdate->checkForUpdate();

	$changelogUrl = "https://raw.githubusercontent.com/reloxx13/TasmoAdmin/master/CHANGELOG.md?r=".time();
	$ch           = curl_init();
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_URL, $changelogUrl );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$changelog = curl_exec( $ch );


	//$changelog = file_get_contents( _APPROOT_."CHANGELOG.md" );
	if( !$changelog || curl_error( $ch ) != "" || $changelog == "" ) {
		$changelog = "";
	} else {
		require_once _LIBSDIR_."parsedown/Parsedown.php";
		$mdParser  = new Parsedown();
		$changelog = $mdParser->parse( $changelog );
	}

?>

<div class='row justify-content-sm-center'>
	<div class='col-12 col-md-6 '>
		<h2 class='text-sm-center mb-5'>
			<?php echo $title; ?>
		</h2>

		<!--	<p class='warning'>-->
		<!--		--><?php //echo __( "SELFUPDATE_WARNING", "SELFUPDATE" ); ?>
		<!--	</p>-->
		<!--	<br/>-->
		<!--	<br/>-->
		<?php if( isset( $msg ) && $msg != "" ): ?>
			<div class="alert alert-success alert-dismissible fade show mb-5" data-dismiss="alert" role="alert">
				<?php echo $msg; ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		<?php endif; ?>
		<?php if( isset( $newUpdate[ "error" ] ) && $newUpdate[ "error" ] != "" ): ?>
			<div class="alert alert-danger alert-dismissible fade show mb-5" data-dismiss="alert" role="alert">
				<?php echo $newUpdate[ "msg" ]; ?>
				<br/>
				<?php echo __( "ERROR_CHECK_CONNECTION", "SELFUPDATE" ); ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		<?php endif; ?>

		<?php if( $newUpdate[ "update" ] ): ?>
			<div class="alert alert-success fade show mb-5" role="alert">
				<?php echo __( "UPDATE_FOUND", "SELFUPDATE" ); ?>!
			</div>
			<div class='mt-3'>
				<?php echo __(
					"OLD_TAG_VERSION",
					"SELFUPDATE",
					[ $Selfupdate->getCurrentTag() ? $Selfupdate->getCurrentTag() : __( "UNKNOWN", "SELFUPDATE" ) ]
				); ?><br/>
				<?php echo __( "NEW_TAG_VERSION", "SELFUPDATE", [ $Selfupdate->getLatestTag() ] ); ?><br/>
				<br/>
			</div>
			<div class='row justify-content-sm-center mt-5'>
				<div class="col-12 col-sm-6 text-center">
					<form name='selfupdateform' method='post'>
						<button type='submit' name='selfupdate' value='selfupdate' class='btn btn-primary'>
							<?php echo __( "BTN_START_SELFUPDATE", "SELFUPDATE" ); ?>
						</button>
					</form>
				</div>
			</div>
		<?php else: ?>
			<div class="alert alert-info fade show mb-5" role="alert">
				<?php echo __( "NO_UPDATE_FOUND", "SELFUPDATE" ); ?>
			</div>
			<div class='row justify-content-sm-center mt-5'>
				<div class="col-12 col-sm-6 text-center">
					<form name='selfupdateform' method='post'>
						<button type='submit' name='selfupdate' value='selfupdate' class='btn btn-secondary'>
							<?php echo __( "BTN_START_SELFUPDATE", "SELFUPDATE" ); ?>
						</button>
					</form>
				</div>
			</div>
		<?php endif; ?>
		<hr class='my-5'>
		<div class='changelog'>
			<?php echo $changelog; ?>
		</div>
	</div>
</div>
