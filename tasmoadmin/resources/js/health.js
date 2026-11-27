import { getRefreshTime, onI18nReady } from "./app";
import { getHealthRefreshInterval, matchesSearch } from "./health_filter";

function applyHealthSearch() {
  const term = $(".health-search").val();
  let visible = 0;

  $(".health-table tbody tr").each(function () {
    const row = $(this);
    const match = matchesSearch(row.data("keywords"), term);
    row.toggleClass("d-none", !match);
    if (match) {
      visible++;
    }
  });

  $(".health-no-match").prop("hidden", visible > 0);
}

// Reload counts and rows in place so the search box keeps its value and focus.
function refreshHealth() {
  if (document.hidden) {
    return;
  }

  $("#health-content").load(
    `${window.location.href} #health-content > *`,
    function (response, status) {
      if (status === "success") {
        applyHealthSearch();
      }
    },
  );
}

onI18nReady(function () {
  $(".health-search").on("input", applyHealthSearch);
  applyHealthSearch();

  const interval = getHealthRefreshInterval(getRefreshTime());
  if (interval) {
    setInterval(refreshHealth, interval);
  }
});
