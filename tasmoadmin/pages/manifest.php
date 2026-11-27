<?php

use TasmoAdmin\Helper\PwaHelper;

// render_raw would send text/html, so answer and stop here.
header('Content-Type: application/manifest+json');
header('Cache-Control: no-cache');

echo json_encode(
    new PwaHelper()->manifest(_BASEURL_, _RESOURCESURL_, $lang, __('DESCRIPTION', 'PWA')),
    JSON_UNESCAPED_SLASHES
);

exit;
