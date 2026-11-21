// Plain substring match so characters like "(" or "." in a search never throw.
function matchesSearch(keywords, term) {
  const needle = String(term ?? "")
    .trim()
    .toLowerCase();
  if (needle === "") {
    return true;
  }

  return String(keywords ?? "")
    .toLowerCase()
    .includes(needle);
}

// MQTT changes land immediately but HTTP is polled every minute by default;
// reloading the page faster than this only adds load.
const MIN_REFRESH_MS = 10000;

function getHealthRefreshInterval(refreshtime) {
  if (!refreshtime) {
    return false;
  }

  return Math.max(refreshtime, MIN_REFRESH_MS);
}

module.exports = {
  MIN_REFRESH_MS,
  getHealthRefreshInterval,
  matchesSearch,
};
