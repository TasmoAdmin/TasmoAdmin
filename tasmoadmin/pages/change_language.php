<?php

use TasmoAdmin\Helper\RedirectHelper;

$redirectHelper = $container->get(RedirectHelper::class);

$_SESSION['lang'] = $new_lang;
$redirect = $redirectHelper->getValidRedirectUrl($_GET['current'] ?? _BASEURL_, _BASEURL_);
header("Location: {$redirect}");
