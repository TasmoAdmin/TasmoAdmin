<?php

use Selective\Container\Container;
use TasmoAdmin\Config;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Backup\BackupHelper;
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
$container->set(DeviceRepository::class,  new DeviceRepository(_CSVFILE_, _TMPDIR_));
$container->set(Sonoff::class, new Sonoff($container->get(DeviceRepository::class)));
$container->set(i18n::class, new i18n());
$container->set(BackupHelper::class, new BackupHelper(
    $container->get(DeviceRepository::class),
    $container->get(Sonoff::class),
    _TMPDIR_ . 'backups/'
));

return $container;
