const { compareVersions } = require("compare-versions");

const LEGACY_ESP8266_MULTI_HOP_BASELINE = "9.1.3";

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

function determineUpgradePlan(target, response) {
  const platform = detectDevicePlatform(response);
  const currentVersion = response?.StatusFWR?.Version ?? "";
  const minimalOtaUrl = target?.minimalOtaUrl ?? "";
  const targetVersion = target?.targetVersion ?? "";

  if (
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
  detectDevicePlatform,
  determineUpgradePlan,
  extractVersionFromResponse,
  versionsEqual,
  versionUpgrade,
  shouldTreatStatusAsSuccessful,
  getFailureDetails,
};
