<?php

use TasmoAdmin\Backup\BackupHelper;
use TasmoAdmin\Sonoff;

$Sonoff = $container->get(Sonoff::class);
$devices = $Sonoff->getDevices();

if (isset($_POST['batch_action'], $_POST['device_ids'])) {
    $backupHelper = $container->get(BackupHelper::class);

    try {
        if ('backup' === $_POST['batch_action']) {
            $backupResults = $backupHelper->backup($_POST['device_ids']);
            $backupAction = $backupResults->successful() ? 'success' : 'danger';
        }

        if ('restore' === $_POST['batch_action']) {
            $restoreResults = $backupHelper->restore($_POST['device_ids'], $_FILES['restore_backup'] ?? null);
            $restoreAction = $restoreResults->successful() ? 'success' : 'danger';
        }
    } catch (RuntimeException $exception) {
        if ('backup' === $_POST['batch_action']) {
            $backupAction = 'danger';
            $backupError = $exception->getMessage();
        }

        if ('restore' === $_POST['batch_action']) {
            $restoreAction = 'danger';
            $restoreError = $exception->getMessage();
        }
    }
}
?>
<div class='row justify-content-sm-center'>
	<div class='col col-12 devices-page'>

		<?php if (!empty($devices)) { ?>
            <?php if (isset($restoreAction)) { ?>
                <div class="devices-panel">
                    <div class="alert alert-<?php echo $restoreAction; ?> fade show mb-0" role="alert">
                        <div class="col col-12">
                            <?php if (isset($restoreError)) { ?>
                                <?php echo $restoreError; ?>
                            <?php } elseif (isset($restoreResults) && $restoreResults->successful()) { ?>
                                <?php echo __('RESTORE_STARTED', 'BACKUP'); ?>
                                <br>
                                <small><?php echo __('RESTORE_WARNING', 'BACKUP'); ?></small>
                            <?php } elseif (isset($restoreResults)) { ?>
                                <?php echo __('RESTORE_FAILED', 'BACKUP'); ?>
                                <ul class="mb-0">
                                    <?php foreach ($restoreResults->getFailures() as $failure) { ?>
                                        <li><?php echo $failure->getDevice()->getName().': '.$failure->getFailureReason(); ?></li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php if (isset($backupAction)) { ?>
                <div class="devices-panel">
                    <div class="alert alert-<?php echo $backupAction; ?> fade show mb-0" role="alert">
                        <div class="col col-12">
                            <?php if (isset($backupError)) { ?>
                                <?php echo $backupError; ?>
                            <?php } elseif (isset($backupResults)) { ?>
                            <?php echo __('BACKUP_FINISHED', 'BACKUP'); ?> -
                            <a href="<?php echo _BASEURL_; ?>actions?downloadBackup"><?php echo __('DOWNLOAD_BACKUP', 'BACKUP'); ?></a>
                            <?php if (!$backupResults->successful()) { ?>
                                </br>
                                </br>
                                <?php echo __('BACKUP_FAILED', 'BACKUP'); ?>
                                <ul>
                                    <?php foreach ($backupResults->getFailures() as $failure) { ?>
                                        <li><?php echo $failure->getDevice()->getName().': '.$failure->getFailureReason(); ?></li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
			<div class="devices-panel devices-toolbar">
				<div class='row g-3 align-items-end devices-toolbar-row'>
					<div class="col col-12 col-md-auto">
						<div class="dropdown" data-bs-auto-close="outside">
							<button class="btn btn-secondary dropdown-toggle"
									type="button"
									id="deviceColumnsMenuButton"
									data-bs-toggle="dropdown"
									aria-expanded="false"
							>
								<?php echo __('VISIBLE_COLUMNS', 'DEVICES'); ?>
							</button>
							<div class="dropdown-menu p-3 device-columns-menu"
								 aria-labelledby="deviceColumnsMenuButton"
							></div>
						</div>
					</div>
					<?php if (1 == $Config->read('show_search')) { ?>
						<div class="col col-12 col-lg-5 devices-toolbar-search">
							<div class="input-group device-search-group">
								<input type="text"
									   name="searchterm"
									   class='form-control device-search has-clearer'
									   autocomplete="off"
									   placeholder="<?php echo __('FILTER', 'DEVICES'); // (Name, IP#123, ID#321, POS#1)?>"
								>
								<div class="input-group-text">
									<i class="fas fa-search" aria-hidden="true"></i>
								</div>
							</div>
						</div>
					<?php } ?>
						<div class="col col-12 col-lg-auto ms-lg-auto devices-toolbar-lock-col">
						<div class="form-check ps-0 devices-protection-toggle">
							<input type="checkbox"
								   class="form-check-input ignoreProtections d-none"
								   id="ignoreProtections"
								   name='ignoreProtections'
							>
							<label class="form-check-label btn btn-secondary w-100"
								   title="<?php echo __('BTN_UNLOCK_TOOLTIP', 'DEVICES'); ?>"
								   for="ignoreProtections"
							>
								<i class="fas fa-lock" style="width: 18px;"></i>
							</label>
						</div>
					</div>
				</div>
			</div>
            <form method='post' action='<?php echo _BASEURL_; ?>devices' class='devices-batch-form' enctype='multipart/form-data'>
                <input type='hidden' name='batch_action' class='batchActionField' value=''>
                <div class="devices-panel devices-table-panel">
					<div class='table-responsive double-scroll'>
                        <?php
                        $deviceLinks = true;
		    $deviceLinksDefaultHide = false;
		    $deviceLinkActionText = __('CB_COMMAND', 'DEVICES');

		    include 'elements/devices_table.php';
		    ?>
					</div>
                </div>
                <div class="devices-panel devices-batch-panel">
                    <div class='row g-3 align-items-end devices-batch-row'>
                        <div class="col col-12 col-lg-auto devices-batch-select-col">
                            <select class='form-select batchActionSelect'
                                    name='batch_action_select'
                                    aria-label="<?php echo __('PLEASE_SELECT'); ?>"
                            >
                                <option value=''><?php echo __('PLEASE_SELECT'); ?></option>
                                <option value='command'><?php echo __('BTN_COMMAND', 'DEVICES'); ?></option>
                                <option value='backup'><?php echo __('BATCH_BACKUP_CREATE', 'BACKUP'); ?></option>
                                <option value='restore'><?php echo __('BATCH_BACKUP_RESTORE', 'BACKUP'); ?></option>
                                <option value='restart'><?php echo __('RESTART_SELECTED', 'DEVICES'); ?></option>
                                <option value='delete'><?php echo __('DELETE_SELECTED', 'DEVICES'); ?></option>
                            </select>
                        </div>
                        <div class="col col-12 col-lg batchActionCommandWrapper devices-batch-command-col d-none">
                            <input type='text'
                                   name='command'
                                   class='form-control batchActionCommandInput'
                                   placeholder="<?php echo __('CB_COMMAND', 'DEVICES'); ?>"
                            >
                        </div>
                        <div class="col col-12 col-lg batchActionFileWrapper devices-batch-command-col d-none">
                            <input type='file'
                                   name='restore_backup'
                                   class='form-control batchActionFileInput'
                                   accept='.dmp'
                            >
                            <small class="form-text text-body-secondary"><?php echo __('RESTORE_WARNING', 'BACKUP'); ?></small>
                        </div>
                        <div class="col col-12 col-sm-auto devices-batch-submit-col">
                            <button type='button' class='btn btn-primary applyBatchAction w-100' disabled>
                                <?php echo __('PLEASE_SELECT'); ?>
                            </button>
                        </div>
                        <div class="col col-12 col-lg-auto ms-lg-auto devices-batch-add-col">
                            <a href='<?php echo _BASEURL_; ?>device_action/add' class="btn btn-primary">
                                <?php echo __('TABLE_HEAD_NEW_DEVICE', 'DEVICES'); ?>
                            </a>
                        </div>
                        <div class="col col-12">
                            <small class="form-text batchActionFeedback d-none"></small>
                        </div>
                    </div>
                </div>
            </form>
		<?php } else { ?>
			<div class="devices-panel devices-empty-state text-center">
				<div class='devices-empty-copy'>
					<?php echo __('NO_DEVICES_FOUND', 'STARTPAGE'); ?>
				</div>
				<div class='row g-3 justify-content-center devices-empty-actions'>
					<div class='col col-12 col-sm-4 col-lg-3'>
					<a class="btn btn-primary"
					   href="<?php echo _BASEURL_; ?>devices_autoscan"
					>
						<?php echo __('DEVICES_AUTOSCAN', 'NAVI'); ?>
					</a>
				</div>
					<div class='col col-12 col-sm-4 col-lg-3'>
					<a href='<?php echo _BASEURL_; ?>device_action/add' class="btn btn-primary">
						<?php echo __('TABLE_HEAD_NEW_DEVICE', 'DEVICES'); ?>
					</a>
					</div>
				</div>
			</div>

		<?php } ?>


	</div>
</div>
<?php include 'elements/modal_delete_device.php'; ?>

<script src="<?php echo $urlHelper->js('compiled/devices'); ?>"></script>
