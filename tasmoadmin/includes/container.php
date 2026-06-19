<?php

use Selective\Container\Container;
use TasmoAdmin\Backup\BackupHelper;
use TasmoAdmin\Config;
use TasmoAdmin\DevicePasswordCipher;
use TasmoAdmin\DevicePasswordKeyProvider;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Helper\RedirectHelper;
use TasmoAdmin\Helper\UrlHelper;
use TasmoAdmin\Helper\ViewHelper;
use TasmoAdmin\Http\HttpClientFactory;
use TasmoAdmin\Mqtt\MqttDiscoveryService;
use TasmoAdmin\Mqtt\PhpMqttClientFactory;
use TasmoAdmin\Mqtt\SystemTimeProvider;
use TasmoAdmin\Sonoff;
use TasmoAdmin\Tasmota\ResponseParser;

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
$container->set(DevicePasswordKeyProvider::class, new DevicePasswordKeyProvider(_DATADIR_));
$container->set(DevicePasswordCipher::class, new DevicePasswordCipher($container->get(DevicePasswordKeyProvider::class)));
$container->set(ResponseParser::class, new ResponseParser());
$container->set(DeviceRepository::class, new DeviceRepository(
    _CSVFILE_,
    _TMPDIR_,
    $container->get(DevicePasswordCipher::class),
    '1' === $container->get(Config::class)->read('confirm_device_toggles')
));
$container->set(Sonoff::class, new Sonoff(
    $container->get(DeviceRepository::class),
    $container->get(HttpClientFactory::class)->getClient(),
    $container->get(Config::class)
));
$container->set(MqttDiscoveryService::class, new MqttDiscoveryService(
    $container->get(DeviceRepository::class),
    $container->get(ResponseParser::class),
    new PhpMqttClientFactory(),
    new SystemTimeProvider()
));
$container->set(i18n::class, new i18n());
$container->set(BackupHelper::class, new BackupHelper(
    $container->get(DeviceRepository::class),
    $container->get(Sonoff::class),
    _TMPDIR_.'backups/',
    $container->get(Config::class),
    _BASEURL_
));
$container->set(ViewHelper::class, new ViewHelper($container->get(Config::class)));
$container->set(RedirectHelper::class, new RedirectHelper(_BASEURL_));

return $container;
