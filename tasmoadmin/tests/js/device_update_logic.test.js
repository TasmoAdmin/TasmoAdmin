const test = require("node:test");
const assert = require("node:assert/strict");

const {
  detectDevicePlatform,
  resolveUpdateTarget,
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

test("platform detection keeps ESP8266 devices on ESP8266 firmware targets", () => {
  assert.equal(
    detectDevicePlatform({
      StatusFWR: {
        Hardware: "ESP8285",
        Version: "14.4.0(tasmota)",
      },
    }),
    "esp8266",
  );
});

test("platform detection identifies ESP32 devices from hardware info", () => {
  assert.equal(
    detectDevicePlatform({
      StatusFWR: {
        Hardware: "ESP32-S3",
        Version: "14.4.0(tasmota32s3)",
      },
    }),
    "esp32",
  );
});

test("target resolution selects the matching platform firmware", () => {
  assert.deepEqual(
    resolveUpdateTarget(
      {
        esp8266: {
          otaUrl: "http://example.test/tasmota.bin.gz",
          targetVersion: "14.4.1",
        },
        esp32: {
          otaUrl: "http://example.test/tasmota32.bin",
          targetVersion: "14.4.2",
        },
      },
      {
        StatusFWR: {
          Hardware: "ESP32-D0WDQ6",
          Version: "14.4.0(tasmota32)",
        },
      },
    ),
    {
      otaUrl: "http://example.test/tasmota32.bin",
      targetVersion: "14.4.2",
    },
  );
});

test("target resolution falls back to the default target for manual uploads", () => {
  assert.deepEqual(
    resolveUpdateTarget(
      {
        default: {
          otaUrl: "http://example.test/upload.bin",
          targetVersion: "",
        },
      },
      {
        StatusFWR: {
          Hardware: "ESP8285",
          Version: "14.4.0(tasmota)",
        },
      },
    ),
    {
      otaUrl: "http://example.test/upload.bin",
      targetVersion: "",
    },
  );
});
