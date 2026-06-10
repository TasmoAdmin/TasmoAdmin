const test = require("node:test");
const assert = require("node:assert/strict");
const {
  getBatchActionConfig,
  validateBatchAction,
} = require("../../resources/js/device_batch_actions.js");

test("getBatchActionConfig returns command metadata", () => {
  assert.deepEqual(getBatchActionConfig("command"), {
    action: "command",
    requiresCommand: true,
    submitLabelKey: "SEND_COMMAND",
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

test("validateBatchAction accepts valid delete and command actions", () => {
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
});
