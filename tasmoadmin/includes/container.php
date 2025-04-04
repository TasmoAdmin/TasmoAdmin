<?php

use Selective\Container\Container;
use TasmoAdmin\Backup\BackupHelper;
use TasmoAdmin\Config;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Helper\RedirectHelper;
use TasmoAdmin\Helper\UrlHelper;
use TasmoAdmin\Helper\ViewHelper;
use TasmoAdmin\Http\HttpClientFactory;
use TasmoAdmin\Sonoff;

$container = new Container();

$container->set(Config::class, new Config(_DATADIR_, _APPROOT_));
$container->set(
    UrlHelper::class,
    new UrlHelper(
        $container->get(Config::class),
        _RESOURCESURL_,
        _RESOURCESDIR_
    )
);
$container->set(HttpClientFactory::class, new HttpClientFactory($container->get(Config::class)));
$container->set(DeviceRepository::class, new DeviceRepository(_CSVFILE_, _TMPDIR_));
$container->set(Sonoff::class, new Sonoff(
    $container->get(DeviceRepository::class),
    $container->get(HttpClientFactory::class)->getClient(),
    $container->get(Config::class)
));
$container->set(i18n::class, new i18n());
$container->set(BackupHelper::class, new BackupHelper(
    $container->get(DeviceRepository::class),
    $container->get(Sonoff::class),
    _TMPDIR_.'backups/'
));
$container->set(ViewHelper::class, new ViewHelper($container->get(Config::class)));
$container->set(RedirectHelper::class, new RedirectHelper(_BASEURL_));

return $container;
