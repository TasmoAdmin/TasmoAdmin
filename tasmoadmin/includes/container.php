<?php

use Selective\Container\Container;
use TasmoAdmin\Config;
use TasmoAdmin\Sonoff;

$container = new Container();

$container->set(Config::class, new Config(_DATADIR_, _APPROOT_));
$container->set(Sonoff::class, new Sonoff());
$container->set(i18n::class, new i18n());

return $container;
