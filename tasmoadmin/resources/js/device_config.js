var TIMER_DAY_MASKS = ["S", "M", "T", "W", "T", "F", "S"];
var $_forms = {};
var timerTimePickerFactory;

function normalizeTimerClockTimeValue(value) {
  var matches = String(value || "")
    .trim()
    .match(/^(\d{1,2}):(\d{1,2})$/);

  if (!matches) {
    return "00:00";
  }

  var hours = Number(matches[1]);
  var minutes = Number(matches[2]);

  if (
    Number.isNaN(hours) ||
    Number.isNaN(minutes) ||
    hours < 0 ||
    hours > 23 ||
    minutes < 0 ||
    minutes > 59
  ) {
    return "00:00";
  }

  return (
    String(hours).padStart(2, "0") + ":" + String(minutes).padStart(2, "0")
  );
}

function resolveTimerTimePickerFactory() {
  if (timerTimePickerFactory !== undefined) {
    return timerTimePickerFactory;
  }

  if (typeof window !== "undefined" && typeof window.flatpickr === "function") {
    timerTimePickerFactory = window.flatpickr;
    return timerTimePickerFactory;
  }

  if (typeof require === "function") {
    try {
      var flatpickrModule = require("flatpickr");
      timerTimePickerFactory = flatpickrModule.default || flatpickrModule;
      return timerTimePickerFactory;
    } catch (error) {
      timerTimePickerFactory = null;
    }
  } else {
    timerTimePickerFactory = null;
  }

  return timerTimePickerFactory;
}

function findClosestTimerCard(field) {
  if (!field || typeof field.closest !== "function") {
    return null;
  }

  return field.closest(".device-config-timer-card");
}

function setElementVisibility(element, isVisible) {
  if (!element) {
    return;
  }

  if (element.classList) {
    element.classList.toggle("hidden", !isVisible);
  }

  element.hidden = !isVisible;
  element.style.display = isVisible ? "" : "none";
}

function syncTimerTimeInput(timerCard) {
  if (!timerCard || typeof timerCard.querySelector !== "function") {
    return;
  }

  var modeField = timerCard.querySelector("[data-timer-mode]");
  var timeField = timerCard.querySelector("[data-timer-time-value]");
  var timeSelectField = timerCard.querySelector("[data-timer-time-select]");
  var offsetSelectField = timerCard.querySelector("[data-timer-offset-select]");
  var timeGroup = timerCard.querySelector("[data-timer-time-group]");
  var offsetGroup = timerCard.querySelector("[data-timer-offset-group]");

  if (!modeField || !timeField || !timeSelectField || !offsetSelectField) {
    return;
  }

  var usesClockTime = String(modeField.value) === "0";
  var normalizedTimerTime = normalizeTimerClockTimeValue(timeSelectField.value);

  if (usesClockTime) {
    timeSelectField.value = normalizedTimerTime;
    if (
      timeSelectField._flatpickr &&
      typeof timeSelectField._flatpickr.setDate === "function" &&
      timeSelectField._flatpickr.input &&
      timeSelectField._flatpickr.input.value !== normalizedTimerTime
    ) {
      timeSelectField._flatpickr.setDate(normalizedTimerTime, false, "H:i");
    }
  }

  timeField.value = usesClockTime
    ? normalizedTimerTime
    : offsetSelectField.value;

  setElementVisibility(timeGroup, usesClockTime);
  setElementVisibility(offsetGroup, !usesClockTime);
}

function initializeTimerTimePicker(timeField) {
  if (!timeField || timeField.dataset.timerTimePickerBound) {
    return;
  }

  var timerTimePicker = resolveTimerTimePickerFactory();
  if (!timerTimePicker) {
    return;
  }

  timerTimePicker(timeField, {
    allowInput: true,
    clickOpens: true,
    dateFormat: "H:i",
    defaultDate: normalizeTimerClockTimeValue(timeField.value),
    disableMobile: true,
    enableTime: true,
    minuteIncrement: 5,
    noCalendar: true,
    time_24hr: true,
    onChange: function () {
      syncTimerTimeInput(findClosestTimerCard(timeField));
    },
    onClose: function () {
      syncTimerTimeInput(findClosestTimerCard(timeField));
    },
    onReady: function (_, __, instance) {
      instance.input.value = normalizeTimerClockTimeValue(instance.input.value);
    },
    onValueUpdate: function () {
      syncTimerTimeInput(findClosestTimerCard(timeField));
    },
  });

  timeField.dataset.timerTimePickerBound = "true";
}

function initializeTimerTimePickers(documentRef) {
  if (!documentRef || typeof documentRef.querySelectorAll !== "function") {
    return;
  }

  Array.prototype.forEach.call(
    documentRef.querySelectorAll("[data-timer-time-picker]"),
    function (timeField) {
      initializeTimerTimePicker(timeField);
    },
  );
}

function bindTimerTimeInputs(documentRef) {
  if (!documentRef || typeof documentRef.querySelectorAll !== "function") {
    return;
  }

  Array.prototype.forEach.call(
    documentRef.querySelectorAll(".device-config-timer-card"),
    function (timerCard) {
      var modeField = timerCard.querySelector("[data-timer-mode]");
      var timeSelectField = timerCard.querySelector("[data-timer-time-select]");
      var offsetSelectField = timerCard.querySelector(
        "[data-timer-offset-select]",
      );

      syncTimerTimeInput(timerCard);

      if (modeField && !modeField.dataset.timerModeBound) {
        modeField.addEventListener("change", function () {
          syncTimerTimeInput(timerCard);
        });
        modeField.dataset.timerModeBound = "true";
      }

      if (timeSelectField && !timeSelectField.dataset.timerTimeBound) {
        timeSelectField.addEventListener("change", function () {
          syncTimerTimeInput(timerCard);
        });
        timeSelectField.dataset.timerTimeBound = "true";
      }

      if (offsetSelectField && !offsetSelectField.dataset.timerOffsetBound) {
        offsetSelectField.addEventListener("change", function () {
          syncTimerTimeInput(timerCard);
        });
        offsetSelectField.dataset.timerOffsetBound = "true";
      }
    },
  );
}

function normalizeTimerDayMask(dayMask) {
  var normalizedMask = "";
  var stringMask = String(dayMask || "");

  for (var index = 0; index < TIMER_DAY_MASKS.length; index += 1) {
    var currentValue = stringMask.charAt(index);
    normalizedMask +=
      currentValue && currentValue !== "-" && currentValue !== "0"
        ? TIMER_DAY_MASKS[index]
        : "-";
  }

  return normalizedMask;
}

function buildTimerDayMask(dayCheckboxes) {
  return dayCheckboxes
    .map(function (checkbox, index) {
      return checkbox.checked ? TIMER_DAY_MASKS[index] : "-";
    })
    .join("");
}

function syncTimerDayGroup(timerDayGroup) {
  if (!timerDayGroup || typeof timerDayGroup.querySelectorAll !== "function") {
    return;
  }

  var hiddenInput = timerDayGroup.querySelector("[data-timer-days-value]");
  if (!hiddenInput) {
    return;
  }

  var dayCheckboxes = Array.prototype.slice.call(
    timerDayGroup.querySelectorAll("[data-timer-days-checkbox]"),
  );

  hiddenInput.value = buildTimerDayMask(dayCheckboxes);
}

function bindTimerDayGroups(documentRef) {
  if (!documentRef || typeof documentRef.querySelectorAll !== "function") {
    return;
  }

  Array.prototype.forEach.call(
    documentRef.querySelectorAll("[data-timer-days-group]"),
    function (timerDayGroup) {
      syncTimerDayGroup(timerDayGroup);

      Array.prototype.forEach.call(
        timerDayGroup.querySelectorAll("[data-timer-days-checkbox]"),
        function (dayCheckbox) {
          dayCheckbox.addEventListener("change", function () {
            syncTimerDayGroup(timerDayGroup);
          });
        },
      );
    },
  );
}

if (typeof document !== "undefined" && typeof $ !== "undefined") {
  $(function () {
    initializeTimerTimePickers(document);
    bindTimerTimeInputs(document);
    bindTimerDayGroups(document);
    saveDefaultFormValues();
    console.log($_forms);

    $(document).on(
      "change",
      "[data-timer-mode], [data-timer-time-select], [data-timer-offset-select]",
      function (e) {
        var timerCard = findClosestTimerCard(e.target || this);
        syncTimerTimeInput(timerCard);
      },
    );

    $(".config-form").on("submit", function (e) {
      submitForm(e);
    });
  });
}

function saveDefaultFormValues() {
  $.each($(".config-form"), function (idx, form) {
    var formName = $(form).attr("name");
    $_forms[formName] = convertSerializedArrayToHash($(form).serializeArray());
  });
}

function submitForm(e) {
  var form = $(e.currentTarget);
  console.log("[DEVICE_CONFIG] " + "Submitted Form: " + form.attr("name"));
  bindTimerDayGroups(form.get(0));

  var currentItems = convertSerializedArrayToHash(form.serializeArray());
  console.log(currentItems);
  var itemsToSubmit = hashDiff($_forms[form.attr("name")], currentItems);

  console.log(itemsToSubmit);
  if (!$.isEmptyObject(itemsToSubmit)) {
    $.each(
      form.find(":input:not([type=submit]):not([name=tab-index])"),
      function (idx, felem) {
        felem = $(felem);

        if (itemsToSubmit[felem.attr("name")] === undefined) {
          felem.attr("disabled", "disabled");
        }
      },
    );
    return true;
  } else {
    console.log("[DEVICE_CONFIG] " + "nothing to submit");
    e.stopPropagation();
    e.preventDefault();
    return false;
  }
}

function hashDiff(h1, h2) {
  var d = {};
  for (k in h2) {
    if (h1[k] !== h2[k]) {
      d[k] = h2[k];
    }
  }
  return d;
}

function convertSerializedArrayToHash(a) {
  var r = {};
  for (var i = 0; i < a.length; i++) {
    r[a[i].name] = a[i].value;
  }
  return r;
}

if (typeof jQuery !== "undefined") {
  (function ($) {
    var _base_serializeArray = $.fn.serializeArray;
    $.fn.serializeArray = function () {
      var a = _base_serializeArray.apply(this);
      $.each(this.find("input"), function (i, e) {
        if (e.type == "checkbox" && e.name) {
          e.checked
            ? (a[i].value = "true")
            : a.splice(i, 0, { name: e.name, value: "0" });
        }
      });
      return a;
    };
  })(jQuery);
}

if (typeof module !== "undefined") {
  module.exports = {
    bindTimerTimeInputs,
    buildTimerDayMask,
    findClosestTimerCard,
    initializeTimerTimePickers,
    normalizeTimerDayMask,
    normalizeTimerClockTimeValue,
    resolveTimerTimePickerFactory,
    setElementVisibility,
    syncTimerTimeInput,
    syncTimerDayGroup,
  };
}
