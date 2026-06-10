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

module.exports = {
  extractVersionFromResponse,
  versionsEqual,
  versionUpgrade,
  shouldTreatStatusAsSuccessful,
  getFailureDetails,
};
