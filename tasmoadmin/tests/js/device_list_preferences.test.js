const test = require("node:test");
const assert = require("node:assert/strict");

const {
  parseHiddenColumns,
  serializeHiddenColumns,
} = require("../../resources/js/device_list_preferences.js");

test("parseHiddenColumns keeps unique known columns in cookie order", () => {
  assert.deepEqual(
    parseHiddenColumns("ip,runtime,ip,unknown", ["ip", "runtime", "rssi"]),
    ["ip", "runtime"],
  );
});

test("serializeHiddenColumns removes duplicates and blanks", () => {
  assert.equal(
    serializeHiddenColumns(["ip", "runtime", "ip", ""]),
    "ip,runtime",
  );
});
