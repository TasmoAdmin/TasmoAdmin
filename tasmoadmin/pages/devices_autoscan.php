<?php

use TasmoAdmin\Config;
use TasmoAdmin\DeviceFactory;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Helper\IpHelper;
use TasmoAdmin\Sonoff;

$Sonoff = $container->get(Sonoff::class);
$Config = $container->get(Config::class);

$status = false;
$devices = null;
$devicesFound = null;
$msg = null;
$action = '';
$error = false;

if (isset($_REQUEST) && !empty($_REQUEST)) {
    try {
        if (isset($_REQUEST['search'])) {
            $fromIp = htmlspecialchars($_REQUEST['from_ip']);
            $toIp = htmlspecialchars($_REQUEST['to_ip']);
            $port = htmlspecialchars($_REQUEST['port']);

            $ipHelper = new IpHelper();
            $devices = $Sonoff->getDevices();
            $skipIps = [];
            foreach ($devices as $device) {
                $skipIps[] = $device->ip;
            }

            $ips = $ipHelper->fetchIps($fromIp, $toIp, $skipIps);
            $Config->write('scan_from_ip', $fromIp);
            $Config->write('scan_to_ip', $toIp);
            $Config->write('port', $port);

            $urls = [];
            foreach ($ips as $ip) {
                $fakeDevice = DeviceFactory::fakeDevice(
                    $ip,
                    $port,
                    htmlspecialchars($_REQUEST['device_username'] ?? ''),
                    htmlspecialchars($_REQUEST['device_password'] ?? '')
                );
                $urls[] = $Sonoff->buildCmndUrl($fakeDevice, Sonoff::COMMAND_INFO_STATUS_ALL);
                unset($fakeDevice);
            }

            $devicesFound = $Sonoff->search($urls);

            if (empty($devicesFound)) {
                $msg = __('MSG_NO_DEVICES_FOUND', 'DEVICES_AUTOSCAN');
                $error = true;
            } else {
                $devicesFoundTmp = $devicesFound;
                $devicesFound = [];
                foreach ($devicesFoundTmp as $device) {
                    if (empty($device) || !empty($device->error)) {
                        continue;
                    }
                    if (empty($device->StatusNET)) {
                        continue; // TODO: show error message per device
                    }
                    $ip = explode('.', $device->StatusNET->IPAddress);
                    $devicesFound[$ip[3]] = $device;
                }
                ksort($devicesFound);
                $devicesFound = array_values($devicesFound);
                unset($devicesFoundTmp);
                $msg = __('MSG_DEVICES_FOUND_COUNT', 'DEVICES_AUTOSCAN').': '.count($devicesFound);
            }
        } elseif (isset($_REQUEST['save_all'])) {
            $deviceRepository = $container->get(DeviceRepository::class);
            $deviceUsername = htmlspecialchars($_REQUEST['device_username'] ?? '');
            $devicePassword = htmlspecialchars($_REQUEST['device_password'] ?? '');
            $deviceRepository->addDevices($_REQUEST['devices'], $deviceUsername, $devicePassword);
            $msg = __('MSG_DEVICES_ADD_DONE', 'DEVICES_AUTOSCAN');
            $action = 'done';
        }
    } catch (InvalidArgumentException $ex) {
        $error = true;
        $msg = $ex->getMessage();
    }
}

$scanFromIp = $Config->read('scan_from_ip');
$scanToIp = $Config->read('scan_to_ip');
$port = $Config->read('port');

?>
<div class='row justify-content-sm-center'>
    <div class='col col-12 col-md-8 col-xl-6'>
        <h2 class='text-sm-center mb-5'>
            <?php echo $title; ?>
        </h2>
        <?php if (isset($error) && $error) { ?>
            <div class='row justify-content-sm-center'>
                <div class='col col-12'>
                    <div class="alert alert-danger fade show mb-5" data-dismiss="alert" role="alert">
                        <?php echo $msg; ?>
                    </div>
                </div>
            </div>
        <?php } elseif (isset($msg) && '' != $msg) { ?>
            <div class='row justify-content-sm-center'>
                <div class='col col-12'>
                    <div class="alert alert-success fade show mb-5" role="alert">
                        <?php echo $msg; ?>
                        <?php if ('done' == $action) { ?>
                            <div class="text-left mt-3">
                                <a class="btn btn-secondary  col-12 col-sm-auto" href='<?php echo _BASEURL_; ?>devices'>
                                    <?php echo __('BTN_BACK', 'DEVICE_ACTIONS'); ?>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>


        <form class='form'
              name='autoscan_form'
              method='post'
              autocomplete="off"
        >

            <div class="form-row">
                <div class="form-group col col-12 col-sm-4">
                    <label for="from_ip">
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
                    <small id="from_ipHelp" class="form-text text-muted">
                        <?php echo __('FROM_IP_HELP', 'DEVICES_AUTOSCAN'); ?>
                    </small>
                </div>
                <div class="form-group col col-12 col-sm-4">
                    <label for="to_ip">
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
                    <small id="from_ipHelp" class="form-text text-muted">
                        <?php echo __('TO_IP_HELP', 'DEVICES_AUTOSCAN'); ?>
                    </small>
                </div>
                <div class="form-group col col-12 col-sm-4">
                    <label for="port">
                        <?php echo __('PORT', 'DEVICES_AUTOSCAN'); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="port"
                           name='port'
                           placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                           value='<?php echo $port; ?>'
                           required
                    >
                    <small id="from_ipHelp" class="form-text text-muted">
                        <?php echo __('PORT_HELP', 'DEVICES_AUTOSCAN'); ?>
                    </small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col">
                    <label for="device_username">
                        <?php echo __('DEVICE_USERNAME', 'DEVICE_ACTIONS'); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="device_username"
                           name='device_username'
                           value='<?php echo $_REQUEST['device_username'] ?? 'admin'; ?>'
                    >
                    <small id="device_usernameHelp" class="form-text text-muted">
                        <?php echo __('DEVICE_USERNAME_HELP', 'DEVICE_ACTIONS'); ?>
                    </small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col">
                    <label for="device_password">
                        <?php echo __('DEVICE_PASSWORD', 'DEVICE_ACTIONS'); ?>
                    </label>
                    <div class="input-group mb-3">
                        <input type="password"
                               class="form-control"
                               id="device_password"
                               name='device_password'
                               autocomplete="off"
                               aria-autocomplete="none"
                               value='<?php echo $_REQUEST['device_password'] ?? ''; ?>'
                        >
                        <div class="input-group-append">
                            <span class="input-group-text show-hide-password" id=""><i class="far fa-eye"></i></span>
                        </div>
                    </div>
                    <small id="device_passwordHelp" class="form-text text-muted">
                        <?php echo __('DEVICE_PASSWORD_HELP', 'DEVICE_ACTIONS'); ?>
                    </small>
                </div>
            </div>
            <div class='row justify-content-sm-center mt-5'>
                <div class='d-none d-sm-inline-flex col flex-column'></div>
                <div class="col col-12 col-sm-6">
                    <button type='submit'
                            name='search'
                            value='search'
                            class='btn btn-primary col-12 col-sm-auto'
                            data-toggle="modal" data-target="#deviceScanModal"
                    >
                        <?php echo __('BTN_START_AUTOSCAN', 'DEVICES_AUTOSCAN'); ?>
                    </button>
                </div>
            </div>


            <?php if (!empty($devicesFound)) { ?>
                <?php foreach (
                    $devicesFound as $idx => $device
                ) { ?>
                    <hr class='my-5'/>
                    <h3 class='text-sm-center mb-5'>
                        <?php echo __('DEVICE', 'DEVICES_AUTOSCAN').' '.($idx + 1); ?>
                    </h3>
                    <div class="form-row">
                        <div class="form-group col col-12 col-sm-12">
                            <label for="device_ip_fake">
                                <?php echo __('DEVICE_IP', 'DEVICE_ACTIONS'); ?>
                            </label>
                            <input type="text"
                                   class="form-control disabled"
                                   id="device_ip_fake"
                                   name='devices[<?php echo $idx; ?>][device_ip]'
                                   placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                   value='<?php echo $device->StatusNET->IPAddress; ?>'
                                   disabled required
                            >
                            <input type='hidden'
                                   name='devices[<?php echo $idx; ?>][device_ip]'
                                   value='<?php echo $device->StatusNET->IPAddress; ?>'
                            >
                            <small id="device_ipHelp" class="form-text text-muted">
                                <?php echo __('DEVICE_IP_HELP', 'DEVICE_ACTIONS'); ?>
                            </small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col col-12 col-sm-12">
                            <label for="device_port">
                                <?php echo __('DEVICE_PORT', 'DEVICE_ACTIONS'); ?>
                            </label>
                            <input type="text"
                                   class="form-control disabled"
                                   id="device_port_fake"
                                   name='devices[<?php echo $idx; ?>][device_port]'
                                   placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                   value='<?php echo $port; ?>'
                                   disabled required
                            >
                            <input type='hidden'
                                   name='devices[<?php echo $idx; ?>][device_port]'
                                   value='<?php echo $port; ?>'
                            >
                            <small id="device_portHelp" class="form-text text-muted">
                                <?php echo __('DEVICE_PORT_HELP', 'DEVICE_ACTIONS'); ?>
                            </small>
                        </div>
                    </div>


                    <div class="form-row">
                        <div class="form-group col">
                            <label for="device_position">
                                <?php echo __('DEVICE_POSITION', 'DEVICE_ACTIONS'); ?>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="device_position"
                                   name='devices[<?php echo $idx; ?>][device_position]'
                                   value='<?php echo $idx + 1; ?>'
                            >
                            <small id="device_positionHelp" class="form-text text-muted">
                                <?php echo __('DEVICE_POSITION_HELP', 'DEVICE_ACTIONS'); ?>
                            </small>
                        </div>
                    </div>
                    <?php if (isset($device->StatusSTS->POWER)) { ?>
                        <?php
                        $friendlyName = is_array($device->Status->FriendlyName) // array since 5.12.0h
                            ? $device->Status->FriendlyName[0] : $device->Status->FriendlyName;
                        ?>
                        <div class="form-row">
                            <div class="form-group col col-12 col-sm-6">
                                <label for="device_name">
                                    <?php echo __('LABEL_NAME', 'DEVICE_ACTIONS'); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="device_name"
                                       name='devices[<?php echo $idx; ?>][device_name][]'
                                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                       value='<?php echo $friendlyName; ?>'
                                       required
                                >
                                <small id="device_nameHelp" class="form-text text-muted d-none d-sm-block">
                                    &nbsp;
                                </small>
                            </div>
                            <div class="form-group col col-12 col-sm-3">
                                <label class="d-none d-sm-block mb-3">&nbsp;</label>
                                (
                                <a href='#'
                                   class='default-name'
                                ><?php echo $friendlyName; ?></a>
                                )
                                <small id="default_nameHelp" class="form-text text-muted">
                                    <?php echo __('DEVICE_NAME_TOOLTIP', 'DEVICE_ACTIONS'); ?>
                                </small>


                            </div>
                            <div class="form-group col col-12 col-sm-3">
                                <a id='test_device' class='btn btn-secondary col-12 test_device'
                                   style='margin-top: 1.8rem;'
                                   data-device_ip='<?php echo $device->StatusNET->IPAddress; ?>'
                                   data-device_relais='1'
                                >
                                    <?php echo __('BTN_TEST', 'DEVICES_AUTOSCAN'); ?>
                                </a>
                            </div>
                        </div>
                    <?php } ?>


                    <?php
                    $i = 1;
                    $power = 'POWER'.$i;
                    $channelFound = false;

                    while (isset($device->StatusSTS->{$power})) { ?>
                        <?php $channelFound = true; ?>
                        <?php
                        $friendlyName = is_array($device->Status->FriendlyName) // array since 5.12.0h
                            ? (isset($device->Status->FriendlyName[$i - 1]) ? $device->Status->FriendlyName[$i - 1]
                                : '') : $device->Status->FriendlyName.' '.$i;
                        ?>
                        <div class="form-row">
                            <div class="form-group col col-12 col-sm-6">
                                <label for="device_name_<?php echo $i; ?>">
                                    <?php echo __('LABEL_NAME', 'DEVICE_ACTIONS'); ?><?php echo $i; ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="device_name_<?php echo $i; ?>"
                                       name='devices[<?php echo $idx; ?>][device_name][<?php echo $i; ?>]'
                                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                       value='<?php echo isset($device->names[$i - 1])
                                       && !empty(
                                           $device->names[$i - 1]
                                       ) ? $device->names[$i - 1] : $friendlyName; ?>'
                                       required
                                >
                                <small id="device_name_<?php echo $i; ?>Help"
                                       class="form-text text-muted d-none d-sm-block"
                                >
                                    &nbsp;
                                </small>
                            </div>
                            <div class="form-group col col-12 col-sm-3">
                                <label class="d-none d-sm-block mb-3">&nbsp;</label>
                                (
                                <a href='#' title='<?php echo __('DEVICE_NAME_TOOLTIP', 'DEVICE_ACTIONS'); ?>'
                                   class='default-name'
                                >
                                    <?php echo $friendlyName; ?>
                                </a>
                                )
                                <small id="default_nameHelp" class="form-text text-muted">
                                    <?php echo __('DEVICE_NAME_TOOLTIP', 'DEVICE_ACTIONS'); ?>
                                </small>


                            </div>
                            <div class="form-group col col-12 col-sm-3">
                                <a id='' class='btn btn-secondary col-12 test_device'
                                   style='margin-top: 1.8rem;'
                                   data-device_ip='<?php echo $device->StatusNET->IPAddress; ?>'
                                   data-device_relais='<?php echo $i; ?>'
                                >
                                    <?php echo __('BTN_TEST', 'DEVICES_AUTOSCAN'); ?>
                                </a>
                            </div>
                        </div>


                        <?php

                        ++$i;
                        $power = 'POWER'.$i;
                        ?>

                    <?php } ?>

                    <?php if (!isset($device->StatusSTS->POWER) && !$channelFound) {
                        // no channel found?>
                        <?php
                        $friendlyName = is_array($device->Status->FriendlyName) // array since 5.12.0h
                            ? $device->Status->FriendlyName[0] : $device->Status->FriendlyName;
                        ?>
                        <div class="form-row">
                            <div class="form-group col col-12 col-sm-9">
                                <label for="device_name">
                                    <?php echo __('LABEL_NAME', 'DEVICE_ACTIONS'); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="device_name"
                                       name='devices[<?php echo $idx; ?>][device_name][]'
                                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                                       value='<?php echo $friendlyName; ?>'
                                       required
                                >
                                <small id="device_nameHelp" class="form-text text-muted d-none d-sm-block">
                                    &nbsp;
                                </small>
                            </div>
                            <div class="form-group col col-12 col-sm-3">
                                <label class="d-none d-sm-block mb-3">&nbsp;</label>
                                (
                                <a href='#'
                                   class='default-name'
                                ><?php echo $friendlyName; ?></a>
                                )
                                <small id="default_nameHelp" class="form-text text-muted">
                                    <?php echo __('DEVICE_NAME_TOOLTIP', 'DEVICE_ACTIONS'); ?>
                                </small>
                            </div>
                        </div>
                    <?php } ?>


                <?php } ?>
                <div class="row">
                    <div class="col col-12 col-sm-6 text-left">
                        <a class="btn btn-secondary  col-12 col-sm-auto" href='<?php echo _BASEURL_; ?>devices'>
                            <?php echo __('BTN_BACK', 'DEVICE_ACTIONS'); ?>
                        </a>
                    </div>
                    <div class="col col-12 col-sm-6 text-right">
                        <button type='submit'
                                name='save_all'
                                value='save_all'
                                class='btn btn-primary col-12 col-sm-auto'

                        >
                            <?php echo __('BTN_SAVE_ALL', 'DEVICES_AUTOSCAN'); ?>
                        </button>
                    </div>
                </div>

                </table>


            <?php } ?>
        </form>


    </div>
</div>

<div class="modal fade" id="deviceScanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5><?php echo __('MSG_SCANNING', 'DEVICES_AUTOSCAN'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
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
            $(this).parent().parent().find("input").val($(this).html());
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
