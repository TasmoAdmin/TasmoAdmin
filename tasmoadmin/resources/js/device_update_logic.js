const { compareVersions } = require("compare-versions");

function extractVersionFromResponse(response) {
  const actualMatch = /(\d+(\.\d+)+(\d+)*)/.exec(response);
  if (actualMatch === null) {
    throw Error(`Failed to match version from ${response}`);
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

function shouldTreatStatusAsSuccessful({
  targetVersion,
  beforeVersion,
  currentVersion,
}) {
  if (targetVersion) {
    return versionsEqual(targetVersion, currentVersion);
  }

  return !versionsEqual(beforeVersion, currentVersion);
}

function getFailureDetails({ targetVersion, beforeVersion, currentVersion }) {
  if (targetVersion) {
    return {
      key: "BLOCK_UPDATE_ERROR_VERSION_COMPARE_MISMATCH",
      values: [targetVersion, currentVersion],
    };
  }

  return {
    key: "BLOCK_UPDATE_ERROR_VERSION_NOT_CHANGED",
    values: [beforeVersion],
  };
}

function detectDevicePlatform(response) {
  const hardware = response?.StatusFWR?.Hardware ?? "";
  const version = response?.StatusFWR?.Version ?? "";

  if (hardware.toUpperCase().includes("ESP32")) {
    return "esp32";
  }

  if (version.toLowerCase().includes("(tasmota32")) {
    return "esp32";
  }

  return "esp8266";
}

function resolveUpdateTarget(updateTargets, response) {
  const platform = detectDevicePlatform(response);

  if (updateTargets[platform]) {
    return updateTargets[platform];
  }

  if (updateTargets.default) {
    return updateTargets.default;
  }

  throw Error(`No update target configured for ${platform}`);
}

module.exports = {
  detectDevicePlatform,
  extractVersionFromResponse,
  resolveUpdateTarget,
  versionsEqual,
  versionUpgrade,
  shouldTreatStatusAsSuccessful,
  getFailureDetails,
};
