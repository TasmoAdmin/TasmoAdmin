<?php

$deviceLinksHideClass = isset($deviceLinksDefaultHide) && $deviceLinksDefaultHide ? 'd-none' : '';

if (isset($deviceLinks) && $deviceLinks && !isset($deviceLinkActionText)) {
    $deviceLinkActionText = '';
}

?>


<table id='device-list'
       class='table table-striped table-hover tablesaw tablesaw-stack'
       data-tablesaw-mode="stack"
       data-tablesaw-sortable
>
    <thead>
    <tr>
        <?php if (isset($deviceLinks) && true === $deviceLinks) { ?>
        <th class='link cmd_cb <?php echo $deviceLinksHideClass; ?>'>
            <div class="form-check">
                <input class="form-check-input select_all"
                       type="checkbox"
                       value='select_all'
                       id="select_all"
                       name='select_all'
                >
                <label class="form-check-label" for="select_all">
                    <?php echo __('TABLE_HEAD_ALL', 'DEVICES'); ?>
                </label>
            </div>
        </th>
        <?php } ?>
        <th data-column-id='id' data-column-label='<?php echo __('TABLE_HEAD_ID', 'DEVICES'); ?>' data-column-toggle='true' data-tablesaw-sortable-col data-tablesaw-sortable-numeric><?php echo __('TABLE_HEAD_ID', 'DEVICES'); ?></th>
        <th data-column-id='position' data-column-label='<?php echo __('TABLE_HEAD_POSITION', 'DEVICES'); ?>' data-column-toggle='true' data-tablesaw-sortable-col data-tablesaw-sortable-numeric><?php echo __('TABLE_HEAD_POSITION', 'DEVICES'); ?></th>
        <th data-column-id='name' data-column-label='<?php echo __('TABLE_HEAD_NAME', 'DEVICES'); ?>' data-column-toggle='true' data-tablesaw-sortable-col><?php echo __('TABLE_HEAD_NAME', 'DEVICES'); ?></th>
        <th data-column-id='ip' data-column-label='<?php echo __('TABLE_HEAD_IP', 'DEVICES'); ?>' data-column-toggle='true' data-tablesaw-sortable-col><?php echo __('TABLE_HEAD_IP', 'DEVICES'); ?></th>
        <th data-column-id='status'><?php echo __('TABLE_HEAD_STATE', 'DEVICES'); ?></th>
        <th data-column-id='rssi' data-column-label='<?php echo __('TABLE_HEAD_RSSI', 'DEVICES'); ?>' data-column-toggle='true' data-tablesaw-sortable-col data-tablesaw-sortable-numeric>
            <i class="fas fa-signal"
               data-bs-toggle="tooltip" data-bs-title='<?php echo __('TABLE_HEAD_RSSI', 'DEVICES'); ?>'
            ></i>
        </th>
        <th data-column-id='version' data-column-label='<?php echo __('TABLE_HEAD_VERSION', 'DEVICES'); ?>' data-column-toggle='true' data-tablesaw-sortable-col><?php echo __('TABLE_HEAD_VERSION', 'DEVICES'); ?></th>
        <th data-column-id='runtime' data-column-label='<?php echo __('TABLE_HEAD_RUNTIME', 'DEVICES'); ?>' data-column-toggle='true'><?php echo __('TABLE_HEAD_RUNTIME', 'DEVICES'); ?></th>
        <th data-column-id='energyPower' data-column-label='<?php echo __('TABLE_HEAD_ENERGY', 'DEVICES'); ?>' data-column-toggle='true' class='energyPower hidden'><?php echo __(
            'TABLE_HEAD_ENERGY',
            'DEVICES'
        ); ?></th>
        <th data-column-id='temp' data-column-label='<?php echo __('TABLE_HEAD_TEMP', 'DEVICES'); ?>' data-column-toggle='true' class='temp hidden'><?php echo __('TABLE_HEAD_TEMP', 'DEVICES'); ?></th>
        <th data-column-id='humidity' data-column-label='<?php echo __('TABLE_HEAD_HUMIDITY', 'DEVICES'); ?>' data-column-toggle='true' class='humidity hidden'><?php echo __(
            'TABLE_HEAD_HUMIDITY',
            'DEVICES'
        ); ?></th>
        <th data-column-id='illuminance' data-column-label='<?php echo __('TABLE_HEAD_ILLUMINANCE', 'DEVICES'); ?>' data-column-toggle='true' class='illuminance hidden'><?php echo __(
            'TABLE_HEAD_ILLUMINANCE',
            'DEVICES'
        ); ?></th>
        <th data-column-id='hostname' data-column-label='<?php echo __('HOSTNAME', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('HOSTNAME', 'DEVICES'); ?></th>
        <th data-column-id='mac' data-column-label='<?php echo __('MAC', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('MAC', 'DEVICES'); ?></th>
        <th data-column-id='mqtt' data-column-label='<?php echo __('MQTT', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('MQTT', 'DEVICES'); ?></th>
        <th data-column-id='idx' data-column-label='<?php echo __('TABLE_HEAD_IDX', 'DEVICES'); ?>' data-column-toggle='true' class='more idx hidden'><?php echo __(
            'TABLE_HEAD_IDX',
            'DEVICES'
        ); ?></th>
        <th data-column-id='poweronstate' data-column-label='<?php echo __('POWERONSTATE', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('POWERONSTATE', 'DEVICES'); ?></th>
        <th data-column-id='ledstate' data-column-label='<?php echo __('LEDSTATE', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('LEDSTATE', 'DEVICES'); ?></th>
        <th data-column-id='savedata' data-column-label='<?php echo __('SAVEDATA', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('SAVEDATA', 'DEVICES'); ?></th>
        <th data-column-id='sleep' data-column-label='<?php echo __('SLEEP', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('SLEEP', 'DEVICES'); ?></th>
        <th data-column-id='bootcount' data-column-label='<?php echo __('BOOTCOUNT', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('BOOTCOUNT', 'DEVICES'); ?></th>
        <th data-column-id='savecount' data-column-label='<?php echo __('SAVECOUNT', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('SAVECOUNT', 'DEVICES'); ?></th>
        <th data-column-id='log' data-column-label='<?php echo __('LOGSTATES', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('LOGSTATES', 'DEVICES'); ?></th>
        <th data-column-id='wificonfig' data-column-label='<?php echo __('WIFICONFIG', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('WIFICONFIG', 'DEVICES'); ?></th>
        <th data-column-id='vcc' data-column-label='<?php echo __('VCC', 'DEVICES'); ?>' data-column-toggle='true' class='more'><?php echo __('VCC', 'DEVICES'); ?></th>

        <th data-column-id='actions' class='link text-sm-right'>
            <a href='<?php echo _BASEURL_; ?>device_action/add'>
                <i class="fas fa-plus add"
                  data-bs-toggle="tooltip" data-bs-title='<?php echo __('TABLE_HEAD_NEW_DEVICE', 'DEVICES'); ?>'
                ></i>
                <?php echo __('TABLE_HEAD_NEW_DEVICE', 'DEVICES'); ?>
            </a>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php
    $odd = true;
if (isset($devices) && !empty($devices)) {
    foreach ($devices as $device_group) {
        foreach ($device_group->names as $key => $devicename) { ?>
                <?php
            $device_group->keywords[] = strtolower($devicename);
            ?>
                <tr class='<?php echo $odd ? 'odd' : 'even'; ?>'
                    data-device_id='<?php echo $device_group->id; ?>'
                    data-device_group='<?php echo count($device_group->names) > 1
                    ? 'multi' : 'single'; ?>'
                    data-device_ip='<?php echo $device_group->ip; ?>'
                    data-device_relais='<?php echo $key + 1; ?>'
                    data-keywords="<?php echo implode(' ', $device_group->keywords); ?>"
                >
                    <?php if (isset($deviceLinks) && true === $deviceLinks) { ?>
                    <td class='cmd_cb <?php echo $deviceLinksHideClass; ?>'>
                        <?php if (0 == $key) { ?>
                            <div class="form-check">
                                <input class="form-check-input device_checkbox"
                                       type="checkbox"
                                       <?php if (isset($disabledDeviceIds) && array_key_exists($device_group->id, $disabledDeviceIds)) { ?>
                                           disabled="disabled"
                                       <?php } ?>
                                       value='<?php echo $device_group->id; ?>'
                                       id="cb_<?php echo $device_group->id; ?>"
                                       name='device_ids[]'
                                >
                                <label class="form-check-label"
                                       for="cb_<?php echo $device_group->id; ?>"
                                >
                                </label>
                            </div>


                        <?php } ?>
                    </td>
                    <?php } ?>
                    <td data-column-id='id'><?php echo $device_group->id; ?></td>
                    <td data-column-id='position'><?php echo $device_group->position; ?></td>
                    <td class='device_name' data-column-id='name'>
                        <a href='<?php echo $device_group->getUrlWithAuth(); ?>'
                           target='_blank'
                          data-bs-toggle="tooltip" data-bs-title='<?php echo __(
                              'LINK_OPEN_DEVICE_WEBUI',
                              'DEVICES'
                          ); ?>'
                        ><?php echo str_replace(
                            ' ',
                            '&nbsp;',
                            $devicename
                        ); ?></a>
                    </td>
                    <td data-column-id='ip'><?php echo $device_group->ip; ?></td>
                    <td class='status' data-column-id='status'>
                        <label class="form-switch">
                            <input type="checkbox">
                            <i></i>
                        </label>

                    </td>
                    <td class='rssi' data-column-id='rssi'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='version' data-column-id='version'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='runtime' data-column-id='runtime'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='energyPower hidden' data-column-id='energyPower'>
													<span>
														-
													</span>
                    </td>
                    <td class='temp hidden' data-column-id='temp'>
														<span>
															-
														</span>
                    </td>
                    <td class='humidity hidden' data-column-id='humidity'>
														<span>
															-
														</span>
                    </td>
                    <td class='illuminance hidden' data-column-id='illuminance'>
													<span>
														-
													</span>
                    </td>


                    <td class='more hostname dblcEdit' data-column-id='hostname' data-cmnd='Hostname'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more mac' data-column-id='mac'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more mqtt dblcEdit' data-column-id='mqtt' data-cmnd='Mqtt'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more idx hidden' data-column-id='idx'>
														<span>
															-
														</span>
                    </td>
                    <td class='more poweronstate dblcEdit' data-column-id='poweronstate' data-cmnd='PowerOnState'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more ledstate dblcEdit' data-column-id='ledstate' data-cmnd='LedState'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more savedata dblcEdit' data-column-id='savedata' data-cmnd='SaveData'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more sleep dblcEdit' data-column-id='sleep' data-cmnd='Sleep'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more bootcount' data-column-id='bootcount'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more savecount' data-column-id='savecount'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more log' data-column-id='log'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more wificonfig dblcEdit' data-column-id='wificonfig' data-cmnd='WifiConfig'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>
                    <td class='more vcc' data-column-id='vcc'>
														<span>
															<div class='loader'>
																<img src='<?php echo _RESOURCESURL_; ?>img/loading.gif'
                                                                     alt='<?php echo __('TEXT_LOADING'); ?>'
                                                                    data-bs-toggle="tooltip" data-bs-title='<?php echo __('TEXT_LOADING'); ?>'
                                                                >
															</div>
														</span>
                    </td>


                    <td class='col actions text-sm-right' data-column-id='actions'>
                        <a href='<?php echo _BASEURL_; ?>device_config/<?php echo $device_group->id; ?>'>
                            <i class="fas fa-cogs fa-lg"
                               data-bs-toggle="tooltip" data-bs-title='<?php echo __(
                                   'LINK_DEVICE_CONFIG',
                                   'DEVICES'
                               ); ?>'
                            ></i></a>
                        <a href='<?php echo _BASEURL_; ?>device_action/edit/<?php echo $device_group->id; ?>'>
                            <i class="fas fa-edit fa-lg"
                              data-bs-toggle="tooltip" data-bs-title='<?php echo __(
                                  'LINK_DEVICE_EDIT',
                                  'DEVICES'
                              ); ?>'
                            ></i></a>
                        <a class="delete"
                           data-dialog-btn-cancel-text='<?php echo __(
                               'CANCEL'
                           ); ?>'
                           data-dialog-btn-ok-text='<?php echo __(
                               'DELETE_DEVICE',
                               'DEVICES'
                           ); ?>'
                           data-dialog-title='<?php echo __(
                               'DELETE_DEVICE_CONFIRM_TITLE',
                               'DEVICES'
                           ); ?>'
                           data-dialog-text='<?php echo __(
                               'DELETE_DEVICE_CONFIRM_TEXT',
                               'DEVICES',
                               [
                                   $devicename,
                                   $device_group->ip,
                               ]
                           ); ?>'
                           href='<?php echo _BASEURL_; ?>device_action/delete/<?php echo $device_group->id; ?>'
                        >
                            <i class="fas fa-trash fa-lg"
                              data-bs-toggle="tooltip" data-bs-title='<?php echo __(
                                  'LINK_DEVICE_DELETE',
                                  'DEVICES'
                              ); ?>'
                            ></i></a>
                        <a href='#' class='restart-device'>
                            <i class="fas fa-sync fa-lg"
                              data-bs-toggle="tooltip" data-bs-title='<?php echo __(
                                  'LINK_DEVICE_RESTART',
                                  'DEVICES'
                              ); ?>'
                            ></i></a>
                    </td>

                </tr>
                <?php
                $odd = !$odd;
        }
    }
} ?>
    </tbody>
    <tfoot>
    <tr class='bottom'>
        <?php if (isset($deviceLinks) && true === $deviceLinks) { ?>
        <th class='link cmd_cb <?php echo $deviceLinksHideClass; ?>'>
            <div class="form-check">
                <input class="form-check-input select_all"
                       type="checkbox"
                       value='select_all'
                       id="select_all"
                       name='select_all'
                >
                <label class="form-check-label" for="select_all">
                    <?php echo __('TABLE_HEAD_ALL', 'DEVICES'); ?>
                </label>
            </div>
        </th>
        <?php } ?>
        <th data-column-id='id'><?php echo __('TABLE_HEAD_ID', 'DEVICES'); ?></th>
        <th data-column-id='position'><?php echo __('TABLE_HEAD_POSITION', 'DEVICES'); ?></th>
        <th data-column-id='name'><?php echo __('TABLE_HEAD_NAME', 'DEVICES'); ?></th>
        <th data-column-id='ip'><?php echo __('TABLE_HEAD_IP', 'DEVICES'); ?></th>
        <th data-column-id='status'><?php echo __('TABLE_HEAD_STATE', 'DEVICES'); ?></th>
        <th data-column-id='rssi'>
            <i class="fas fa-signal"
              data-bs-toggle="tooltip" data-bs-title='<?php echo __('TABLE_HEAD_RSSI', 'DEVICES'); ?>'
            ></i>
        </th>
        <th data-column-id='version'><?php echo __('TABLE_HEAD_VERSION', 'DEVICES'); ?></th>
        <th data-column-id='runtime'><?php echo __('TABLE_HEAD_RUNTIME', 'DEVICES'); ?></th>
        <th data-column-id='energyPower' class='energyPower hidden'><?php echo __(
            'TABLE_HEAD_ENERGY',
            'DEVICES'
        ); ?></th>
        <th data-column-id='temp' class='temp hidden'><?php echo __('TABLE_HEAD_TEMP', 'DEVICES'); ?></th>
        <th data-column-id='humidity' class='humidity hidden'><?php echo __(
            'TABLE_HEAD_HUMIDITY',
            'DEVICES'
        ); ?></th>
        <th data-column-id='illuminance' class='illuminance hidden'><?php echo __(
            'TABLE_HEAD_ILLUMINANCE',
            'DEVICES'
        ); ?></th>
        <th data-column-id='hostname' class='more'><?php echo __('HOSTNAME', 'DEVICES'); ?></th>
        <th data-column-id='mac' class='more'><?php echo __('MAC', 'DEVICES'); ?></th>
        <th data-column-id='mqtt' class='more'><?php echo __('MQTT', 'DEVICES'); ?></th>
        <th data-column-id='idx' class='more idx hidden'><?php echo __(
            'TABLE_HEAD_IDX',
            'DEVICES'
        ); ?></th>
        <th data-column-id='poweronstate' class='more'><?php echo __('POWERONSTATE', 'DEVICES'); ?></th>
        <th data-column-id='ledstate' class='more'><?php echo __('LEDSTATE', 'DEVICES'); ?></th>
        <th data-column-id='savedata' class='more'><?php echo __('SAVEDATA', 'DEVICES'); ?></th>
        <th data-column-id='sleep' class='more'><?php echo __('SLEEP', 'DEVICES'); ?></th>
        <th data-column-id='bootcount' class='more'><?php echo __('BOOTCOUNT', 'DEVICES'); ?></th>
        <th data-column-id='savecount' class='more'><?php echo __('SAVECOUNT', 'DEVICES'); ?></th>
        <th data-column-id='log' class='more'><?php echo __('LOGSTATES', 'DEVICES'); ?></th>
        <th data-column-id='wificonfig' class='more'><?php echo __('WIFICONFIG', 'DEVICES'); ?></th>
        <th data-column-id='vcc' class='more'><?php echo __('VCC', 'DEVICES'); ?></th>
        <th data-column-id='actions' class='link text-sm-right'>
            <a href='<?php echo _BASEURL_; ?>device_action/add'>
                <i class="fas fa-plus add"
                  data-bs-toggle="tooltip" data-bs-title='<?php echo __('TABLE_HEAD_NEW_DEVICE', 'DEVICES'); ?>'
                ></i>
                <?php echo __('TABLE_HEAD_NEW_DEVICE', 'DEVICES'); ?>
            </a>
        </th>
    </tr>
    </tfoot>
</table>
