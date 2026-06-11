import {
  getHumidity,
  getTemp,
  getPressure,
  getSeaPressure,
  getGas,
  getDistance,
  getEnergyPower,
  getRefreshTime,
  chunkArray,
  onI18nReady,
} from "./app";
import toggleConfirmation from "./toggle_confirmation";

var longPressTimer;
const {
  confirmAction,
  createTouchConfirmController,
  getToggleConfirmationOptions,
  resolveToggleConfirmationSetting,
} = toggleConfirmation;

const refreshtime = getRefreshTime();

onI18nReady(function () {
  deviceTools();
  updateStatus();

  if (refreshtime) {
    console.log("[Global][Refreshtime]" + refreshtime + "ms");
    setInterval(function () {
      updateStatus();
    }, refreshtime);
  } else {
    console.log("[Global][Refreshtime]Dont refresh");
  }
});

function updateStatus() {
  const batchSize = config.request_concurrency;
  const boxes = $("#content .box_device:not(#all_off)").toArray();
  const batches = chunkArray(boxes, batchSize);

  console.log(
    `[Devices][updateStatus] Processing ${boxes.length} in ${batches.length} batches with batch size of ${batchSize}`,
  );

  processBatchesSequentially(batches);
}

function processBatchesSequentially(batches) {
  const processBatch = (batch) => {
    const promises = batch.map((box) => processBox($(box)));
    return Promise.all(promises); // Wait for all AJAX calls in the batch to complete
  };

  let promiseChain = Promise.resolve(); // Start with an empty promise
  batches.forEach((batch) => {
    promiseChain = promiseChain.then(() => processBatch(batch));
  });

  promiseChain.catch((error) => {
    console.error("Error processing batches:", error);
  });
}

function processBox($box) {
  return new Promise((resolve) => {
    let device_ip = $box.data("device_ip");
    let device_id = $box.data("device_id");
    let device_relais = $box.data("device_relais");
    let device_group = $box.data("device_group");

    if (!$box.hasClass("updating")) {
      console.log("[Start][updateStatus]get status from " + device_ip);

      if (device_group === "multi" && device_relais > 1) {
        console.log("[Start][updateStatus]skip multi " + device_ip);
        resolve(); // Skip this box
        return;
      }

      $box.addClass("updating");

      sonoff.getStatus(device_ip, device_id, function (data) {
        if (
          data &&
          !data.ERROR &&
          !data.WARNING &&
          data !== "" &&
          data !== undefined &&
          data.statusText === undefined
        ) {
          if (device_group === "multi") {
            $(
              '#content .box_device[data-device_group="multi"][data-device_ip="' +
                device_ip +
                '"]',
            ).each(function (key, groupbox) {
              let img = $(groupbox).find("img");
              let src =
                config.resource_url +
                "img/device_icons/" +
                img.data("icon") +
                "_%pw.png?v=160";

              let device_relais = $(groupbox).data("device_relais");
              let device_status = sonoff.parseDeviceStatus(data, device_relais);
              src = src.replace("%pw", device_status.toLowerCase());
              img.attr("src", src).parent().removeClass("animated");
              updateBox($(groupbox), data, device_status);
              $(groupbox)
                .removeClass("error")
                .find(".animated")
                .removeClass("animated");
              $(groupbox).removeClass("updating");
            });
          } else {
            let img = $box.find("img");
            let src =
              config.resource_url +
              "img/device_icons/" +
              img.data("icon") +
              "_%pw.png?v=160";

            let device_status = sonoff.parseDeviceStatus(data, 1);

            if (device_status !== undefined) {
              $box.data("device_state", device_status.toLowerCase());
              src = src.replace("%pw", device_status.toLowerCase());
              img.attr("src", src).parent().removeClass("animated");

              if (device_status === "NONE") {
                $box.data("device_group", "sensor");
              }
            }
            updateBox($box, data, device_status);
            $box.removeClass("error").find(".animated").removeClass("animated");
            $box.removeClass("updating");
          }
        } else {
          console.log("ERROR => " + JSON.stringify(data));

          if (device_group === "multi") {
            $(
              '#content .box_device[data-device_group="multi"][data-device_ip="' +
                device_ip +
                '"]',
            ).each(function (key, groupbox) {
              $(groupbox)
                .addClass("error")
                .find(".animated")
                .removeClass("animated");
              $(groupbox).removeClass("updating");
              let img = $(groupbox).find("img");
              let src =
                config.resource_url +
                "img/device_icons/" +
                img.data("icon") +
                "_error.png?v=160";
              img.attr("src", src);
            });
          } else {
            $box.addClass("error").find(".animated").removeClass("animated");
            $box.removeClass("updating");
            let img = $box.find("img");
            let src =
              config.resource_url +
              "img/device_icons/" +
              img.data("icon") +
              "_error.png?v=160";
            img.attr("src", src);
          }
        }
        resolve(); // Mark this box as processed
      });
    } else {
      console.log("[Start][updateStatus]skip get status from " + device_ip);
      resolve(); // Already updating, skip
    }
  });
}

function deviceTools() {
  const toggleConfirmationController = createTouchConfirmController();

  $("#content .box_device:not(#all_off)")
    .on("mousedown touchstart", function () {
      clearTimeout(longPressTimer);
      let device_box = $(this);

      if (device_box.hasClass("disabled")) {
        longPressTimer = setTimeout(function () {
          device_box.addClass("ignoreProtection");
          clearTimeout(longPressTimer);
          setTimeout(function () {
            device_box.addClass("ignoreProtection");
          }, 60 * 1000);
        }, 5 * 1000);
      }
    })
    .on("mouseup mouseleave touchend touchmove", function () {
      clearTimeout(longPressTimer);
    });

  $("#content .box_device:not(#all_off)").on("click", function (e) {
    e.preventDefault();

    let device_box = $(this);
    let device_ip = device_box.data("device_ip");
    let device_id = device_box.data("device_id");
    let device_relais = device_box.data("device_relais");
    let device_group = device_box.data("device_group");
    let device_state = device_box.data("device_state");
    let confirmationEnabled = resolveToggleConfirmationSetting(
      device_box.data("device_confirm_toggle"),
      config.confirm_device_toggles,
    );

    let device_protect_on = device_box.data("device_protect_on");
    let device_protect_off = device_box.data("device_protect_off");

    if (device_group === "sensor") {
      console.log(
        "[Start][updateStatus]skip sensor " + device_box.data("device_ip"),
      );
      return; //relais 1 will update all others
    }

    if ($(this).hasClass("toggled")) {
      console.log(
        "[Start][updateStatus]is toggling " + device_box.data("device_ip"),
      );
      return;
    }

    if (
      device_state === "on" &&
      device_protect_off === 1 &&
      !device_box.hasClass("ignoreProtection")
    ) {
      return;
    }
    if (
      device_state === "off" &&
      device_protect_on === 1 &&
      !device_box.hasClass("ignoreProtection")
    ) {
      return;
    }

    const nextStatusLabel =
      device_state === "on"
        ? $.i18n("SWITCH_STATE_OFF")
        : $.i18n("SWITCH_STATE_ON");
    const deviceName =
      device_box.find(".box_device_name").text().trim() || `#${device_id}`;

    void confirmAction({
      requiresConfirmation: confirmationEnabled,
      confirm: toggleConfirmationController.confirm,
      modalOptions: getToggleConfirmationOptions({
        i18n: $.i18n,
        deviceName,
        nextStatusLabel,
      }),
      onConfirm: function () {
        device_box.addClass("toggled");
        device_box.find("img").shake(3, 5, 500);

        sonoff.toggle(device_ip, device_id, device_relais, function (data) {
          if (data && !data.ERROR && !data.WARNING) {
            let img = device_box.find("img");
            let src =
              config.resource_url +
              "img/device_icons/" +
              img.data("icon") +
              "_%pw.png?v=160";

            let device_status = sonoff.parseDeviceStatus(data, device_relais);

            if (device_status !== undefined) {
              device_box.data("device_state", device_status.toLowerCase());

              src = src.replace("%pw", device_status.toLowerCase());
              img.attr("src", src);

              updateStatus();
            }

            img.parent().removeClass("animated");
            device_box.removeClass("error");
          } else {
            device_box.addClass("error");
            let img = device_box.find("img");
            let src =
              config.resource_url +
              "img/device_icons/" +
              img.data("icon") +
              "_error.png?v=160";
            img.attr("src", src).parent().removeClass("animated");
            console.log(
              "[Start][toggle]ERROR " + device_ip + " => " + data.ERROR ||
                "Unknown Error",
            );
          }
          $("#content .box_device").removeClass("toggled");
        });
      },
    });
  });

  $("#all_off").on("click", function (e) {
    e.preventDefault();
    const boxes = $("#content .box_device:not(#all_off)")
      .toArray()
      .filter(function (box) {
        const boxElement = $(box);
        let device_group = boxElement.data("device_group");
        let device_relais = boxElement.data("device_relais");
        let device_all_off = boxElement.data("device_all_off");

        if (device_group === "multi" && device_relais > 1) {
          return false;
        }

        if (device_group === "sensor") {
          return false;
        }

        return device_all_off === 1;
      });

    if (boxes.length === 0) {
      return;
    }

    const requiresConfirmation = boxes.some((box) =>
      resolveToggleConfirmationSetting(
        $(box).data("device_confirm_toggle"),
        config.confirm_device_toggles,
      ),
    );

    void confirmAction({
      requiresConfirmation,
      confirm: toggleConfirmationController.confirm,
      modalOptions: getToggleConfirmationOptions({
        i18n: $.i18n,
        allOff: true,
      }),
      onConfirm: function () {
        boxes.forEach(function (box) {
          let device_ip = $(box).data("device_ip");
          let device_id = $(box).data("device_id");
          let device_relais = $(box).data("device_relais");

          console.log(
            "[Start][updateStatus]get status from " + $(box).data("device_ip"),
          );

          sonoff.off(device_ip, device_id, device_relais, function (data) {
            if (data && !data.ERROR && !data.WARNING) {
              let img = $(box).find("img");
              let src =
                config.resource_url +
                "img/device_icons/" +
                img.data("icon") +
                "_%pw.png?v=160";

              let device_status = sonoff.parseDeviceStatus(data, device_relais);

              if (device_status !== undefined) {
                $(box).data("device_state", device_status.toLowerCase());

                src = src.replace("%pw", device_status.toLowerCase());
                img.attr("src", src);
              }
              img.parent().removeClass("animated");
              $(box).removeClass("error");
            } else {
              $(box).addClass("error");
              let img = $(box).find("img");
              let src =
                config.resource_url +
                "img/device_icons/" +
                img.data("icon") +
                "_error.png?v=160";
              img.attr("src", src).parent().removeClass("animated");
              console.log(
                "[Start][toggle]ERROR " + device_ip + " => " + data.ERROR ||
                  "Unknown Error",
              );
            }
          });
        });
      },
    });
  });
}

function updateBox(row, data, device_status) {
  let device_protect_on = $(row).data("device_protect_on");
  let device_protect_off = $(row).data("device_protect_off");

  data = sonoff.parseStatusData(data);
  let infoBoxCounter = 1;
  let temp = getTemp(data, ", ");

  if (temp !== "") {
    $(row)
      .find(".info-" + infoBoxCounter + " span")
      .html(temp)
      .parent()
      .removeClass("hidden");
    infoBoxCounter++;
  }
  let humidity = getHumidity(data, ", ");

  if (humidity !== "") {
    $(row)
      .find(".info-" + infoBoxCounter + " span")
      .html(humidity)
      .parent()
      .removeClass("hidden");
    infoBoxCounter++;
  }
  let pressure = getPressure(data, ", ");

  if (pressure !== "") {
    $(row)
      .find(".info-" + infoBoxCounter + " span")
      .html(pressure)
      .parent()
      .removeClass("hidden");
    infoBoxCounter++;
  }

  let seapressure = getSeaPressure(data, ", ");

  if (seapressure !== "") {
    $(row)
      .find(".info-" + infoBoxCounter + " span")
      .html(seapressure)
      .parent()
      .removeClass("hidden");
    infoBoxCounter++;
  }

  let distance = getDistance(data, ", ");

  if (distance !== "") {
    $(row)
      .find(".info-" + infoBoxCounter + " span")
      .html(distance)
      .parent()
      .removeClass("hidden");
    infoBoxCounter++;
  }

  let energyPower = getEnergyPower(data);

  if (energyPower !== "") {
    $(row)
      .find(".info-" + infoBoxCounter + " span")
      .html(energyPower)
      .parent()
      .removeClass("hidden");
    infoBoxCounter++;
  }

  let gas = getGas(data, ", ");

  if (gas !== "") {
    $(row)
      .find(".info-" + infoBoxCounter + " span")
      .html(gas)
      .parent()
      .removeClass("hidden");
    infoBoxCounter++;
  }

  let idx = data.idx ? data.idx : "";

  if (idx !== "") {
    $(row).find(".idx span").html(idx);
    $("#device-list .idx").removeClass("hidden").show();
  }

  $(row).find(".version span").html(data.StatusFWR.Version);

  $(row).data("device_state", device_status.toLowerCase());

  if (device_status === "ON") {
    $(row)
      .find(".status")
      .find("input")
      .prop("checked", "checked")
      .parent()
      .removeClass("error");

    if (device_protect_off === 1) {
      $(row).addClass("disabled");
    } else {
      $(row).removeClass("disabled");
    }
  } else {
    $(row)
      .find(".status")
      .find("input")
      .removeProp("checked")
      .parent()
      .removeClass("error");

    if (device_protect_on === 1) {
      $(row).addClass("disabled");
    } else {
      $(row).removeClass("disabled");
    }
  }

  //MORE
  $(row)
    .find(".hostname span")
    .html(
      data.StatusNET.Hostname !== undefined ? data.StatusNET.Hostname : "?",
    );
  $(row)
    .find(".mac span")
    .html(data.StatusNET.Mac !== undefined ? data.StatusNET.Mac : "?");
  $(row)
    .find(".mqtt span")
    .html(data.StatusMQT !== undefined ? "1" : "0");
  $(row)
    .find(".poweronstate span")
    .html(data?.Status?.PowerOnState ?? "?");
  $(row)
    .find(".ledstate span")
    .html(data?.Status?.LedState ?? "?");
  $(row)
    .find(".savedata span")
    .html(data?.Status?.SaveData ?? "?");
  $(row)
    .find(".sleep span")
    .html(
      data.StatusPRM.Sleep !== undefined ? data.StatusPRM.Sleep + "ms" : "?",
    );
  $(row)
    .find(".bootcount span")
    .html(
      data.StatusPRM.BootCount !== undefined ? data.StatusPRM.BootCount : "?",
    );
  $(row)
    .find(".savecount span")
    .html(
      data.StatusPRM.SaveCount !== undefined ? data.StatusPRM.SaveCount : "?",
    );
  $(row)
    .find(".log span")
    .html(
      (data.StatusLOG.SerialLog !== undefined
        ? data.StatusLOG.SerialLog
        : "?") +
        "|" +
        (data.StatusLOG.WebLog !== undefined ? data.StatusLOG.WebLog : "?") +
        "|" +
        (data.StatusLOG.SysLog !== undefined ? data.StatusLOG.SysLog : "?"),
    );
  $(row)
    .find(".wificonfig span")
    .html(
      data.StatusNET.WifiConfig !== undefined ? data.StatusNET.WifiConfig : "?",
    );
  $(row)
    .find(".vcc span")
    .html(data.StatusSTS.Vcc !== undefined ? data.StatusSTS.Vcc + "V" : "?");

  $(row).removeClass("updating");
}
