<?php

use TasmoAdmin\Helper\GuzzleFactory;
use TasmoAdmin\Helper\TasmoAdminHelper;
use TasmoAdmin\SelfUpdate;
use TasmoAdmin\Update\UpdateChecker;

$msg        = "";

$currentGitTag = $Config->read("current_git_tag");

$updateChecker = new UpdateChecker(
    $Config->read("update_channel"),
    $currentGitTag,
    GuzzleFactory::getClient($Config)
);

if (isset($_REQUEST["selfupdate"]) || isset($_GET["selfupdate"])) {
    $selfUpdate = new SelfUpdate($Config, GuzzleFactory::getClient($Config));
    $updateResult = $selfUpdate->update($_POST['release_url'], $_POST['latest_tag']);
    $msg  = implode("<br/>", $updateResult);
}

$newUpdate = $updateChecker->checkForUpdate();

$tasmoAdminHelper = new TasmoAdminHelper(new Parsedown(), GuzzleFactory::getClient($Config));
$changelog = $tasmoAdminHelper->getChangelog();

?>

<div class='row justify-content-sm-center'>
	<div class='col col-12 col-md-10 col-lg-8 col-xl-6'>
		<h2 class='text-sm-center mb-5'>
			<?php if (!$docker): ?>
				<?php echo $title; ?>
			<?php else: ?>
				<?php echo __("HELP_CHANGELOG", "NAVI"); ?>
			<?php endif; ?>
		</h2>

		<?php if (isset($msg) && $msg != ""): ?>
			<div class="alert alert-success alert-dismissible fade show mb-5" data-dismiss="alert" role="alert">
				<?php echo $msg; ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		<?php endif; ?>
		<?php if (isset($newUpdate["error"]) && $newUpdate["error"] != ""): ?>
			<div class="alert alert-danger alert-dismissible fade show mb-5" data-dismiss="alert" role="alert">
				<?php echo $newUpdate["msg"]; ?>
				<br/>
				<?php echo __("ERROR_CHECK_CONNECTION", "SELFUPDATE"); ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		<?php endif; ?>
		
		<?php if ($newUpdate["update"]): ?>
			<div class="alert alert-success fade show mb-5" role="alert">
				<?php echo __("UPDATE_FOUND", "SELFUPDATE"); ?>!
			</div>
			<div class='mt-3 row'>
				<div class='col col-12 col-sm-5'>
					<a class='btn btn-secondary w-100'
					   <?php if (!empty($currentGitTag)): ?>href='https://github.com/TasmoAdmin/TasmoAdmin/releases/tag/<?php echo $currentGitTag; ?>'
					   target='_blank' <?php endif; ?>>
						<?php echo __(
							"OLD_TAG_VERSION",
							"SELFUPDATE",
							[
                                $currentGitTag
									?: __(
									"UNKNOWN",
									"SELFUPDATE"
								),
							]
						); ?>
					</a>
				</div>
				<div class='col col-12 col-sm-2 text-center align-text-top'>
					<i class="fas fa-angle-double-right fa-3x d-none d-sm-inline-block" style='font-size:2.5rem;'></i> <i
						class="fas fa-angle-double-down d-inline-block d-sm-none fa-3x my-3"
						style='font-size:2.5rem;'
					></i>
				</div>
				<div class='col col-12 col-sm-5'>
					<a class='btn btn-primary w-100 btn-green'
					   <?php if (!empty($newUpdate["latest_tag"])): ?>href='https://github.com/TasmoAdmin/TasmoAdmin/releases/tag/<?php echo $newUpdate["latest_tag"]; ?>'
					   target='_blank' <?php endif; ?>>
						<?php echo __("NEW_TAG_VERSION", "SELFUPDATE", [$newUpdate["latest_tag"]]); ?>
					</a>
				
				</div>
			</div>
			<?php if (in_array($Config->read("update_channel"), ["dev", "beta", "stable"])): ?>
				<div class='row justify-content-sm-center mt-5'>
					<div class="col col-12 col-sm-6 col-md-3 col-lg-4 text-center">
						<form name='selfupdateform' method='post'>
                            <input type="hidden" name="latest_tag" value="<?php echo $newUpdate["latest_tag"]; ?>" />
                            <input type="hidden" name="release_url" value="<?php echo $newUpdate["release_url"]; ?>" />
                            <button type='submit' name='selfupdate' value='selfupdate' class='btn btn-primary'>
								<?php echo __("BTN_START_SELFUPDATE", "SELFUPDATE"); ?>
							</button>
						</form>
					</div>
				</div>
			<?php endif; ?>
		<?php else: ?>
			<div class="alert alert-info fade show mb-5" role="alert">
				<?php echo __("NO_UPDATE_FOUND", "SELFUPDATE"); ?>
			</div>
		<?php endif; ?>
		<hr class='my-5'>
		<div class='changelog'>
			<?php echo $changelog; ?>
		</div>
	</div>
</div>
