<?php

use TasmoAdmin\Backup\BackupHelper;

use TasmoAdmin\Sonoff;

$Sonoff = $container->get(Sonoff::class);

$devices = $Sonoff->getDevices();
if (isset($_POST['device_ids'])) {
    $backupHelper = $container->get(BackupHelper::class);
    $backupResults = $backupHelper->backup($_POST['device_ids']);
    $backupAction = $backupResults->successful() ? 'success' : 'danger';
}
?>

<?php if (isset($backupResults, $backupAction)): ?>
<div class='row justify-content-sm-center'>
    <div class='col col-12 col-md-6 '>
        <div class="alert alert-<?php echo $backupAction; ?> fade show mb-3" role="alert">
            <div class="col col-12">
                <?php echo __("BACKUP_FINISHED", "BACKUP"); ?> - <a href="/actions?downloadBackup"><?php echo __("DOWNLOAD_BACKUP", "BACKUP"); ?></a>
                <?php if (!$backupResults->successful()): ?>
                    </br>
                    </br>
                    <?php echo __("BACKUP_FAILED", "BACKUP"); ?>
                    <ul>
                    <?php foreach ($backupResults->getFailures() as $failure): ?>
                        <li><?php echo $failure->getDevice()->getName() . ': ' . $failure->getFailureReason(); ?></li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class='row justify-content-center'>
    <div class='col'>
        <div class='mb-3 text-center '>
            <h3>
                <?php echo __("CHOOSE_DEVICES_TO_BACKUP", "BACKUP"); ?>
            </h3>
        </div>
        <form name='backup'
              class=''
              id='backup'
              method='post'
              action='<?php echo _BASEURL_; ?>backup'
        >
        <div class='form-row mb-3'>
            <div class='offset-1 col-auto col col-auto'>
                <button type='submit' class='btn btn-success' name='submit' value='submit'>
                    <?php echo __("BTN_START_BACKUP", "BACKUP"); ?>
                </button>
            </div>
            <div class='col col-auto'>
                <div class="form-check pl-0">
                    <input type="checkbox"
                           class="form-check-input showmore d-none"
                           id="showmore"
                           name='showmore'
                    >
                    <label class="form-check-label  btn btn-secondary" for="showmore">
                        <?php echo __("SHOW_MORE", "DEVICES"); ?>
                    </label>
                </div>
            </div>
            <?php if ($Config->read("show_search") == 1): ?>
                <div class="col col-auto">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text"
                                   name="searchterm"
                                   class='form-control device-search has-clearer'
                                   placeholder="<?php echo __("FILTER", "DEVICES"); ?>"
                            >
                            <div class="input-group-append">
										<span class="input-group-text">
											<i class="fas fa-search"></i>
										</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class='table-responsive double-scroll'>
            <?php
            $deviceLinks = true;
$deviceLinkActionText = __("BACKUP", "BACKUP");
include "elements/devices_table.php";
?>
        </div>
        </form>
    </div>
</div>
<script src="<?php echo $urlHelper->js("devices"); ?>"></script>
