const test = require("node:test");
const assert = require("node:assert/strict");

const {
  shouldTreatStatusAsSuccessful,
  getFailureDetails,
} = require("../../resources/js/device_update_logic.js");

test("manual uploads stay pending while the device reports its pre-update version", () => {
  assert.equal(
    shouldTreatStatusAsSuccessful({
      beforeVersion: "14.4.0(TasmoCompiler-esp8266generic)",
      currentVersion: "14.4.0(TasmoCompiler-esp8266generic)",
      targetVersion: "",
    }),
    false,
  );
});

test("manual uploads succeed once the reported version changes", () => {
  assert.equal(
    shouldTreatStatusAsSuccessful({
      beforeVersion: "14.4.0(TasmoCompiler-esp8266generic)",
      currentVersion: "14.4.1(TasmoCompiler-esp8266generic)",
      targetVersion: "",
    }),
    true,
  );
});

test("targeted uploads still require the exact target version", () => {
  assert.equal(
    shouldTreatStatusAsSuccessful({
      beforeVersion: "14.4.0(tasmota)",
      currentVersion: "14.4.1(tasmota)",
      targetVersion: "14.4.2",
    }),
    false,
  );
});

test("manual upload failures report the unchanged version context", () => {
  assert.deepEqual(
    getFailureDetails({
      beforeVersion: "14.4.0(tasmota)",
      currentVersion: "14.4.0(tasmota)",
      targetVersion: "",
    }),
    {
      key: "BLOCK_UPDATE_ERROR_VERSION_NOT_CHANGED",
      values: ["14.4.0(tasmota)"],
    },
  );
});
