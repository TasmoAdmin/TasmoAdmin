const test = require("node:test");
const assert = require("node:assert/strict");
const {
  extractAssetUrls,
  getOfflineUrl,
  isStaticAsset,
  stripSearch,
} = require("../../resources/js/service_worker.js");

const scope = "https://ta.example.com/";

test("getOfflineUrl resolves the offline page inside the scope", () => {
  assert.equal(getOfflineUrl(scope), "https://ta.example.com/offline");
  assert.equal(
    getOfflineUrl("https://ta.example.com/tasmo/"),
    "https://ta.example.com/tasmo/offline",
  );
});

test("isStaticAsset only matches files under resources/", () => {
  assert.equal(isStaticAsset(`${scope}resources/css/all.css?_=1`, scope), true);
  assert.equal(isStaticAsset(`${scope}devices`, scope), false);
  assert.equal(isStaticAsset(`${scope}actions?i18n=1`, scope), false);
  assert.equal(isStaticAsset(`${scope}data/firmwares/x.bin`, scope), false);
  assert.equal(
    isStaticAsset("https://other.example.com/resources/a.js", scope),
    false,
  );
});

test("extractAssetUrls returns unique same-scope resource URLs", () => {
  const html = `
    <link href="/resources/css/compiled/all.css?_=5" rel="stylesheet">
    <link href="/resources/css/compiled/all.css?_=5" rel="stylesheet">
    <img src="/resources/img/favicons/android-chrome-192x192.png">
    <a href="/devices">devices</a>
    <script src="https://cdn.example.com/resources/x.js"></script>`;

  assert.deepEqual(extractAssetUrls(html, scope, "https://ta.example.com"), [
    "https://ta.example.com/resources/css/compiled/all.css?_=5",
    "https://ta.example.com/resources/img/favicons/android-chrome-192x192.png",
  ]);
});

test("stripSearch drops the cache tag", () => {
  assert.equal(
    stripSearch(`${scope}resources/js/compiled/app.js?_=123`),
    `${scope}resources/js/compiled/app.js`,
  );
});
