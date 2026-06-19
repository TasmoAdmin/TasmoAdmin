function ensureStatusSections(data = {}) {
  return {
    ...data,
    Status: data.Status ?? {},
    StatusPRM: data.StatusPRM ?? {},
    StatusFWR: data.StatusFWR ?? {},
    StatusLOG: data.StatusLOG ?? {},
    StatusNET: data.StatusNET ?? {},
    StatusMQT: data.StatusMQT,
    StatusSNS: data.StatusSNS ?? {},
    StatusSTS: data.StatusSTS ?? {},
  };
}

function normalizeStatusData(data = {}) {
  const normalized = ensureStatusSections(data);
  const wifiStatus = normalized.StatusSTS.WLAN ?? normalized.StatusSTS.Wifi;

  normalized.hasWifi = false;

  if (wifiStatus !== undefined) {
    normalized.hasWifi = true;
    normalized.wifi = {
      channel: wifiStatus.Channel,
      rssi: wifiStatus.RSSI,
      ssid: wifiStatus.SSID ?? wifiStatus.SSId,
      uptime:
        normalized.StatusSTS.Laufzeit ??
        normalized.StatusSTS.Uptime ??
        normalized.StatusPRM.Uptime,
    };
  }

  return normalized;
}

function getWifiDisplayInfo(data = {}) {
  if (!data.hasWifi || data.wifi === undefined) {
    return {
      summary: "-",
      details: "",
      tooltip: "",
    };
  }

  const summary =
    data.wifi.rssi !== undefined && data.wifi.rssi !== null
      ? `${data.wifi.rssi}%`
      : "?";
  const details = [
    data.wifi.ssid,
    data.wifi.channel ? `ch${data.wifi.channel}` : "",
  ]
    .filter(Boolean)
    .join(" / ");

  return {
    summary,
    details,
    tooltip: [details, summary].filter(Boolean).join(" / "),
  };
}

function parseUptimeToSeconds(uptime) {
  if (typeof uptime !== "string") {
    return null;
  }

  const normalizedUptime = uptime.trim();
  const match = normalizedUptime.match(
    /^(?:(?<days>\d+)T)?(?<hours>\d{1,2}):(?<minutes>\d{2}):(?<seconds>\d{2})$/,
  );

  if (!match || !match.groups) {
    return null;
  }

  const days = Number.parseInt(match.groups.days ?? "0", 10);
  const hours = Number.parseInt(match.groups.hours, 10);
  const minutes = Number.parseInt(match.groups.minutes, 10);
  const seconds = Number.parseInt(match.groups.seconds, 10);

  if (
    Number.isNaN(days) ||
    Number.isNaN(hours) ||
    Number.isNaN(minutes) ||
    Number.isNaN(seconds)
  ) {
    return null;
  }

  return days * 24 * 3600 + hours * 3600 + minutes * 60 + seconds;
}

function extractFirstNumericValue(value) {
  if (typeof value !== "string") {
    return null;
  }

  const normalizedValue = value
    .replace(/<[^>]+>/g, " ")
    .replace(/&nbsp;/g, " ");
  const match = normalizedValue.match(/[-+]?\d*\.?\d+/);

  if (match === null) {
    return null;
  }

  const parsedValue = Number.parseFloat(match[0]);
  return Number.isNaN(parsedValue) ? null : parsedValue;
}

function getSortableVersionValue(version) {
  if (typeof version !== "string") {
    return null;
  }

  const match = version.match(/(\d+(?:\.\d+)+)/);
  if (match === null) {
    return null;
  }

  return match[1]
    .split(".")
    .map((part) => part.padStart(6, "0"))
    .join(".");
}

function getRuntimeInfo(data, labels, now = new Date()) {
  const normalized = ensureStatusSections(data);
  const startup =
    normalized.StatusPRM.StartupDateTimeUtc ??
    normalized.StatusPRM.StartupUTC ??
    "";

  if (startup !== "") {
    const startupDateTime = new Date(`${startup}Z`);

    if (!Number.isNaN(startupDateTime.getTime())) {
      const secNum = Math.max(0, Math.floor((now - startupDateTime) / 1000));
      const days = Math.floor(secNum / (3600 * 24));
      const hours = Math.floor((secNum - days * (3600 * 24)) / 3600);
      const minutes = Math.floor(
        (secNum - days * (3600 * 24) - hours * 3600) / 60,
      );
      const seconds = Math.floor(
        secNum - days * (3600 * 24) - hours * 3600 - minutes * 60,
      );

      const text = (
        (days !== 0 ? `${days}${labels.day}` : "") +
        " " +
        (hours !== 0 || days !== 0 ? `${hours}${labels.hour}` : "") +
        " " +
        (minutes !== 0 || hours !== 0 || days !== 0
          ? `${minutes}${labels.minute}`
          : "") +
        " " +
        (seconds !== 0 || minutes !== 0 || hours !== 0
          ? `${seconds}${labels.second}`
          : "-")
      ).trim();

      return {
        text,
        sortValue: secNum,
        startupDateTime,
      };
    }
  }

  const fallbackText =
    normalized.StatusSTS.Uptime ?? normalized.StatusPRM.Uptime ?? "?";

  return {
    text: fallbackText,
    sortValue: parseUptimeToSeconds(fallbackText),
    startupDateTime: null,
  };
}

function getIlluminance(data, joinString = "<br/>") {
  const normalized = ensureStatusSections(data);
  const illuminance = [];

  Object.values(normalized.StatusSNS).forEach((sensor) => {
    if (
      sensor !== null &&
      typeof sensor === "object" &&
      sensor.Illuminance !== undefined
    ) {
      illuminance.push(`${sensor.Illuminance} lx`);
    }
  });

  return illuminance.join(joinString);
}

function getEnergyPower(data, joinString = "<br/>") {
  const normalized = ensureStatusSections(data);
  const energyPower = [];
  const energy = normalized.StatusSNS.ENERGY;

  if (energy === undefined) {
    return "";
  }

  if (energy.Power !== undefined) {
    energyPower.push(`${energy.Power} W`);
  }

  if (energy.Today !== undefined) {
    const totals = [energy.Today];

    if (energy.Yesterday !== undefined) {
      totals.push(energy.Yesterday);
    }

    if (energy.Total !== undefined) {
      totals.push(energy.Total);
    }

    energyPower.push(`${totals.join(" / ")} kWh`);
  }

  if (energy.Current !== undefined) {
    energyPower.push(`${energy.Current} A`);
  }

  return energyPower.join(joinString);
}

module.exports = {
  extractFirstNumericValue,
  getSortableVersionValue,
  normalizeStatusData,
  getWifiDisplayInfo,
  getRuntimeInfo,
  parseUptimeToSeconds,
  getEnergyPower,
  getIlluminance,
};
