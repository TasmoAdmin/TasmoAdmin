const NIGHTMODE_OVERRIDE_KEY = "nightmode_override";

function getAutomaticNightmodeState(date = new Date()) {
  const hour = date.getHours();

  return hour >= 18 || hour <= 8;
}

function normalizeNightmodeOverride(value) {
  return value === "day" || value === "night" ? value : null;
}

function resolveNightmodeEnabled(
  configMode = "auto",
  override = null,
  date = new Date(),
) {
  const normalizedOverride = normalizeNightmodeOverride(override);

  if (normalizedOverride === "night") {
    return true;
  }

  if (normalizedOverride === "day") {
    return false;
  }

  if (configMode === "always") {
    return true;
  }

  if (configMode === "auto") {
    return getAutomaticNightmodeState(date);
  }

  return false;
}

function getNextNightmodeOverride(isNightmodeEnabled) {
  return isNightmodeEnabled ? "day" : "night";
}

module.exports = {
  NIGHTMODE_OVERRIDE_KEY,
  getAutomaticNightmodeState,
  normalizeNightmodeOverride,
  resolveNightmodeEnabled,
  getNextNightmodeOverride,
};
