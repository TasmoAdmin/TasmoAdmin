<?php

use TasmoAdmin\Config;
use TasmoAdmin\DeviceFactory;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Helper\IpHelper;
use TasmoAdmin\Mqtt\MqttDiscoveryRequest;
use TasmoAdmin\Mqtt\MqttDiscoveryResult;
use TasmoAdmin\Mqtt\MqttDiscoveryService;
use TasmoAdmin\Sonoff;

if (!function_exists('getAutoscanMode')) {
    function getAutoscanMode(array $request): string
    {
        return isset($request['scan_mode']) && 'mqtt' === $request['scan_mode'] ? 'mqtt' : 'network';
    }
}

if (!function_exists('normalizeAutoscanStatusDevices')) {
    function normalizeAutoscanStatusDevices(array $devicesFound): array
    {
        $normalizedDevices = [];
        foreach ($devicesFound as $device) {
            if (empty($device) || !($device instanceof stdClass) || !empty($device->error)) {
                continue;
            }
            if (empty($device->StatusNET->IPAddress)) {
                continue;
            }

            $normalizedDevices[(string) ip2long($device->StatusNET->IPAddress)] = $device;
        }

        ksort($normalizedDevices, SORT_NUMERIC);

        return array_values($normalizedDevices);
    }
}

if (!function_exists('getAutoscanFriendlyNames')) {
    function getAutoscanFriendlyNames(stdClass $device): array
    {
        $friendlyNames = $device->Status->FriendlyName ?? [];
        if (!is_array($friendlyNames)) {
            $friendlyNames = [$friendlyNames];
        }

        $friendlyNames = array_values(array_filter(array_map('trim', $friendlyNames), static fn ($value) => '' !== $value));
        if ([] !== $friendlyNames) {
            return $friendlyNames;
        }

        return [''];
    }
}

if (!function_exists('getAutoscanChannelCount')) {
    function getAutoscanChannelCount(stdClass $device): int
    {
        if (isset($device->StatusSTS->POWER)) {
            return 1;
        }

        $channelCount = 0;
        for ($i = 1; isset($device->StatusSTS->{'POWER'.$i}); ++$i) {
            ++$channelCount;
        }

        return max($channelCount, 1);
    }
}

if (!function_exists('getAutoscanModeUrl')) {
    function getAutoscanModeUrl(string $mode): string
    {
        return sprintf('%sdevices_autoscan?scan_mode=%s', _BASEURL_, $mode);
    }
}

$Sonoff = $container->get(Sonoff::class);
$Config = $container->get(Config::class);

$status = false;
$devices = null;
$devicesFound = [];
$msg = null;
$action = '';
$error = false;
$scanMode = getAutoscanMode($_REQUEST);
$mqttDiscoveryResult = new MqttDiscoveryResult();
$rawConfig = $Config->readAll();

if (isset($_REQUEST) && !empty($_REQUEST)) {
    try {
        if (isset($_REQUEST['search'])) {
            $httpPort = (int) htmlspecialchars($_REQUEST['port'] ?? $Config->read('port'));
            $deviceUsername = htmlspecialchars($_REQUEST['device_username'] ?? '');
            $devicePassword = htmlspecialchars($_REQUEST['device_password'] ?? '');

            if ('mqtt' === $scanMode) {
                $mqttDiscoveryHost = trim((string) ($_REQUEST['mqtt_discovery_host'] ?? ''));
                $mqttDiscoveryPort = (int) ($_REQUEST['mqtt_discovery_port'] ?? 1883);
                $mqttDiscoveryUsername = trim((string) ($_REQUEST['mqtt_discovery_username'] ?? ''));
                $mqttDiscoveryPasswordInput = (string) ($_REQUEST['mqtt_discovery_password'] ?? '');
                $mqttDiscoveryPassword = '' !== $mqttDiscoveryPasswordInput
                    ? $mqttDiscoveryPasswordInput
                    : (string) ($rawConfig['mqtt_discovery_password'] ?? '');
                $mqttDiscoveryCmndPrefix = trim((string) ($_REQUEST['mqtt_discovery_cmnd_prefix'] ?? 'cmnd'));
                $mqttDiscoveryStatPrefix = trim((string) ($_REQUEST['mqtt_discovery_stat_prefix'] ?? 'stat'));
                $mqttDiscoveryTelePrefix = trim((string) ($_REQUEST['mqtt_discovery_tele_prefix'] ?? 'tele'));
                $mqttDiscoverySubscriptions = trim((string) ($_REQUEST['mqtt_discovery_subscriptions'] ?? ''));
                $mqttDiscoveryTimeoutSeconds = (int) ($_REQUEST['mqtt_discovery_timeout_seconds'] ?? 5);
                $subscriptionFilters = array_values(array_filter(
                    array_map('trim', preg_split('/\R+/', $mqttDiscoverySubscriptions) ?: []),
                    static fn ($value) => '' !== $value
                ));

                if ('' === $mqttDiscoveryHost) {
                    throw new InvalidArgumentException(__('MQTT_DISCOVERY_HOST_REQUIRED', 'DEVICES_AUTOSCAN'));
                }
                if ([] === $subscriptionFilters) {
                    throw new InvalidArgumentException(__('MQTT_DISCOVERY_SUBSCRIPTIONS_REQUIRED', 'DEVICES_AUTOSCAN'));
                }

                $configUpdates = [
                    'port' => (string) $httpPort,
                    'mqtt_discovery_host' => $mqttDiscoveryHost,
                    'mqtt_discovery_port' => (string) $mqttDiscoveryPort,
                    'mqtt_discovery_username' => $mqttDiscoveryUsername,
                    'mqtt_discovery_cmnd_prefix' => $mqttDiscoveryCmndPrefix,
                    'mqtt_discovery_stat_prefix' => $mqttDiscoveryStatPrefix,
                    'mqtt_discovery_tele_prefix' => $mqttDiscoveryTelePrefix,
                    'mqtt_discovery_subscriptions' => implode(PHP_EOL, $subscriptionFilters),
                    'mqtt_discovery_timeout_seconds' => (string) max($mqttDiscoveryTimeoutSeconds, 1),
                ];
                if ('' !== $mqttDiscoveryPasswordInput) {
                    $configUpdates['mqtt_discovery_password'] = $mqttDiscoveryPasswordInput;
                }
                $Config->writeAll($configUpdates);

                $mqttDiscoveryResult = $container->get(MqttDiscoveryService::class)->scan(new MqttDiscoveryRequest(
                    $mqttDiscoveryHost,
                    $mqttDiscoveryPort,
                    $mqttDiscoveryUsername,
                    $mqttDiscoveryPassword,
                    $mqttDiscoveryCmndPrefix,
                    $mqttDiscoveryStatPrefix,
                    $mqttDiscoveryTelePrefix,
                    $subscriptionFilters,
                    max($mqttDiscoveryTimeoutSeconds, 1),
                    $httpPort,
                    $deviceUsername,
                    $devicePassword
                ));

                $devicesFound = normalizeAutoscanStatusDevices($mqttDiscoveryResult->newDevices);
                $msg = __('MSG_MQTT_SCAN_SUMMARY', 'DEVICES_AUTOSCAN', [
                    (string) count($mqttDiscoveryResult->updatedDevices),
                    (string) count($devicesFound),
                    (string) count($mqttDiscoveryResult->offlineTopics),
                    (string) count($mqttDiscoveryResult->conflicts),
                ]);

                if ([] === $devicesFound && [] === $mqttDiscoveryResult->updatedDevices && [] === $mqttDiscoveryResult->offlineTopics
                    && [] === $mqttDiscoveryResult->conflicts) {
                    $msg = __('MSG_NO_DEVICES_FOUND', 'DEVICES_AUTOSCAN');
                    $error = true;
                }
            } else {
                $fromIp = htmlspecialchars($_REQUEST['from_ip']);
                $toIp = htmlspecialchars($_REQUEST['to_ip']);
                $additionalScanRanges = trim((string) ($_REQUEST['additional_scan_ranges'] ?? ''));

                $ipHelper = new IpHelper();
                $devices = $Sonoff->getDevices();
                $skippedAddresses = [];
                foreach ($devices as $device) {
                    $skippedAddresses[] = $device->getAddress();
                }

                $ips = $ipHelper->fetchIpsForRanges(array_merge(
                    [sprintf('%s-%s', $fromIp, $toIp)],
                    preg_split('/\R+/', $additionalScanRanges) ?: []
                ));
                $Config->write('scan_from_ip', $fromIp);
                $Config->write('scan_to_ip', $toIp);
                $Config->write('additional_scan_ranges', $additionalScanRanges);
                $Config->write('port', (string) $httpPort);

                $urls = [];
                foreach ($ips as $ip) {
                    $fakeDevice = DeviceFactory::fakeDevice($ip, $httpPort, $deviceUsername, $devicePassword);

                    if (in_array($fakeDevice->getAddress(), $skippedAddresses, true)) {
                        continue;
                    }

                    $urls[] = $Sonoff->buildCmndUrl($fakeDevice, Sonoff::COMMAND_INFO_STATUS_ALL);
                }

                $devicesFound = normalizeAutoscanStatusDevices($Sonoff->search($urls));

                if ([] === $devicesFound) {
                    $msg = __('MSG_NO_DEVICES_FOUND', 'DEVICES_AUTOSCAN');
                    $error = true;
                } else {
                    $msg = __('MSG_DEVICES_FOUND_COUNT', 'DEVICES_AUTOSCAN').': '.count($devicesFound);
                }
            }
        } elseif (isset($_REQUEST['save_device'])) {
            $deviceRepository = $container->get(DeviceRepository::class);
            $deviceUsername = htmlspecialchars($_REQUEST['device_username'] ?? '');
            $devicePassword = htmlspecialchars($_REQUEST['device_password'] ?? '');
            $deviceIndex = filter_var($_REQUEST['save_device'], FILTER_VALIDATE_INT);

            if (false === $deviceIndex || !isset($_REQUEST['devices'][$deviceIndex])) {
                throw new InvalidArgumentException(__('MSG_NO_DEVICES_FOUND', 'DEVICES_AUTOSCAN'));
            }

            $deviceRepository->addDevices([$_REQUEST['devices'][$deviceIndex]], $deviceUsername, $devicePassword);
            $msg = __('MSG_DEVICES_ADD_DONE', 'DEVICES_AUTOSCAN');
            $action = 'done';
        } elseif (isset($_REQUEST['save_all'])) {
            $deviceRepository = $container->get(DeviceRepository::class);
            $deviceUsername = htmlspecialchars($_REQUEST['device_username'] ?? '');
            $devicePassword = htmlspecialchars($_REQUEST['device_password'] ?? '');
            $deviceRepository->addDevices($_REQUEST['devices'], $deviceUsername, $devicePassword);
            $msg = __('MSG_DEVICES_ADD_DONE', 'DEVICES_AUTOSCAN');
            $action = 'done';
        }
    } catch (Throwable $ex) {
        $error = true;
        $msg = $ex->getMessage();
    }
}

$scanFromIp = $Config->read('scan_from_ip');
$scanToIp = $Config->read('scan_to_ip');
$additionalScanRanges = $Config->read('additional_scan_ranges');
$port = $_REQUEST['port'] ?? $Config->read('port');
$mqttDiscoveryHost = $_REQUEST['mqtt_discovery_host'] ?? $Config->read('mqtt_discovery_host');
$mqttDiscoveryPort = $_REQUEST['mqtt_discovery_port'] ?? $Config->read('mqtt_discovery_port');
$mqttDiscoveryUsername = $_REQUEST['mqtt_discovery_username'] ?? $Config->read('mqtt_discovery_username');
$mqttDiscoveryPasswordStored = '' !== $Config->read('mqtt_discovery_password');
$mqttDiscoveryCmndPrefix = $_REQUEST['mqtt_discovery_cmnd_prefix'] ?? $Config->read('mqtt_discovery_cmnd_prefix');
$mqttDiscoveryStatPrefix = $_REQUEST['mqtt_discovery_stat_prefix'] ?? $Config->read('mqtt_discovery_stat_prefix');
$mqttDiscoveryTelePrefix = $_REQUEST['mqtt_discovery_tele_prefix'] ?? $Config->read('mqtt_discovery_tele_prefix');
$mqttDiscoverySubscriptions = $_REQUEST['mqtt_discovery_subscriptions'] ?? $Config->read('mqtt_discovery_subscriptions');
$mqttDiscoveryTimeoutSeconds = $_REQUEST['mqtt_discovery_timeout_seconds'] ?? $Config->read('mqtt_discovery_timeout_seconds');

?>
<div class='row justify-content-sm-center devices-autoscan-page'>
    <div class='col col-12 col-md-8 col-xl-6'>
        <h2 class='text-sm-center mb-5'>
            <?php echo $title; ?>
        </h2>

        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link <?php echo 'network' === $scanMode ? 'active' : ''; ?>"
                   href="<?php echo getAutoscanModeUrl('network'); ?>">
                    <?php echo __('NETWORK', 'DEVICES_AUTOSCAN'); ?>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?php echo 'mqtt' === $scanMode ? 'active' : ''; ?>"
                   href="<?php echo getAutoscanModeUrl('mqtt'); ?>">
                    <?php echo __('MQTT', 'DEVICES_AUTOSCAN'); ?>
                </a>
            </li>
        </ul>

        <?php if (isset($error) && $error) { ?>
            <div class="alert alert-danger fade show mb-5" role="alert">
                <?php echo $msg; ?>
            </div>
        <?php } elseif (isset($msg) && '' != $msg) { ?>
            <div class="alert alert-success fade show mb-5" role="alert">
                <?php echo $msg; ?>
                <?php if ('done' == $action) { ?>
                    <div class="text-start mt-3">
                        <a class="btn btn-secondary col-12 col-sm-auto" href='<?php echo _BASEURL_; ?>devices'>
                            <?php echo __('BTN_BACK', 'DEVICE_ACTIONS'); ?>
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ('mqtt' === $scanMode && ([] !== $mqttDiscoveryResult->updatedDevices
            || [] !== $mqttDiscoveryResult->offlineTopics || [] !== $mqttDiscoveryResult->conflicts)) { ?>
            <div class="card mb-4 autoscan-summary-card">
                <div class="card-body">
                    <h3 class="h5 mb-3"><?php echo __('MQTT_DISCOVERY_RESULTS', 'DEVICES_AUTOSCAN'); ?></h3>
                    <div class="row g-3">
                        <div class="col col-6 col-md-3">
                            <strong><?php echo __('MQTT_DISCOVERY_UPDATED', 'DEVICES_AUTOSCAN'); ?></strong><br>
                            <?php echo count($mqttDiscoveryResult->updatedDevices); ?>
                        </div>
                        <div class="col col-6 col-md-3">
                            <strong><?php echo __('MQTT_DISCOVERY_NEW', 'DEVICES_AUTOSCAN'); ?></strong><br>
                            <?php echo count($devicesFound); ?>
                        </div>
                        <div class="col col-6 col-md-3">
                            <strong><?php echo __('MQTT_DISCOVERY_OFFLINE', 'DEVICES_AUTOSCAN'); ?></strong><br>
                            <?php echo count($mqttDiscoveryResult->offlineTopics); ?>
                        </div>
                        <div class="col col-6 col-md-3">
                            <strong><?php echo __('MQTT_DISCOVERY_CONFLICTS', 'DEVICES_AUTOSCAN'); ?></strong><br>
                            <?php echo count($mqttDiscoveryResult->conflicts); ?>
                        </div>
                    </div>
                    <?php foreach ($mqttDiscoveryResult->updatedDevices as $updatedDevice) { ?>
                        <div class="small mt-3">
                            <?php echo __('MQTT_DISCOVERY_UPDATED_DEVICE', 'DEVICES_AUTOSCAN', [
                                (string) $updatedDevice['name'],
                                (string) $updatedDevice['oldIp'],
                                (string) $updatedDevice['newIp'],
                                (string) $updatedDevice['mqttTopic'],
                            ]); ?>
                        </div>
                    <?php } ?>
                    <?php foreach ($mqttDiscoveryResult->offlineTopics as $offlineTopic) { ?>
                        <div class="small mt-2 text-body-secondary">
                            <?php echo __('MQTT_DISCOVERY_OFFLINE_DEVICE', 'DEVICES_AUTOSCAN', [$offlineTopic['mqttTopic']]); ?>
                        </div>
                    <?php } ?>
                    <?php foreach ($mqttDiscoveryResult->conflicts as $conflict) { ?>
                        <div class="small mt-2 text-danger">
                            <?php echo __('MQTT_DISCOVERY_CONFLICT_DEVICE', 'DEVICES_AUTOSCAN', [$conflict['mqttTopic']]); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <form class='form autoscan-form' name='autoscan_form' method='post' autocomplete="off">
            <input type="hidden" name="scan_mode" value="<?php echo $scanMode; ?>">
            <div class="card autoscan-form-card mb-4">
                <div class="card-body">

                    <?php if ('mqtt' === $scanMode) { ?>
                        <div class="row mb-3">
                            <div class="col col-12 col-sm-8">
                                <label for="mqtt_discovery_host" class="form-label">
                                    <?php echo __('MQTT_HOST', 'DEVICES_AUTOSCAN'); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="mqtt_discovery_host"
                                       name='mqtt_discovery_host'
                                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                       value='<?php echo htmlspecialchars((string) $mqttDiscoveryHost, ENT_QUOTES); ?>'
                                       required
                                       autofocus="autofocus"
                                >
                            </div>
                            <div class="col col-12 col-sm-4">
                                <label for="mqtt_discovery_port" class="form-label">
                                    <?php echo __('MQTT_PORT', 'DEVICES_AUTOSCAN'); ?>
                                </label>
                                <input type="number"
                                       class="form-control"
                                       id="mqtt_discovery_port"
                                       name='mqtt_discovery_port'
                                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                       value='<?php echo htmlspecialchars((string) $mqttDiscoveryPort, ENT_QUOTES); ?>'
                                       required
                                >
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col col-12 col-sm-6">
                                <label for="mqtt_discovery_username" class="form-label">
                                    <?php echo __('MQTT_USER', 'DEVICES_AUTOSCAN'); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="mqtt_discovery_username"
                                       name='mqtt_discovery_username'
                                       value='<?php echo htmlspecialchars((string) $mqttDiscoveryUsername, ENT_QUOTES); ?>'
                                >
                            </div>
                            <div class="col col-12 col-sm-6">
                                <label for="mqtt_discovery_password" class="form-label">
                                    <?php echo __('MQTT_PASSWORD', 'DEVICES_AUTOSCAN'); ?>
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="mqtt_discovery_password"
                                       name='mqtt_discovery_password'
                                       autocomplete="off"
                                       placeholder="<?php echo $mqttDiscoveryPasswordStored
                                           ? __('MQTT_DISCOVERY_PASSWORD_STORED', 'DEVICES_AUTOSCAN')
                                           : __('PLEASE_ENTER'); ?>"
                                       value=''
                                >
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col col-12 col-sm-4">
                                <label for="mqtt_discovery_cmnd_prefix" class="form-label">
                                    <?php echo __('PREFIX1', 'DEVICE_CONFIG'); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="mqtt_discovery_cmnd_prefix"
                                       name='mqtt_discovery_cmnd_prefix'
                                       value='<?php echo htmlspecialchars((string) $mqttDiscoveryCmndPrefix, ENT_QUOTES); ?>'
                                       required
                                >
                            </div>
                            <div class="col col-12 col-sm-4">
                                <label for="mqtt_discovery_stat_prefix" class="form-label">
                                    <?php echo __('PREFIX2', 'DEVICE_CONFIG'); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="mqtt_discovery_stat_prefix"
                                       name='mqtt_discovery_stat_prefix'
                                       value='<?php echo htmlspecialchars((string) $mqttDiscoveryStatPrefix, ENT_QUOTES); ?>'
                                       required
                                >
                            </div>
                            <div class="col col-12 col-sm-4">
                                <label for="mqtt_discovery_tele_prefix" class="form-label">
                                    <?php echo __('PREFIX3', 'DEVICE_CONFIG'); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="mqtt_discovery_tele_prefix"
                                       name='mqtt_discovery_tele_prefix'
                                       value='<?php echo htmlspecialchars((string) $mqttDiscoveryTelePrefix, ENT_QUOTES); ?>'
                                       required
                                >
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col col-12 col-sm-8">
                                <label for="mqtt_discovery_subscriptions" class="form-label">
                                    <?php echo __('MQTT_DISCOVERY_SUBSCRIPTIONS', 'DEVICES_AUTOSCAN'); ?>
                                </label>
                                <textarea class="form-control"
                                          id="mqtt_discovery_subscriptions"
                                          name='mqtt_discovery_subscriptions'
                                          rows="3"
                                          required><?php echo htmlspecialchars((string) $mqttDiscoverySubscriptions, ENT_QUOTES); ?></textarea>
                            </div>
                            <div class="col col-12 col-sm-4">
                                <label for="mqtt_discovery_timeout_seconds" class="form-label">
                                    <?php echo __('MQTT_DISCOVERY_TIMEOUT', 'DEVICES_AUTOSCAN'); ?>
                                </label>
                                <input type="number"
                                       class="form-control"
                                       id="mqtt_discovery_timeout_seconds"
                                       name='mqtt_discovery_timeout_seconds'
                                       value='<?php echo htmlspecialchars((string) $mqttDiscoveryTimeoutSeconds, ENT_QUOTES); ?>'
                                       min="1"
                                       required
                                >
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="row mb-3">
                            <div class="col col-12 col-sm-4">
                                <label for="from_ip" class="form-label">
                                    <?php echo __('FROM_IP', 'DEVICES_AUTOSCAN'); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="from_ip"
                                       name='from_ip'
                                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                       value='<?php echo $scanFromIp; ?>'
                                       required
                                       autofocus="autofocus"
                                >
                                <small id="from_ipHelp" class="text-muted">
                                    <?php echo __('FROM_IP_HELP', 'DEVICES_AUTOSCAN'); ?>
                                </small>
                            </div>
                            <div class="col col-12 col-sm-4">
                                <label for="to_ip" class="form-label">
                                    <?php echo __('TO_IP', 'DEVICES_AUTOSCAN'); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="to_ip"
                                       name='to_ip'
                                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                       value='<?php echo $scanToIp; ?>'
                                       required
                                >
                                <small id="to_ipHelp" class="text-muted">
                                    <?php echo __('TO_IP_HELP', 'DEVICES_AUTOSCAN'); ?>
                                </small>
                            </div>
                            <div class="col col-12 col-sm-4">
                                <label for="port" class="form-label">
                                    <?php echo __('PORT', 'DEVICES_AUTOSCAN'); ?>
                                </label>
                                <input type="number"
                                       class="form-control"
                                       id="port"
                                       name='port'
                                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                       value='<?php echo htmlspecialchars((string) $port, ENT_QUOTES); ?>'
                                       required
                                >
                                <small id="portHelp" class="text-muted">
                                    <?php echo __('PORT_HELP', 'DEVICES_AUTOSCAN'); ?>
                                </small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="additional_scan_ranges" class="form-label">
                                    <?php echo __('ADDITIONAL_SCAN_RANGES', 'DEVICES_AUTOSCAN'); ?>
                                </label>
                                <textarea class="form-control"
                                          id="additional_scan_ranges"
                                          name='additional_scan_ranges'
                                          rows="3"
                                          placeholder="192.168.2.2-192.168.2.254&#10;10.0.0.5"
                                ><?php echo $additionalScanRanges; ?></textarea>
                                <small id="additionalScanRangesHelp" class="text-muted">
                                    <?php echo __('ADDITIONAL_SCAN_RANGES_HELP', 'DEVICES_AUTOSCAN'); ?>
                                </small>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ('mqtt' === $scanMode) { ?>
                        <div class="row mb-3">
                            <div class="col col-12 col-sm-4">
                                <label for="port" class="form-label">
                                    <?php echo __('DEVICE_PORT', 'DEVICE_ACTIONS'); ?>
                                </label>
                                <input type="number"
                                       class="form-control"
                                       id="port"
                                       name='port'
                                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                       value='<?php echo htmlspecialchars((string) $port, ENT_QUOTES); ?>'
                                       required
                                >
                            </div>
                        </div>
                    <?php } ?>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="device_username" class="form-label">
                                <?php echo __('DEVICE_USERNAME', 'DEVICE_ACTIONS'); ?>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="device_username"
                                   name='device_username'
                                   value='<?php echo htmlspecialchars((string) ($_REQUEST['device_username'] ?? 'admin'), ENT_QUOTES); ?>'
                            >
                            <small id="device_usernameHelp" class="text-muted">
                                <?php echo __('DEVICE_USERNAME_HELP', 'DEVICE_ACTIONS'); ?>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="device_password" class="form-label">
                                <?php echo __('DEVICE_PASSWORD', 'DEVICE_ACTIONS'); ?>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="device_password"
                                       name='device_password'
                                       autocomplete="off"
                                       aria-autocomplete="none"
                                       value='<?php echo htmlspecialchars((string) ($_REQUEST['device_password'] ?? ''), ENT_QUOTES); ?>'
                                >
                                <div class="input-group-text">
                                    <span class="input-group-text show-hide-password"><i class="far fa-eye"></i></span>
                                </div>
                            </div>
                            <small id="device_passwordHelp" class="text-muted">
                                <?php echo __('DEVICE_PASSWORD_HELP', 'DEVICE_ACTIONS'); ?>
                            </small>
                        </div>
                    </div>
                    <div class='row justify-content-sm-center mt-4 autoscan-submit-row'>
                        <div class="col col-12 d-flex justify-content-sm-end">
                            <button type='submit'
                                    name='search'
                                    value='search'
                                    class='btn btn-primary col-12 col-sm-auto'
                                    data-bs-toggle="modal" data-bs-target="#deviceScanModal"
                            >
                                <?php echo 'mqtt' === $scanMode
                                    ? __('BTN_START_MQTT_SCAN', 'DEVICES_AUTOSCAN')
                                    : __('BTN_START_AUTOSCAN', 'DEVICES_AUTOSCAN'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($devicesFound)) { ?>
                <?php foreach ($devicesFound as $idx => $device) { ?>
                    <?php
                    $friendlyNames = getAutoscanFriendlyNames($device);
                    $channelCount = getAutoscanChannelCount($device);
                    $mqttTopic = htmlspecialchars((string) ($device->TasmoAdminMqttTopic ?? ''), ENT_QUOTES);
                    $devicePort = htmlspecialchars((string) ($device->TasmoAdminDevicePort ?? $port), ENT_QUOTES);
                    ?>
                    <div class="card mt-5 mb-5 autoscan-device-card">
                        <div class="card-body">
                            <h3 class='text-sm-center mb-4 autoscan-device-title'>
                                <?php echo __('DEVICE', 'DEVICES_AUTOSCAN').' '.($idx + 1); ?>
                            </h3>
                            <div class="form-row">
                                <div class="form-group col col-12 col-sm-12">
                                    <label for="device_ip_fake_<?php echo $idx; ?>">
                                        <?php echo __('DEVICE_IP', 'DEVICE_ACTIONS'); ?>
                                    </label>
                                    <input type="text"
                                           class="form-control disabled"
                                           id="device_ip_fake_<?php echo $idx; ?>"
                                           value='<?php echo htmlspecialchars((string) $device->StatusNET->IPAddress, ENT_QUOTES); ?>'
                                           disabled
                                    >
                                    <input type='hidden'
                                           name='devices[<?php echo $idx; ?>][device_ip]'
                                           value='<?php echo htmlspecialchars((string) $device->StatusNET->IPAddress, ENT_QUOTES); ?>'
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col col-12 col-sm-12">
                                    <label for="device_port_fake_<?php echo $idx; ?>">
                                        <?php echo __('DEVICE_PORT', 'DEVICE_ACTIONS'); ?>
                                    </label>
                                    <input type="text"
                                           class="form-control disabled"
                                           id="device_port_fake_<?php echo $idx; ?>"
                                           value='<?php echo $devicePort; ?>'
                                           disabled
                                    >
                                    <input type='hidden'
                                           name='devices[<?php echo $idx; ?>][device_port]'
                                           value='<?php echo $devicePort; ?>'
                                    >
                                    <input type='hidden'
                                           name='devices[<?php echo $idx; ?>][device_mqtt_topic]'
                                           value='<?php echo $mqttTopic; ?>'
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col">
                                    <label for="device_position_<?php echo $idx; ?>">
                                        <?php echo __('DEVICE_POSITION', 'DEVICE_ACTIONS'); ?>
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           id="device_position_<?php echo $idx; ?>"
                                           name='devices[<?php echo $idx; ?>][device_position]'
                                           value='<?php echo $idx + 1; ?>'
                                    >
                                    <small class="form-text text-muted">
                                        <?php echo __('DEVICE_POSITION_HELP', 'DEVICE_ACTIONS'); ?>
                                    </small>
                                </div>
                            </div>

                            <?php for ($i = 0; $i < $channelCount; ++$i) { ?>
                                <?php
                                $friendlyName = $friendlyNames[$i] ?? trim(($friendlyNames[0] ?? '').' '.($i + 1));
                                $fieldIndex = 1 === $channelCount ? '' : (string) ($i + 1);
                                $nameIndex = 1 === $channelCount ? '[]' : '['.($i + 1).']';
                                ?>
                                <div class="row g-3 align-items-end autoscan-device-name-row">
                                    <div class="form-group col col-12 col-sm-8">
                                        <label for="device_name_<?php echo $idx.'_'.$i; ?>">
                                            <?php echo __('LABEL_NAME', 'DEVICE_ACTIONS').$fieldIndex; ?>
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               id="device_name_<?php echo $idx.'_'.$i; ?>"
                                               name='devices[<?php echo $idx; ?>][device_name]<?php echo $nameIndex; ?>'
                                               placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                               value='<?php echo htmlspecialchars($friendlyName, ENT_QUOTES); ?>'
                                               required
                                        >
                                        <input type='hidden'
                                               name='devices[<?php echo $idx; ?>][device_friendly_name]<?php echo $nameIndex; ?>'
                                               value='<?php echo htmlspecialchars($friendlyName, ENT_QUOTES); ?>'
                                        >
                                    </div>
                                    <div class="form-group col col-12 col-sm-4">
                                        <a class='btn btn-secondary col-12 test_device'
                                           data-device_ip='<?php echo htmlspecialchars((string) $device->StatusNET->IPAddress, ENT_QUOTES); ?>'
                                           data-device_relais='<?php echo $i + 1; ?>'
                                        >
                                            <?php echo __('BTN_TEST', 'DEVICES_AUTOSCAN'); ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="row mt-2 mb-3 autoscan-device-name-help">
                                    <div class="col col-12">
                                        <small class="form-text text-muted d-block">
                                            (
                                            <a href='#' class='default-name'><?php echo htmlspecialchars($friendlyName, ENT_QUOTES); ?></a>
                                            )
                                            <?php echo __('DEVICE_NAME_TOOLTIP', 'DEVICE_ACTIONS'); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="row mt-4 autoscan-device-save-row">
                                <div class="col col-12 text-end">
                                    <button type='submit'
                                            name='save_device'
                                            value='<?php echo $idx; ?>'
                                            class='btn btn-primary col-12 col-sm-auto'
                                    >
                                        <?php echo __('BTN_SAVE_DEVICE_CONFIG', 'DEVICE_CONFIG'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <div class="row mb-5 autoscan-final-actions">
                    <div class="col col-12 col-sm-6 text-start">
                        <a class="btn btn-secondary col-12 col-sm-auto" href='<?php echo _BASEURL_; ?>devices'>
                            <?php echo __('BTN_BACK', 'DEVICE_ACTIONS'); ?>
                        </a>
                    </div>
                    <div class="col col-12 col-sm-6 text-end">
                        <button type='submit'
                                name='save_all'
                                value='save_all'
                                class='btn btn-primary col-12 col-sm-auto'
                        >
                            <?php echo __('BTN_SAVE_ALL', 'DEVICES_AUTOSCAN'); ?>
                        </button>
                    </div>
                </div>
            <?php } ?>
        </form>
    </div>
</div>

<div class="modal fade" id="deviceScanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5><?php echo __('MSG_SCANNING', 'DEVICES_AUTOSCAN'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar"
                         aria-valuenow="75"
                         aria-valuemin="0"
                         aria-valuemax="100"
                         style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function()
    {
        $(".default-name").on("click", function (e)
        {
            e.preventDefault();
            $(this).parent().parent().find("input[type='text']").val($(this).html());
        });

        $(".test_device").on("click", function (e)
        {
            e.preventDefault();
            var device_ip = $(this).data("device_ip");
            var device_relais = $(this).data("device_relais");
            var cmnd = "Power" + device_relais + "%20toggle";

            var url = decodeURIComponent(Sonoff.buildCmndUrl(device_ip, cmnd));
            Sonoff.directAjax(url);
        });
    });
</script>
