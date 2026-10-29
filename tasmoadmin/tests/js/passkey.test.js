const test = require("node:test");
const assert = require("node:assert/strict");
const {
  base64UrlToBuffer,
  bufferToBase64Url,
  decodeCreateOptions,
  decodeGetOptions,
  isPasskeySupported,
} = require("../../resources/js/passkey.js");

test("base64url helpers round-trip binary data", () => {
  const bytes = new Uint8Array([0, 251, 255, 62, 63, 1]);
  const encoded = bufferToBase64Url(bytes.buffer);
  assert.equal(encoded.includes("="), false);
  assert.equal(encoded.includes("+"), false);
  assert.deepEqual(new Uint8Array(base64UrlToBuffer(encoded)), bytes);
});

test("decodeCreateOptions converts challenge, user id and excluded ids", () => {
  const options = decodeCreateOptions({
    publicKey: {
      challenge: "AAEC",
      user: { id: "AwQF", name: "admin" },
      excludeCredentials: [{ id: "BgcI", type: "public-key" }],
    },
  });
  assert.deepEqual(
    new Uint8Array(options.publicKey.challenge),
    new Uint8Array([0, 1, 2]),
  );
  assert.deepEqual(
    new Uint8Array(options.publicKey.user.id),
    new Uint8Array([3, 4, 5]),
  );
  assert.equal(options.publicKey.user.name, "admin");
  assert.deepEqual(
    new Uint8Array(options.publicKey.excludeCredentials[0].id),
    new Uint8Array([6, 7, 8]),
  );
});

test("decodeGetOptions leaves allowCredentials absent for discoverable login", () => {
  const options = decodeGetOptions({ publicKey: { challenge: "AAEC" } });
  assert.equal("allowCredentials" in options.publicKey, false);
});

test("isPasskeySupported requires WebAuthn and a secure context", () => {
  const credentials = {};
  assert.equal(isPasskeySupported(undefined), false);
  assert.equal(
    isPasskeySupported({
      PublicKeyCredential: function () {},
      navigator: { credentials },
      isSecureContext: false,
    }),
    false,
  );
  assert.equal(
    isPasskeySupported({
      PublicKeyCredential: function () {},
      navigator: { credentials },
      isSecureContext: true,
    }),
    true,
  );
});
