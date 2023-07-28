<?php

use TasmoAdmin\DeviceFactory;
use TasmoAdmin\DeviceRepository;
use TasmoAdmin\Helper\IpHelper;
use TasmoAdmin\Sonoff;



$config = $Config->readAll();
?>
<div class='row justify-content-sm-center'>
    <div class='col col-12 col-md-8 col-xl-6'>
        <h2 class='text-sm-center mb-5'>
            <?php echo $title; ?>
        </h2>
        <form class='form'
              name='autoscan_form'
              method='post'
              autocomplete="off"
        >

            <div class="form-row">
                <div class="form-group col col-12 col-sm-6">
                    <label for="mqtt_host">
                        <?php echo __("MQTT_HOST", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="mqtt_host"
                           name='mqtt_host'
                           placeholder="<?php echo __("PLEASE_ENTER"); ?>"
                           value='<?php echo $config["mqtt_host"]; ?>'
                           required
                           autofocus="autofocus"
                    >
                    <small id="mqtt_host_help" class="form-text text-muted">
                        <?php echo __("MQTT_HOST_HELP", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </small>
                </div>
                <div class="form-group col col-12 col-sm-6">
                    <label for="mqtt_port">
                        <?php echo __("MQTT_PORT", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="mqtt_port"
                           name='mqtt_port'
                           placeholder="<?php echo __("PLEASE_ENTER"); ?>"
                           value='<?php echo $config["mqtt_port"]; ?>'
                           required
                    >
                    <small id="mqtt_port_help" class="form-text text-muted">
                        <?php echo __("MQTT_PORT_HELP", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col col-12 col-sm-6">
                    <label for="mqtt_host">
                        <?php echo __("MQTT_USERNAME", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="mqtt_username"
                           name='mqtt_username'
                           placeholder="<?php echo __("PLEASE_ENTER"); ?>"
                           value='<?php echo $config["mqtt_username"]; ?>'
                           required
                           autofocus="autofocus"
                    >
                    <small id="mqtt_username_help" class="form-text text-muted">
                        <?php echo __("MQTT_USERNAME_HELP", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </small>
                </div>
                <div class="form-group col col-12 col-sm-6">
                    <label for="mqtt_port">
                        <?php echo __("MQTT_PASSWORD", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <div class="input-group mb-3">
                        <input type="password"
                               class="form-control"
                               id="mqtt_password"
                               name='mqtt_password'
                               autocomplete="off"
                               aria-autocomplete="none"
                               value='<?php echo $_REQUEST["mqtt_password"] ?? ""; ?>'
                        >
                        <div class="input-group-append">
                            <span class="input-group-text show-hide-password" id=""><i class="far fa-eye"></i></span>
                        </div>
                    </div>
                    <small id="mqtt_port_help" class="form-text text-muted">
                        <?php echo __("MQTT_PASSWORD_HELP", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col">
                    <label for="mqtt_topic">
                        <?php echo __("MQTT_TOPIC", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="mqtt_topic"
                           name='mqtt_topic'
                           value='<?php echo $_REQUEST["mqtt_topic"] ?? $config["mqtt_topic"]; ?>'
                    >
                    <small id="mqtt_topic_help" class="form-text text-muted">
                        <?php echo __("MQTT_TOPIC_HELP", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col">
                    <label for="mqtmqtt_topic_formatt_topic">
                        <?php echo __("MQTT_TOPIC_FORMAT", "DEVICES_AUTOSCAN_MQTT"); ?>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="mqtt_topic_format"
                           name='mqtt_topic_format'
                           value='<?php echo $_REQUEST["mqtt_topic_format"] ?? $config["mqtt_topic_format"]; ?>'
                    >
                    <small id="mqtt_topic_format_help" class="form-text text-muted">
                        <?php echo __("MQTT_TOPIC_FORMAT_HELP", "DEVICES_AUTOSCAN_MQTT"); ?>
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
                            onclick='waitingDialog.show("", { headerText:
                                    "<?php echo __("MSG_SCANNING", "DEVICES_AUTOSCAN"); ?>"
                                    }
                                    );'
                    >
                        <?php echo __("BTN_START_AUTOSCAN", "DEVICES_AUTOSCAN"); ?>
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
                        <?php echo __("DEVICE", "DEVICES_AUTOSCAN") . " " . ($idx + 1); ?>
                    </h3>
                    <div class="form-row">
                        <div class="form-group col col-12 col-sm-12">
                            <label for="device_ip">
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
                                    <?php echo __("BTN_TEST", "DEVICES_AUTOSCAN"); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>


                    <?php
                    $i            = 1;
                    $power        = "POWER" . $i;
                    $channelFound = FALSE;

                    while (isset($device->StatusSTS->$power))  : ?>
                        <?php $channelFound = TRUE; ?>
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
                                    <?php echo __("BTN_TEST", "DEVICES_AUTOSCAN"); ?>
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
                            <?php echo __("BTN_SAVE_ALL", "DEVICES_AUTOSCAN"); ?>
                        </button>
                    </div>
                </div>

                </table>


            <?php endif; ?>
        </form>


    </div>
</div>
<script>
    $(document).ready(function()
    {
        $(".default-name").on("click", function (e)
        {
            e.preventDefault();
            // console.log( $( this ).parent().parent().find( "input" ) );
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
