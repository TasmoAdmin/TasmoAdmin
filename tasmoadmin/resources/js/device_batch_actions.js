function getBatchActionConfig(action) {
  if (action === "command") {
    return {
      action,
      requiresCommand: true,
      requiresFile: false,
      submitLabelKey: "SEND_COMMAND",
    };
  }

  if (action === "delete") {
    return {
      action,
      requiresCommand: false,
      requiresFile: false,
      submitLabelKey: "DELETE_SELECTED",
    };
  }

  if (action === "restart") {
    return {
      action,
      requiresCommand: false,
      requiresFile: false,
      submitLabelKey: "RESTART_SELECTED",
    };
  }

  if (action === "backup") {
    return {
      action,
      requiresCommand: false,
      requiresFile: false,
      submitLabelKey: "BTN_START_BACKUP",
    };
  }

  if (action === "restore") {
    return {
      action,
      requiresCommand: false,
      requiresFile: true,
      submitLabelKey: "BTN_START_RESTORE",
    };
  }

  return null;
}

function validateBatchAction({
  action = "",
  selectedDeviceIds = [],
  command = "",
  uploadedFileName = "",
} = {}) {
  const actionConfig = getBatchActionConfig(action);

  if (actionConfig === null) {
    return "PLEASE_SELECT";
  }

  if (selectedDeviceIds.length === 0) {
    return "ERROR_COMMAND_NO_DEVICE_SELECTED";
  }

  if (actionConfig.requiresCommand && command.trim() === "") {
    return "ERROR_PLS_ENTER_COMMAND";
  }

  if (action === "restore" && selectedDeviceIds.length !== 1) {
    return "ERROR_RESTORE_SINGLE_DEVICE_ONLY";
  }

  if (actionConfig.requiresFile && uploadedFileName.trim() === "") {
    return "ERROR_RESTORE_SELECT_FILE";
  }

  return null;
}

function shouldSubmitBatchForm(action = "") {
  return action === "backup" || action === "restore";
}

module.exports = {
  getBatchActionConfig,
  shouldSubmitBatchForm,
  validateBatchAction,
};
