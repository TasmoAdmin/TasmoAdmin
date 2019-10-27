<form class='center config-form' name='device_config_mqtt' method='post'>
	<?php
		$o = &$status->StatusLOG->SetOptionDecoded;

		//TODO: add link to https://github.com/arendst/Tasmota/wiki/MQTT-Features
	?>

	<input type='hidden' name='tab-index' value='2'>


	<!-- SetOption3 MQTT enabled -->
	<div class="form-group col">
		<div class="form-check custom-control custom-checkbox">
			<input id='SetOption3h' type='hidden' value='0' name='SetOption3'>
			<input class="form-check-input custom-control-input"
			       type="checkbox"
			       value='1'
			       id="SetOption3"
			       name='SetOption3'
				<?php echo( $o->SetOption3->value == 1 ? "checked=\"checked\"" : "" ); ?>
			>
			<label class="form-check-label custom-control-label"
			       for="SetOption3">
				<?php echo __( "MQTT_ENABLED", "DEVICE_CONFIG" ); ?>
			</label>
		</div>
	</div>

	<div class="form-row">
		<div class="form-group col col-12 col-sm-8">
			<label for="MqttHost">
				<?php echo __( "MQTT_HOST", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="MqttHost"
			       name='MqttHost'
			       maxlength='32'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo isset( $status->StatusMQT->MqttHost )
			                         && !empty( $status->StatusMQT->MqttHost ) ? $status->StatusMQT->MqttHost : ""; ?>'
			>
			<small id="MqttHostHelp" class="form-text text-muted">
				<?php echo __( "MQTT_HOST_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-4">
			<label for="MqttPort">
				<?php echo __( "MQTT_PORT", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="number"
			       class="form-control"
			       id="MqttPort"
			       name='MqttPort'
			       min='2'
			       max='32766'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo isset( $status->StatusMQT->MqttPort )
			                         && !empty( $status->StatusMQT->MqttPort ) ? $status->StatusMQT->MqttPort : ""; ?>'
			>
			<small id="MqttPortHelp" class="form-text text-muted">
				<?php echo __( "MQTT_PORT_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col col-12 col-sm-6">
			<label for="MqttClient">
				<?php echo __( "MQTT_CLIENT", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="MqttClient"
			       name='MqttClient'
			       maxlength='32'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo isset( $status->StatusMQT->MqttClient )
			                         && !empty( $status->StatusMQT->MqttClient ) ? $status->StatusMQT->MqttClient
				       : ""; ?>'
			>
			<small id="MqttClientHelp" class="form-text text-muted">
				<?php echo __( "MQTT_CLIENT_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-6">
			<label for="MqttFingerprint">
				<?php echo __( "MQTTFINGERPRINT", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="MqttFingerprint"
			       name='MqttFingerprint'
			       maxlength='59'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo !empty( $status->StatusMQT->MqttFingerprint ) ? $status->StatusMQT->MqttFingerprint
				       : ""; ?>'
			>
			<small id="MqttFingerprintHelp" class="form-text text-muted">
				<?php echo __( "MQTTFINGERPRINT_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col col-12 col-sm-6">
			<label for="MqttUser">
				<?php echo __( "MQTT_USER", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="MqttUser"
			       name='MqttUser'
			       maxlength='32'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo isset( $status->StatusMQT->MqttUser )
			                         && !empty( $status->StatusMQT->MqttUser ) ? $status->StatusMQT->MqttUser : ""; ?>'
			>
			<small id="MqttUserHelp" class="form-text text-muted">
				<?php echo __( "MQTT_USER_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-6">
			<label for="MqttPassword">
				<?php echo __( "MQTT_PASSWORD", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="MqttPassword"
			       name='MqttPassword'
			       maxlength='32'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo isset( $status->StatusMQT->MqttPassword )
			                         && !empty( $status->StatusMQT->MqttPassword ) ? $status->StatusMQT->MqttPassword
				       : ""; ?>'
			>
			<small id="MqttPasswordHelp" class="form-text text-muted">
				<?php echo __( "MQTT_PASSWORD_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
	</div>


	<div class="form-row">
		<div class="form-group col col-12 col-sm-6">
			<label for="Topic">
				<?php echo __( "TOPIC", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="Topic"
			       name='Topic'
			       maxlength='32'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo isset( $status->Status->Topic )
			                         && !empty( $status->Status->Topic ) ? $status->Status->Topic : ""; ?>'
			>
			<small id="TopicHelp" class="form-text text-muted">
				<?php echo __( "TOPIC_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-6">
			<label for="FullTopic">
				<?php echo __( "FULLTOPIC", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="FullTopic"
			       name='FullTopic'
			       maxlength='100'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo isset( $status->StatusMQT->FullTopic )
			                         && !empty( $status->StatusMQT->FullTopic ) ? $status->StatusMQT->FullTopic
				       : ""; ?>'
			>
			<small id="FullTopicHelp" class="form-text text-muted">
				<?php echo __( "FULLTOPIC_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
	</div>


	<div class="form-row">
		<div class="form-group col col-12 col-sm-4">
			<label for="GroupTopic">
				<?php echo __( "GROUPTOPIC", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="GroupTopic"
			       name='GroupTopic'
			       maxlength='32'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo isset( $status->StatusPRM->GroupTopic )
			                         && !empty( $status->StatusPRM->GroupTopic ) ? $status->StatusPRM->GroupTopic
				       : ""; ?>'
			>
			<small id="GroupTopicHelp" class="form-text text-muted">
				<?php echo __( "GROUPTOPIC_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-4">
			<label for="ButtonTopic">
				<?php echo __( "BUTTONTOPIC", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="ButtonTopic"
			       name='ButtonTopic'
			       maxlength='32'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo !empty( $status->Status->ButtonTopic )
			                         || $status->Status->ButtonTopic == "0" ? $status->Status->ButtonTopic : ""; ?>'
			>
			<small id="ButtonTopicHelp" class="form-text text-muted">
				<?php echo __( "BUTTONTOPIC_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-4">
			<label for="SwitchTopic">
				<?php echo __( "SWITCHTOPIC", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="SwitchTopic"
			       name='SwitchTopic'
			       maxlength='32'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo !empty( $status->StatusMQT->SwitchTopic )
			                         || $status->StatusMQT->SwitchTopic == "0" ? $status->StatusMQT->SwitchTopic
				       : ""; ?>'
			>
			<small id="SwitchTopicHelp" class="form-text text-muted">
				<?php echo __( "SWITCHTOPIC_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>

	</div>

	<div class="form-row">
		<div class="form-group col col-12 col-sm-4">
			<label for="Prefix1">
				<?php echo __( "PREFIX1", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="Prefix1"
			       name='Prefix1'
			       maxlength='10'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo !empty( $status->StatusMQT->Prefixe->Prefix1 )
				       ? $status->StatusMQT->Prefixe->Prefix1 : ""; ?>'
			>
			<small id="Prefix1Help" class="form-text text-muted">
				<?php echo __( "PREFIX1_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-4">
			<label for="Prefix2">
				<?php echo __( "PREFIX2", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="Prefix2"
			       name='Prefix2'
			       maxlength='10'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo !empty( $status->StatusMQT->Prefixe->Prefix2 )
				       ? $status->StatusMQT->Prefixe->Prefix2 : ""; ?>'
			>
			<small id="Prefix2Help" class="form-text text-muted">
				<?php echo __( "PREFIX2_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-4">
			<label for="Prefix3">
				<?php echo __( "PREFIX3", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="Prefix3"
			       name='Prefix3'
			       maxlength='10'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo !empty( $status->StatusMQT->Prefixe->Prefix3 )
				       ? $status->StatusMQT->Prefixe->Prefix3 : ""; ?>'
			>
			<small id="Prefix3Help" class="form-text text-muted">
				<?php echo __( "PREFIX3_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>

	</div>


	<div class="form-row">
		<div class="form-group col col-12 col-sm-6">
			<label for="StateText1">
				<?php echo __( "STATUSTEXT1", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="StateText1"
			       name='StateText1'
			       maxlength='10'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo !empty( $status->StatusMQT->StateTexts->StateText1 )
				       ? $status->StatusMQT->StateTexts->StateText1 : ""; ?>'
			>
			<small id="StateText1Help" class="form-text text-muted">
				<?php echo __( "STATUSTEXT1_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-6">
			<label for="StateText2">
				<?php echo __( "STATUSTEXT2", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="StateText2"
			       name='StateText2'
			       maxlength='10'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo !empty( $status->StatusMQT->StateTexts->StateText2 )
				       ? $status->StatusMQT->StateTexts->StateText2 : ""; ?>'
			>
			<small id="StateText2Help" class="form-text text-muted">
				<?php echo __( "STATUSTEXT2_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col col-12 col-sm-6">
			<label for="StateText3">
				<?php echo __( "STATUSTEXT3", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="StateText3"
			       name='StateText3'
			       maxlength='10'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo !empty( $status->StatusMQT->StateTexts->StateText3 )
				       ? $status->StatusMQT->StateTexts->StateText3 : ""; ?>'
			>
			<small id="StateText3Help" class="form-text text-muted">
				<?php echo __( "STATUSTEXT3_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-6">
			<label for="StateText4">
				<?php echo __( "STATUSTEXT4", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="text"
			       class="form-control"
			       id="StateText4"
			       name='StateText4'
			       maxlength='10'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo !empty( $status->StatusMQT->StateTexts->StateText4 )
				       ? $status->StatusMQT->StateTexts->StateText4 : ""; ?>'
			>
			<small id="StateText4Help" class="form-text text-muted">
				<?php echo __( "STATUSTEXT4_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
	</div>

	<div class="form-row">
		<div class="form-group col col-12 col-sm-6">
			<label for="MqttRetry">
				<?php echo __( "MQTTRETRY", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="number"
			       class="form-control"
			       id="MqttRetry"
			       name='MqttRetry'
			       max='32000'
			       min='10'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo isset( $status->StatusMQT->MqttRetry )
			                         && !empty( $status->StatusMQT->MqttRetry ) ? $status->StatusMQT->MqttRetry
				       : ""; ?>'
			>
			<small id="MqttRetryHelp" class="form-text text-muted">
				<?php echo __( "MQTTRETRY_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
		<div class="form-group col col-12 col-sm-6">
			<label for="TelePeriod">
				<?php echo __( "TELEPERIOD", "DEVICE_CONFIG" ); ?>
			</label>
			<input type="number"
			       class="form-control"
			       id="TelePeriod"
			       name='TelePeriod'
			       max='32000'
			       min='0'
			       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
			       value='<?php echo isset( $status->StatusMQT->TelePeriod )
			                         && !empty( $status->StatusMQT->TelePeriod ) ? $status->StatusMQT->TelePeriod
				       : ""; ?>'
			>
			<small id="TelePeriodHelp" class="form-text text-muted">
				<?php echo __( "TELEPERIOD_HELP", "DEVICE_CONFIG" ); ?>
			</small>
		</div>
	</div>


	<div class="form-row">
		<div class="form-group col col-12 col-sm-4">
			<div class="form-check custom-control custom-checkbox">
				<input id='ButtonRetainh' type='hidden' value='0' name='ButtonRetain'>
				<input class="form-check-input custom-control-input"
				       type="checkbox"
				       value='1'
				       id="ButtonRetain"
				       name='ButtonRetain'
					<?php echo( $status->Status->ButtonRetain == 1 ? "checked=\"checked\"" : "" ); ?>
				>
				<label class="form-check-label custom-control-label"
				       for="ButtonRetain">
					<?php echo __( "BUTTONRETAIN", "DEVICE_CONFIG" ); ?>
				</label>
				<!--				<small id="ButtonRetainHelp" class="form-text text-muted">-->
				<!--					--><?php //echo __( "BUTTONRETAIN_HELP", "DEVICE_CONFIG" ); ?>
				<!--				</small>-->
			</div>
		</div>
		<div class="form-group col col-12 col-sm-4">
			<div class="form-check custom-control custom-checkbox">
				<input id='PowerRetainh' type='hidden' value='0' name='PowerRetain'>
				<input class="form-check-input custom-control-input"
				       type="checkbox"
				       value='1'
				       id="PowerRetain"
				       name='PowerRetain'
					<?php echo( $status->Status->PowerRetain == 1 ? "checked=\"checked\"" : "" ); ?>
				>
				<label class="form-check-label custom-control-label"
				       for="PowerRetain">
					<?php echo __( "POWERRETAIN", "DEVICE_CONFIG" ); ?>
				</label>
				<!--				<small id="PowerRetainHelp" class="form-text text-muted">-->
				<!--					--><?php //echo __( "POWERRETAIN_HELP", "DEVICE_CONFIG" ); ?>
				<!--				</small>-->
			</div>
		</div>
		<div class="form-group col col-12 col-sm-4">
			<div class="form-check custom-control custom-checkbox">
				<input id='SensorRetainh' type='hidden' value='0' name='SensorRetain'>
				<input class="form-check-input custom-control-input"
				       type="checkbox"
				       value='1'
				       id="SensorRetain"
				       name='SensorRetain'
					<?php echo( $status->StatusMQT->SensorRetain == "ON" ? "checked=\"checked\"" : "" ); ?>
				>
				<label class="form-check-label custom-control-label"
				       for="SensorRetain">
					<?php echo __( "SENSORRETAIN", "DEVICE_CONFIG" ); ?>
				</label>
				<!--				<small id="SensorRetainHelp" class="form-text text-muted">-->
				<!--					--><?php //echo __( "SENSORRETAIN_HELP", "DEVICE_CONFIG" ); ?>
				<!--				</small>-->
			</div>
		</div>
	</div>


	<div class="form-row">
		<div class="form-group col col-12 col-sm-4">
			<div class="form-check custom-control custom-checkbox">
				<input id='SetOption2h' type='hidden' value='0' name='SetOption2'>
				<input class="form-check-input custom-control-input"
				       type="checkbox"
				       value='1'
				       id="SetOption2"
				       name='SetOption2'
					<?php echo( $o->SetOption2->value == 1 ? "checked=\"checked\"" : "" ); ?>
				>
				<label class="form-check-label custom-control-label"
				       for="SetOption2">
					<?php echo __( "SETOPTION2", "DEVICE_CONFIG" ); ?>
				</label>
				<small id="SetOption2Help" class="form-text text-muted">
					<?php echo __( "SETOPTION2_HELP", "DEVICE_CONFIG" ); ?>
				</small>
			</div>
		</div>
		<div class="form-group col col-12 col-sm-4">
			<div class="form-check custom-control custom-checkbox">
				<input id='SetOption4h' type='hidden' value='0' name='SetOption4'>
				<input class="form-check-input custom-control-input"
				       type="checkbox"
				       value='1'
				       id="SetOption4"
				       name='SetOption4'
					<?php echo( $o->SetOption4->value == 1 ? "checked=\"checked\"" : "" ); ?>
				>
				<label class="form-check-label custom-control-label"
				       for="SetOption4">
					<?php echo __( "SETOPTION4", "DEVICE_CONFIG" ); ?>
				</label>
				<small id="SetOption4Help" class="form-text text-muted">
					<?php echo __( "SETOPTION4_HELP", "DEVICE_CONFIG" ); ?>
				</small>
			</div>
		</div>
		<div class="form-group col col-12 col-sm-4">
			<div class="form-check custom-control custom-checkbox">
				<input id='SetOption10h' type='hidden' value='0' name='SetOption10'>
				<input class="form-check-input custom-control-input"
				       type="checkbox"
				       value='1'
				       id="SetOption10"
				       name='SetOption10'
					<?php echo( $o->SetOption10->value == 1 ? "checked=\"checked\"" : "" ); ?>
				>
				<label class="form-check-label custom-control-label"
				       for="SetOption10">
					<?php echo __( "SETOPTION10", "DEVICE_CONFIG" ); ?>
				</label>
				<small id="SetOption10Help" class="form-text text-muted">
					<?php echo __( "SETOPTION10_HELP", "DEVICE_CONFIG" ); ?>
				</small>
			</div>
		</div>
	</div>


	<div class="row mt-5">
		<div class="col col-12">
			<div class="text-right">
				<button type='submit' class='btn btn-primary ' name='save' value='submit'>
					<?php echo __( "BTN_SAVE_DEVICE_CONFIG", "DEVICE_CONFIG" ); ?>
				</button>
			</div>
		</div>
	</div>
</form>
