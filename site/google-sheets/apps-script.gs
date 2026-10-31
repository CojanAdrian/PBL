/**
 * Big Ed's Jerky: preorders -> this Google Sheet, live.
 *
 * The website sends every new order to this script, which adds it as a row
 * with Status "New". Ed sets the status here, with the dropdown in the Status
 * column. An order code that is already in the sheet is skipped, so the
 * website never overwrites anything Ed changed. Deleting an order on the
 * website's admin page deletes its row here too.
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
  ["Added", "added"],
];

var STATUSES = ["New", "Confirmed", "Done", "Cancelled"];

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
    if (body.action === "delete") {
      var deleted = 0;
      var codes = body.codes || [];
      for (var j = 0; j < codes.length; j++) {
        var row = findRow(sheet, codes[j]);
        if (row) {
          sheet.deleteRow(row);
          deleted++;
        }
      }
      return reply({ ok: true, deleted: deleted });
    }
    var orders = body.orders || [];
    var added = 0;
    for (var i = 0; i < orders.length; i++) if (addIfMissing(sheet, orders[i])) added++;
    return reply({ ok: true, count: orders.length, added: added });
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
    // Status cells take a colour from their value, also when Ed changes it.
    var statusCol = sheet.getRange(2, col("status"), sheet.getMaxRows() - 1, 1);
    sheet.setConditionalFormatRules(Object.keys(STATUS_COLOURS).map(function (label) {
      return SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo(label).setBackground(STATUS_COLOURS[label]).setRanges([statusCol]).build();
    }));
  }
  return sheet;
}

function col(key) {
  for (var i = 0; i < COLUMNS.length; i++) if (COLUMNS[i][1] === key) return i + 1;
  return -1;
}

// Row number of an order code, or 0 when it is not in the sheet.
function findRow(sheet, code) {
  var last = sheet.getLastRow();
  if (last < 2) return 0;
  var found = sheet.getRange(2, col("code"), last - 1, 1)
    .createTextFinder(String(code)).matchEntireCell(true).findNext();
  return found ? found.getRow() : 0;
}

// Adds the order as a new row, unless its code is already in the sheet.
function addIfMissing(sheet, order) {
  if (findRow(sheet, order.code)) return false;
  var last = sheet.getLastRow();
  order.status = "New";
  order.added = new Date();
  if (order.created) order.created = new Date(order.created);
  var values = COLUMNS.map(function (c) {
    var v = order[c[1]];
    return v === undefined || v === null ? "" : v;
  });

  var row = last + 1;
  // Phone numbers stay as text so Sheets keeps the + and leading zeros.
  sheet.getRange(row, col("phone")).setNumberFormat("@");
  sheet.getRange(row, 1, 1, values.length).setValues([values]).setVerticalAlignment("top");
  sheet.getRange(row, col("created")).setNumberFormat("yyyy-mm-dd hh:mm");
  sheet.getRange(row, col("added")).setNumberFormat("yyyy-mm-dd hh:mm");
  sheet.getRange(row, col("items")).setWrap(true);
  sheet.getRange(row, col("status"))
    .setDataValidation(SpreadsheetApp.newDataValidation().requireValueInList(STATUSES, true).setAllowInvalid(false).build())
    .setFontWeight("bold");
  return true;
}

function reply(data) {
  // The spreadsheet's own address, for the "Open Google Sheet" button in the admin.
  try {
    data.sheetUrl = SpreadsheetApp.getActiveSpreadsheet().getUrl();
  } catch (err) {}
  return ContentService.createTextOutput(JSON.stringify(data)).setMimeType(ContentService.MimeType.JSON);
}
