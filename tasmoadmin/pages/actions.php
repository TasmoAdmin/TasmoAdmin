<?php

use TasmoAdmin\Backup\BackupHelper;

use TasmoAdmin\Sonoff;

$Sonoff = $container->get(Sonoff::class);

if (isset($_GET["doAjax"])) {
    session_write_close(); //stop blocking other ajax batch
    if(isset($_REQUEST["target"])) {
        $data = $Sonoff->setDeviceValue((int)$_REQUEST["id"], $_REQUEST["field"], $_REQUEST["newvalue"]);
    } else {
        $data = $Sonoff->doAjax($_REQUEST["id"], urldecode($_REQUEST['cmnd']));
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    die();
}

if(isset($_GET["doAjaxAll"])) {
    session_write_close(); //stop blocking other ajax batch
    $data = $Sonoff->doAjaxAll();

    header('Content-Type: application/json');
    echo json_encode($data);
    die();
}

if (isset($_GET['downloadBackup'])) {
    $backup = $container->get(BackupHelper::class);

    header('Content-type: application/zip');
    header('Content-Disposition: attachment; filename="tasmota-backup.zip"');
    header('Content-Length: '.filesize($backup->getBackupZipPath()));
    ob_clean();
    flush();
    readfile($backup->getBackupZipPath());
    die();
}
