<?php

use TasmoAdmin\Backup\BackupHelper;
use TasmoAdmin\DevicePasswordKeyProvider;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Helper\CacheCleanupHelper;
use TasmoAdmin\Helper\FirmwareFolderHelper;
use TasmoAdmin\Helper\SupportedLanguageHelper;
use TasmoAdmin\Sonoff;

$Sonoff = $container->get(Sonoff::class);

if (isset($_GET['removeDevices'], $_GET['ids'])) {
    $deviceRepository = $container->get(DeviceRepository::class);
    $ids = array_map('intval', explode(',', $_GET['ids']));
    $deviceRepository->removeDevices($ids);

    exit;
}

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

if (isset($_GET['i18n'])) {
    $requestedLang = $_GET['lang'] ?? $lang;
    $supportedLanguages = SupportedLanguageHelper::getSupportedLanguages();
    $language = array_key_exists($requestedLang, $supportedLanguages) ? $requestedLang : $lang;
    $cacheFile = _TMPDIR_.'cache/i18n/json_i18n_'.$language.'.cache.json';

    if (!is_file($cacheFile)) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Language cache not found']);

        exit;
    }

    header('Content-Type: application/json');
    readfile($cacheFile);

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

if (isset($_GET['downloadRestore'])) {
    $backup = $container->get(BackupHelper::class);
    $restorePath = $backup->getRestoreFilePath((string) $_GET['downloadRestore']);
    if (null === $restorePath) {
        http_response_code(404);

        exit;
    }

    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename="restore.dmp"');
    header('Content-Length: '.filesize($restorePath));
    ob_clean();
    flush();
    readfile($restorePath);

    exit;
}

if (isset($_GET['clean'])) {
    $what = explode('_', $_GET['clean']);

    if (array_intersect(['sessions', 'i18n'], $what)) {
        CacheCleanupHelper::cleanTargets(_TMPDIR_, $what);
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

        $devicePasswordKeyFile = _DATADIR_.DevicePasswordKeyProvider::SIDECAR_FILENAME;
        if (is_file($devicePasswordKeyFile)) {
            @unlink($devicePasswordKeyFile);
        }
    }

    exit;
}
