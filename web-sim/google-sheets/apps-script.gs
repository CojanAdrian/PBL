/**
 * Big Ed's Jerky: preorders -> this Google Sheet, live.
 *
 * The website sends every new order, and every status change, to this script.
 * Each order is one row, found by its order code: a new code adds a row, a
 * known code updates that row in place.
 *
 * Setup (once): see "Google Sheets" in the project README.
 *   1. Extensions -> Apps Script, paste this whole file, save.
 *   2. Project Settings -> Script properties -> add TOKEN = a long random secret
 *      (the same value goes into SHEETS_TOKEN on Railway).
 *   3. Deploy -> New deployment -> Web app, Execute as: Me, Who has access: Anyone.
 *      Copy the web app URL into SHEETS_WEBHOOK_URL on Railway.
 */

var SHEET_NAME = "Orders";

// Column title -> field sent by the website. Change titles freely; keep the keys.
var COLUMNS = [
  ["Order code", "code"],
  ["Placed", "created"],
  ["Status", "status"],
  ["Name", "name"],
  ["Phone", "phone"],
  ["Email", "email"],
  ["Pickup / delivery", "delivery"],
  ["Address", "address"],
  ["Wanted date", "date"],
  ["Items", "items"],
  ["Packs", "packs"],
  ["Weight (g)", "grams"],
  ["Total", "total"],
  ["Currency", "currency"],
  ["Note", "note"],
  ["Last update", "updated"],
];

var STATUS_COLOURS = { New: "#fff4cc", Confirmed: "#d9eaff", Done: "#dcf5e3", Cancelled: "#eeeeee" };

function doPost(e) {
  var body;
  try {
    body = JSON.parse(e.postData.contents);
  } catch (err) {
    return reply({ ok: false, error: "Bad request." });
  }
  var token = PropertiesService.getScriptProperties().getProperty("TOKEN");
  if (!token) return reply({ ok: false, error: "TOKEN is not set in the script's properties." });
  if (body.token !== token) return reply({ ok: false, error: "Wrong token. SHEETS_TOKEN on Railway must match TOKEN in the script." });

  var lock = LockService.getScriptLock();
  lock.waitLock(25000);
  try {
    var sheet = getSheet();
    var orders = body.orders || [];
    for (var i = 0; i < orders.length; i++) upsert(sheet, orders[i]);
    return reply({ ok: true, count: orders.length });
  } finally {
    lock.releaseLock();
  }
}

// Opening the web app URL in a browser shows this, handy to check it is deployed.
function doGet() {
  return reply({ ok: true, message: "Big Ed's order sheet is ready. The website sends orders here." });
}

function getSheet() {
  var book = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = book.getSheetByName(SHEET_NAME) || book.insertSheet(SHEET_NAME);
  if (sheet.getLastRow() === 0) {
    var titles = COLUMNS.map(function (c) { return c[0]; });
    sheet.getRange(1, 1, 1, titles.length).setValues([titles]).setFontWeight("bold").setBackground("#1c1815").setFontColor("#ffffff");
    sheet.setFrozenRows(1);
    sheet.setColumnWidth(col("items"), 260);
    sheet.setColumnWidth(col("note"), 220);
  }
  return sheet;
}

function col(key) {
  for (var i = 0; i < COLUMNS.length; i++) if (COLUMNS[i][1] === key) return i + 1;
  return -1;
}

function upsert(sheet, order) {
  order.updated = new Date();
  if (order.created) order.created = new Date(order.created);
  var values = COLUMNS.map(function (c) {
    var v = order[c[1]];
    return v === undefined || v === null ? "" : v;
  });

  var found = sheet.getRange(1, col("code"), Math.max(1, sheet.getLastRow()), 1)
    .createTextFinder(String(order.code)).matchEntireCell(true).findNext();
  var row = found ? found.getRow() : sheet.getLastRow() + 1;

  // Phone numbers stay as text so Sheets keeps the + and leading zeros.
  sheet.getRange(row, col("phone")).setNumberFormat("@");
  sheet.getRange(row, 1, 1, values.length).setValues([values]).setVerticalAlignment("top");
  sheet.getRange(row, col("created")).setNumberFormat("yyyy-mm-dd hh:mm");
  sheet.getRange(row, col("updated")).setNumberFormat("yyyy-mm-dd hh:mm");
  sheet.getRange(row, col("items")).setWrap(true);
  sheet.getRange(row, col("status")).setBackground(STATUS_COLOURS[order.status] || null).setFontWeight("bold");
}

function reply(data) {
  return ContentService.createTextOutput(JSON.stringify(data)).setMimeType(ContentService.MimeType.JSON);
}
