const deviceContainerId = 'progressbox';

const Level = {
	info: 'info',
	error: 'error',
	success: 'success',
}

const otaURL = document.getElementById('ota_new_firmware_url').value;
const targetVersion = document.getElementById('target_version').value;

const sleep = (milliseconds) => {
	return new Promise(resolve => setTimeout(resolve, milliseconds))
}

const defaultRetryOptions = {
	maxRetries: 3,
	sleepDuration: 5000,
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
			throw Error(`Failed to load ${url} returned ${response.status}`);
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
	const url = `http://localhost:8000/index.php?doAjax&id=${deviceId}&cmnd=${encodeURIComponent(cmnd)}`;
	console.log(`Calling ${deviceId} on ${url} with ${cmnd}`);
	let response = await fetchWithRetries(url);
	response = await response.json();

	if (response.hasOwnProperty('ERROR'))
	{
		throw Error(`Error from backend ${response.ERROR}`);
	}

	return response;
}

async function checkOtaUrlAccessible(otaUrl) {
	try {
		let response = await fetchWithRetries(otaUrl, {method: 'HEAD'});

		return response.status === 200;
	} catch (e) {
		// TODO: Print issue about unable to get error
		return false;
	}
}

function setOtaUrl(deviceId) {
	try {
		const otaUrl = 'http://ota.tasmota.com/tasmota/tasmota.bin.gz';
		doAjax(deviceId, `OtaUrl ${otaUrl}`);
	} catch (e) {
		console.log(e)
		throw e;
	}
}

function startUpgrade(deviceId) {
	try {
		doAjax(deviceId, 'Upgrade 1');
	} catch (e) {
		console.log(e)
		throw e;
	}
}

async function checkStatus(deviceId) {
	try {
		return doAjax(deviceId, 'Status 0');
	} catch (e) {
		console.log(e)
		throw e;
	}
}

function deviceSelector(deviceId) {
	return `device${deviceId}`
}

function log(deviceId, message, level = Level.info) {
	const deviceContainer = document.getElementById(deviceSelector(deviceId));
	const logLine = document.createElement('span');
	logLine.classList.add(level);
	logLine.append(`[${new Date().toISOString()}] ${message}`);
	deviceContainer.appendChild(logLine);
}

function createDeviceElement(deviceId) {
	const deviceContainer = document.createElement('div');
	deviceContainer.setAttribute('id', deviceSelector(deviceId));
	deviceContainer.classList.add('device');

	const deviceTitle = document.createElement('h1');
	deviceTitle.appendChild(document.createTextNode(`Device ${deviceId}`));
	deviceContainer.appendChild(deviceTitle);

	return deviceContainer;
}

function compareVersion(target, actual) {
	let actualMatch = /(\d+\.\d+\.\d+)/.exec(actual);
	if (actualMatch === null) {
		throw new Error(`Failed to match version from ${actual}`);
	}

	actualMatch = actualMatch[1];

	let targetMatch = /(\d+\.\d+\.\d+)/.exec(target);
	if (targetMatch === null) {
		throw new Error(`Failed to match version from ${target}`);
	}
	targetMatch = targetMatch[1];

	if (targetMatch !== actualMatch) {
		throw new Error(`Failed to update to ${targetMatch} version is ${actualMatch}`);
	}
}

async function doUpgrade(deviceId) {
	const deviceContainer = document.getElementById(deviceContainerId);
	deviceContainer.appendChild(createDeviceElement(deviceId));

	try
	{
		log(deviceId, $.i18n( 'BLOCK_GLOBAL_START'));
		log(deviceId, 'Checking version...');
		let response = await checkStatus(deviceId);
		const beforeVersion = response.StatusFWR.Version;
		log(deviceId, `Current version is ${beforeVersion}`);
		log(deviceId, $.i18n( 'BLOCK_GLOBAL_START_STEP_2'));
		log(deviceId, $.i18n( 'BLOCK_OTAURL_SET_URL_FWURL') + otaURL);
		log(deviceId, 'Setting OTA URL...');
		await sleep(1000);
		setOtaUrl(deviceId);
		log(deviceId, $.i18n( 'BLOCK_UPDATE_START'));
		await sleep(1000);
		// startUpgrade(deviceId);
		log(deviceId, $.i18n( 'BLOCK_UPDATE_SUCCESS'));
		response = await checkStatus(deviceId);
		log(deviceId, `Version is ${response.StatusFWR.Version}`);
		compareVersion(targetVersion, response.StatusFWR.Version);
		log(deviceId, 'Device upgrade successful!', Level.success);
	} catch(e) {
		log(deviceId, e.message, Level.error);
	}
}

document.addEventListener('DOMContentLoaded', function(event) {
	if (!checkOtaUrlAccessible(otaURL)) {
		return;
	}

	const deviceIds = $.parseJSON( device_ids );
	deviceIds.forEach(doUpgrade);
});

