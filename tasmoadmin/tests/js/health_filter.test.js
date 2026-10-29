const test = require("node:test");
const assert = require("node:assert/strict");
const {
  MIN_REFRESH_MS,
  getHealthRefreshInterval,
  matchesSearch,
} = require("../../resources/js/health_filter.js");

test("matchesSearch matches keywords case-insensitively", () => {
  assert.equal(matchesSearch("Kitchen Plug 192.168.1.20", "kitchen"), true);
  assert.equal(matchesSearch("Kitchen Plug 192.168.1.20", "1.20"), true);
  assert.equal(matchesSearch("Kitchen Plug 192.168.1.20", "garage"), false);
});

test("matchesSearch treats an empty term as a match", () => {
  assert.equal(matchesSearch("anything", "   "), true);
  assert.equal(matchesSearch(undefined, ""), true);
});

test("matchesSearch does not throw on regex characters", () => {
  assert.equal(matchesSearch("Lamp (hall)", "(hall"), true);
  assert.equal(matchesSearch(undefined, "["), false);
});

test("getHealthRefreshInterval keeps a minimum and respects no refresh", () => {
  assert.equal(getHealthRefreshInterval(false), false);
  assert.equal(getHealthRefreshInterval(3000), MIN_REFRESH_MS);
  assert.equal(getHealthRefreshInterval(60000), 60000);
});
