const DEVICE_LIST_PREFERENCE_COOKIES = Object.freeze({
  hiddenColumns: "devices_hidden_columns",
});

function parseHiddenColumns(value, availableColumns = []) {
  const knownColumns = new Set(availableColumns);

  if (typeof value !== "string" || value.trim() === "") {
    return [];
  }

  return value
    .split(",")
    .map((column) => column.trim())
    .filter((column, index, columns) => {
      return (
        column !== "" &&
        (!knownColumns.size || knownColumns.has(column)) &&
        columns.indexOf(column) === index
      );
    });
}

function serializeHiddenColumns(columns = []) {
  return columns
    .filter((column, index, values) => {
      return column !== "" && values.indexOf(column) === index;
    })
    .join(",");
}

module.exports = {
  DEVICE_LIST_PREFERENCE_COOKIES,
  parseHiddenColumns,
  serializeHiddenColumns,
};
