<?php

// Served from the base URL (not resources/js) so the worker can control every page.
$worker = _RESOURCESDIR_.'js/compiled/service_worker.min.js';
if (!is_file($worker)) {
    $worker = _RESOURCESDIR_.'js/compiled/service_worker.js';
}

header('Content-Type: application/javascript');
header('Cache-Control: no-cache');
header('Service-Worker-Allowed: '._BASEURL_);

readfile($worker);

exit;
