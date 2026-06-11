const test = require("node:test");
const assert = require("node:assert/strict");

const {
  getAutomaticNightmodeState,
  getNextNightmodeOverride,
  normalizeNightmodeOverride,
  resolveNightmodeEnabled,
} = require("../../resources/js/nightmode.js");

test("auto nightmode is enabled in the evening", () => {
  assert.equal(
    getAutomaticNightmodeState(new Date("2026-06-11T19:00:00")),
    true,
  );
});

test("auto nightmode is disabled during the day", () => {
  assert.equal(
    getAutomaticNightmodeState(new Date("2026-06-11T12:00:00")),
    false,
  );
});

test("manual night override wins over disabled config", () => {
  assert.equal(resolveNightmodeEnabled("disable", "night"), true);
});

test("manual day override wins over always config", () => {
  assert.equal(resolveNightmodeEnabled("always", "day"), false);
});

test("invalid override falls back to config mode", () => {
  assert.equal(
    resolveNightmodeEnabled(
      "auto",
      "invalid",
      new Date("2026-06-11T12:00:00"),
    ),
    false,
  );
});

test("toggle switches from day to night override", () => {
  assert.equal(getNextNightmodeOverride(false), "night");
});

test("normalizeNightmodeOverride accepts only day and night", () => {
  assert.equal(normalizeNightmodeOverride("day"), "day");
  assert.equal(normalizeNightmodeOverride("night"), "night");
  assert.equal(normalizeNightmodeOverride("auto"), null);
});
