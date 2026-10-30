// Keeps a Google Sheet in step with the preorders. The sheet runs a small Apps
// Script (google-sheets/apps-script.gs) published as a web app; we POST orders
// to it and it adds a row for each one it doesn't have yet, and deletes rows on request.
const { describeOrder } = require("./orders");

const STATUS_LABEL = { new: "New", confirmed: "Confirmed", fulfilled: "Done", cancelled: "Cancelled" };

// One ready-to-write row per order, so the script never needs to know about prices or flavours.
function sheetRow(order, config) {
  return {
    code: order.code,
    created: order.createdAt,
    status: STATUS_LABEL[order.status] || order.status,
    name: order.name,
    phone: order.phone,
    email: order.email,
    delivery: order.delivery === "delivery" ? "Delivery" : "Pickup",
    address: order.address,
    date: order.date,
    items: describeOrder(order, config).join("\n"),
    packs: order.items.reduce((s, i) => s + i.qty, 0),
    grams: order.totalGrams,
    total: order.totalPrice,
    currency: order.currency,
    note: order.note,
  };
}

// Returns null when no sheet is configured.
function createSheetSync({ url, token, config, fetchImpl = fetch } = {}) {
  if (!url) return null;
  async function post(payload) {
    const res = await fetchImpl(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ token: token || "", ...payload }),
      redirect: "follow",
      signal: AbortSignal.timeout(20000),
    });
    const text = typeof res.text === "function" ? await res.text() : "";
    let data = null;
    try {
      data = JSON.parse(text);
    } catch {
      /* not JSON: Google sent an error or login page */
    }
    if (!res.ok || !data || !data.ok) {
      const reason = data?.error || (/<html/i.test(text) ? "Google returned a web page instead of the script. Is the web app deployed with access for 'Anyone'?" : text.slice(0, 200));
      throw new Error(`Google Sheets: ${reason || `responded ${res.status}`}`);
    }
    return data;
  }

  return {
    push: (orders) => post({ orders: orders.map((o) => sheetRow(o, config)) }),
    remove: (codes) => post({ action: "delete", codes }),
  };
}

module.exports = { createSheetSync, sheetRow };
