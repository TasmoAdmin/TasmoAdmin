<form class='center config-form' name='device_config_network' method='post'>
	<input type='hidden' name='tab-index' value='1'>
	<div class="row g-3 device-config-card-grid">
		<div class="col col-12">
			<div class="card device-config-card">
				<div class="card-body">
					<h5 class="card-title mb-3"><?php echo __('TAB_HL_NETWORK', 'DEVICE_CONFIG'); ?></h5>
					<div class="row g-3">
						<div class="form-group col col-12 col-md-6">
							<label for="Hostname" class="form-label">
								<?php echo __('CONFIG_HOSTNAME', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="Hostname"
							       name='Hostname'
							       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
							       value='<?php echo isset($status->StatusNET->Hostname)
                                 && !empty($status->StatusNET->Hostname) ? $status->StatusNET->Hostname : ''; ?>'>
							<small id="HostnameHelp" class="form-text text-muted">
								<?php echo __('CONFIG_HOSTNAME_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
						<div class="form-group col col-12 col-md-6">
							<label for="NtpServer1" class="form-label">
								<?php echo __('CONFIG_NTPSERVER', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="NtpServer1"
							       name='NtpServer1'
							       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
							       value='<?php echo isset($status->statusNTP->NtpServer1)
                                 && !empty($status->statusNTP->NtpServer1) ? $status->statusNTP->NtpServer1 : ''; ?>'>
							<small id="NtpServer1Help" class="form-text text-muted">
								<?php echo __('CONFIG_NTPSERVER_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
						<div class="form-group col col-12 col-md-6">
							<label for="IPAddress1" class="form-label">
								<?php echo __('CONFIG_IPADDRESS', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="IPAddress1"
							       name='IPAddress1'
							       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
							       value='<?php echo isset($status->StatusNET->IPAddress)
                                 && !empty($status->StatusNET->IPAddress) ? $status->StatusNET->IPAddress : ''; ?>'>
							<small id="IPAddress1Help" class="form-text text-muted">
								<?php echo __('CONFIG_IPADDRESS_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
						<div class="form-group col col-12 col-md-6">
							<label for="IPAddress2" class="form-label">
								<?php echo __('CONFIG_GATEWAY', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="IPAddress2"
							       name='IPAddress2'
							       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
							       value='<?php echo isset($status->StatusNET->Gateway)
                                 && !empty($status->StatusNET->Gateway) ? $status->StatusNET->Gateway : ''; ?>'>
							<small id="IPAddress2Help" class="form-text text-muted">
								<?php echo __('CONFIG_GATEWAY_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
						<div class="form-group col col-12 col-md-6">
							<label for="IPAddress3" class="form-label">
								<?php echo __('CONFIG_SUBNETMASK', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="IPAddress3"
							       name='IPAddress3'
							       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
							       value='<?php echo isset($status->StatusNET->Subnetmask)
                                 && !empty($status->StatusNET->Subnetmask) ? $status->StatusNET->Subnetmask : ''; ?>'>
							<small id="IPAddress3Help" class="form-text text-muted">
								<?php echo __('CONFIG_SUBNETMASK_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
						<div class="form-group col col-12 col-md-6">
							<label for="IPAddress4" class="form-label">
								<?php echo __('CONFIG_DNSSERVER', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="IPAddress4"
							       name='IPAddress4'
							       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
							       value='<?php echo isset($status->StatusNET->DNSServer)
                                 && !empty($status->StatusNET->DNSServer) ? $status->StatusNET->DNSServer : ''; ?>'>
							<small id="IPAddress4Help" class="form-text text-muted">
								<?php echo __('CONFIG_DNSSERVER_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
						<div class="form-group col col-12">
							<label for="Mac" class="form-label">
								<?php echo __('CONFIG_MACADDRESS', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="Mac"
							       name='' readonly disabled
							       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
							       value='<?php echo isset($status->StatusNET->Mac)
                                 && !empty($status->StatusNET->Mac) ? $status->StatusNET->Mac : ''; ?>'>
							<small id="MacHelp" class="form-text text-muted">
								<?php echo __('CONFIG_MACADDRESS_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col col-12">
			<div class="card device-config-card">
				<div class="card-body">
					<h5 class="card-title mb-3"><?php echo __('CONFIG_USE_AP', 'DEVICE_CONFIG'); ?></h5>
					<div class="form-group">
						<label for="AP1" class='form-label d-block mb-2'>
							<?php echo __('CONFIG_USE_AP', 'DEVICE_CONFIG'); ?>
						</label>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="AP" id="AP1" value="1"
								<?php echo isset($status->StatusSTS->Wifi->AP)
                           && !empty($status->StatusSTS->Wifi->AP)
                           && 1 == $status->StatusSTS->Wifi->AP ? 'checked="checked"' : ''; ?>>
							<label class="form-check-label" for="AP1">
								<?php echo __('CONFIG_AP1', 'DEVICE_CONFIG'); ?>
							</label>
						</div>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="AP" id="AP2" value="2"
								<?php echo isset($status->StatusSTS->Wifi->AP)
                           && !empty($status->StatusSTS->Wifi->AP)
                           && 2 == $status->StatusSTS->Wifi->AP ? 'checked="checked"' : ''; ?>>
							<label class="form-check-label" for="AP2">
								<?php echo __('CONFIG_AP2', 'DEVICE_CONFIG'); ?>
							</label>
						</div>
						<small id="APHelp" class="form-text text-muted d-block">
							<?php echo __('CONFIG_USE_AP_HELP', 'DEVICE_CONFIG'); ?>
						</small>
					</div>
					<div class="row g-3 mt-1">
						<div class="form-group col col-12 col-md-6">
							<label for="SSId1" class="form-label">
								<?php echo __('CONFIG_SSID1', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="SSId1"
							       name='SSId1'
							       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
							       value='<?php echo isset($status->StatusLOG->SSId1)
                                 && !empty($status->StatusLOG->SSId1) ? $status->StatusLOG->SSId1 : ''; ?>'>
							<small id="SSId1Help" class="form-text text-muted">
								<?php echo __('CONFIG_SSID1_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
						<div class="form-group col col-12 col-md-6">
							<label for="Password1" class="form-label">
								<?php echo __('CONFIG_SSID1PW', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="Password1"
							       name='Password1'
							       placeholder=""
							       value=''>
							<small id="Password1Help" class="form-text text-muted">
								<?php echo __('CONFIG_SSID1PW_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
						<div class="form-group col col-12 col-md-6">
							<label for="SSId2" class="form-label">
								<?php echo __('CONFIG_SSID2', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="SSId2"
							       name='SSId2'
							       placeholder="<?php echo __('PLEASE_ENTER'); ?>"
							       value='<?php echo isset($status->StatusLOG->SSId2)
                                 && !empty($status->StatusLOG->SSId2) ? $status->StatusLOG->SSId2 : ''; ?>'>
							<small id="SSId2Help" class="form-text text-muted">
								<?php echo __('CONFIG_SSID2_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
						<div class="form-group col col-12 col-md-6">
							<label for="Password2" class="form-label">
								<?php echo __('CONFIG_SSID2PW', 'DEVICE_CONFIG'); ?>
							</label>
							<input type="text"
							       class="form-control"
							       id="Password2"
							       name='Password2'
							       placeholder=""
							       value=''>
							<small id="Password2Help" class="form-text text-muted">
								<?php echo __('CONFIG_SSID2PW_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
						<div class="form-group col col-12">
							<label for="WifiConfig" class="form-label">
								<?php echo __('CONFIG_WIFICONFIG', 'DEVICE_CONFIG'); ?>
							</label>
							<select class="form-control form-select" id="WifiConfig" name='WifiConfig'>
								<option value='0' <?php echo isset($status->StatusNET->WifiConfig)
                                         && 0 == $status->StatusNET->WifiConfig ? 'selected="selected"' : ''; ?>>
									<?php echo __('CONFIG_WIFICONFIG_OPTION_0', 'DEVICE_CONFIG'); ?>
								</option>
								<option value='1' <?php echo isset($status->StatusNET->WifiConfig)
                                         && 1 == $status->StatusNET->WifiConfig ? 'selected="selected"' : ''; ?>>
									<?php echo __('CONFIG_WIFICONFIG_OPTION_1', 'DEVICE_CONFIG'); ?>
								</option>
								<option value='2' <?php echo isset($status->StatusNET->WifiConfig)
                                         && 2 == $status->StatusNET->WifiConfig ? 'selected="selected"' : ''; ?>>
									<?php echo __('CONFIG_WIFICONFIG_OPTION_2', 'DEVICE_CONFIG'); ?>
								</option>
								<option value='3' <?php echo isset($status->StatusNET->WifiConfig)
                                         && 3 == $status->StatusNET->WifiConfig ? 'selected="selected"' : ''; ?>>
									<?php echo __('CONFIG_WIFICONFIG_OPTION_3', 'DEVICE_CONFIG'); ?>
								</option>
								<option value='4' <?php echo isset($status->StatusNET->WifiConfig)
                                         && 4 == $status->StatusNET->WifiConfig ? 'selected="selected"' : ''; ?>>
									<?php echo __('CONFIG_WIFICONFIG_OPTION_4', 'DEVICE_CONFIG'); ?>
								</option>
								<option value='5' <?php echo isset($status->StatusNET->WifiConfig)
                                         && 5 == $status->StatusNET->WifiConfig ? 'selected="selected"' : ''; ?>>
									<?php echo __('CONFIG_WIFICONFIG_OPTION_5', 'DEVICE_CONFIG'); ?>
								</option>
							</select>
							<small id="WifiConfigHelp" class="form-text text-muted">
								<?php echo __('CONFIG_WIFICONFIG_HELP', 'DEVICE_CONFIG'); ?>
							</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mt-5">
		<div class="col col-12">
			<div class="text-end">
				<button type='submit' class='btn btn-primary ' name='save' value='submit'>
					<?php echo __('BTN_SAVE_DEVICE_CONFIG', 'DEVICE_CONFIG'); ?>
				</button>
			</div>
		</div>
	</div>
</form>
