function base64UrlToBuffer(value) {
  const base64 = value.replace(/-/g, "+").replace(/_/g, "/");
  const padded = base64 + "=".repeat((4 - (base64.length % 4)) % 4);
  const binary = atob(padded);
  const bytes = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i++) {
    bytes[i] = binary.charCodeAt(i);
  }
  return bytes.buffer;
}

function bufferToBase64Url(buffer) {
  const bytes = new Uint8Array(buffer);
  let binary = "";
  for (let i = 0; i < bytes.length; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  return btoa(binary)
    .replace(/\+/g, "-")
    .replace(/\//g, "_")
    .replace(/=+$/, "");
}

// The server encodes binary fields as base64url strings; WebAuthn wants buffers.
function decodeCreateOptions(options) {
  const publicKey = { ...options.publicKey };
  publicKey.challenge = base64UrlToBuffer(publicKey.challenge);
  publicKey.user = {
    ...publicKey.user,
    id: base64UrlToBuffer(publicKey.user.id),
  };
  publicKey.excludeCredentials = (publicKey.excludeCredentials || []).map(
    (credential) => ({ ...credential, id: base64UrlToBuffer(credential.id) }),
  );
  return { publicKey };
}

function decodeGetOptions(options) {
  const publicKey = { ...options.publicKey };
  publicKey.challenge = base64UrlToBuffer(publicKey.challenge);
  if (publicKey.allowCredentials) {
    publicKey.allowCredentials = publicKey.allowCredentials.map(
      (credential) => ({ ...credential, id: base64UrlToBuffer(credential.id) }),
    );
  }
  return { publicKey };
}

function encodeAttestation(credential) {
  return {
    id: credential.id,
    clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
    attestationObject: bufferToBase64Url(credential.response.attestationObject),
  };
}

function encodeAssertion(credential) {
  return {
    id: bufferToBase64Url(credential.rawId),
    clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
    authenticatorData: bufferToBase64Url(credential.response.authenticatorData),
    signature: bufferToBase64Url(credential.response.signature),
  };
}

function isPasskeySupported(win) {
  return Boolean(
    win &&
    win.PublicKeyCredential &&
    win.navigator &&
    win.navigator.credentials &&
    win.isSecureContext,
  );
}

async function postJson(url, body) {
  const response = await fetch(url, {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body || {}),
  });
  const data = await response.json().catch(() => ({}));
  if (!response.ok) {
    throw new Error(data.error || response.statusText);
  }
  return data;
}

function showMessage(target, text, type) {
  if (!target) {
    return;
  }
  target.textContent = text;
  target.className = `passkey-message alert alert-${type} mt-3`;
  target.hidden = false;
}

function initPasskeyLogin(button) {
  const message = document.getElementById("passkey-message");
  if (!isPasskeySupported(window)) {
    button.hidden = true;
    return;
  }

  button.addEventListener("click", async () => {
    button.disabled = true;
    try {
      const options = await postJson(`${config.base_url}passkey/login_options`);
      const credential = await navigator.credentials.get(
        decodeGetOptions(options),
      );
      const result = await postJson(
        `${config.base_url}passkey/login`,
        encodeAssertion(credential),
      );
      window.location.href = result.redirect || config.base_url;
    } catch (error) {
      if (error && error.name !== "NotAllowedError") {
        showMessage(
          message,
          error.message || button.dataset.errorText,
          "danger",
        );
      }
      button.disabled = false;
    }
  });
}

function renderPasskeyList(list, passkeys) {
  list.replaceChildren();
  const emptyText = list.dataset.emptyText;
  if (passkeys.length === 0) {
    const item = document.createElement("li");
    item.className = "list-group-item passkey-empty";
    item.textContent = emptyText;
    list.appendChild(item);
    return;
  }

  passkeys.forEach((passkey) => {
    const item = document.createElement("li");
    item.className =
      "list-group-item d-flex align-items-center justify-content-between gap-3";

    const label = document.createElement("div");
    const name = document.createElement("strong");
    name.textContent = passkey.name;
    const meta = document.createElement("div");
    meta.className = "small text-body-secondary";
    const created = new Date(passkey.createdAt * 1000).toLocaleDateString();
    const used = passkey.lastUsedAt
      ? new Date(passkey.lastUsedAt * 1000).toLocaleString()
      : "—";
    meta.textContent = `${list.dataset.createdLabel} ${created} · ${list.dataset.usedLabel} ${used}`;
    label.append(name, meta);

    const remove = document.createElement("button");
    remove.type = "button";
    remove.className = "btn btn-sm btn-outline-danger";
    remove.dataset.passkeyId = passkey.id;
    remove.setAttribute("aria-label", list.dataset.deleteLabel);
    remove.innerHTML = '<i class="fas fa-trash"></i>';

    item.append(label, remove);
    list.appendChild(item);
  });
}

function initPasskeySettings(section) {
  const list = section.querySelector(".passkey-list");
  const addButton = section.querySelector(".passkey-add");
  const nameInput = section.querySelector(".passkey-name");
  const message = section.querySelector(".passkey-message");

  if (!isPasskeySupported(window)) {
    addButton.disabled = true;
    showMessage(message, addButton.dataset.unsupportedText, "warning");
  }

  const refresh = () =>
    postJson(`${config.base_url}passkey/list`)
      .then((data) => renderPasskeyList(list, data.passkeys || []))
      .catch((error) => showMessage(message, error.message, "danger"));

  addButton.addEventListener("click", async () => {
    addButton.disabled = true;
    try {
      const options = await postJson(
        `${config.base_url}passkey/register_options`,
      );
      const credential = await navigator.credentials.create(
        decodeCreateOptions(options),
      );
      const result = await postJson(`${config.base_url}passkey/register`, {
        ...encodeAttestation(credential),
        name: nameInput.value,
      });
      nameInput.value = "";
      renderPasskeyList(list, result.passkeys || []);
      showMessage(message, addButton.dataset.successText, "success");
    } catch (error) {
      if (error && error.name !== "NotAllowedError") {
        showMessage(
          message,
          error.message || addButton.dataset.errorText,
          "danger",
        );
      }
    }
    addButton.disabled = false;
  });

  list.addEventListener("click", async (event) => {
    const remove = event.target.closest("[data-passkey-id]");
    if (!remove || !window.confirm(list.dataset.deleteConfirm)) {
      return;
    }
    await postJson(`${config.base_url}passkey/delete`, {
      id: remove.dataset.passkeyId,
    }).catch((error) => showMessage(message, error.message, "danger"));
    refresh();
  });

  refresh();
}

if (typeof document !== "undefined") {
  document.addEventListener("DOMContentLoaded", () => {
    const loginButton = document.getElementById("passkey-login");
    if (loginButton) {
      initPasskeyLogin(loginButton);
    }
    const settings = document.querySelector(".passkey-settings-section");
    if (settings) {
      initPasskeySettings(settings);
    }
  });
}

if (typeof module !== "undefined") {
  module.exports = {
    base64UrlToBuffer,
    bufferToBase64Url,
    decodeCreateOptions,
    decodeGetOptions,
    encodeAssertion,
    isPasskeySupported,
  };
}
