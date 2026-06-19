const test = require("node:test");
const assert = require("node:assert/strict");

const {
  bindTimerTimeInputs,
  buildTimerDayMask,
  findClosestTimerCard,
  normalizeTimerClockTimeValue,
  normalizeTimerDayMask,
  setElementVisibility,
  syncTimerTimeInput,
  syncTimerDayGroup,
} = require("../../resources/js/device_config.js");

test("normalizeTimerDayMask keeps selected weekdays readable and canonical", () => {
  assert.equal(normalizeTimerDayMask("--TWT--"), "--TWT--");
  assert.equal(normalizeTimerDayMask("1010101"), "S-T-T-S");
  assert.equal(normalizeTimerDayMask(""), "-------");
});

test("buildTimerDayMask converts weekday checkboxes into the Tasmota mask", () => {
  assert.equal(
    buildTimerDayMask([
      { checked: false },
      { checked: true },
      { checked: false },
      { checked: true },
      { checked: false },
      { checked: true },
      { checked: false },
    ]),
    "-M-W-F-",
  );
});

test("normalizeTimerClockTimeValue keeps timer input in HH:MM format", () => {
  assert.equal(normalizeTimerClockTimeValue("6:5"), "06:05");
  assert.equal(normalizeTimerClockTimeValue("23:59"), "23:59");
  assert.equal(normalizeTimerClockTimeValue("99:00"), "00:00");
});

test("syncTimerDayGroup updates the hidden timer mask field", () => {
  const hiddenInput = { value: "" };
  const dayCheckboxes = [
    { checked: true },
    { checked: false },
    { checked: true },
    { checked: false },
    { checked: false },
    { checked: false },
    { checked: true },
  ];

  syncTimerDayGroup({
    querySelector(selector) {
      if (selector === "[data-timer-days-value]") {
        return hiddenInput;
      }

      return null;
    },
    querySelectorAll(selector) {
      if (selector === "[data-timer-days-checkbox]") {
        return dayCheckboxes;
      }

      return [];
    },
  });

  assert.equal(hiddenInput.value, "S-T---S");
});

test("syncTimerTimeInput uses a native time field for fixed timer mode", () => {
  const modeField = { value: "0" };
  const timeField = {
    value: "",
  };
  const timeSelectField = {
    value: "06:30",
  };
  const offsetSelectField = {
    value: "+00:15",
  };
  const timeGroup = {
    hidden: false,
    style: {},
    classList: {
      toggleCalls: [],
      toggle(name, state) {
        this.toggleCalls.push([name, state]);
      },
    },
  };
  const offsetGroup = {
    hidden: false,
    style: {},
    classList: {
      toggleCalls: [],
      toggle(name, state) {
        this.toggleCalls.push([name, state]);
      },
    },
  };

  syncTimerTimeInput({
    querySelector(selector) {
      if (selector === "[data-timer-mode]") {
        return modeField;
      }

      if (selector === "[data-timer-time-value]") {
        return timeField;
      }

      if (selector === "[data-timer-time-select]") {
        return timeSelectField;
      }

      if (selector === "[data-timer-offset-select]") {
        return offsetSelectField;
      }

      if (selector === "[data-timer-time-group]") {
        return timeGroup;
      }

      if (selector === "[data-timer-offset-group]") {
        return offsetGroup;
      }

      return null;
    },
  });

  assert.equal(timeField.value, "06:30");
  assert.deepEqual(timeGroup.classList.toggleCalls.at(-1), ["hidden", false]);
  assert.deepEqual(offsetGroup.classList.toggleCalls.at(-1), ["hidden", true]);
  assert.equal(timeGroup.hidden, false);
  assert.equal(offsetGroup.hidden, true);
  assert.equal(offsetGroup.style.display, "none");
});

test("syncTimerTimeInput keeps offset mode as text for sunrise and sunset", () => {
  const modeField = { value: "2" };
  const timeField = {
    value: "",
  };
  const timeSelectField = {
    value: "06:30",
  };
  const offsetSelectField = {
    value: "-01:15",
  };
  const timeGroup = {
    hidden: false,
    style: {},
    classList: {
      toggleCalls: [],
      toggle(name, state) {
        this.toggleCalls.push([name, state]);
      },
    },
  };
  const offsetGroup = {
    hidden: false,
    style: {},
    classList: {
      toggleCalls: [],
      toggle(name, state) {
        this.toggleCalls.push([name, state]);
      },
    },
  };

  syncTimerTimeInput({
    querySelector(selector) {
      if (selector === "[data-timer-mode]") {
        return modeField;
      }

      if (selector === "[data-timer-time-value]") {
        return timeField;
      }

      if (selector === "[data-timer-time-select]") {
        return timeSelectField;
      }

      if (selector === "[data-timer-offset-select]") {
        return offsetSelectField;
      }

      if (selector === "[data-timer-time-group]") {
        return timeGroup;
      }

      if (selector === "[data-timer-offset-group]") {
        return offsetGroup;
      }

      return null;
    },
  });

  assert.equal(timeField.value, "-01:15");
  assert.deepEqual(timeGroup.classList.toggleCalls.at(-1), ["hidden", true]);
  assert.deepEqual(offsetGroup.classList.toggleCalls.at(-1), ["hidden", false]);
  assert.equal(timeGroup.hidden, true);
  assert.equal(timeGroup.style.display, "none");
  assert.equal(offsetGroup.hidden, false);
});

test("bindTimerTimeInputs wires mode changes to the matching timer card", () => {
  const modeField = {
    value: "0",
    dataset: {},
    addEventListener() {},
  };
  const timeField = {
    value: "",
  };
  const timeSelectField = {
    value: "05:00",
    dataset: {},
    addEventListener() {},
  };
  const offsetSelectField = {
    value: "+00:30",
    dataset: {},
    addEventListener() {},
  };
  const timeGroup = {
    hidden: false,
    style: {},
    classList: {
      toggle() {},
    },
  };
  const offsetGroup = {
    hidden: false,
    style: {},
    classList: {
      toggle() {},
    },
  };
  const timerCard = {
    querySelector(selector) {
      if (selector === "[data-timer-mode]") {
        return modeField;
      }

      if (selector === "[data-timer-time-value]") {
        return timeField;
      }

      if (selector === "[data-timer-time-select]") {
        return timeSelectField;
      }

      if (selector === "[data-timer-offset-select]") {
        return offsetSelectField;
      }

      if (selector === "[data-timer-time-group]") {
        return timeGroup;
      }

      if (selector === "[data-timer-offset-group]") {
        return offsetGroup;
      }

      return null;
    },
  };

  bindTimerTimeInputs({
    querySelectorAll(selector) {
      if (selector === ".device-config-timer-card") {
        return [timerCard];
      }

      return [];
    },
  });

  assert.equal(timeField.value, "05:00");
});

test("findClosestTimerCard resolves the current timer card from a changed field", () => {
  const timerCard = { id: "timer-card-1" };
  const field = {
    closest(selector) {
      assert.equal(selector, ".device-config-timer-card");
      return timerCard;
    },
  };

  assert.equal(findClosestTimerCard(field), timerCard);
});

test("setElementVisibility updates class, hidden attribute and display", () => {
  const toggleCalls = [];
  const element = {
    hidden: false,
    style: {},
    classList: {
      toggle(name, state) {
        toggleCalls.push([name, state]);
      },
    },
  };

  setElementVisibility(element, false);
  assert.deepEqual(toggleCalls.at(-1), ["hidden", true]);
  assert.equal(element.hidden, true);
  assert.equal(element.style.display, "none");

  setElementVisibility(element, true);
  assert.deepEqual(toggleCalls.at(-1), ["hidden", false]);
  assert.equal(element.hidden, false);
  assert.equal(element.style.display, "");
});
