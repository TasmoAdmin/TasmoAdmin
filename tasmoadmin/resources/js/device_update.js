import {compareVersions} from 'compare-versions';

const deviceContainerId = 'progressbox';

const Level = {
	info: 'info',
	error: 'error',
	success: 'success',
}

const otaUrl = document.getElementById('ota_new_firmware_url').value;
const targetVersion = document.getElementById('target_version').value;

const sleep = (milliseconds) => {
	return new Promise(resolve => setTimeout(resolve, milliseconds))
}

const defaultTries = 15;
const defaultSleepDuration = 10000;

const defaultRetryOptions = {
	maxRetries: defaultTries,
	sleepDuration: defaultSleepDuration,
};

const fetchWithRetries = async (
	url,
	options,
	retryOptions = defaultRetryOptions,
	retryCount = 0,
) => {
	try {
		const response = await fetch(url, options);
		if (!response.ok) {
			throw Error($.i18n('FETCH_ERROR', url, response.status));
		}

		return response;
	} catch (error) {
		if (retryCount < retryOptions.maxRetries) {
			await sleep(retryOptions.sleepDuration)
			return fetchWithRetries(url, options, retryOptions, retryCount + 1);
		}

		throw error;
	}
}

async function doAjax(deviceId, cmnd) {
	const url = `${config.base_url}actions?doAjax&id=${deviceId}&cmnd=${encodeURIComponent(cmnd)}`;
	let response = await fetchWithRetries(url);
	response = await response.json();

	if (response.hasOwnProperty('ERROR'))
	{
		throw Error($.i18n('BLOCK_UPDATE_ERROR_FROM_BACKEND', response.ERROR));
	}

	if (response.hasOwnProperty('Command') && response.Command === 'Unknown') {
		throw Error($.i18n('BLOCK_UPDATE_ERROR_FROM_BACKEND', response.Command));
	}

	return response;
}

async function checkOtaUrlAccessible(otaUrl) {
	try {
		let response = await fetchWithRetries(otaUrl, {method: 'HEAD'}, {
			maxRetries: 0,
		});

		return response.status === 200;
	} catch (e) {
		logGlobal($.i18n('BLOCK_UPDATE_ERROR_OTA_NOT_ACCESSIBLE', otaUrl, e), Level.error);
		return false;
	}
}

async function setOtaUrl(deviceId, otaUrl) {
	try {
		await doAjax(deviceId, `OtaUrl ${otaUrl}`);
	} catch (e) {
		console.error(e)
		throw e;
	}
}

async function startUpgrade(deviceId) {
	try {
		await doAjax(deviceId, 'Upgrade 1');
	} catch (e) {
		console.error(e)
		throw e;
	}
}

async function checkStatus(deviceId, tries = defaultTries) {
	try {
		log(deviceId, $.i18n('BLOCK_UPDATE_CHECKING_VERSION'));
		return await doAjax(deviceId, 'Status 0');
	} catch (e) {
		if (tries > 0) {
			const remainingTries = --tries;
			log(deviceId, $.i18n('BLOCK_UPDATE_FETCH_FAILED', remainingTries, defaultSleepDuration/1000));
			await sleep(defaultSleepDuration);
			return await checkStatus(deviceId, remainingTries);
		} else {
			console.error(e)
			throw e;
		}
	}
}

function deviceSelector(deviceId) {
	return `device${deviceId}`
}

function logGlobal(message, level = Level.info) {
	const logContainer = document.getElementById('logGlobal');
	const logLine = document.createElement('span');
	logLine.classList.add(level);
	logLine.append(message);
	logContainer.appendChild(logLine);
}

function log(deviceId, message, level = Level.info) {
	const deviceContainer = document.getElementById(deviceSelector(deviceId));
	const logLine = document.createElement('span');
	logLine.classList.add(level);
	logLine.append(`[${new Date().toISOString()}] ${message}`);
	deviceContainer.appendChild(logLine);
}

function createDeviceElement(device) {
	const deviceContainer = document.createElement('div');
	deviceContainer.setAttribute('id', deviceSelector(device.id));
	deviceContainer.classList.add('device');

	const deviceTitle = document.createElement('h1');
	deviceTitle.appendChild(document.createTextNode($.i18n('DEVICE') + ` ${device.id} (${device.name})`));
	deviceContainer.appendChild(deviceTitle);

	return deviceContainer;
}

function extractVersionFromResponse(response) {
	let actualMatch = /(\d+(\.\d+)+(\d+)*)/.exec(response);
	if (actualMatch === null) {
		throw Error($.i18n('BLOCK_UPDATE_ERROR_VERSION_COMPARE', response));
	}

	return actualMatch[1];
}

function versionsEqual(target, actual) {
	const actualMatch = extractVersionFromResponse(actual);
	const targetMatch = extractVersionFromResponse(target);

	return targetMatch === actualMatch;
}

function versionUpgrade(target, action) {
	const actualMatch = extractVersionFromResponse(action);
	const targetMatch = extractVersionFromResponse(target);

	return compareVersions(targetMatch, actualMatch) === 1;
}

async function updateDevice(device) {
	const deviceContainer = document.getElementById(deviceContainerId);
	deviceContainer.appendChild(createDeviceElement(device));

	try
	{
		log(device.id, $.i18n('BLOCK_GLOBAL_START'));
		if (targetVersion) {
			log(device.id, $.i18n('BLOCK_UPDATE_ATTEMPT_TO_VERSION', targetVersion));
		}
		let response = await checkStatus(device.id);
		const beforeVersion = response.StatusFWR.Version;
		log(device.id, $.i18n('BLOCK_UPDATE_CURRENT_VERSION_IS', beforeVersion));
		if (targetVersion && !config.force_upgrade && versionsEqual(targetVersion, beforeVersion)) {
			log(device.id, $.i18n('BLOCK_UPDATE_DEVICE_AT_TARGET_VERSION'), Level.success);
			return true;
		}

		if (targetVersion && config.update_newer_only && !versionUpgrade(targetVersion, beforeVersion)) {
			log(device.id, $.i18n('BLOCK_UPDATE_DEVICE_NEWER_THAN_TARGET_VERSION'), Level.success);
			return true;
		}

		log(device.id, $.i18n('BLOCK_OTAURL_SET_URL_FWURL') + otaUrl);
		await setOtaUrl(device.id, otaUrl);
		log(device.id, $.i18n('BLOCK_UPDATE_START'));
		await startUpgrade(device.id);
		log(device.id, $.i18n('BLOCK_UPDATE_SLEEPING', defaultSleepDuration/1000));
		await sleep(defaultSleepDuration);
		log(device.id, $.i18n('BLOCK_UPDATE_SUCCESS'));

		let upgradeSuccessful = false;
		for (let i = 0; i < defaultTries; i++) {
			response = await checkStatus(device.id);

			if (!targetVersion) {
				upgradeSuccessful = true;
				break;
			}

			if (versionsEqual(targetVersion, response.StatusFWR.Version)) {
				upgradeSuccessful = true;
				break;
			}

			log(device.id, $.i18n('BLOCK_UPDATE_VERSION_NOT_AT_TARGET_VERSION'));
			log(device.id, $.i18n('BLOCK_UPDATE_SLEEPING', defaultSleepDuration/1000));
			await sleep(defaultSleepDuration);
		}

		if (!upgradeSuccessful) {
			log(device.id, $.i18n('BLOCK_UPDATE_ERROR_VERSION_COMPARE_MISMATCH', targetVersion, response.StatusFWR.Version), Level.error);
			return false;
		}

		log(device.id, $.i18n('BLOCK_UPDATE_VERSION_IS', response.StatusFWR.Version));
		log(device.id, $.i18n('BLOCK_UPDATE_FINISH_SUCCESS'), Level.success);

		return true
	} catch(e) {
		log(device.id, e.message, Level.error);
		return false
	}
}

document.addEventListener('DOMContentLoaded', async () => {
	if (config.update_fe_check && !await checkOtaUrlAccessible(otaUrl)) {
		return;
	}
	const results = await Promise.all(devices.map(device =>  updateDevice(device)));
	const successful = results.reduce((count, value) => value ? count + 1 : count, 0);
	const resultLogLevel = successful === devices.length ? Level.success : Level.error;
	logGlobal( $.i18n('BLOCK_UPDATE_RESULTS',successful, devices.length), resultLogLevel)
});
