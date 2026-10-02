// Emails Ed's team when a preorder comes in. Uses an email service's web API
// (Resend or Brevo) rather than SMTP, because many hosts, Railway's cheaper
// plans included, block outgoing SMTP.

const esc = (v) => String(v ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);
const money = (n) => Number(n).toLocaleString("en-US");

function parseFrom(from) {
  const m = /^\s*(.*?)\s*<([^>]+)>\s*$/.exec(from || "");
  if (m) return { name: m[1].replace(/^"|"$/g, "") || undefined, email: m[2].trim() };
  return { email: String(from || "").trim() };
}

// Returns null when no provider is configured.
function createMailer({ resendKey, brevoKey, from, fetchImpl = fetch } = {}) {
  const post = async (url, headers, body, label) => {
    const res = await fetchImpl(url, {
      method: "POST",
      headers: { "Content-Type": "application/json", ...headers },
      body: JSON.stringify(body),
      signal: AbortSignal.timeout(10000),
    });
    if (!res.ok) {
      const detail = typeof res.text === "function" ? (await res.text()).slice(0, 300) : "";
      throw new Error(`${label} responded ${res.status} ${detail}`);
    }
  };

  if (resendKey) {
    return {
      provider: "resend",
      send: (msg) =>
        post(
          "https://api.resend.com/emails",
          { Authorization: `Bearer ${resendKey}` },
          {
            from: from || "Big Ed's Jerky <onboarding@resend.dev>",
            to: [msg.to],
            subject: msg.subject,
            text: msg.text,
            html: msg.html,
            ...(msg.replyTo ? { reply_to: msg.replyTo } : {}),
          },
          "Resend"
        ),
    };
  }
  if (brevoKey) {
    const sender = parseFrom(from);
    if (!sender.email) throw new Error("EMAIL_FROM is required when using BREVO_API_KEY");
    return {
      provider: "brevo",
      send: (msg) =>
        post(
          "https://api.brevo.com/v3/smtp/email",
          { "api-key": brevoKey },
          {
            sender: { email: sender.email, name: sender.name || "Big Ed's Jerky" },
            to: [{ email: msg.to }],
            subject: msg.subject,
            textContent: msg.text,
            htmlContent: msg.html,
            ...(msg.replyTo ? { replyTo: { email: msg.replyTo } } : {}),
          },
          "Brevo"
        ),
    };
  }
  return null;
}

function buildOrderEmail({ code, order, config, adminUrl }) {
  const names = new Map(config.flavours.map((f) => [f.id, f.name]));
  const cur = order.currency;
  const packs = order.totalPacks ?? order.items.reduce((s, i) => s + i.qty, 0);
  const lines = order.items.map((i) => ({ label: `${i.qty} x ${i.grams} g ${names.get(i.flavour) || i.flavour}`, price: i.price * i.qty }));
  const contact = [order.phone && `Phone: ${order.phone}`, order.email && `Email: ${order.email}`].filter(Boolean);
  const where = order.delivery === "delivery" ? `Delivery to: ${order.address}` : "Pickup";

  const text = [
    `New preorder ${code}`,
    "",
    `Total: ${money(order.totalPrice)} ${cur} (${money(order.totalGrams)} g, ${packs} ${packs === 1 ? "pack" : "packs"})`,
    "",
    ...lines.map((l) => `- ${l.label}: ${money(l.price)} ${cur}`),
    "",
    `Customer: ${order.name}`,
    ...contact,
    where,
    order.date ? `Wanted date: ${order.date}` : "",
    order.note ? `Note: ${order.note}` : "",
    "",
    adminUrl ? `Manage preorders: ${adminUrl}` : "",
  ]
    .filter((l, i, arr) => l !== "" || (arr[i - 1] !== "" && i > 0))
    .join("\n")
    .trim();

  const row = (a, b, bold) =>
    `<tr><td style="padding:8px 0;border-bottom:1px solid #eee;${bold ? "font-weight:700;" : ""}">${a}</td><td style="padding:8px 0;border-bottom:1px solid #eee;text-align:right;white-space:nowrap;${bold ? "font-weight:700;" : ""}">${b}</td></tr>`;
  const html = `<!doctype html><html><body style="margin:0;background:#f4efe9;font-family:Arial,Helvetica,sans-serif;color:#1b1614">
<div style="max-width:560px;margin:0 auto;padding:24px 16px">
  <div style="background:#0a0908;color:#fff;border-radius:16px 16px 0 0;padding:20px 24px">
    <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#ff6a2b;font-weight:700">New preorder</div>
    <div style="font-size:30px;font-weight:800;letter-spacing:2px;margin-top:4px">${esc(code)}</div>
  </div>
  <div style="background:#fff;border-radius:0 0 16px 16px;padding:24px">
    <table style="width:100%;border-collapse:collapse;font-size:15px">
      ${lines.map((l) => row(esc(l.label), `${money(l.price)} ${esc(cur)}`)).join("")}
      ${row(`Total (${money(order.totalGrams)} g, ${packs} ${packs === 1 ? "pack" : "packs"})`, `${money(order.totalPrice)} ${esc(cur)}`, true)}
    </table>
    <p style="margin:20px 0 4px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#7a6f67;font-weight:700">Customer</p>
    <p style="margin:0;font-size:15px;line-height:1.6"><strong>${esc(order.name)}</strong><br>
      ${order.phone ? `Phone: <a href="tel:${esc(order.phone)}" style="color:#c71e18">${esc(order.phone)}</a><br>` : ""}
      ${order.email ? `Email: <a href="mailto:${esc(order.email)}" style="color:#c71e18">${esc(order.email)}</a><br>` : ""}
      ${esc(where)}${order.date ? `<br>Wanted date: ${esc(order.date)}` : ""}</p>
    ${order.note ? `<p style="margin:16px 0 0;padding:12px 14px;background:#f4efe9;border-radius:10px;font-size:14px"><strong>Note:</strong> ${esc(order.note)}</p>` : ""}
    ${adminUrl ? `<p style="margin:24px 0 0"><a href="${esc(adminUrl)}" style="display:inline-block;background:#c71e18;color:#fff;text-decoration:none;font-weight:700;padding:12px 22px;border-radius:999px">Open preorders</a></p>` : ""}
  </div>
</div></body></html>`;

  return {
    subject: `New preorder ${code}: ${money(order.totalPrice)} ${cur}`,
    text,
    html,
    replyTo: order.email || undefined,
  };
}

module.exports = { createMailer, buildOrderEmail, parseFrom };
