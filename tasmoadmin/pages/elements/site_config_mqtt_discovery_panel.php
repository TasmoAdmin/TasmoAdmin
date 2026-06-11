<div class="settings-section mqtt-discovery-settings-section">
    <div class="row g-4 settings-row">
        <div class="col col-12">
            <h2 class="settings-section-title"><?php echo __('CONFIG_MQTT_DISCOVERY_TITLE', 'USER_CONFIG'); ?></h2>
            <small class="d-block text-body-secondary mt-2">
                <?php echo __('CONFIG_MQTT_DISCOVERY_HELP', 'USER_CONFIG'); ?>
            </small>
        </div>
        <div class="col col-12 col-md-8">
            <div class="settings-option mqtt-discovery-field">
                <label for="mqtt_discovery_host">
                    <?php echo __('MQTT_HOST', 'DEVICE_CONFIG'); ?>
                </label>
                <input type="text"
                       class="form-control"
                       id="mqtt_discovery_host"
                       name='mqtt_discovery_host'
                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                       value='<?php echo htmlspecialchars((string) $config['mqtt_discovery_host'], ENT_QUOTES); ?>'
                >
            </div>
        </div>
        <div class="col col-12 col-md-4">
            <div class="settings-option mqtt-discovery-field">
                <label for="mqtt_discovery_port">
                    <?php echo __('MQTT_PORT', 'DEVICE_CONFIG'); ?>
                </label>
                <input type="number"
                       class="form-control"
                       id="mqtt_discovery_port"
                       name='mqtt_discovery_port'
                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                       value='<?php echo htmlspecialchars((string) $config['mqtt_discovery_port'], ENT_QUOTES); ?>'
                >
            </div>
        </div>
        <div class="col col-12 col-md-6">
            <div class="settings-option mqtt-discovery-field">
                <label for="mqtt_discovery_username">
                    <?php echo __('MQTT_USER', 'DEVICE_CONFIG'); ?>
                </label>
                <input type="text"
                       class="form-control"
                       id="mqtt_discovery_username"
                       name='mqtt_discovery_username'
                       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
                       value='<?php echo htmlspecialchars((string) $config['mqtt_discovery_username'], ENT_QUOTES); ?>'
                >
            </div>
        </div>
        <div class="col col-12 col-md-6">
            <div class="settings-option mqtt-discovery-field">
                <label for="mqtt_discovery_password">
                    <?php echo __('MQTT_PASSWORD', 'DEVICE_CONFIG'); ?>
                </label>
                <input type="password"
                       class="form-control"
                       id="mqtt_discovery_password"
                       name='mqtt_discovery_password'
                       placeholder="<?php echo $mqttDiscoveryPasswordStored
                           ? __('CONFIG_MQTT_DISCOVERY_PASSWORD_STORED', 'USER_CONFIG')
                           : __('PLEASE_ENTER'); ?>"
                       value=''
                       autocomplete="off"
                >
                <?php if ($mqttDiscoveryPasswordStored) { ?>
                    <div class="form-check mqtt-discovery-clear-password">
                        <input class="form-check-input"
                               type="checkbox"
                               value="1"
                               id="clear_mqtt_discovery_password"
                               name='clear_mqtt_discovery_password'>
                        <label class="form-check-label" for="clear_mqtt_discovery_password">
                            <?php echo __('CONFIG_MQTT_DISCOVERY_PASSWORD_CLEAR', 'USER_CONFIG'); ?>
                        </label>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="col col-12 col-md-4">
            <div class="settings-option mqtt-discovery-field">
                <label for="mqtt_discovery_cmnd_prefix">
                    <?php echo __('CONFIG_MQTT_DISCOVERY_COMMAND_PREFIX', 'USER_CONFIG'); ?>
                </label>
                <input type="text"
                       class="form-control"
                       id="mqtt_discovery_cmnd_prefix"
                       name='mqtt_discovery_cmnd_prefix'
                       value='<?php echo htmlspecialchars((string) $config['mqtt_discovery_cmnd_prefix'], ENT_QUOTES); ?>'
                >
            </div>
        </div>
        <div class="col col-12 col-md-4">
            <div class="settings-option mqtt-discovery-field">
                <label for="mqtt_discovery_stat_prefix">
                    <?php echo __('CONFIG_MQTT_DISCOVERY_STAT_PREFIX', 'USER_CONFIG'); ?>
                </label>
                <input type="text"
                       class="form-control"
                       id="mqtt_discovery_stat_prefix"
                       name='mqtt_discovery_stat_prefix'
                       value='<?php echo htmlspecialchars((string) $config['mqtt_discovery_stat_prefix'], ENT_QUOTES); ?>'
                >
            </div>
        </div>
        <div class="col col-12 col-md-4">
            <div class="settings-option mqtt-discovery-field">
                <label for="mqtt_discovery_tele_prefix">
                    <?php echo __('CONFIG_MQTT_DISCOVERY_TELE_PREFIX', 'USER_CONFIG'); ?>
                </label>
                <input type="text"
                       class="form-control"
                       id="mqtt_discovery_tele_prefix"
                       name='mqtt_discovery_tele_prefix'
                       value='<?php echo htmlspecialchars((string) $config['mqtt_discovery_tele_prefix'], ENT_QUOTES); ?>'
                >
            </div>
        </div>
        <div class="col col-12 col-md-8">
            <div class="settings-option mqtt-discovery-field mqtt-discovery-field-textarea">
                <label for="mqtt_discovery_subscriptions">
                    <?php echo __('CONFIG_MQTT_DISCOVERY_SUBSCRIPTIONS', 'USER_CONFIG'); ?>
                </label>
                <textarea class="form-control"
                          id="mqtt_discovery_subscriptions"
                          name='mqtt_discovery_subscriptions'
                          rows="3"><?php echo htmlspecialchars((string) $config['mqtt_discovery_subscriptions'], ENT_QUOTES); ?></textarea>
            </div>
        </div>
        <div class="col col-12 col-md-4">
            <div class="settings-option mqtt-discovery-field">
                <label for="mqtt_discovery_timeout_seconds">
                    <?php echo __('CONFIG_MQTT_DISCOVERY_TIMEOUT', 'USER_CONFIG'); ?>
                </label>
                <input type="number"
                       class="form-control"
                       id="mqtt_discovery_timeout_seconds"
                       name='mqtt_discovery_timeout_seconds'
                       value='<?php echo htmlspecialchars((string) $config['mqtt_discovery_timeout_seconds'], ENT_QUOTES); ?>'
                >
            </div>
        </div>
    </div>
</div>
