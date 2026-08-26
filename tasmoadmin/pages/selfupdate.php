<?php

use League\CommonMark\GithubFlavoredMarkdownConverter;
use TasmoAdmin\Helper\GuzzleFactory;
use TasmoAdmin\Helper\RequestHelper;
use TasmoAdmin\Helper\TasmoAdminHelper;
use TasmoAdmin\SelfUpdate;
use TasmoAdmin\Update\UpdateChecker;

$msg = '';

$currentGitTag = $Config->read('current_git_tag');

$updateChecker = new UpdateChecker(
    $Config->read('update_channel'),
    $currentGitTag,
    GuzzleFactory::getClient($Config)
);

$newUpdate = $updateChecker->checkForUpdate();
$officialReleaseUrl = (string) ($newUpdate['release_url'] ?? '');
$releaseUrl = (string) ($_POST['release_url'] ?? $officialReleaseUrl);
$latestTag = (string) ($_POST['latest_tag'] ?? $newUpdate['latest_tag'] ?? $currentGitTag);
$currentHost = (string) parse_url('http://'.($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
$unfamiliarSource = RequestHelper::isUnfamiliarUpdateSource($releaseUrl, $officialReleaseUrl, $currentHost);

if (isset($_POST['selfupdate'])) {
    $scheme = parse_url($releaseUrl, PHP_URL_SCHEME);
    if (!filter_var($releaseUrl, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
        $msg = __('INVALID_RELEASE_URL', 'SELFUPDATE');
        $msgClass = 'danger';
    } elseif ($unfamiliarSource && '1' !== ($_POST['confirm_unfamiliar_source'] ?? '')) {
        $msg = __('UNFAMILIAR_SOURCE_WARNING', 'SELFUPDATE');
        $msgClass = 'warning';
    } else {
        $selfUpdate = new SelfUpdate($Config, GuzzleFactory::getClient($Config));
        $result = $selfUpdate->update($releaseUrl, $latestTag);
        $msg = implode('<br/>', $result['logs']);
        $msgClass = $result['success'] ? 'success' : 'danger';
    }
}

$tasmoAdminHelper = new TasmoAdminHelper(new GithubFlavoredMarkdownConverter(), GuzzleFactory::getClient($Config));
$changelog = $tasmoAdminHelper->getChangelog();

?>

<div class='row justify-content-sm-center update-page selfupdate-page'>
	<div class='col col-12 col-xl-8'>
		<h2 class='text-sm-center mb-4'>
			<?php if (!$docker) { ?>
				<?php echo $title; ?>
			<?php } else { ?>
				<?php echo __('HELP_CHANGELOG', 'NAVI'); ?>
			<?php } ?>
		</h2>

		<?php if (!empty($msg)) { ?>
			<div class="alert alert-<?php echo $msgClass; ?> alert-dismissible fade show mb-5" data-bs-dismiss="alert" role="alert">
				<?php echo $msg; ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php } ?>
		<?php if (isset($newUpdate['error']) && '' != $newUpdate['error']) { ?>
			<div class="alert alert-danger alert-dismissible fade show mb-5" data-bs-dismiss="alert" role="alert">
				<?php echo $newUpdate['msg']; ?>
				<br/>
				<?php echo __('ERROR_CHECK_CONNECTION', 'SELFUPDATE'); ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php } ?>

		<?php if ($newUpdate['update']) { ?>
			<div class="alert alert-success fade show mb-5" role="alert">
				<?php echo __('UPDATE_FOUND', 'SELFUPDATE'); ?>!
			</div>
			<div class='card update-card update-version-card mb-4'>
				<div class='card-body'>
					<div class='row g-4 align-items-center update-version-row'>
						<div class='col col-12 col-sm-5'>
							<a class='btn btn-secondary w-100'
							   <?php if (!empty($currentGitTag)) { ?>href='https://github.com/TasmoAdmin/TasmoAdmin/releases/tag/<?php echo $currentGitTag; ?>'
							   target='_blank' <?php } ?>>
								<?php echo __('OLD_TAG_VERSION', 'SELFUPDATE', [$currentGitTag ?: __('UNKNOWN', 'SELFUPDATE')]); ?>
							</a>
						</div>
						<div class='col col-12 col-sm-2 text-center update-version-arrow'>
							<i class="fas fa-angle-double-right fa-3x d-none d-sm-inline-block" style='font-size:2.5rem;'></i> <i
								class="fas fa-angle-double-down d-inline-block d-sm-none fa-3x"
								style='font-size:2.5rem;'
							></i>
						</div>
						<div class='col col-12 col-sm-5'>
							<a class='btn btn-primary w-100 btn-green'
							   <?php if (!empty($newUpdate['latest_tag'])) { ?>href='https://github.com/TasmoAdmin/TasmoAdmin/releases/tag/<?php echo $newUpdate['latest_tag']; ?>'
							   target='_blank' <?php } ?>>
								<?php echo __('NEW_TAG_VERSION', 'SELFUPDATE', [$newUpdate['latest_tag']]); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php } else { ?>
			<div class="alert alert-info fade show mb-5" role="alert">
				<?php echo __('NO_UPDATE_FOUND', 'SELFUPDATE'); ?>
			</div>
			<?php } ?>

			<?php if (!$docker && in_array($Config->read('update_channel'), ['dev', 'beta', 'stable'])) { ?>
				<div class='card update-card mb-5'>
					<div class='card-body'>
						<form name='selfupdateform' method='post'>
							<?php echo RequestHelper::csrfTokenField(); ?>
							<div class='row g-3 justify-content-sm-center'>
								<div class="col col-12 col-sm-8">
									<label for="release_url" class="form-label"><?php echo __('RELEASE_URL', 'SELFUPDATE'); ?></label>
									<input type="url" class="form-control" id="release_url" name="release_url" value="<?php echo htmlspecialchars($releaseUrl, ENT_QUOTES, 'UTF-8'); ?>" required>
								</div>
								<div class="col col-12 col-sm-8">
									<label for="latest_tag" class="form-label"><?php echo __('RELEASE_TAG', 'SELFUPDATE'); ?></label>
									<input type="text" class="form-control" id="latest_tag" name="latest_tag" value="<?php echo htmlspecialchars($latestTag, ENT_QUOTES, 'UTF-8'); ?>" required>
								</div>
								<?php if ($unfamiliarSource) { ?>
									<div class="col col-12 col-sm-8">
										<div class="alert alert-warning mb-0" role="alert"><?php echo __('UNFAMILIAR_SOURCE_WARNING', 'SELFUPDATE'); ?></div>
										<div class="form-check mt-3">
											<input class="form-check-input" type="checkbox" value="1" id="confirm_unfamiliar_source" name="confirm_unfamiliar_source">
											<label class="form-check-label" for="confirm_unfamiliar_source"><?php echo __('CONFIRM_UNFAMILIAR_SOURCE', 'SELFUPDATE'); ?></label>
										</div>
									</div>
								<?php } ?>
								<div class="col col-12 col-sm-8 text-center">
									<button type='submit' name='selfupdate' value='selfupdate' class='btn btn-primary w-100'>
										<?php echo __('BTN_START_SELFUPDATE', 'SELFUPDATE'); ?>
									</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			<?php } ?>

		<?php if (!empty($changelog)) { ?>
			<div class='card update-card update-changelog-card'>
				<div class='card-body changelog'>
					<h1>
						Changelog
					</h1>
					<?php foreach ($changelog as $entry) { ?>
						<?php if ('stable' != $Config->read('update_channel') or ('stable' == $Config->read('update_channel') and !$entry->prerelease)) { ?>
							<hr/>
							<h2 class='mb-3'>
								<a target="blank" href="<?php echo $entry->html_url; ?>"><?php echo $entry->name; ?></a>
							</h2>
							<?php echo $entry->body; ?>
						<?php } ?>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
