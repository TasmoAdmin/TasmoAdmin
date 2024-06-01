<?php

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use TasmoAdmin\Config;

$Config = $container->get(Config::class);

$mqttIp = $Config->read('mqtt_ip');
$mqttPort = $Config->read('mqtt_port');
$mqttClientId = $Config->read('mqtt_client_id');
$mqttUsername = $Config->read('mqtt_username');
$mqttPassword = $Config->read('mqtt_password');


if (isset($_REQUEST["search"])) {
    $mqttIp = $_REQUEST["mqtt_ip"];
    $mqttPort = $_REQUEST["mqtt_port"];
    $mqttClientId = $_REQUEST["mqtt_client_id"];
    $mqttUsername = $_REQUEST['mqtt_username'];
    $mqttPassword = $_REQUEST['mqtt_password'];

    $connectionSettings = (new ConnectionSettings)
    ->setUsername($mqttUsername)
    ->setPassword($mqttPassword);


    $mqtt = new MqttClient($mqttIp, $mqttPort, $mqttClientId);
    $mqtt->connect($connectionSettings, true);
    $mqtt->subscribe('php-mqtt/client/test', function ($topic, $message, $retained, $matchedWildcards) {
        echo sprintf("Received message on topic [%s]: %s\n", $topic, $message);
    }, 0);

    $mqtt->loop(true, true, 5);
    $mqtt->disconnect();


    $Config->write('mqtt_ip', $mqttIp);
    $Config->write('mqtt_port', $mqttPort);
    $Config->write('mqtt_client_id', $mqttClientId);
    $Config->write('mqtt_username', $mqttUsername);
    $Config->write('mqtt_password', $mqttPassword);
}

?>
<div class='row justify-content-sm-center'>
    <div class='col col-12 col-md-8 col-xl-6'>
        <h2 class='text-sm-center mb-5'>
            <?php echo $title; ?>
        </h2>
        <?php if (isset($error) && $error): ?>
            <div class='row justify-content-sm-center'>
                <div class='col col-12'>
                    <div class="alert alert-danger fade show mb-5" data-dismiss="alert" role="alert">
                        <?php echo $msg; ?>
                    </div>
                </div>
            </div>
        <?php elseif (isset($msg) && $msg != ""): ?>
            <div class='row justify-content-sm-center'>
                <div class='col col-12'>
                    <div class="alert alert-success fade show mb-5" role="alert">
                        <?php echo $msg; ?>
                        <?php if ($action == "done"): ?>
                            <div class="text-left mt-3">
                                <a class="btn btn-secondary  col-12 col-sm-auto" href='<?php echo _BASEURL_; ?>devices'>
                                    <?php echo __("BTN_BACK", "DEVICE_ACTIONS"); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>


        <form class='form'
              name='autoscan_form'
              method='post'
              autocomplete="off"
        >

            <div class="form-row">
                <div class="form-group col col-12 col-sm-8">
                    <label for="mqtt_ip">
                        <?php echo __("MQTT_IP", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="mqtt_ip"
                           name='mqtt_ip'
                           placeholder="<?php echo __("PLEASE_ENTER"); ?>"
                           value='<?php echo $mqttIp ?>'
                           required
                           autofocus="autofocus"
                    >
                    <small id="mqtt_ipHelp" class="form-text text-muted">
                        <?php echo __("MQTT_IP_HELP", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </small>
                </div>
                <div class="form-group col col-12 col-sm-4">
                    <label for="port">
                        <?php echo __("MQTT_PORT", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="mqtt_port"
                           name='mqtt_port'
                           placeholder="<?php echo __("PLEASE_ENTER"); ?>"
                           value='<?php echo $mqttPort; ?>'
                           required
                    >
                    <small id="mqtt_portHelp" class="form-text text-muted">
                        <?php echo __("MQTT_PORT", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col col-12 col-sm-12">
                    <label for="mqtt_client_id">
                        <?php echo __("MQTT_CLIENT_ID", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="mqtt_client_id"
                           name='mqtt_client_id'
                           placeholder="<?php echo __("PLEASE_ENTER"); ?>"
                           value='<?php echo $mqttClientId ?>'
                           required
                           autofocus="autofocus"
                    >
                    <small id="mqtt_client_idHelp" class="form-text text-muted">
                        <?php echo __("MQTT_CLIENT_ID_HELP", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col col-12 col-sm-6">
                    <label for="mqtt_username">
                        <?php echo __("MQTT_USERNAME", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="mqtt_username"
                           name='mqtt_username'
                           placeholder="<?php echo __("PLEASE_ENTER"); ?>"
                           value='<?php echo $mqttUsername ?>'
                    >
                    <small id="mqtt_usernameHelp" class="form-text text-muted">
                        <?php echo __("MQTT_USERNAME_HELP", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </small>
                </div>
                <div class="form-group col col-12 col-sm-6">
                    <label for="mqtt_password">
                        <?php echo __("MQTT_PASSWORD", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <input type="password"
                           class="form-control"
                           id="mqtt_password"
                           name='mqtt_password'
                           placeholder="<?php echo __("PLEASE_ENTER"); ?>"
                           value='<?php echo $mqttPassword; ?>'
                    >
                    <small id="mqtt_passwordHelp" class="form-text text-muted">
                        <?php echo __("MQTT_PASSWORD_HELP", "DEVICES_AUTOSCAN_MQTT"); ?>
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
                        <?php echo __("BTN_START_AUTOSCAN", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </button>
                </div>
            </div>


            <?php if (!empty($devicesFound)): ?>
                <?php foreach (
                    $devicesFound

                    as $idx => $device
                ): ?>
                    <hr class='my-5'/>
                    <h3 class='text-sm-center mb-5'>
                        <?php echo __("DEVICE", "DEVICES_AUTOSCAN_MQTT") . " " . ($idx + 1); ?>
                    </h3>
                    <div class="form-row">
                        <div class="form-group col col-12 col-sm-12">
                            <label for="device_ip_fake">
                                <?php echo __("DEVICE_IP", "DEVICE_ACTIONS"); ?>
                            </label>
                            <input type="text"
                                   class="form-control disabled"
                                   id="device_ip_fake"
                                   name='devices[<?php echo $idx; ?>][device_ip]'
                                   placeholder="<?php echo __("PLEASE_ENTER"); ?>"
                                   value='<?php echo $device->StatusNET->IPAddress; ?>'
                                   disabled required
                            >
                            <input type='hidden'
                                   name='devices[<?php echo $idx; ?>][device_ip]'
                                   value='<?php echo $device->StatusNET->IPAddress; ?>'
                            >
                            <small id="device_ipHelp" class="form-text text-muted">
                                <?php echo __("DEVICE_IP_HELP", "DEVICE_ACTIONS"); ?>
                            </small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col col-12 col-sm-12">
                            <label for="device_port">
                                <?php echo __("DEVICE_PORT", "DEVICE_ACTIONS"); ?>
                            </label>
                            <input type="text"
                                   class="form-control disabled"
                                   id="device_port_fake"
                                   name='devices[<?php echo $idx; ?>][device_port]'
                                   placeholder="<?php echo __("PLEASE_ENTER"); ?>"
                                   value='<?php echo $port; ?>'
                                   disabled required
                            >
                            <input type='hidden'
                                   name='devices[<?php echo $idx; ?>][device_port]'
                                   value='<?php echo $port; ?>'
                            >
                            <small id="device_portHelp" class="form-text text-muted">
                                <?php echo __("DEVICE_PORT_HELP", "DEVICE_ACTIONS"); ?>
                            </small>
                        </div>
                    </div>


                    <div class="form-row">
                        <div class="form-group col">
                            <label for="device_position">
                                <?php echo __("DEVICE_POSITION", "DEVICE_ACTIONS"); ?>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="device_position"
                                   name='devices[<?php echo $idx; ?>][device_position]'
                                   value='<?php echo $idx + 1; ?>'
                            >
                            <small id="device_positionHelp" class="form-text text-muted">
                                <?php echo __("DEVICE_POSITION_HELP", "DEVICE_ACTIONS"); ?>
                            </small>
                        </div>
                    </div>
                    <?php if (isset($device->StatusSTS->POWER)): ?>
                        <?php
                        $friendlyName = is_array($device->Status->FriendlyName) //array since 5.12.0h
                            ? $device->Status->FriendlyName[0] : $device->Status->FriendlyName;
                        ?>
                        <div class="form-row">
                            <div class="form-group col col-12 col-sm-6">
                                <label for="device_name">
                                    <?php echo __("LABEL_NAME", "DEVICE_ACTIONS"); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="device_name"
                                       name='devices[<?php echo $idx; ?>][device_name][]'
                                       placeholder="<?php echo __("PLEASE_ENTER"); ?>"
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
                                    <?php echo __("DEVICE_NAME_TOOLTIP", "DEVICE_ACTIONS"); ?>
                                </small>


                            </div>
                            <div class="form-group col col-12 col-sm-3">
                                <a id='test_device' class='btn btn-secondary col-12 test_device'
                                   style='margin-top: 1.8rem;'
                                   data-device_ip='<?php echo $device->StatusNET->IPAddress; ?>'
                                   data-device_relais='1'
                                >
                                    <?php echo __("BTN_TEST", "DEVICES_AUTOSCAN_MQTT"); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>


                    <?php
                    $i            = 1;
                    $power        = "POWER" . $i;
                    $channelFound = false;

                    while (isset($device->StatusSTS->$power))  : ?>
                        <?php $channelFound = true; ?>
                        <?php
                        $friendlyName = is_array($device->Status->FriendlyName) //array since 5.12.0h
                            ? (isset($device->Status->FriendlyName[$i - 1]) ? $device->Status->FriendlyName[$i - 1]
                                : "") : $device->Status->FriendlyName . " " . $i;
                        ?>
                        <div class="form-row">
                            <div class="form-group col col-12 col-sm-6">
                                <label for="device_name_<?php echo $i; ?>">
                                    <?php echo __("LABEL_NAME", "DEVICE_ACTIONS"); ?><?php echo $i; ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="device_name_<?php echo $i; ?>"
                                       name='devices[<?php echo $idx; ?>][device_name][<?php echo $i; ?>]'
                                       placeholder="<?php echo __("PLEASE_ENTER"); ?>"
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
                                <a href='#' title='<?php echo __("DEVICE_NAME_TOOLTIP", "DEVICE_ACTIONS"); ?>'
                                   class='default-name'
                                >
                                    <?php echo $friendlyName; ?>
                                </a>
                                )
                                <small id="default_nameHelp" class="form-text text-muted">
                                    <?php echo __("DEVICE_NAME_TOOLTIP", "DEVICE_ACTIONS"); ?>
                                </small>


                            </div>
                            <div class="form-group col col-12 col-sm-3">
                                <a id='' class='btn btn-secondary col-12 test_device'
                                   style='margin-top: 1.8rem;'
                                   data-device_ip='<?php echo $device->StatusNET->IPAddress; ?>'
                                   data-device_relais='<?php echo $i; ?>'
                                >
                                    <?php echo __("BTN_TEST", "DEVICES_AUTOSCAN_MQTT"); ?>
                                </a>
                            </div>
                        </div>


                        <?php

                        $i++;
                        $power = "POWER" . $i;
                        ?>

                    <?php endwhile; ?>

                    <?php if (!isset($device->StatusSTS->POWER) && !$channelFound) :
                        //no channel found?>
                        <?php
                        $friendlyName = is_array($device->Status->FriendlyName) //array since 5.12.0h
                            ? $device->Status->FriendlyName[0] : $device->Status->FriendlyName;
                        ?>
                        <div class="form-row">
                            <div class="form-group col col-12 col-sm-9">
                                <label for="device_name">
                                    <?php echo __("LABEL_NAME", "DEVICE_ACTIONS"); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="device_name"
                                       name='devices[<?php echo $idx; ?>][device_name][]'
                                       placeholder="<?php echo __("PLEASE_ENTER"); ?>"
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
                                    <?php echo __("DEVICE_NAME_TOOLTIP", "DEVICE_ACTIONS"); ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>


                <?php endforeach; ?>
                <div class="row">
                    <div class="col col-12 col-sm-6 text-left">
                        <a class="btn btn-secondary  col-12 col-sm-auto" href='<?php echo _BASEURL_; ?>devices'>
                            <?php echo __("BTN_BACK", "DEVICE_ACTIONS"); ?>
                        </a>
                    </div>
                    <div class="col col-12 col-sm-6 text-right">
                        <button type='submit'
                                name='save_all'
                                value='save_all'
                                class='btn btn-primary col-12 col-sm-auto'

                        >
                            <?php echo __("BTN_SAVE_ALL", "DEVICES_AUTOSCAN_MQTT"); ?>
                        </button>
                    </div>
                </div>

                </table>


            <?php endif; ?>
        </form>


    </div>
</div>

<div class="modal fade" id="deviceScanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5><?php echo __("MSG_SCANNING", "DEVICES_AUTOSCAN_MQTT"); ?></h5>
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
