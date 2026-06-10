const test = require("node:test");
const assert = require("node:assert/strict");
const {
  normalizeStatusData,
  getRuntimeInfo,
  getIlluminance,
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
