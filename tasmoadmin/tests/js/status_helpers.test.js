const test = require("node:test");
const assert = require("node:assert/strict");
const {
  normalizeStatusData,
  getRuntimeInfo,
  parseUptimeToSeconds,
  extractFirstNumericValue,
  getEnergyPower,
  getIlluminance,
  getSortableVersionValue,
} = require("../../resources/js/status_helpers.js");

test("normalizeStatusData fills missing sections for ethernet payloads", () => {
  const payload = {
    StatusPRM: {
      Uptime: "87T15:29:29",
    },
    StatusFWR: {
      Version: "9.4.0.4(ethernet)",
    },
    StatusNET: {
      Hostname: "Letterbox-6744_eth",
      WifiConfig: 0,
    },
    StatusSNS: {
      Time: "2021-12-04T04:11:22",
      Switch1: "OFF",
    },
  };

  const data = normalizeStatusData(payload);

  assert.deepEqual(data.StatusLOG, {});
  assert.deepEqual(data.StatusSTS, {});
  assert.equal(data.StatusMQT, undefined);
  assert.equal(data.hasWifi, false);
  assert.equal(
    getRuntimeInfo(data, {
      day: "d",
      hour: "h",
      minute: "m",
      second: "s",
    }).text,
    "87T15:29:29",
  );
  assert.equal(
    getRuntimeInfo(data, {
      day: "d",
      hour: "h",
      minute: "m",
      second: "s",
    }).sortValue,
    7572569,
  );
});

test("normalizeStatusData keeps wifi details when wifi status is present", () => {
  const data = normalizeStatusData({
    StatusPRM: {
      Uptime: "2T00:00:00",
    },
    StatusSTS: {
      Wifi: {
        RSSI: 64,
        SSId: "home-net",
      },
    },
  });

  assert.equal(data.hasWifi, true);
  assert.deepEqual(data.wifi, {
    rssi: 64,
    ssid: "home-net",
    uptime: "2T00:00:00",
  });
});

test("getIlluminance extracts illuminance readings from sensor payloads", () => {
  assert.equal(
    getIlluminance(
      {
        StatusSNS: {
          Time: "2021-12-04T04:11:22",
          BH1750: {
            Illuminance: 40,
          },
          "BH1750-1": {
            Illuminance: 125,
          },
        },
      },
      " | ",
    ),
    "40 lx | 125 lx",
  );
});

test("getEnergyPower keeps the visible energy order stable", () => {
  assert.equal(
    getEnergyPower(
      {
        StatusSNS: {
          ENERGY: {
            Power: 105,
            Today: 0.329,
            Yesterday: 0.513,
            Total: 12.345,
            Current: 0.456,
          },
        },
      },
      " | ",
    ),
    "105 W | 0.329 / 0.513 / 12.345 kWh | 0.456 A",
  );
});

test("getRuntimeInfo returns a numeric sort value for startup-derived runtime", () => {
  const info = getRuntimeInfo(
    {
      StatusPRM: {
        StartupDateTimeUtc: "2026-06-11T10:00:00",
      },
    },
    {
      day: "d",
      hour: "h",
      minute: "m",
      second: "s",
    },
    new Date("2026-06-11T12:03:04Z"),
  );

  assert.equal(info.text, "2h 3m 4s");
  assert.equal(info.sortValue, 7384);
});

test("parseUptimeToSeconds parses Tasmota uptime strings", () => {
  assert.equal(parseUptimeToSeconds("87T15:29:29"), 7572569);
  assert.equal(parseUptimeToSeconds("00:47:59"), 2879);
  assert.equal(parseUptimeToSeconds("?"), null);
});

test("extractFirstNumericValue reads the first sortable number from formatted values", () => {
  assert.equal(extractFirstNumericValue("105 W / 0.329 / 0.513 / 12.345 kWh"), 105);
  assert.equal(extractFirstNumericValue("-4.5°C<br/>12.3°C"), -4.5);
  assert.equal(extractFirstNumericValue("3.21V"), 3.21);
  assert.equal(extractFirstNumericValue("?"), null);
});

test("getSortableVersionValue normalizes version strings for semantic sorting", () => {
  assert.equal(
    getSortableVersionValue("14.4.0.1(tasmota32)") >
      getSortableVersionValue("9.5.0.4(ethernet)"),
    true,
  );
  assert.equal(
    getSortableVersionValue("14.4.10(tasmota)") >
      getSortableVersionValue("14.4.2(tasmota)"),
    true,
  );
  assert.equal(getSortableVersionValue("?"), null);
});
