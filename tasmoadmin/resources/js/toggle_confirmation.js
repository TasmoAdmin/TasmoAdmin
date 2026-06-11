function normalizeBoolean(value, fallback = false) {
  if (value === undefined || value === null || value === "") {
    return fallback;
  }

  if (typeof value === "boolean") {
    return value;
  }

  if (typeof value === "number") {
    return value === 1;
  }

  if (typeof value === "string") {
    const normalizedValue = value.trim().toLowerCase();
    if (["1", "true", "yes", "on"].includes(normalizedValue)) {
      return true;
    }

    if (["0", "false", "no", "off"].includes(normalizedValue)) {
      return false;
    }
  }

  return fallback;
}

function resolveToggleConfirmationSetting(
  deviceSetting,
  globalSetting = false,
) {
  return normalizeBoolean(deviceSetting, normalizeBoolean(globalSetting));
}

function getToggleConfirmationOptions({
  i18n,
  deviceName = "",
  nextStatusLabel = "",
  allOff = false,
}) {
  return {
    title: i18n("CONFIRM_SWITCHES"),
    body: allOff
      ? i18n("CONFIRM_ALL_OFF")
      : i18n("CONFIRM_DEVICE_TOGGLE", deviceName, nextStatusLabel),
    confirmLabel: i18n("CONFIRM"),
    cancelLabel: i18n("CANCEL"),
  };
}

function createTouchConfirmController({
  documentRef = globalThis.document,
  bootstrapRef = globalThis.window?.bootstrap,
} = {}) {
  const modalElement = documentRef?.getElementById?.("touchToggleConfirmModal");
  if (!modalElement || !bootstrapRef?.Modal) {
    return {
      confirm: () => Promise.resolve(true),
    };
  }

  const titleElement = modalElement.querySelector(
    ".toggle-confirm-modal-title",
  );
  const bodyElement = modalElement.querySelector(".toggle-confirm-modal-body");
  const confirmButton = modalElement.querySelector(
    ".toggle-confirm-modal-confirm",
  );
  const cancelButton = modalElement.querySelector(
    ".toggle-confirm-modal-cancel",
  );
  const modal = bootstrapRef.Modal.getOrCreateInstance(modalElement);

  let activeResolve = null;
  let confirmed = false;

  confirmButton?.addEventListener("click", function () {
    confirmed = true;
    modal.hide();
  });

  cancelButton?.addEventListener("click", function () {
    confirmed = false;
  });

  modalElement.addEventListener("hidden.bs.modal", function () {
    if (activeResolve === null) {
      return;
    }

    const resolve = activeResolve;
    activeResolve = null;
    resolve(confirmed);
    confirmed = false;
  });

  return {
    confirm({
      title = "",
      body = "",
      confirmLabel = "",
      cancelLabel = "",
    } = {}) {
      if (activeResolve !== null) {
        activeResolve(false);
        activeResolve = null;
      }

      confirmed = false;

      if (titleElement) {
        titleElement.textContent = title;
      }
      if (bodyElement) {
        bodyElement.textContent = body;
      }
      if (confirmButton) {
        confirmButton.textContent = confirmLabel;
      }
      if (cancelButton) {
        cancelButton.textContent = cancelLabel;
      }

      return new Promise((resolve) => {
        activeResolve = resolve;
        modal.show();
      });
    },
  };
}

function confirmAction({
  requiresConfirmation,
  confirm,
  modalOptions,
  onConfirm,
}) {
  const execute = () => {
    if (typeof onConfirm === "function") {
      onConfirm();
    }

    return true;
  };

  if (!requiresConfirmation) {
    return Promise.resolve(execute());
  }

  const confirmFn =
    typeof confirm === "function" ? confirm : () => Promise.resolve(true);

  return Promise.resolve(confirmFn(modalOptions)).then((isConfirmed) => {
    if (!isConfirmed) {
      return false;
    }

    return execute();
  });
}

module.exports = {
  confirmAction,
  createTouchConfirmController,
  getToggleConfirmationOptions,
  normalizeBoolean,
  resolveToggleConfirmationSetting,
};
