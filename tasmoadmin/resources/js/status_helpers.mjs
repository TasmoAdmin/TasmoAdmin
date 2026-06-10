function ensureStatusSections(data = {}) {
  return {
    ...data,
    Status: data.Status ?? {},
    StatusPRM: data.StatusPRM ?? {},
    StatusFWR: data.StatusFWR ?? {},
    StatusLOG: data.StatusLOG ?? {},
    StatusNET: data.StatusNET ?? {},
    StatusMQT: data.StatusMQT ?? {},
    StatusSNS: data.StatusSNS ?? {},
    StatusSTS: data.StatusSTS ?? {},
  };
}

export function normalizeStatusData(data = {}) {
  const normalized = ensureStatusSections(data);
  const wifiStatus = normalized.StatusSTS.WLAN ?? normalized.StatusSTS.Wifi;

  normalized.hasWifi = false;

  if (wifiStatus !== undefined) {
    normalized.hasWifi = true;
    normalized.wifi = {
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

export function getRuntimeInfo(data, labels, now = new Date()) {
  const normalized = ensureStatusSections(data);
  const startup =
    normalized.StatusPRM.StartupDateTimeUtc ??
    normalized.StatusPRM.StartupUTC ??
    "";

  if (startup !== "") {
    const startupDateTime = new Date(`${startup}Z`);

    if (!Number.isNaN(startupDateTime.getTime())) {
      const secNum = (now - startupDateTime) / 1000;
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
        startupDateTime,
      };
    }
  }

  return {
    text: normalized.StatusSTS.Uptime ?? normalized.StatusPRM.Uptime ?? "?",
    startupDateTime: null,
  };
}

export function getIlluminance(data, joinString = "<br/>") {
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
