const test = require("node:test");
const assert = require("node:assert/strict");

const {
  confirmAction,
  createTouchConfirmController,
  getToggleConfirmationOptions,
} = require("../../resources/js/toggle_confirmation.js");

function createFakeEventTarget() {
  return {
    textContent: "",
    listeners: {},
    addEventListener(type, handler) {
      this.listeners[type] ??= [];
      this.listeners[type].push(handler);
    },
    dispatch(type) {
      for (const handler of this.listeners[type] ?? []) {
        handler({
          preventDefault() {},
        });
      }
    },
  };
}

function createModalFixture() {
  const title = createFakeEventTarget();
  const body = createFakeEventTarget();
  const confirmButton = createFakeEventTarget();
  const cancelButton = createFakeEventTarget();
  const modalElement = createFakeEventTarget();

  modalElement.querySelector = function (selector) {
    return {
      ".toggle-confirm-modal-title": title,
      ".toggle-confirm-modal-body": body,
      ".toggle-confirm-modal-confirm": confirmButton,
      ".toggle-confirm-modal-cancel": cancelButton,
    }[selector];
  };

  let showCount = 0;
  const bootstrapRef = {
    Modal: {
      getOrCreateInstance(element) {
        assert.equal(element, modalElement);

        return {
          show() {
            showCount += 1;
          },
          hide() {
            modalElement.dispatch("hidden.bs.modal");
          },
        };
      },
    },
  };

  return {
    body,
    bootstrapRef,
    confirmButton,
    getShowCount() {
      return showCount;
    },
    modalElement,
    title,
    cancelButton,
  };
}

function createI18n() {
  return function i18n(key, ...values) {
    const messages = {
      CANCEL: "Cancel",
      CONFIRM: "Confirm",
      CONFIRM_ALL_OFF: "Do you want to switch all included devices off?",
      CONFIRM_SWITCHES: "Confirm Switches",
      SWITCH_STATE_OFF: "off",
    };

    if (key === "CONFIRM_DEVICE_TOGGLE") {
      return `Do you want to switch "${values[0]}" ${values[1]}?`;
    }

    return messages[key] ?? key;
  };
}

test("devices toggle opens the dialog and runs the action after confirmation", async () => {
  const fixture = createModalFixture();
  const controller = createTouchConfirmController({
    documentRef: {
      getElementById() {
        return fixture.modalElement;
      },
    },
    bootstrapRef: fixture.bootstrapRef,
  });
  let actionRuns = 0;

  const resultPromise = confirmAction({
    requiresConfirmation: true,
    confirm: controller.confirm,
    modalOptions: getToggleConfirmationOptions({
      i18n: createI18n(),
      deviceName: "Desk Lamp",
      nextStatusLabel: "off",
    }),
    onConfirm() {
      actionRuns += 1;
    },
  });

  assert.equal(fixture.getShowCount(), 1);
  assert.equal(fixture.title.textContent, "Confirm Switches");
  assert.match(fixture.body.textContent, /Desk Lamp/);

  fixture.confirmButton.dispatch("click");

  assert.equal(await resultPromise, true);
  assert.equal(actionRuns, 1);
});

test("start toggle keeps the UI action untouched when the dialog is cancelled", async () => {
  const fixture = createModalFixture();
  const controller = createTouchConfirmController({
    documentRef: {
      getElementById() {
        return fixture.modalElement;
      },
    },
    bootstrapRef: fixture.bootstrapRef,
  });
  let actionRuns = 0;

  const resultPromise = confirmAction({
    requiresConfirmation: true,
    confirm: controller.confirm,
    modalOptions: getToggleConfirmationOptions({
      i18n: createI18n(),
      deviceName: "Hallway Light",
      nextStatusLabel: "off",
    }),
    onConfirm() {
      actionRuns += 1;
    },
  });

  fixture.cancelButton.dispatch("click");
  fixture.modalElement.dispatch("hidden.bs.modal");

  assert.equal(await resultPromise, false);
  assert.equal(actionRuns, 0);
});

test("all off uses its own dialog copy and only switches after confirmation", async () => {
  const fixture = createModalFixture();
  const controller = createTouchConfirmController({
    documentRef: {
      getElementById() {
        return fixture.modalElement;
      },
    },
    bootstrapRef: fixture.bootstrapRef,
  });
  let actionRuns = 0;

  const resultPromise = confirmAction({
    requiresConfirmation: true,
    confirm: controller.confirm,
    modalOptions: getToggleConfirmationOptions({
      i18n: createI18n(),
      allOff: true,
    }),
    onConfirm() {
      actionRuns += 1;
    },
  });

  assert.equal(
    fixture.body.textContent,
    "Do you want to switch all included devices off?",
  );

  fixture.confirmButton.dispatch("click");

  assert.equal(await resultPromise, true);
  assert.equal(actionRuns, 1);
});
