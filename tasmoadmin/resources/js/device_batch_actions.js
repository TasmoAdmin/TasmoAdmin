function getBatchActionConfig(action) {
  if (action === "command") {
    return {
      action,
      requiresCommand: true,
      submitLabelKey: "SEND_COMMAND",
    };
  }

  if (action === "delete") {
    return {
      action,
      requiresCommand: false,
      submitLabelKey: "DELETE_SELECTED",
    };
  }

  if (action === "restart") {
    return {
      action,
      requiresCommand: false,
      submitLabelKey: "RESTART_SELECTED",
    };
  }

  if (action === "backup") {
    return {
      action,
      requiresCommand: false,
      submitLabelKey: "BTN_START_BACKUP",
    };
  }

  return null;
}

function validateBatchAction({
  action = "",
  selectedDeviceIds = [],
  command = "",
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

  return null;
}

function shouldSubmitBatchForm(action = "") {
  return action === "backup";
}

module.exports = {
  getBatchActionConfig,
  shouldSubmitBatchForm,
  validateBatchAction,
};
