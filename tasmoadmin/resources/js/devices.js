import {
  getHumidity,
  getTemp,
  getPressure,
  getSeaPressure,
  getGas,
  getDistance,
  getEnergyPower,
  getRefreshTime,
} from "./app";
const refreshtime = getRefreshTime();

var ignoreProtectionsTimer;
$(document).ready(function () {
  deviceTools();
  updateAllStatus();

  initCommandHelper();

  if ($(".device-search").length > 0) {
    initDeviceFilter();
  }

  $(".showmore").on("change", function (e) {
    if ($(this).prop("checked")) {
      $(".showmore").prop("checked", true);
      Cookies.set("devices_show_more", "1");
      $("#device-list .more:not(.hidden)").show();
      $("label[for=" + $(".showmore").attr("id") + "]")
        .removeClass("btn-secondary")
        .addClass("btn-primary");
    } else {
      $(".showmore").prop("checked", false);
      Cookies.set("devices_show_more", "0");
      $("#device-list .more").hide();
      $("label[for=" + $(".showmore").attr("id") + "]")
        .removeClass("btn-primary")
        .addClass("btn-secondary");
    }
    $(".doubleScroll-scroll")
      .css({
        width: $("#device-list").width(),
      })
      .parent()
      .trigger("resize");
  });

  $(".ignoreProtections").on("change", function (e) {
    clearTimeout(ignoreProtectionsTimer);

    if ($(this).prop("checked")) {
      ignoreProtectionsTimer = setTimeout(function (e) {
        $(".ignoreProtections").prop("checked", false);
        $("label[for=" + $(".ignoreProtections").attr("id") + "]")
          .removeClass("btn-primary")
          .addClass("btn-secondary");
        $("label[for=" + $(".ignoreProtections").attr("id") + "]")
          .find("i")
          .removeClass("fa-lock-open");
      }, 60 * 1000);

      $(".ignoreProtections").prop("checked", true);
      $("label[for=" + $(".ignoreProtections").attr("id") + "]")
        .removeClass("btn-secondary")
        .addClass("btn-primary");
      $("label[for=" + $(".ignoreProtections").attr("id") + "]")
        .find("i")
        .addClass("fa-lock-open");
    } else {
      $(".ignoreProtections").prop("checked", false);
      $("label[for=" + $(".ignoreProtections").attr("id") + "]")
        .removeClass("btn-primary")
        .addClass("btn-secondary");
      $("label[for=" + $(".ignoreProtections").attr("id") + "]")
        .find("i")
        .removeClass("fa-lock-open");
    }
  });

  if (
    Cookies.get("devices_show_more") !== undefined &&
    Cookies.get("devices_show_more") == "1"
  ) {
    $(".showmore").prop("checked", true);
    $("#device-list .more:not(.hidden)").show();
    $("label[for=" + $(".showmore").attr("id") + "]")
      .removeClass("btn-secondary")
      .addClass("btn-primary");
    $(".doubleScroll-scroll")
      .css({
        width: $("#device-list").width(),
      })
      .parent()
      .trigger("resize");
  }

  $(".table-responsive").attachDragger();
  if (refreshtime) {
    console.log("[Global][Refreshtime]" + refreshtime + "ms");
    setInterval(function () {
      console.log("[Global][Refreshtime] updateStatus now");
      //updateStatus();
      updateStatus();
    }, refreshtime);
  } else {
    console.log("[Global][Refreshtime] " + $.i18n("NO_REFRESH") + "");
  }
});

function initCommandHelper() {
  $(".showCommandInput").on("click", function (e) {
    $(this).toggleClass("btn-secondary").toggleClass("btn-primary");
    $(".command-hidden").toggleClass("d-none");
    $(".delete-hidden").addClass("d-none");
    $(".showDelete").toggleClass("btn-primary").toggleClass("btn-secondary");
    $(".cmd_cb").toggleClass("d-none").find("input").prop("checked", false);
    $(".cmdContainer ").removeClass("has-error");
    $(".cmdContainer ").find("input").val("");
    $(".cmdContainer ").find("#commandInputError").html("");
  });

  $(".showDelete").on("click", function (e) {
    $(this).toggleClass("btn-secondary").toggleClass("btn-primary");
    $(".delete-hidden").toggleClass("d-none");
    $(".command-hidden").addClass("d-none");
    $(".showCommand").toggleClass("btn-primary").toggleClass("btn-secondary");
    $(".cmd_cb").toggleClass("d-none").find("input").prop("checked", false);
    $(".deleteContainer ").removeClass("has-error");
    $(".deleteContainer ").find("input").val("");
    $(".cmdContainer ").find("#commandInputError").html("");
  });

  $(".cmdContainer ")
    .find("input")
    .keypress(function (e) {
      if (e.which == 13) {
        //Enter key pressed
        $(".sendCommand").click(); //Trigger search button click event
      }
    });

  $(".select_all").change(function () {
    let status = this.checked;
    $("#device-list tr:not(.d-none) .device_checkbox:not(:disabled)").each(
      function () {
        this.checked = status;
      },
    );

    $(".select_all").each(function () {
      this.checked = status;
    });
  });

  $(".sendDelete").on("click", function (e) {
    const selectedDevices = getSelectedDevices();
    $.each(selectedDevices, function (_idx, deviceId) {
      $.get(`${config.base_url}device_action/delete/${deviceId}`);
    });

    location.reload();
  });

  $(".sendCommand").on("click", function (e) {
    $(this)
      .parent()
      .parent()
      .removeClass("has-error")
      .find("#commandInputError")
      .addClass("d-none")
      .html("");

    let selectedDevices = getSelectedDevices();
    if (selectedDevices.length === 0) {
      $(this)
        .parent()
        .parent()
        .addClass("has-error")
        .find("#commandInputError")
        .removeClass("d-none")
        .html($.i18n("ERROR_COMMAND_NO_DEVICE_SELECTED"));
      return false;
    }

    let cmnd = $(this).parent().parent().find(".commandInput").val();
    if (cmnd === "") {
      $(this)
        .parent()
        .parent()
        .addClass("has-error")
        .find("#commandInputError")
        .removeClass("d-none")
        .html($.i18n("ERROR_PLS_ENTER_COMMAND"));
      return false;
    }

    $.each(selectedDevices, function (idx, device_id) {
      sonoff.generic(device_id, cmnd, undefined, function (result) {
        let device_name = $("[data-device_id=" + device_id + "]:first")
          .find(".device_name a")
          .text()
          .trim();
        $("#commandInputError").append(
          "ID " +
            device_id +
            " (" +
            device_name +
            ") => " +
            JSON.stringify(result) +
            "<br/>",
        );
      });
    });

    $(this)
      .parent()
      .parent()
      .find("#commandInputError")
      .removeClass("d-none")
      .append($.i18n("SUCCESS_COMMAND_SEND") + "</br>");
  });
}

function getSelectedDevices() {
  return $.map(
    $(".cmd_cb:not(.link ) input:not(.select_all):checked"),
    function (elem, idx) {
      let d = new Array($(elem).val());
      return d;
    },
  );
}
function updateStatus() {
  $("#device-list tbody tr").each(function (key, tr) {
    let device_ip = $(tr).data("device_ip");
    let device_id = $(tr).data("device_id");
    let device_relais = $(tr).data("device_relais");
    let device_group = $(tr).data("device_group");
    if (!$(tr).hasClass("updating")) {
      console.log(
        "[Devices][updateStatus]get status from " + $(tr).data("device_ip"),
      );
      $(tr).addClass("updating");

      if (device_group === "multi" && device_relais > 1) {
        console.log(
          "[Devices][updateStatus]SKIP multi " + $(tr).data("device_ip"),
        );
        return; //relais 1 will update all others
      }

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
              '#device-list tbody tr[data-device_group="multi"][data-device_ip="' +
                device_ip +
                '"]',
            ).each(function (key, grouptr) {
              let device_relais = $(grouptr).data("device_relais");
              let device_status = sonoff.parseDeviceStatus(data, device_relais);

              updateRow($(grouptr), data, device_status);
              $(grouptr).removeClass("updating");
            });
          } else {
            let device_status = sonoff.parseDeviceStatus(data, device_relais);
            updateRow($(tr), data, device_status);
          }
        } else {
          console.log("ERROR => " + JSON.stringify(data));
          if (device_group === "multi") {
            $(
              '#device-list tbody tr[data-device_group="multi"][data-device_ip="' +
                device_ip +
                '"]',
            ).each(function (key, grouptr) {
              $(grouptr)
                .find(".status")
                .find("input")
                .parent()
                .addClass("error");
              $(grouptr)
                .find("td")
                .each(function (key, td) {
                  if ($(td).find(".loader").length > 0) {
                    $(td).find("span").html("-");
                  }
                });

              $(grouptr).removeClass("updating");
            });
          } else {
            $(tr)
              .find(".status")
              .find("input")
              .parent()
              .addClass("error");
            $(tr).removeClass("updating");
          }
        }
      });
    } else {
      console.log(
        "[Devices][updateStatus]SKIP get status from " +
          $(tr).data("device_ip"),
      );
    }
  });
}

function updateAllStatus() {
  let device_holder = $("#device-list");

  if (!device_holder.hasClass("updating")) {
    device_holder.addClass("updating");

    console.log("[Devices][updateAllStatus]START");

    let timeout = device_holder.find("tbody tr").length * 15; //max 12 sec per device

    sonoff.getAllStatus(timeout, function (result) {
      device_holder.find("tbody tr").each(function (key, tr) {
        let device_id = $(tr).data("device_id");
        let device_relais = $(tr).data("device_relais");
        let device_group = $(tr).data("device_group");
        let data = result[device_id] || undefined;
        if (
          data !== undefined &&
          !$.isEmptyObject(data) &&
          !data.ERROR &&
          !data.WARNING &&
          data !== "" &&
          data.statusText === undefined
        ) {
          console.log(
            "[LIST][updateAllStatus][" +
              device_id +
              "]MSG => " +
              JSON.stringify(data),
          );

          let device_status = sonoff.parseDeviceStatus(data, device_relais);

          $(tr).removeAttr("data-original-title").removeAttr("data-toggle");

          updateRow($(tr), data, device_status);
          $(tr).find(".status").find("input").parent().removeClass("error");
        } else {
          console.log(
            "[LIST][updateAllStatus][" +
              device_id +
              "][ERROR] DATA => " +
              JSON.stringify(data),
          );

          if ($(tr).hasClass("toggled")) {
            $(tr).removeClass("toggled");
          } else {
            $(tr)
              .find(".status")
              .find("input")
              //.removeProp( "checked" )
              .parent()
              .addClass("error");
          }

          let msg = $.i18n("ERROR");
          if (data !== undefined) {
            if (data.ERROR !== undefined) {
              msg = data.ERROR;
            } else if (data.WARNING !== undefined) {
              msg = data.WARNING;
            } else if (data.statusText !== undefined) {
              msg = data.statusText;
            }
          } else {
            msg = "data is empty";
          }

          $(tr)
            .attr("data-original-title", msg)
            .attr("data-toggle", "tooltip")
            .tooltip({
              html: true,
              delay: 700,
            });

          //$( tr ).find( ".rssi span" ).html( $.i18n( 'ERROR' ) );
          //$( tr ).find( ".runtime span" ).html( "-" );
          //$( tr ).find( ".version span" ).html( "-" );
          //$( tr ).find( "td.more:not(.static) span" ).html( "-" );
          $(tr)
            .find("td")
            .each(function (key, td) {
              if ($(td).find(".loader").length > 0) {
                $(td).find("span").html("-");
              }
            });
        }
      });

      device_holder.removeClass("updating");
    });
  } else {
    console.log("[Devices][updateAllStatus]SKIP");
  }
}
function deviceTools() {
  $("#device-list tbody tr td.status").on("click", function (e) {
    e.preventDefault();
    let statusField = $(this);
    let device_ip = $(this).closest("tr").data("device_ip");
    let device_id = $(this).closest("tr").data("device_id");
    let device_relais = $(this).closest("tr").data("device_relais");
    let device_protect_on = $(this).closest("tr").data("device_protect_on");
    let device_protect_off = $(this).closest("tr").data("device_protect_off");

    const input = statusField.find("input");

    if (input.prop("checked")) {
      if (
        device_protect_off === 1 &&
        !$(".ignoreProtections").prop("checked")
      ) {
        return;
      }
      input.prop("checked", false);
    } else {
      if (device_protect_on === 1 && !$(".ignoreProtections").prop("checked")) {
        return;
      }

      input.prop("checked", true);
    }

    statusField.closest("tr").addClass("toggled");

    sonoff.toggle(device_ip, device_id, device_relais, function (data) {
      if (!data || data.ERROR || data.WARNING) {
        statusField.find("input").parent().addClass("error");
        return;
      }

      let device_status = sonoff.parseDeviceStatus(data, device_relais);
      if (device_status === "ON") {
        if (device_protect_off === 1) {
          input.prop("disabled", "disabled").parent().addClass("disabled");
        } else {
          input
            .removeProp("disabled", "disabled")
            .parent()
            .removeClass("disabled");
        }
      } else if (device_status === "OFF") {
        if (device_protect_on === 1) {
          input.prop("disabled", "disabled").parent().addClass("disabled");
        } else {
          input
            .removeProp("disabled", "disabled")
            .parent()
            .removeClass("disabled");
        }
      }
    });
  });

  $("#deleteDeviceModal").on("show.bs.modal", function (event) {
    let modal = $(this);
    let button = $(event.relatedTarget); // Button that triggered the modal
    modal.find(".btn-ok").attr("href", button.attr("href"));

    let body = button.data("dialog-text");
    modal.find(".modal-body").html(body);
  });

  $("#device-list tbody tr td a.restart-device").on("click", function (e) {
    e.preventDefault();
    let device_id = $(this).closest("tr").data("device_id");
    sonoff.generic(device_id, "Restart", 1);
  });

  $(document).on("dblclick", ".dblcEdit span", function () {
    oriVal = $(this).text().toString().trim();
    $(this).text("").addClass("dont-update");
    let w = oriVal.toString().length * 10 + 20;
    input = $(
      "<input class='dblEdit-Input form-control' type='text' style='width: " +
        w +
        "px; padding: 3px;'>",
    );
    input.appendTo($(this)).focus();
  });

  $(document).on("focusout keypress", ".dblEdit-Input", function (e) {
    if (e.type === "keypress" && e.which !== 13) {
      return;
    }
    if (input.val() !== "") {
      let newvalue = input.val();
      let device_id = $(this).closest("tr").data("device_id");
      let target = $(this).closest("td").data("target") || "device";
      let cmnd = $(this).closest("td").data("cmnd") || "";
      let field = $(this).closest("td").data("field") || "";
      $(this).hide();
      let td = $(this).parent();
      $(this)
        .parent()
        .removeClass("dont-update")
        .html($.i18n("TEXT_LOADING"))
        .removeClass("dont-update");
      if (target == "device") {
        sonoff.updateConfig(device_id, cmnd, newvalue, updateStatus);
      } else if (target == "csv") {
        sonoff.setDeviceValue(device_id, field, newvalue, td);
      }
    } else {
      $(this).parent().removeClass("dont-update").text(oriVal);
    }
  });
}

function updateRow(row, data, device_status) {
  const id = $(row).data("device_id");
  let device_protect_on = $(row).data("device_protect_on");
  let device_protect_off = $(row).data("device_protect_off");

  data = sonoff.parseStatusData(data);

  if (data.hasWifi) {
    let rssi = data.wifi.rssi;
    let ssid = data.wifi.ssid;

    $(row)
      .find(".rssi span")
      .html(rssi + "%")
      .attr("data-original-title", ssid)
      .attr("data-toggle", "tooltip")
      .tooltip({
        html: true,
        delay: 700,
      });
  }

  let energyPower = getEnergyPower(data);
  if (energyPower !== "") {
    $(row).find(".energyPower span").html(energyPower);
    $("#device-list .energyPower").removeClass("hidden");
  }

  let temp = getTemp(data);

  if (temp !== "") {
    $(row).find(".temp span").html(temp);
    $("#device-list .temp").removeClass("hidden");
  }

  let humidity = getHumidity(data);

  if (humidity !== "") {
    $(row).find(".humidity span").html(humidity);
    $("#device-list .humidity").removeClass("hidden");
  }

  let pressure = getPressure(data);

  if (pressure !== "") {
    $(row).find(".pressure span").html(pressure);
    $("#device-list .pressure").removeClass("hidden");
  }

  let seapressure = getSeaPressure(data);

  if (seapressure !== "") {
    $(row).find(".seapressure span").html(seapressure);
    $("#device-list .seapressure").removeClass("hidden");
  }

  let distance = getDistance(data);

  if (distance !== "") {
    $(row).find(".distance span").html(distance);
    $("#device-list .distance").removeClass("hidden");
  }

  let gas = getGas(data);

  if (gas !== "") {
    $(row).find(".gas span").html(gas);
    $("#device-list .gas").removeClass("hidden");
  }

  let idx = data.idx ? data.idx : "";
  if (idx !== "") {
    $(row).find(".idx span").html(idx);
    $("#device-list .idx").removeClass("hidden").show();
  }

  $(row).find(".version span").html(data.StatusFWR.Version);

  if ($(row).hasClass("toggled")) {
    $(row).removeClass("toggled");
  } else {
    if (device_status === "ON") {
      $(row)
        .find(".status")
        .find("input")
        .prop("checked", true)
        .parent()
        .removeClass("error");
      if (device_protect_off === 1) {
        $(row)
          .find(".status")
          .find("input")
          .prop("disabled", "disabled")
          .parent()
          .addClass("disabled");
      } else {
        $(row)
          .find(".status")
          .find("input")
          .removeProp("disabled", "disabled")
          .parent()
          .removeClass("disabled");
      }
    } else if (device_status === "NONE") {
      $(row)
        .find(".status")
        .find("input")
        .prop("disabled", "disabled")
        .parent()
        .addClass("disabled");

      $(row)
        .find(".status")
        .find("input")
        .prop("checked", false)
        .parent()
        .removeClass("error");
      $(row).find(".status").find("label").addClass("d-none");
    } else {
      if (device_protect_on === 1) {
        $(row)
          .find(".status")
          .find("input")
          .prop("disabled", "disabled")
          .parent()
          .addClass("disabled");
      } else {
        $(row)
          .find(".status")
          .find("input")
          .removeProp("disabled", "disabled")
          .parent()
          .removeClass("disabled");
      }
      $(row)
        .find(".status")
        .find("input")
        .prop("checked", false)
        .parent()
        .removeClass("error");
    }
  }

  let startup =
    data.StatusPRM.StartupDateTimeUtc !== undefined
      ? data.StatusPRM.StartupDateTimeUtc
      : data.StatusPRM.StartupUTC !== undefined
        ? data.StatusPRM.StartupUTC
        : "";
  if (startup !== "") {
    let startupdatetime = startup + "Z".replace(/-/g, "/");
    startupdatetime = new Date(startupdatetime);
    let now = new Date();
    let sec_num = (now - startupdatetime) / 1000;
    let days = Math.floor(sec_num / (3600 * 24));
    let hours = Math.floor((sec_num - days * (3600 * 24)) / 3600);
    let minutes = Math.floor(
      (sec_num - days * (3600 * 24) - hours * 3600) / 60,
    );
    let seconds = Math.floor(
      sec_num - days * (3600 * 24) - hours * 3600 - minutes * 60,
    );

    uptime =
      (days !== 0 ? days + $.i18n("UPTIME_SHORT_DAY") : "") +
      " " +
      (hours !== 0 || days !== 0 ? hours + $.i18n("UPTIME_SHORT_HOUR") : "") +
      " " +
      (minutes !== 0 || hours !== 0 || days !== 0
        ? minutes + $.i18n("UPTIME_SHORT_MIN")
        : "") +
      " " +
      (seconds !== 0 || minutes !== 0 || hours !== 0
        ? seconds + $.i18n("UPTIME_SHORT_SEC")
        : "-");

    uptime = $.trim(uptime);

    $(row)
      .find(".runtime span")
      .html(uptime)
      .attr(
        "data-original-title",
        startupdatetime.toLocaleString(
          $("html").attr("lang") + "-" + $("html").attr("lang").toUpperCase(),
          { hour12: false },
        ),
      )
      .attr("data-toggle", "tooltip")
      .tooltip({
        html: true,
        delay: 700,
      });
  } else {
    $(row)
      .find(".runtime span")
      .html(uptime + "h");
  }

  //MORE
  if (!$(row).find(".hostname span").hasClass("dont-update")) {
    $(row)
      .find(".hostname span")
      .html(
        data.StatusNET.Hostname !== undefined ? data.StatusNET.Hostname : "?",
      );
  }

  if (!$(row).find(".mac span").hasClass("dont-update")) {
    $(row)
      .find(".mac span")
      .html(data.StatusNET.Mac !== undefined ? data.StatusNET.Mac : "?");
  }

  if (!$(row).find(".mqtt span").hasClass("dont-update")) {
    $(row)
      .find(".mqtt span")
      .html(data.StatusMQT !== undefined ? "1" : "0");
  }

  if (!$(row).find(".poweronstate span").hasClass("dont-update")) {
    $(row)
      .find(".poweronstate span")
      .html(
        data.Status.PowerOnState !== undefined ? data.Status.PowerOnState : "?",
      );
  }

  if (!$(row).find(".ledstate span").hasClass("dont-update")) {
    $(row)
      .find(".ledstate span")
      .html(data.Status.LedState !== undefined ? data.Status.LedState : "?");
  }

  if (!$(row).find(".savedata span").hasClass("dont-update")) {
    $(row)
      .find(".savedata span")
      .html(data.Status.SaveData !== undefined ? data.Status.SaveData : "?");
  }

  if (!$(row).find(".sleep span").hasClass("dont-update")) {
    $(row)
      .find(".sleep span")
      .html(
        data.StatusPRM.Sleep !== undefined ? data.StatusPRM.Sleep + "ms" : "?",
      );
  }

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

  if (!$(row).find(".wificonfig span").hasClass("dont-update")) {
    $(row)
      .find(".wificonfig span")
      .html(
        data.StatusNET.WifiConfig !== undefined
          ? data.StatusNET.WifiConfig
          : "?",
      );
  }

  $(row)
    .find(".vcc span")
    .html(data.StatusSTS.Vcc !== undefined ? data.StatusSTS.Vcc + "V" : "?");

  let device_hostname = sonoff.parseDeviceHostname(data);
  if (device_hostname !== false) {
    $(row).data("keywords", $(row).data("keywords") + " " + device_hostname);
  }

  $(".doubleScroll-scroll")
    .css({
      width: $("#device-list").width(),
    })
    .parent()
    .trigger("resize");

  $(row).removeClass("updating");
}

function initDeviceFilter() {
  $(".device-search").on("keyup", function (e) {
    // var input, filter, table, tr, td, i, txtValue;
    let input = $(this);
    let searchterm = input.val().trim();

    let table = $("#device-list");
    let deviceRows = table.find("tr");

    if (searchterm !== "") {
      let regex = new RegExp(searchterm, "i");
      $.each(deviceRows, function (key, elem) {
        let deviceRow = $(elem);

        if (key === 0 || key === deviceRows.length - 1) {
          return; //skip header and footer row
        }
        let keywords = deviceRow.data("keywords");

        if (keywords !== undefined && keywords !== "") {
          if (regex.test(keywords)) {
            deviceRow.removeClass("d-none");
          } else {
            deviceRow.addClass("d-none");
            deviceRow.find(".device_checkbox").prop("checked", false);
          }
        } else {
          deviceRow.addClass("d-none");
        }
      });
    } else {
      deviceRows.removeClass("d-none");
    }
  });
}
