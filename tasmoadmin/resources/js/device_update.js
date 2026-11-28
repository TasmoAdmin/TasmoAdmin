import {
  determineUpgradePlan,
  resolveUpdateTarget,
  versionsEqual,
  versionUpgrade,
  getFailureDetails,
  describeReportedVersion,
  getReportedVersion,
  isUpgradeStepComplete,
} from "./device_update_logic.js";
import { waitForI18n } from "./app";

const deviceContainerId = "progressbox";

const Level = {
  info: "info",
  error: "error",
  success: "success",
};

const State = {
  pending: "pending",
  active: "active",
  success: "success",
  skipped: "skipped",
  error: "error",
};

// rows sorted by urgency: failures first, then running, queued, done
const stateOrder = ["error", "active", "pending", "skipped", "success"];

const updateTargets = JSON.parse(
  document.getElementById("update_targets").value || "{}",
);

const sleep = (milliseconds) => {
  return new Promise((resolve) => setTimeout(resolve, milliseconds));
};

const defaultTries = 15;
const defaultSleepDuration = 10000;

const defaultRetryOptions = {
  maxRetries: defaultTries,
  sleepDuration: defaultSleepDuration,
};

const rows = new Map();
let startedAt = null;
let timerInterval = null;
let running = false;

function formatDuration(totalSeconds) {
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = Math.floor(totalSeconds % 60);
  return `${minutes}:${String(seconds).padStart(2, "0")}`;
}

const fetchWithRetries = async (
  url,
  options,
  retryOptions = defaultRetryOptions,
  retryCount = 0,
) => {
  try {
    const response = await fetch(url, options);
    if (!response.ok) {
      throw Error($.i18n("FETCH_ERROR", url, response.status));
    }

    return response;
  } catch (error) {
    if (retryCount < retryOptions.maxRetries) {
      await sleep(retryOptions.sleepDuration);
      return fetchWithRetries(url, options, retryOptions, retryCount + 1);
    }

    throw error;
  }
};

async function doAjax(deviceId, cmnd, { allowUnknownCommand = false } = {}) {
  let response = await fetchWithRetries(`${config.base_url}actions`, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      doAjax: "1",
      id: String(deviceId),
      cmnd,
      csrf_token: config.csrf_token,
    }),
  });
  response = await response.json();

  if (response.hasOwnProperty("ERROR")) {
    throw Error($.i18n("BLOCK_UPDATE_ERROR_FROM_BACKEND", response.ERROR));
  }

  if (
    response.hasOwnProperty("Command") &&
    response.Command === "Unknown" &&
    !allowUnknownCommand
  ) {
    throw Error($.i18n("BLOCK_UPDATE_ERROR_FROM_BACKEND", response.Command));
  }

  return response;
}

async function checkOtaUrlAccessible(otaUrl) {
  try {
    let response = await fetchWithRetries(
      otaUrl,
      { method: "HEAD" },
      {
        maxRetries: 0,
      },
    );

    return response.status === 200;
  } catch (e) {
    logGlobal(
      $.i18n("BLOCK_UPDATE_ERROR_OTA_NOT_ACCESSIBLE", otaUrl, e),
      Level.error,
    );
    return false;
  }
}

async function setOtaUrl(deviceId, otaUrl) {
  try {
    await doAjax(deviceId, `OtaUrl ${otaUrl}`);
  } catch (e) {
    console.error(e);
    throw e;
  }
}

async function startUpgrade(deviceId) {
  try {
    await doAjax(deviceId, "Upgrade 1");
  } catch (e) {
    console.error(e);
    throw e;
  }
}

async function checkStatus(deviceId, tries = defaultTries, options = {}) {
  try {
    log(deviceId, $.i18n("BLOCK_UPDATE_CHECKING_VERSION"));
    return await doAjax(deviceId, "Status 0", options);
  } catch (e) {
    if (tries > 0) {
      const remainingTries = --tries;
      log(
        deviceId,
        $.i18n(
          "BLOCK_UPDATE_FETCH_FAILED",
          remainingTries,
          defaultSleepDuration / 1000,
        ),
      );
      await sleep(defaultSleepDuration);
      return await checkStatus(deviceId, remainingTries, options);
    } else {
      console.error(e);
      throw e;
    }
  }
}

function deviceSelector(deviceId) {
  return `device${deviceId}`;
}

function logGlobal(message, level = Level.info) {
  const logContainer = document.getElementById("logGlobal");
  const logLine = document.createElement("span");
  logLine.classList.add(level);
  logLine.append(message);
  logContainer.appendChild(logLine);
}

function log(deviceId, message, level = Level.info) {
  const row = rows.get(deviceId);
  if (!row) {
    return;
  }
  const logLine = document.createElement("span");
  logLine.classList.add(level);
  const time = new Date().toTimeString().slice(0, 8);
  logLine.append(`[${time}] ${message}`);
  row.els.logLines.appendChild(logLine);
  if (row.els.logDetails.open) {
    row.els.logLines.scrollTop = row.els.logLines.scrollHeight;
  }
}

function buildSteps(row, plan) {
  row.els.steps.innerHTML = "";
  row.stepEls = [];
  if (plan.steps.length < 2) {
    row.els.steps.classList.add("d-none");
    return;
  }
  row.els.steps.classList.remove("d-none");
  plan.steps.forEach((step, index) => {
    if (index > 0) {
      const connector = document.createElement("span");
      connector.className = "update-step-connector";
      row.els.steps.appendChild(connector);
    }
    const stepEl = document.createElement("span");
    stepEl.className = "update-step";
    stepEl.dataset.state = "pending";
    const dot = document.createElement("i");
    const label = document.createElement("span");
    label.className = "update-step-label";
    label.textContent =
      step.kind === "minimal"
        ? $.i18n("DEVICE_UPDATE_STEP_MINIMAL")
        : step.targetVersion || $.i18n("DEVICE_UPDATE_STEP_FINAL");
    stepEl.append(dot, label);
    row.els.steps.appendChild(stepEl);
    row.stepEls.push(stepEl);
  });
}

function setStepState(row, index, state) {
  const stepEl = row.stepEls[index];
  if (stepEl) {
    stepEl.dataset.state = state;
  }
}

function sortRows() {
  const container = document.getElementById(deviceContainerId);
  const sorted = [...rows.values()].sort(
    (a, b) =>
      stateOrder.indexOf(a.els.root.dataset.state) -
      stateOrder.indexOf(b.els.root.dataset.state),
  );
  for (const row of sorted) {
    container.appendChild(row.els.root);
  }
}

function refreshSummary() {
  const counts = { success: 0, error: 0, active: 0, finished: 0 };
  for (const row of rows.values()) {
    const state = row.els.root.dataset.state;
    if (state === State.success || state === State.skipped) {
      counts.success++;
      counts.finished++;
    } else if (state === State.error) {
      counts.error++;
      counts.finished++;
    } else if (state === State.active) {
      counts.active++;
    }
  }
  const total = rows.size;
  document.getElementById("updateCountSuccess").textContent = counts.success;
  document.getElementById("updateCountError").textContent = counts.error;
  document.getElementById("updateCountActive").textContent = counts.active;

  const bar = document.getElementById("updateSummaryBar");
  const percent = total ? Math.round((counts.finished / total) * 100) : 0;
  bar.style.width = `${percent}%`;
  bar.setAttribute("aria-valuenow", String(percent));
  bar.classList.toggle(
    "bg-danger",
    counts.error > 0 && counts.finished === total,
  );

  document.getElementById("updateSummaryTitle").textContent = $.i18n(
    "DEVICE_UPDATE_FINISHED_COUNT",
    counts.finished,
    total,
  );

  if (running && counts.finished === total) {
    finishTimer();
  }
}

function setState(deviceId, state) {
  const row = rows.get(deviceId);
  if (!row) {
    return;
  }
  row.els.root.dataset.state = state;
  row.els.retry.classList.toggle("d-none", state !== State.error);
  if (state !== State.pending) {
    row.els.phase.classList.remove("text-muted");
  }
  sortRows();
  refreshSummary();
}

function setPhase(deviceId, text, { sleepUntil = null } = {}) {
  const row = rows.get(deviceId);
  if (!row) {
    return;
  }
  row.els.phaseText.textContent = text;
  row.sleepUntil = sleepUntil;
  row.sleepLabel = text;
}

function tickCountdowns() {
  const now = Date.now();
  if (startedAt) {
    document.getElementById("updateSummaryElapsed").textContent =
      formatDuration((now - startedAt) / 1000);
  }
  for (const row of rows.values()) {
    if (!row.sleepUntil) {
      continue;
    }
    const remaining = Math.max(0, Math.ceil((row.sleepUntil - now) / 1000));
    if (remaining > 0) {
      row.els.phaseText.textContent = `${row.sleepLabel} · ${$.i18n("DEVICE_UPDATE_NEXT_CHECK", remaining)}`;
    } else {
      row.els.phaseText.textContent = row.sleepLabel;
      row.sleepUntil = null;
    }
  }
}

function finishTimer() {
  running = false;
  if (timerInterval) {
    clearInterval(timerInterval);
    timerInterval = null;
  }
  for (const row of rows.values()) {
    row.sleepUntil = null;
  }
}

function writeClipboard(text) {
  if (navigator.clipboard) {
    return navigator.clipboard.writeText(text);
  }
  // plain-HTTP fallback: clipboard API requires a secure context
  return new Promise((resolve, reject) => {
    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed";
    textarea.style.opacity = "0";
    document.body.appendChild(textarea);
    textarea.select();
    try {
      document.execCommand("copy")
        ? resolve()
        : reject(new Error("copy failed"));
    } catch (e) {
      reject(e);
    } finally {
      textarea.remove();
    }
  });
}

function copyLog(row, event) {
  event.preventDefault();
  event.stopPropagation();
  const text = [...row.els.logLines.children]
    .map((line) => line.textContent)
    .join("\n");
  writeClipboard(text).then(
    () => {
      row.els.copyLog.textContent = $.i18n("DEVICE_UPDATE_COPIED");
      setTimeout(() => {
        row.els.copyLog.textContent = $.i18n("DEVICE_UPDATE_COPY_LOG");
      }, 2000);
    },
    () => {
      logGlobal($.i18n("DEVICE_UPDATE_COPY_FAILED"), Level.error);
    },
  );
}

function createDeviceRow(device) {
  const root = document.createElement("div");
  root.setAttribute("id", deviceSelector(device.id));
  root.classList.add("update-device-row");
  root.dataset.state = State.pending;

  const status = document.createElement("span");
  status.className = "update-status";
  status.setAttribute("aria-hidden", "true");

  const body = document.createElement("div");
  body.className = "update-device-body";

  const titleRow = document.createElement("div");
  titleRow.className = "update-device-title-row";
  const name = document.createElement("span");
  name.className = "update-device-name";
  name.textContent = `${device.name} (#${device.id})`;
  const version = document.createElement("span");
  version.className = "update-device-version";
  titleRow.append(name, version);

  const phase = document.createElement("div");
  phase.className = "update-device-phase text-muted";
  const phaseText = document.createElement("span");
  phaseText.textContent = $.i18n("DEVICE_UPDATE_WAITING");
  phase.appendChild(phaseText);

  const steps = document.createElement("div");
  steps.className = "update-steps d-none";

  body.append(titleRow, phase, steps);

  const actions = document.createElement("div");
  actions.className = "update-device-actions";

  const retry = document.createElement("button");
  retry.type = "button";
  retry.className = "btn btn-sm btn-outline-danger update-retry d-none";
  retry.textContent = $.i18n("DEVICE_UPDATE_RETRY");
  retry.addEventListener("click", () => retryDevice(device.id));

  const logDetails = document.createElement("details");
  logDetails.className = "update-log";
  const logSummary = document.createElement("summary");
  logSummary.textContent = $.i18n("DEVICE_UPDATE_LOG");
  const copyLogBtn = document.createElement("button");
  copyLogBtn.type = "button";
  copyLogBtn.className = "btn btn-sm btn-link update-copy-log";
  copyLogBtn.textContent = $.i18n("DEVICE_UPDATE_COPY_LOG");
  const logLines = document.createElement("div");
  logLines.className = "update-log-lines";
  logDetails.append(logSummary, logLines);

  actions.append(retry, copyLogBtn);
  root.append(status, body, actions, logDetails);

  const row = {
    device,
    stepEls: [],
    sleepUntil: null,
    sleepLabel: "",
    startedAt: null,
    els: {
      root,
      status,
      name,
      version,
      phase,
      phaseText,
      steps,
      retry,
      logDetails,
      logLines,
      copyLog: copyLogBtn,
    },
  };
  copyLogBtn.addEventListener("click", (event) => copyLog(row, event));
  return row;
}

function setVersion(row, beforeVersion, targetVersion) {
  row.els.version.textContent = targetVersion
    ? `${beforeVersion} → ${targetVersion}`
    : beforeVersion;
}

function finishDeviceVersion(row, beforeVersion, currentVersion) {
  row.els.version.textContent = `${beforeVersion} → ${currentVersion}`;
  row.els.version.classList.add("text-success");
}

let retriesInFlight = 0;
let resultReported = false;

function completeIfDone() {
  if (resultReported || retriesInFlight > 0 || !allFinished()) {
    return;
  }
  resultReported = true;
  finishTimer();
  logFinalResult();
  refreshSummary();
}

async function retryDevice(deviceId) {
  const row = rows.get(deviceId);
  if (!row) {
    return;
  }
  row.els.version.classList.remove("text-success");
  for (const stepEl of row.stepEls) {
    stepEl.dataset.state = "pending";
  }
  if (!timerInterval) {
    timerInterval = setInterval(tickCountdowns, 1000);
  }
  row.startedAt = null;
  resultReported = false;
  retriesInFlight++;
  log(deviceId, "———————", Level.info);
  setPhase(deviceId, $.i18n("DEVICE_UPDATE_WAITING"));
  setState(deviceId, State.active);
  const success = await updateDevice(row.device);
  retriesInFlight--;
  refreshSummary();
  completeIfDone();
  return success;
}

function allFinished() {
  return [...rows.values()].every((row) =>
    [State.success, State.skipped, State.error].includes(
      row.els.root.dataset.state,
    ),
  );
}

function successCount() {
  return [...rows.values()].filter((row) =>
    [State.success, State.skipped].includes(row.els.root.dataset.state),
  ).length;
}

function logFinalResult() {
  const successful = successCount();
  const resultLogLevel = successful === rows.size ? Level.success : Level.error;
  logGlobal(
    $.i18n("BLOCK_UPDATE_RESULTS", successful, rows.size),
    resultLogLevel,
  );
}

async function sleepWithCountdown(deviceId, milliseconds, label) {
  setPhase(deviceId, label, { sleepUntil: Date.now() + milliseconds });
  await sleep(milliseconds);
  const row = rows.get(deviceId);
  if (row) {
    row.sleepUntil = null;
  }
}

async function updateDevice(device) {
  const row = rows.get(device.id);
  setState(device.id, State.active);
  setPhase(device.id, $.i18n("BLOCK_UPDATE_CHECKING_VERSION"));
  row.startedAt = row.startedAt ?? Date.now();

  try {
    log(device.id, $.i18n("BLOCK_GLOBAL_START"));
    let response = await checkStatus(device.id);
    const updateTarget = resolveUpdateTarget(updateTargets, response);
    const { targetVersion } = updateTarget;
    const beforeVersion = response.StatusFWR.Version;
    setVersion(row, beforeVersion, targetVersion);
    log(device.id, $.i18n("BLOCK_UPDATE_CURRENT_VERSION_IS", beforeVersion));
    if (
      targetVersion &&
      !config.force_upgrade &&
      versionsEqual(targetVersion, beforeVersion)
    ) {
      log(
        device.id,
        $.i18n("BLOCK_UPDATE_DEVICE_AT_TARGET_VERSION"),
        Level.success,
      );
      setPhase(device.id, $.i18n("BLOCK_UPDATE_DEVICE_AT_TARGET_VERSION"));
      setState(device.id, State.skipped);
      return true;
    }

    if (
      targetVersion &&
      config.update_newer_only &&
      !versionUpgrade(targetVersion, beforeVersion)
    ) {
      log(
        device.id,
        $.i18n("BLOCK_UPDATE_DEVICE_NEWER_THAN_TARGET_VERSION"),
        Level.success,
      );
      setPhase(
        device.id,
        $.i18n("BLOCK_UPDATE_DEVICE_NEWER_THAN_TARGET_VERSION"),
      );
      setState(device.id, State.skipped);
      return true;
    }

    const upgradePlan = determineUpgradePlan(updateTarget, response);
    if (upgradePlan.type === "blocked") {
      log(
        device.id,
        $.i18n(upgradePlan.key, ...upgradePlan.values),
        Level.error,
      );
      setPhase(device.id, $.i18n(upgradePlan.key, ...upgradePlan.values));
      setState(device.id, State.error);
      return false;
    }

    buildSteps(row, upgradePlan);

    for (const [index, step] of upgradePlan.steps.entries()) {
      setStepState(row, index, "active");
      if (step.kind === "minimal") {
        log(device.id, $.i18n("BLOCK_UPDATE_MINIMAL"));
      }

      if (step.targetVersion) {
        log(
          device.id,
          $.i18n("BLOCK_UPDATE_ATTEMPT_TO_VERSION", step.targetVersion),
        );
        setPhase(
          device.id,
          $.i18n("BLOCK_UPDATE_ATTEMPT_TO_VERSION", step.targetVersion),
        );
      }

      log(device.id, $.i18n("BLOCK_OTAURL_SET_URL_FWURL") + step.otaUrl);
      await setOtaUrl(device.id, step.otaUrl);
      log(device.id, $.i18n("BLOCK_UPDATE_START"));
      await startUpgrade(device.id);
      log(
        device.id,
        $.i18n("BLOCK_UPDATE_SLEEPING", defaultSleepDuration / 1000),
      );
      await sleepWithCountdown(
        device.id,
        defaultSleepDuration,
        $.i18n("DEVICE_UPDATE_FLASHING"),
      );
      log(device.id, $.i18n("BLOCK_UPDATE_SUCCESS"));

      let upgradeSuccessful = false;
      const stepBeforeVersion = getReportedVersion(response);
      for (let i = 0; i < defaultTries; i++) {
        setPhase(
          device.id,
          $.i18n("DEVICE_UPDATE_VERIFYING", i + 1, defaultTries),
        );
        response = await checkStatus(device.id, defaultTries, {
          allowUnknownCommand: true,
        });

        if (
          isUpgradeStepComplete({
            stepKind: step.kind,
            targetVersion: step.targetVersion,
            beforeVersion: stepBeforeVersion,
            response,
          })
        ) {
          upgradeSuccessful = true;
          break;
        }

        const statusMessageKey = step.targetVersion
          ? "BLOCK_UPDATE_VERSION_NOT_AT_TARGET_VERSION"
          : "BLOCK_UPDATE_VERSION_NOT_CHANGED";
        log(device.id, $.i18n(statusMessageKey));
        log(
          device.id,
          $.i18n("BLOCK_UPDATE_SLEEPING", defaultSleepDuration / 1000),
        );
        await sleepWithCountdown(
          device.id,
          defaultSleepDuration,
          $.i18n("DEVICE_UPDATE_VERIFYING", i + 1, defaultTries),
        );
      }

      if (!upgradeSuccessful) {
        const failure = getFailureDetails({
          targetVersion: step.targetVersion,
          beforeVersion: stepBeforeVersion,
          currentVersion: describeReportedVersion(response),
        });
        const failureText = $.i18n(failure.key, ...failure.values);
        log(device.id, failureText, Level.error);
        setPhase(device.id, failureText);
        setState(device.id, State.error);
        return false;
      }

      setStepState(row, index, "done");
      log(
        device.id,
        $.i18n("BLOCK_UPDATE_VERSION_IS", describeReportedVersion(response)),
      );
      if (index === upgradePlan.steps.length - 1) {
        log(device.id, $.i18n("BLOCK_UPDATE_FINISH_SUCCESS"), Level.success);
      }
    }

    const duration = formatDuration((Date.now() - row.startedAt) / 1000);
    finishDeviceVersion(row, beforeVersion, describeReportedVersion(response));
    setPhase(
      device.id,
      `${$.i18n("BLOCK_UPDATE_FINISH_SUCCESS")} · ${$.i18n("DEVICE_UPDATE_DONE_IN", duration)}`,
    );
    setState(device.id, State.success);
    return true;
  } catch (e) {
    log(device.id, e.message, Level.error);
    setPhase(device.id, e.message);
    setState(device.id, State.error);
    return false;
  }
}

document.addEventListener("DOMContentLoaded", async () => {
  await waitForI18n();

  const container = document.getElementById(deviceContainerId);
  for (const device of devices) {
    const row = createDeviceRow(device);
    rows.set(device.id, row);
    container.appendChild(row.els.root);
  }
  refreshSummary();

  startedAt = Date.now();
  running = true;
  timerInterval = setInterval(tickCountdowns, 1000);

  if (config.update_fe_check) {
    const otaUrls = [
      ...new Set(
        Object.values(updateTargets)
          .flatMap((target) => [target?.otaUrl, target?.minimalOtaUrl])
          .filter(Boolean),
      ),
    ];

    for (const otaUrl of otaUrls) {
      if (!(await checkOtaUrlAccessible(otaUrl))) {
        for (const device of devices) {
          setPhase(
            device.id,
            $.i18n("DEVICE_UPDATE_OTA_NOT_ACCESSIBLE", otaUrl),
          );
          setState(device.id, State.error);
        }
        finishTimer();
        return;
      }
    }
  }
  await Promise.all(devices.map((device) => updateDevice(device)));
  completeIfDone();
});
