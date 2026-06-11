function getSortableIpValue(ipAddress) {
  if (typeof ipAddress !== "string") {
    return null;
  }

  const parts = ipAddress.trim().split(".");
  if (parts.length !== 4) {
    return null;
  }

  let numericValue = 0;
  for (const part of parts) {
    if (!/^\d+$/.test(part)) {
      return null;
    }

    const octet = Number.parseInt(part, 10);
    if (octet < 0 || octet > 255) {
      return null;
    }

    numericValue = numericValue * 256 + octet;
  }

  return numericValue;
}

function getSortableIpCellValue(cellText, cellElement) {
  if (cellElement && typeof cellElement.querySelector === "function") {
    const visibleIpNode = cellElement.querySelector(".device-ip-text");
    if (visibleIpNode && typeof visibleIpNode.textContent === "string") {
      const visibleIpValue = getSortableIpValue(visibleIpNode.textContent);
      if (visibleIpValue !== null) {
        return visibleIpValue;
      }
    }
  }

  return getSortableIpValue(cellText);
}

module.exports = {
  getSortableIpCellValue,
  getSortableIpValue,
};
