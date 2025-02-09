<?php

use TasmoAdmin\Backup\BackupHelper;
use TasmoAdmin\Helper\FirmwareFolderHelper;
use TasmoAdmin\Sonoff;

$Sonoff = $container->get(Sonoff::class);

if (isset($_GET['doAjax'])) {
    session_write_close(); // stop blocking other ajax batch
    if (isset($_REQUEST['target'])) {
        $data = $Sonoff->setDeviceValue((int) $_REQUEST['id'], $_REQUEST['field'], $_REQUEST['newvalue']);
    } else {
        $data = $Sonoff->doAjax($_REQUEST['id'], urldecode($_REQUEST['cmnd']));
    }
    header('Content-Type: application/json');
    echo json_encode($data);

    exit;
}

if (isset($_GET['doAjaxAll'])) {
    session_write_close(); // stop blocking other ajax batch
    $data = $Sonoff->doAjaxAll();

    header('Content-Type: application/json');
    echo json_encode($data);

    exit;
}

if (isset($_GET['downloadBackup'])) {
    $backup = $container->get(BackupHelper::class);

    header('Content-type: application/zip');
    header('Content-Disposition: attachment; filename="tasmota-backup.zip"');
    header('Content-Length: '.filesize($backup->getBackupZipPath()));
    ob_clean();
    flush();
    readfile($backup->getBackupZipPath());

    exit;
}

if (isset($_GET['clean'])) {
    $what = explode('_', $_GET['clean']);

    if (in_array('sessions', $what)) {
        $files = glob(_TMPDIR_.'/sessions/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file) && false === strpos($file, '.empty')) {
                @unlink($file);
            } // delete file
        }
    }

    if (in_array('i18n', $what)) {
        $files = glob(_TMPDIR_.'/cache/i18n/*'); // get all file names present in folder
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    if (in_array('firmwares', $what)) {
        FirmwareFolderHelper::clean(_DATADIR_.'firmwares/');
    }

    if (in_array('config', $what)) {
        $files = glob(_DATADIR_.'/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file) && (strpos($file, 'MyConfig.json') || strpos($file, 'MyConfig.php'))) {
                @unlink($file);
            } // delete file
        }
        session_destroy();
    }

    if (in_array('devices', $what)) {
        $files = glob(_DATADIR_.'/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file) && strpos($file, 'devices.csv')) {
                @unlink($file);
            } // delete file
        }
    }

    exit;
}
