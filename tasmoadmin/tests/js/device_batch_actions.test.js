const test = require("node:test");
const assert = require("node:assert/strict");
const {
  getBatchActionConfig,
  shouldSubmitBatchForm,
  validateBatchAction,
} = require("../../resources/js/device_batch_actions.js");

test("getBatchActionConfig returns command metadata", () => {
  assert.deepEqual(getBatchActionConfig("command"), {
    action: "command",
    requiresCommand: true,
    requiresFile: false,
    submitLabelKey: "SEND_COMMAND",
  });
});

test("getBatchActionConfig returns backup metadata", () => {
  assert.deepEqual(getBatchActionConfig("backup"), {
    action: "backup",
    requiresCommand: false,
    requiresFile: false,
    submitLabelKey: "BTN_START_BACKUP",
  });
});

test("getBatchActionConfig returns restore metadata", () => {
  assert.deepEqual(getBatchActionConfig("restore"), {
    action: "restore",
    requiresCommand: false,
    requiresFile: true,
    submitLabelKey: "BTN_START_RESTORE",
  });
});

test("getBatchActionConfig returns restart metadata", () => {
  assert.deepEqual(getBatchActionConfig("restart"), {
    action: "restart",
    requiresCommand: false,
    requiresFile: false,
    submitLabelKey: "RESTART_SELECTED",
  });
});

test("validateBatchAction requires a known action first", () => {
  assert.equal(
    validateBatchAction({
      action: "",
      selectedDeviceIds: ["1"],
    }),
    "PLEASE_SELECT",
  );
});

test("validateBatchAction requires a selected device", () => {
  assert.equal(
    validateBatchAction({
      action: "delete",
      selectedDeviceIds: [],
    }),
    "ERROR_COMMAND_NO_DEVICE_SELECTED",
  );
});

test("validateBatchAction requires a command for command batches", () => {
  assert.equal(
    validateBatchAction({
      action: "command",
      selectedDeviceIds: ["1"],
      command: "   ",
    }),
    "ERROR_PLS_ENTER_COMMAND",
  );
});

test("validateBatchAction accepts valid delete, restart and command actions", () => {
  assert.equal(
    validateBatchAction({
      action: "delete",
      selectedDeviceIds: ["1"],
    }),
    null,
  );
  assert.equal(
    validateBatchAction({
      action: "command",
      selectedDeviceIds: ["1"],
      command: "Status 0",
    }),
    null,
  );
  assert.equal(
    validateBatchAction({
      action: "backup",
      selectedDeviceIds: ["1"],
      uploadedFileName: "",
    }),
    null,
  );
  assert.equal(
    validateBatchAction({
      action: "restore",
      selectedDeviceIds: ["1"],
      uploadedFileName: "config.dmp",
    }),
    null,
  );
  assert.equal(
    validateBatchAction({
      action: "restart",
      selectedDeviceIds: ["1"],
    }),
    null,
  );
});

test("validateBatchAction requires exactly one device and a file for restore", () => {
  assert.equal(
    validateBatchAction({
      action: "restore",
      selectedDeviceIds: ["1", "2"],
      uploadedFileName: "config.dmp",
    }),
    "ERROR_RESTORE_SINGLE_DEVICE_ONLY",
  );
  assert.equal(
    validateBatchAction({
      action: "restore",
      selectedDeviceIds: ["1"],
      uploadedFileName: "   ",
    }),
    "ERROR_RESTORE_SELECT_FILE",
  );
});

test("only backup and restore submit the batch form", () => {
  assert.equal(shouldSubmitBatchForm("command"), false);
  assert.equal(shouldSubmitBatchForm("delete"), false);
  assert.equal(shouldSubmitBatchForm("restart"), false);
  assert.equal(shouldSubmitBatchForm("backup"), true);
  assert.equal(shouldSubmitBatchForm("restore"), true);
});
