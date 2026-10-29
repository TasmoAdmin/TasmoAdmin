// Loads the device_action "add" form into a Bootstrap modal and drives its
// two-phase flow (search device -> device found -> saved) over fetch, so the
// user never leaves the device list. The form markup itself lives in
// pages/device_action.php (rendered header/footer-free via the
// device_action_modal route); this module only orchestrates loading, submitting
// and reading the data-da-state marker the fragment exposes on its root.

const MODAL_ID = "addDeviceModal";

function getLoadingMarkup() {
  const loadingText = ($.i18n("TEXT_LOADING") || "").replace(/'/g, "&#39;");
  return (
    "<div class='text-center py-5'>" +
    `<span class='loader' role='status' aria-label='${loadingText}'></span>` +
    "</div>"
  );
}

function getErrorMarkup() {
  return `<div class="alert alert-danger mb-0">${$.i18n("ERROR")}</div>`;
}

function getModalElement() {
  return document.getElementById(MODAL_ID);
}

function getModalBody(modalEl) {
  return modalEl ? modalEl.querySelector(".modal-body") : null;
}

// Reads the state the server stamped on the fragment root: search | found | done.
function readFragmentState(html) {
  const doc = new DOMParser().parseFromString(html, "text/html");
  const marker = doc.querySelector("[data-da-state]");
  return marker ? marker.getAttribute("data-da-state") : null;
}

function loadFragment(url) {
  const modalEl = getModalElement();
  const body = getModalBody(modalEl);
  if (!body) {
    return;
  }

  body.innerHTML = getLoadingMarkup();
  window.bootstrap.Modal.getOrCreateInstance(modalEl).show();

  fetch(url, {
    credentials: "same-origin",
    headers: { "X-Requested-With": "XMLHttpRequest" },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }
      return response.text();
    })
    .then((html) => {
      body.innerHTML = html;
      focusFirstField(body);
    })
    .catch(() => {
      body.innerHTML = getErrorMarkup();
    });
}

function focusFirstField(body) {
  const field = body.querySelector(
    "input[autofocus], input.form-control:not([type=hidden])",
  );
  if (field) {
    window.setTimeout(() => field.focus(), 150);
  }
}

function submitForm(form, submitter) {
  const modalEl = getModalElement();
  const body = getModalBody(modalEl);
  if (!body) {
    return;
  }

  const formData = new FormData(form);
  // fetch() does not include the activating button's name/value, but the server
  // distinguishes "search" from "save" by exactly that, so add it back.
  if (submitter && submitter.name) {
    formData.append(submitter.name, submitter.value || "");
  }

  const $submitButtons = $(form).find("button[type=submit]");
  $submitButtons.prop("disabled", true);
  if (submitter) {
    submitter.dataset.originalHtml = submitter.innerHTML;
    const loadingText = ($.i18n("TEXT_LOADING") || "").replace(/'/g, "&#39;");
    submitter.innerHTML = `<span class='loader' role='status' aria-label='${loadingText}'></span>`;
  }

  fetch(form.getAttribute("action"), {
    method: "POST",
    body: formData,
    credentials: "same-origin",
    headers: { "X-Requested-With": "XMLHttpRequest" },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }
      return response.text();
    })
    .then((html) => {
      if (readFragmentState(html) === "done") {
        // The new device row is server-rendered; reload to pick it up, matching
        // the delete-device flow.
        window.location.reload();
        return;
      }
      body.innerHTML = html;
      focusFirstField(body);
    })
    .catch(() => {
      $submitButtons.prop("disabled", false);
      if (submitter && submitter.dataset.originalHtml !== undefined) {
        submitter.innerHTML = submitter.dataset.originalHtml;
      }
      body.innerHTML = getErrorMarkup() + body.innerHTML;
    });
}

export default function initAddDeviceModal() {
  if (!getModalElement()) {
    return;
  }

  // Trigger links keep their href so add still works without JS; intercept here.
  $(document).on("click", ".js-add-device", function (e) {
    e.preventDefault();
    const href = $(this).attr("href") || "";
    loadFragment(href.replace("/device_action/", "/device_action_modal/"));
  });

  $(document).on("submit", `#${MODAL_ID} form`, function (e) {
    e.preventDefault();
    const submitter =
      (e.originalEvent && e.originalEvent.submitter) ||
      $(this).find("button[type=submit]").get(0);
    submitForm(this, submitter);
  });

  // Behaviours that the standalone page binds via its inline script; rebound here
  // through delegation so they survive the modal body being swapped per phase.
  $(document).on("click", `#${MODAL_ID} .default-name`, function (e) {
    e.preventDefault();
    $(this)
      .closest(".device-name-row")
      .find(".tasmoadmin-name-input")
      .val(($(this).data("default-name") + "").trim());
  });

  $(document).on(
    "change",
    `#${MODAL_ID} #device_hide_from_startpage`,
    function () {
      if ($(this).prop("checked")) {
        $(`#${MODAL_ID} #device_all_off`).prop("checked", false);
      }
    },
  );

  // Reset to a spinner when closed so a stale form never flashes on reopen.
  $(document).on("hidden.bs.modal", `#${MODAL_ID}`, function () {
    const body = getModalBody(this);
    if (body) {
      body.innerHTML = getLoadingMarkup();
    }
  });
}
