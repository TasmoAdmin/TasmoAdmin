import { disposeTooltip, refreshTooltip } from "./app";

let timerSummariesPromise;
let timerSummaries = {};

function normalizeTimerSummaries(data) {
  if (data && typeof data === "object") {
    return data;
  }

  return {};
}

export function loadTimerSummaries() {
  if (timerSummariesPromise) {
    return timerSummariesPromise;
  }

  timerSummariesPromise = $.ajax({
    dataType: "json",
    url: `${config.base_url}actions?timerSummaries=1`,
    cache: false,
  })
    .then((data) => {
      timerSummaries = normalizeTimerSummaries(data);
      return timerSummaries;
    })
    .catch((xhr, status, error) => {
      console.error(
        "[TimerIndicators] Failed to load timer summaries",
        status,
        error,
      );
      timerSummaries = {};
      return timerSummaries;
    });

  return timerSummariesPromise;
}

export function getTimerRelaySummary(deviceId, deviceRelais = 1) {
  const deviceSummary =
    timerSummaries[deviceId] || timerSummaries[String(deviceId)] || null;

  if (!deviceSummary || !deviceSummary.relays) {
    return null;
  }

  return (
    deviceSummary.relays[deviceRelais] ||
    deviceSummary.relays[String(deviceRelais)] ||
    null
  );
}

export function hasExplicitTimerSource(
  data,
  deviceRelais = 1,
  deviceStatus = "",
) {
  if (!data || String(deviceStatus).toUpperCase() !== "ON") {
    return false;
  }

  const relayKeys =
    Number(deviceRelais) > 1
      ? [`POWER${deviceRelais}`]
      : ["POWER", `POWER${deviceRelais}`];
  const candidates = [];

  relayKeys.forEach((relayKey) => {
    if (data.StatusSTS && typeof data.StatusSTS[relayKey] === "object") {
      candidates.push(data.StatusSTS[relayKey]);
    }

    if (typeof data[relayKey] === "object") {
      candidates.push(data[relayKey]);
    }
  });

  return candidates.some((candidate) => {
    const source =
      candidate?.SOURCE || candidate?.Source || candidate?.source || "";

    return typeof source === "string" && source.toLowerCase().includes("timer");
  });
}

function hideTimerIndicator(indicator) {
  disposeTooltip(indicator);
  indicator.classList.add("d-none");
  indicator.classList.remove(
    "timer-indicator-active",
    "timer-indicator-enabled",
  );
  indicator.removeAttribute("title");
  indicator.removeAttribute("aria-label");
  indicator.removeAttribute("data-bs-title");
  indicator.removeAttribute("data-bs-toggle");
}

export function applyTimerIndicatorState(
  element,
  { data = null, deviceStatus = "", enableTooltip = true } = {},
) {
  const indicator = element?.querySelector?.("[data-timer-indicator]");
  if (!indicator) {
    return;
  }

  const deviceId = $(element).data("device_id");
  const deviceRelais = $(element).data("device_relais") || 1;
  const relaySummary = getTimerRelaySummary(deviceId, deviceRelais);

  if (!relaySummary || relaySummary.hasActiveTimer !== true) {
    hideTimerIndicator(indicator);
    return;
  }

  const isTimerPoweredOn = hasExplicitTimerSource(
    data,
    deviceRelais,
    deviceStatus,
  );
  const tooltipKey = isTimerPoweredOn
    ? "DEVICE_TIMER_STATUS_ACTIVE_RUNNING"
    : "DEVICE_TIMER_STATUS_ACTIVE";
  const tooltipText = $.i18n(tooltipKey);

  indicator.classList.remove("d-none");
  indicator.classList.toggle("timer-indicator-enabled", !isTimerPoweredOn);
  indicator.classList.toggle("timer-indicator-active", isTimerPoweredOn);
  indicator.setAttribute("title", tooltipText);
  indicator.setAttribute("aria-label", tooltipText);

  if (enableTooltip) {
    indicator.setAttribute("data-bs-title", tooltipText);
    indicator.setAttribute("data-bs-toggle", "tooltip");
    refreshTooltip(indicator);
  }
}
