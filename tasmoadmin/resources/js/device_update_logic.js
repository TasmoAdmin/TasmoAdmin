const { compareVersions } = require("compare-versions");

const LEGACY_ESP8266_MULTI_HOP_BASELINE = "9.1.3";
const MINIMAL_FIRMWARE_NAME = "tasmota-minimal";

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

// Since Tasmota 11.0.0.4 the ESP8266 minimal image only knows Upgrade, Upload,
// OtaUrl, Seriallog and Restart, so it answers Status 0 with Command Unknown.
function isMinimalFirmwareResponse(response) {
  return response?.Command === "Unknown" && !response?.StatusFWR;
}

function getReportedVersion(response) {
  if (isMinimalFirmwareResponse(response)) {
    return "";
  }

  return response?.StatusFWR?.Version ?? "";
}

function describeReportedVersion(response) {
  if (isMinimalFirmwareResponse(response)) {
    return MINIMAL_FIRMWARE_NAME;
  }

  return getReportedVersion(response);
}

function isUpgradeStepComplete({
  stepKind,
  targetVersion,
  beforeVersion,
  response,
}) {
  if (isMinimalFirmwareResponse(response)) {
    return stepKind === "minimal";
  }

  const currentVersion = getReportedVersion(response);
  if (!targetVersion && !beforeVersion) {
    return currentVersion !== "";
  }

  return shouldTreatStatusAsSuccessful({
    targetVersion,
    beforeVersion,
    currentVersion,
  });
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

function determineUpgradePlan(target, response) {
  const platform = detectDevicePlatform(response);
  const currentVersion = response?.StatusFWR?.Version ?? "";
  const minimalOtaUrl = target?.minimalOtaUrl ?? "";
  const targetVersion = target?.targetVersion ?? "";
  const source = target?.source ?? "";

  if (
    source === "automatic" &&
    platform === "esp8266" &&
    minimalOtaUrl &&
    targetVersion &&
    compareVersions(
      extractVersionFromResponse(currentVersion),
      LEGACY_ESP8266_MULTI_HOP_BASELINE,
    ) === -1
  ) {
    return {
      type: "blocked",
      key: "BLOCK_UPDATE_LEGACY_PATH_REQUIRED",
      values: [
        currentVersion,
        targetVersion,
        LEGACY_ESP8266_MULTI_HOP_BASELINE,
      ],
    };
  }

  if (minimalOtaUrl) {
    return {
      type: "staged",
      steps: [
        {
          kind: "minimal",
          otaUrl: minimalOtaUrl,
          targetVersion: "",
        },
        {
          kind: "final",
          otaUrl: target.otaUrl,
          targetVersion,
        },
      ],
    };
  }

  return {
    type: "direct",
    steps: [
      {
        kind: "final",
        otaUrl: target.otaUrl,
        targetVersion,
      },
    ],
  };
}

module.exports = {
  describeReportedVersion,
  detectDevicePlatform,
  determineUpgradePlan,
  extractVersionFromResponse,
  getFailureDetails,
  getReportedVersion,
  isMinimalFirmwareResponse,
  isUpgradeStepComplete,
  resolveUpdateTarget,
  shouldTreatStatusAsSuccessful,
  versionUpgrade,
  versionsEqual,
};
