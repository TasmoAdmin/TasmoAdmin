<?php

use Selective\Container\Container;
use TasmoAdmin\Config;
use TasmoAdmin\Helper\UrlHelper;
use TasmoAdmin\Sonoff;

$container = new Container();

$container->set(Config::class, new Config(_DATADIR_, _APPROOT_));
$container->set(UrlHelper::class, new UrlHelper(
    $container->get(Config::class),
    _BASEURL_,
    _RESOURCESURL_,
    _RESOURCESDIR_)
);
$container->set(Sonoff::class, new Sonoff());
$container->set(i18n::class, new i18n());

return $container;
