import {
  disposeTooltip,
  getHumidity,
  getIlluminance,
  getTemp,
  getPressure,
  getSeaPressure,
  getGas,
  getDistance,
  getEnergyPower,
  getRefreshTime,
  chunkArray,
  onI18nReady,
  refreshTooltip,
} from "./app";
import deviceListPreferences from "./device_list_preferences";
import batchActions from "./device_batch_actions";
import { getSortableIpCellValue } from "./ip_sort";
import statusHelpers from "./status_helpers";
import toggleConfirmation from "./toggle_confirmation";

const { extractFirstNumericValue, getRuntimeInfo, getSortableVersionValue } =
  statusHelpers;
const {
  confirmAction,
  createTouchConfirmController,
  getToggleConfirmationOptions,
  resolveToggleConfirmationSetting,
} = toggleConfirmation;
const {
  DEVICE_LIST_PREFERENCE_COOKIES,
  parseHiddenColumns,
  serializeHiddenColumns,
} = deviceListPreferences;
const { getBatchActionConfig, shouldSubmitBatchForm, validateBatchAction } =
  batchActions;
const refreshtime = getRefreshTime();

let ignoreProtectionsTimer;
let batchActionFeedbackTimer;
let hiddenDeviceColumns = new Set();
onI18nReady(function () {
  initIpSorting();
  initCellDataSorting("rssi", ".rssi span", "data-sort-number", true);
  initCellDataSorting("version", ".version span", "data-sort-text");
  initCellDataSorting("runtime", ".runtime span", "data-runtime-seconds", true);
  initCellDataSorting(
    "energyPower",
    ".energyPower span",
    "data-sort-number",
    true,
  );
  initCellDataSorting("temp", ".temp span", "data-sort-number", true);
  initCellDataSorting("humidity", ".humidity span", "data-sort-number", true);
  initCellDataSorting(
    "illuminance",
    ".illuminance span",
    "data-sort-number",
    true,
  );
  initCellDataSorting("sleep", ".sleep span", "data-sort-number", true);
  initCellDataSorting("vcc", ".vcc span", "data-sort-number", true);
  deviceTools();
  initDeviceListPreferences();

  if ($(".device-search").length > 0) {
    initDeviceFilter();
  }

  updateAllStatus();

  initCommandHelper();

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

  $(".table-responsive").attachDragger();
  if (refreshtime) {
    console.log("[Global][Refreshtime]" + refreshtime + "ms");
    setInterval(function () {
      console.log("[Global][Refreshtime] updateStatus now");
      updateStatus();
    }, refreshtime);
  } else {
    console.log("[Global][Refreshtime] " + $.i18n("NO_REFRESH") + "");
  }
});

function initIpSorting() {
  const ipHeader = $("#device-list thead th[data-column-id='ip']");
  if (ipHeader.length === 0) {
    return;
  }

  ipHeader.data("tablesaw-sort", function (ascending) {
    return function (a, b) {
      const ipA = getSortableIpCellValue(a.cell, a.element);
      const ipB = getSortableIpCellValue(b.cell, b.element);

      if (ipA !== null && ipB !== null) {
        return ascending ? ipA - ipB : ipB - ipA;
      }

      const textA = (a.cell || "").toLowerCase();
      const textB = (b.cell || "").toLowerCase();
      return compareTextValues(textA, textB, ascending);
    };
  });
}

function compareTextValues(textA, textB, ascending) {
  if (textA === textB) {
    return 0;
  }

  if (ascending) {
    return textA > textB ? 1 : -1;
  }

  return textA < textB ? 1 : -1;
}

function initCellDataSorting(
  columnId,
  selector,
  attributeName,
  numeric = false,
) {
  const header = $(`#device-list thead th[data-column-id='${columnId}']`);
  if (header.length === 0) {
    return;
  }

  header.data("tablesaw-sort", function (ascending) {
    return function (a, b) {
      const rawValueA =
        a.element?.querySelector?.(selector)?.getAttribute?.(attributeName) ??
        "";
      const rawValueB =
        b.element?.querySelector?.(selector)?.getAttribute?.(attributeName) ??
        "";

      if (numeric) {
        const valueA = Number.parseFloat(rawValueA);
        const valueB = Number.parseFloat(rawValueB);

        if (!Number.isNaN(valueA) && !Number.isNaN(valueB)) {
          return ascending ? valueA - valueB : valueB - valueA;
        }
      } else if (rawValueA !== "" && rawValueB !== "") {
        return compareTextValues(rawValueA, rawValueB, ascending);
      }

      const textA = (a.cell || "").toLowerCase();
      const textB = (b.cell || "").toLowerCase();
      return compareTextValues(textA, textB, ascending);
    };
  });
}

function setSortAttribute(row, selector, attributeName, value) {
  const cell = $(row).find(selector);

  if (value === null || value === undefined || value === "") {
    cell.removeAttr(attributeName);
    return;
  }

  cell.attr(attributeName, value);
}

function resizeDeviceListScroller() {
  $(".doubleScroll-scroll")
    .css({
      width: $("#device-list").width(),
    })
    .parent()
    .trigger("resize");
}

function getToggleableDeviceColumns() {
  return $("#device-list thead th[data-column-toggle='true']")
    .map(function () {
      return {
        id: $(this).data("columnId"),
        label: $(this).data("columnLabel"),
        hiddenByDefault: $(this).hasClass("hidden") || $(this).hasClass("more"),
      };
    })
    .get();
}

function getDefaultHiddenDeviceColumns() {
  return getToggleableDeviceColumns()
    .filter((column) => column.hiddenByDefault)
    .map((column) => column.id);
}

function renderDeviceColumnMenu() {
  const menu = $(".device-columns-menu");
  if (menu.length === 0) {
    return;
  }

  menu.empty();
  getToggleableDeviceColumns().forEach((column) => {
    const optionId = `device-column-${column.id}`;
    menu.append(`
      <div class="form-check device-column-option">
        <input class="form-check-input column-toggle-input" type="checkbox" id="${optionId}" value="${column.id}">
        <label class="form-check-label" for="${optionId}">${column.label}</label>
      </div>
    `);
  });
}

function updateColumnToggleInputs() {
  $(".column-toggle-input").each(function () {
    const columnId = $(this).val();
    $(this).prop("checked", !hiddenDeviceColumns.has(columnId));
  });

  $("#deviceColumnsMenuButton")
    .toggleClass("btn-primary", hiddenDeviceColumns.size > 0)
    .toggleClass("btn-secondary", hiddenDeviceColumns.size === 0);
}

function refreshDeviceListLayout() {
  $("#device-list [data-column-id]").each(function () {
    const cell = $(this);
    const columnId = cell.data("columnId");
    const shouldShow = !hiddenDeviceColumns.has(columnId);

    cell.toggleClass("device-column-hidden", !shouldShow);
    cell.toggle(shouldShow);
  });

  $("#device-list").toggleClass("short-list", true);

  updateColumnToggleInputs();
  resizeDeviceListScroller();
}

function initDeviceListPreferences() {
  const availableColumns = getToggleableDeviceColumns().map(
    (column) => column.id,
  );
  const hiddenColumnCookie = Cookies.get(
    DEVICE_LIST_PREFERENCE_COOKIES.hiddenColumns,
  );
  const defaultHiddenColumns = getDefaultHiddenDeviceColumns();
  const initialHiddenColumns =
    typeof hiddenColumnCookie === "string"
      ? parseHiddenColumns(hiddenColumnCookie, availableColumns)
      : defaultHiddenColumns;

  hiddenDeviceColumns = new Set(initialHiddenColumns);

  renderDeviceColumnMenu();
  refreshDeviceListLayout();

  $(".device-columns-menu").on("change", ".column-toggle-input", function () {
    const columnId = $(this).val();

    if ($(this).prop("checked")) {
      hiddenDeviceColumns.delete(columnId);
    } else {
      hiddenDeviceColumns.add(columnId);
    }

    Cookies.set(
      DEVICE_LIST_PREFERENCE_COOKIES.hiddenColumns,
      serializeHiddenColumns(Array.from(hiddenDeviceColumns)),
    );
    refreshDeviceListLayout();
  });

  $(".device-columns-menu").on("click", function (e) {
    e.stopPropagation();
  });
}

function initCommandHelper() {
  $(".batchActionSelect").on("change", function () {
    if ($(this).val() !== "command") {
      $(".batchActionCommandInput").val("");
    }
    $(".batchActionField").val("");
    resetBatchActionFeedback();
    updateBatchActionUi();
  });

  $(".batchActionCommandInput").on("keypress", function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $(".applyBatchAction").trigger("click");
    }
  });

  $(".select_all").change(function () {
    let status = this.checked;
    $(
      "#device-list tbody tr:not(.d-none) .device_checkbox:not(:disabled)",
    ).each(function () {
      this.checked = status;
    });

    $(".select_all").each(function () {
      this.checked = status;
    });

    updateBatchActionUi();
  });

  $(".device_checkbox").on("change", function () {
    syncSelectAllState();
    updateBatchActionUi();
  });

  $(".applyBatchAction").on("click", function () {
    const action = $(".batchActionSelect").val();
    const selectedDevices = getSelectedDevices();
    const command = $(".batchActionCommandInput").val().trim();
    const validationError = validateBatchAction({
      action,
      selectedDeviceIds: selectedDevices,
      command,
    });

    resetBatchActionFeedback();

    if (validationError !== null) {
      showBatchActionFeedback($.i18n(validationError), "text-danger");
      return false;
    }

    if (shouldSubmitBatchForm(action)) {
      $(".batchActionField").val("backup");
      const form = $(this).closest("form").get(0);

      if (form) {
        if (typeof form.requestSubmit === "function") {
          form.requestSubmit();
        } else {
          form.submit();
        }
      }

      return false;
    }

    if (action === "delete") {
      if (!window.confirm(`${$.i18n("DELETE_SELECTED")}?`)) {
        return false;
      }

      const idsParam = selectedDevices.join(",");
      $.get(`${config.base_url}actions?removeDevices&ids=${idsParam}`)
        .done(() => window.location.reload())
        .fail(() => showBatchActionFeedback($.i18n("ERROR"), "text-danger"));

      return false;
    }

    if (action === "restart") {
      const modal = $("#deleteDeviceModal");
      modal.data("dialog-action", "restart-batch");
      modal.data("dialog-device-ids", selectedDevices.join(","));
      modal.find(".modal-title").text($.i18n("DELETE_DEVICE_CONFIRM_TITLE"));
      modal.find(".modal-body").text($.i18n("BATCH_RESTART_CONFIRM_TEXT"));
      modal.find(".btn-secondary").text($.i18n("CANCEL"));
      modal.find(".btn-ok").text($.i18n("RESTART_SELECTED"));
      window.bootstrap.Modal.getOrCreateInstance(modal.get(0)).show();
      return false;
    }

    $(".batchActionField").val("");
    showBatchActionFeedback($.i18n("SUCCESS_COMMAND_SEND"), "text-success");

    $.each(selectedDevices, function (idx, device_id) {
      sonoff.generic(device_id, command, undefined, function (result) {
        let device_name = $("[data-device_id=" + device_id + "]:first")
          .find(".device_name a")
          .text()
          .trim();
        appendBatchActionFeedback(
          "ID " +
            device_id +
            " (" +
            device_name +
            ") => " +
            JSON.stringify(result),
        );
      });
    });

    return false;
  });

  updateBatchActionUi();
}

function getSelectedDevices() {
  return $.map($(".device_checkbox:checked"), function (elem) {
    return [$(elem).val()];
  });
}

function syncSelectAllState() {
  const selectableDevices = $(".device_checkbox:not(:disabled):visible");
  const selectedDevices = selectableDevices.filter(":checked");
  const isAllSelected =
    selectableDevices.length > 0 &&
    selectedDevices.length === selectableDevices.length;

  $(".select_all").prop("checked", isAllSelected);
}

function updateBatchActionUi() {
  const actionConfig = getBatchActionConfig($(".batchActionSelect").val());
  const hasSelection = getSelectedDevices().length > 0;

  $(".batchActionCommandWrapper").toggleClass(
    "d-none",
    !actionConfig || !actionConfig.requiresCommand,
  );

  $(".applyBatchAction")
    .prop("disabled", !actionConfig || !hasSelection)
    .text(
      actionConfig
        ? $.i18n(actionConfig.submitLabelKey)
        : $.i18n("PLEASE_SELECT"),
    );
}

function resetBatchActionFeedback() {
  clearTimeout(batchActionFeedbackTimer);
  $(".batchActionFeedback")
    .removeClass("text-danger text-success")
    .addClass("d-none")
    .html("");
}

function scheduleBatchActionFeedbackReset() {
  clearTimeout(batchActionFeedbackTimer);
  batchActionFeedbackTimer = setTimeout(() => {
    resetBatchActionFeedback();
  }, 5000);
}

function showBatchActionFeedback(message, className) {
  $(".batchActionFeedback")
    .removeClass("d-none text-danger text-success")
    .addClass(className)
    .html(message);
  scheduleBatchActionFeedbackReset();
}

function appendBatchActionFeedback(message) {
  const feedback = $(".batchActionFeedback");
  const currentContent = feedback.html();
  const nextContent =
    currentContent === "" ? message : `${currentContent}<br/>${message}`;
  feedback.removeClass("d-none").html(nextContent);
  scheduleBatchActionFeedbackReset();
}

function getLoadingIndicatorMarkup() {
  const loadingText = ($.i18n("TEXT_LOADING") || "").replace(/'/g, "&#39;");
  return `<span class='loader' role='status' aria-label='${loadingText}'></span>`;
}

function showRestartPendingState(deviceId) {
  $(`#device-list tbody tr[data-device_id="${deviceId}"]`).each(function () {
    const row = $(this);
    disposeTooltip(this);
    row.addClass("restarting");
    row.removeAttr("data-bs-title").removeAttr("data-bs-toggle");
    row.find(".status input").parent().removeClass("error");
    row.find(".runtime span, .rssi span").each(function () {
      disposeTooltip(this);
    });
    row
      .find(".runtime span")
      .html(getLoadingIndicatorMarkup())
      .removeAttr("data-runtime-seconds")
      .removeAttr("data-bs-title")
      .removeAttr("data-bs-toggle");
  });
}

function updateStatus() {
  const batchSize = config.request_concurrency;
  const rows = $("#device-list tbody tr").toArray();
  const batches = chunkArray(rows, batchSize);

  console.log(
    `[Devices][updateStatus] Processing ${rows.length} in ${batches.length} batches with batch size of ${batchSize}`,
  );

  processBatchesSequentially(batches);
}

function processBatchesSequentially(batches) {
  const processBatch = (batch) => {
    const promises = batch.map((tr) => processRow($(tr)));
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

function processRow($tr) {
  return new Promise((resolve) => {
    let device_ip = $tr.data("device_ip");
    let device_id = $tr.data("device_id");
    let device_relais = $tr.data("device_relais");
    let device_group = $tr.data("device_group");

    if (!$tr.hasClass("updating")) {
      console.log("[Devices][updateStatus]get status from " + device_ip);
      $tr.addClass("updating");

      if (device_group === "multi" && device_relais > 1) {
        console.log("[Devices][updateStatus]SKIP multi " + device_ip);
        resolve(); // Skip this row
        return;
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
            updateRow($tr, data, device_status);
          }
        } else {
          console.log("ERROR => " + JSON.stringify(data));
          if (device_group === "multi") {
            $(
              '#device-list tbody tr[data-device_group="multi"][data-device_ip="' +
                device_ip +
                '"]',
            ).each(function (key, grouptr) {
              if (!$(grouptr).hasClass("restarting")) {
                $(grouptr)
                  .find(".status")
                  .find("input")
                  .parent()
                  .addClass("error");
              }
              if (!$(grouptr).hasClass("restarting")) {
                $(grouptr)
                  .find("td")
                  .each(function (key, td) {
                    if ($(td).find(".loader").length > 0) {
                      $(td).find("span").html("-");
                    }
                  });
              }

              $(grouptr).removeClass("updating");
            });
          } else {
            if (!$tr.hasClass("restarting")) {
              $tr.find(".status").find("input").parent().addClass("error");
            }
            $tr.removeClass("updating");
          }
        }
        resolve(); // Mark this row as processed
      });
    } else {
      console.log("[Devices][updateStatus]SKIP get status from " + device_ip);
      resolve(); // Already updating, skip
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

          $(tr).removeAttr("data-bs-title").removeAttr("data-bs-toggle");

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
          } else if (!$(tr).hasClass("restarting")) {
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

          if (!$(tr).hasClass("restarting")) {
            disposeTooltip(tr);
            $(tr).attr("data-bs-title", msg).attr("data-bs-toggle", "tooltip");
            refreshTooltip(tr, {
              html: true,
              delay: 700,
            });
          } else {
            disposeTooltip(tr);
          }

          //$( tr ).find( ".rssi span" ).html( $.i18n( 'ERROR' ) );
          //$( tr ).find( ".runtime span" ).html( "-" );
          //$( tr ).find( ".version span" ).html( "-" );
          //$( tr ).find( "td.more:not(.static) span" ).html( "-" );
          if (!$(tr).hasClass("restarting")) {
            $(tr)
              .find("td")
              .each(function (key, td) {
                if ($(td).find(".loader").length > 0) {
                  $(td).find("span").html("-");
                }
              });
          }
        }
      });

      device_holder.removeClass("updating");
    });
  } else {
    console.log("[Devices][updateAllStatus]SKIP");
  }
}
function deviceTools() {
  const toggleConfirmationController = createTouchConfirmController();

  $("#device-list tbody tr td.status .form-switch").on("click", function (e) {
    e.preventDefault();
    let statusField = $(this);
    let deviceRow = statusField.closest("tr");
    let device_ip = deviceRow.data("device_ip");
    let device_id = deviceRow.data("device_id");
    let device_relais = deviceRow.data("device_relais");
    let device_protect_on = deviceRow.data("device_protect_on");
    let device_protect_off = deviceRow.data("device_protect_off");

    const input = statusField.find("input");
    const nextStatus = input.prop("checked")
      ? $.i18n("SWITCH_STATE_OFF")
      : $.i18n("SWITCH_STATE_ON");
    const confirmationEnabled = resolveToggleConfirmationSetting(
      deviceRow.data("device_confirm_toggle"),
      config.confirm_device_toggles,
    );

    if (input.prop("disabled")) {
      return;
    }

    const deviceName =
      deviceRow.find(".device_name a").text().trim() || `#${device_id}`;

    void confirmAction({
      requiresConfirmation: confirmationEnabled,
      confirm: toggleConfirmationController.confirm,
      modalOptions: getToggleConfirmationOptions({
        i18n: $.i18n,
        deviceName,
        nextStatusLabel: nextStatus,
      }),
      onConfirm: function () {
        if (input.prop("checked")) {
          if (
            device_protect_off === 1 &&
            !$(".ignoreProtections").prop("checked")
          ) {
            return;
          }
          input.prop("checked", false);
        } else {
          if (
            device_protect_on === 1 &&
            !$(".ignoreProtections").prop("checked")
          ) {
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
      },
    });
  });

  $("#deleteDeviceModal").on("show.bs.modal", function (event) {
    let modal = $(this);
    let button = $(event.relatedTarget); // Button that triggered the modal
    if (button.length === 0) {
      return;
    }
    modal.data("dialog-action", button.data("dialog-action") || "delete");
    modal.data("dialog-url", button.data("dialog-url") || button.attr("href"));
    modal.data("dialog-device-id", button.data("dialog-device-id") || "");
    modal.data("dialog-device-ids", button.data("dialog-device-ids") || "");

    modal.find(".modal-title").text(button.data("dialog-title") || "");
    modal.find(".modal-body").html(button.data("dialog-text") || "");
    modal
      .find(".btn-secondary")
      .text(button.data("dialog-btn-cancel-text") || $.i18n("CANCEL"));
    modal
      .find(".btn-ok")
      .text(button.data("dialog-btn-ok-text") || $.i18n("CONFIRM"));
  });

  $("#deleteDeviceModal .btn-ok").on("click", function () {
    let modal = $("#deleteDeviceModal");
    let action = modal.data("dialog-action");

    if (action === "restart") {
      let device_id = modal.data("dialog-device-id");
      bootstrap.Modal.getInstance(modal.get(0))?.hide();
      showRestartPendingState(device_id);
      sonoff.generic(device_id, "Restart", 1);
      return;
    }

    if (action === "restart-batch") {
      const deviceIds = String(modal.data("dialog-device-ids") || "")
        .split(",")
        .map((value) => value.trim())
        .filter((value) => value !== "");
      bootstrap.Modal.getInstance(modal.get(0))?.hide();
      $.each(deviceIds, function (idx, device_id) {
        showRestartPendingState(device_id);
        sonoff.generic(device_id, "Restart", 1);
      });
      showBatchActionFeedback($.i18n("SUCCESS_COMMAND_SEND"), "text-success");
      return;
    }

    let url = modal.data("dialog-url");
    if (url) {
      window.location.href = url;
    }
  });

  $(document).on("dblclick", ".dblcEdit span", function () {
    let oriVal = $(this).text().toString().trim();
    $(this).text("").addClass("dont-update");
    let w = oriVal.toString().length * 10 + 20;
    let input = $(
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

    let input = $(this);
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
      .attr("data-sort-number", rssi)
      .attr("data-bs-title", ssid)
      .attr("data-bs-toggle", "tooltip");
    refreshTooltip($(row).find(".rssi span").get(0), {
      html: true,
      delay: 700,
    });
  } else {
    disposeTooltip($(row).find(".rssi span").get(0));
    $(row)
      .find(".rssi span")
      .html("-")
      .removeAttr("data-sort-number")
      .removeAttr("data-bs-title")
      .removeAttr("data-bs-toggle");
  }

  let energyPower = getEnergyPower(data, " / ");
  if (energyPower !== "") {
    $(row).find(".energyPower span").html(energyPower);
    setSortAttribute(
      row,
      ".energyPower span",
      "data-sort-number",
      extractFirstNumericValue(energyPower),
    );
    $("#device-list .energyPower").removeClass("hidden");
  } else {
    setSortAttribute(row, ".energyPower span", "data-sort-number", null);
  }

  let temp = getTemp(data);

  if (temp !== "") {
    $(row).find(".temp span").html(temp);
    setSortAttribute(
      row,
      ".temp span",
      "data-sort-number",
      extractFirstNumericValue(temp),
    );
    $("#device-list .temp").removeClass("hidden");
  } else {
    setSortAttribute(row, ".temp span", "data-sort-number", null);
  }

  let humidity = getHumidity(data);

  if (humidity !== "") {
    $(row).find(".humidity span").html(humidity);
    setSortAttribute(
      row,
      ".humidity span",
      "data-sort-number",
      extractFirstNumericValue(humidity),
    );
    $("#device-list .humidity").removeClass("hidden");
  } else {
    setSortAttribute(row, ".humidity span", "data-sort-number", null);
  }

  let illuminance = getIlluminance(data);

  if (illuminance !== "") {
    $(row).find(".illuminance span").html(illuminance);
    setSortAttribute(
      row,
      ".illuminance span",
      "data-sort-number",
      extractFirstNumericValue(illuminance),
    );
    $("#device-list .illuminance").removeClass("hidden");
  } else {
    setSortAttribute(row, ".illuminance span", "data-sort-number", null);
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

  const version = data.StatusFWR.Version ?? "?";
  $(row).find(".version span").html(version);
  setSortAttribute(
    row,
    ".version span",
    "data-sort-text",
    getSortableVersionValue(version),
  );

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

  const runtime = getRuntimeInfo(data, {
    day: $.i18n("UPTIME_SHORT_DAY"),
    hour: $.i18n("UPTIME_SHORT_HOUR"),
    minute: $.i18n("UPTIME_SHORT_MIN"),
    second: $.i18n("UPTIME_SHORT_SEC"),
  });

  if (runtime.startupDateTime !== null) {
    const locale = `${$("html").attr("lang")}-${$("html")
      .attr("lang")
      .toUpperCase()}`;

    $(row)
      .find(".runtime span")
      .html(runtime.text)
      .attr("data-runtime-seconds", runtime.sortValue ?? "")
      .attr(
        "data-bs-title",
        runtime.startupDateTime.toLocaleString(locale, { hour12: false }),
      )
      .attr("data-bs-toggle", "tooltip");
    refreshTooltip($(row).find(".runtime span").get(0), {
      html: true,
      delay: 700,
    });
  } else {
    disposeTooltip($(row).find(".runtime span").get(0));
    $(row)
      .find(".runtime span")
      .html(runtime.text)
      .attr("data-runtime-seconds", runtime.sortValue ?? "")
      .removeAttr("data-bs-title")
      .removeAttr("data-bs-toggle");
  }

  $(row).removeClass("restarting");

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
      .html(data?.Status?.PowerOnState ?? "?");
  }

  if (!$(row).find(".ledstate span").hasClass("dont-update")) {
    $(row)
      .find(".ledstate span")
      .html(data?.Status?.LedState ?? "?");
  }

  if (!$(row).find(".savedata span").hasClass("dont-update")) {
    $(row)
      .find(".savedata span")
      .html(data?.Status?.SaveData ?? "?");
  }

  if (!$(row).find(".sleep span").hasClass("dont-update")) {
    $(row)
      .find(".sleep span")
      .html(
        data.StatusPRM.Sleep !== undefined ? data.StatusPRM.Sleep + "ms" : "?",
      );
    setSortAttribute(
      row,
      ".sleep span",
      "data-sort-number",
      data.StatusPRM.Sleep ?? null,
    );
  } else {
    setSortAttribute(row, ".sleep span", "data-sort-number", null);
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
  setSortAttribute(
    row,
    ".vcc span",
    "data-sort-number",
    data.StatusSTS.Vcc ?? null,
  );

  let device_hostname = sonoff.parseDeviceHostname(data);
  if (device_hostname !== false) {
    $(row).data("keywords", $(row).data("keywords") + " " + device_hostname);
  }

  refreshDeviceListLayout();

  $(row).removeClass("updating");
}

function initDeviceFilter() {
  $(".device-search").on("keyup", function (e) {
    let input = $(this);
    let searchterm = input.val().trim();

    let deviceRows = $("#device-list tbody tr");

    if (searchterm !== "") {
      let regex = new RegExp(searchterm, "i");
      $.each(deviceRows, function (key, elem) {
        let deviceRow = $(elem);

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

    syncSelectAllState();
    updateBatchActionUi();
  });
}
