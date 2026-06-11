const test = require("node:test");
const assert = require("node:assert/strict");

const {
  getSortableIpCellValue,
  getSortableIpValue,
} = require("../../resources/js/ip_sort.js");

test("IPv4 addresses are converted to sortable numeric values", () => {
  assert.ok(getSortableIpValue("192.168.178.63") > getSortableIpValue("5.0.0.1"));
  assert.ok(getSortableIpValue("10.0.0.2") < getSortableIpValue("10.0.0.15"));
});

test("invalid IP values fall back to null", () => {
  assert.equal(getSortableIpValue("not-an-ip"), null);
  assert.equal(getSortableIpValue("256.1.1.1"), null);
  assert.equal(getSortableIpValue("10.0.0"), null);
});

test("IP sort prefers the visible IP text from the tablesaw cell", () => {
  const lowIpCell = {
    querySelector(selector) {
      assert.equal(selector, ".device-ip-text");
      return { textContent: "2.0.0.1" };
    },
  };
  const highIpCell = {
    querySelector() {
      return { textContent: "10.0.0.1" };
    },
  };

  assert.ok(
    getSortableIpCellValue("335544332.0.0.1", lowIpCell) <
      getSortableIpCellValue("16777216110.0.0.1", highIpCell),
  );
});
