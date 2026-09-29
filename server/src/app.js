// Express app factory. Dependencies (db pool, secrets, upload dir) are passed
// in so tests can run against an in-memory Postgres and a temp folder.
const crypto = require("crypto");
const fs = require("fs");
const path = require("path");
const express = require("express");
const bcrypt = require("bcryptjs");
const jwt = require("jsonwebtoken");
const multer = require("multer");

// Must match ReportCategory / ReportStatus raw values in Report.swift.
const CATEGORIES = ["pothole", "streetlight", "sidewalk", "markings", "other"];
const STATUSES = ["reported", "inProgress", "resolved"];
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
// A real hash to compare against when the email is unknown, so login takes
// the same time whether or not the account exists.
const DUMMY_HASH = bcrypt.hashSync("not-a-real-password", 10);

function createApp({ pool, jwtSecret, staffCode, uploadDir }) {
  if (!jwtSecret) throw new Error("jwtSecret is required");
  fs.mkdirSync(uploadDir, { recursive: true });

  const app = express();
  app.disable("x-powered-by");
  app.use(express.json({ limit: "100kb" }));

  const upload = multer({
    storage: multer.memoryStorage(),
    limits: { fileSize: 8 * 1024 * 1024, files: 1 },
  });

  const fail = (res, status, error) => res.status(status).json({ error });
  const wrap = (fn) => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

  const userJson = (u) => ({ id: u.id, email: u.email, role: u.role });
  const signToken = (u) => jwt.sign({ sub: u.id }, jwtSecret, { expiresIn: "30d" });

  // Loads the user fresh from the DB each request, so a role change or a
  // deleted account takes effect immediately.
  const requireAuth = wrap(async (req, res, next) => {
    const header = req.get("authorization") || "";
    const token = header.startsWith("Bearer ") ? header.slice(7) : null;
    if (!token) return fail(res, 401, "Not signed in.");
    let payload;
    try {
      payload = jwt.verify(token, jwtSecret);
    } catch {
      return fail(res, 401, "Session expired. Sign in again.");
    }
    const { rows } = await pool.query("SELECT id, email, role FROM users WHERE id = $1", [payload.sub]);
    if (!rows[0]) return fail(res, 401, "Not signed in.");
    req.user = rows[0];
    next();
  });

  const requireStaff = (req, res, next) =>
    req.user.role === "staff" ? next() : fail(res, 403, "Staff only.");

  const reportJson = (r, upvoterIds) => ({
    id: r.id,
    category: r.category,
    description: r.description,
    photoURL: r.photo_path ? `/photos/${r.photo_path}` : null,
    latitude: r.latitude,
    longitude: r.longitude,
    status: r.status,
    upvoterIds,
    reporterId: r.reporter_id,
    createdAt: new Date(r.created_at).toISOString(),
  });

  async function loadReports(id) {
    const where = id ? "WHERE id = $1" : "";
    const params = id ? [id] : [];
    const reports = (await pool.query(`SELECT * FROM reports ${where} ORDER BY created_at DESC`, params)).rows;
    const votes = (
      await pool.query(`SELECT report_id, user_id FROM upvotes ${id ? "WHERE report_id = $1" : ""}`, params)
    ).rows;
    const byReport = new Map();
    for (const v of votes) {
      if (!byReport.has(v.report_id)) byReport.set(v.report_id, []);
      byReport.get(v.report_id).push(v.user_id);
    }
    return reports.map((r) => reportJson(r, byReport.get(r.id) || []));
  }

  app.get("/health", (_req, res) => res.json({ ok: true }));

  // ---- auth ----
  app.post("/auth/signup", wrap(async (req, res) => {
    const email = String(req.body.email || "").trim().toLowerCase();
    const password = String(req.body.password || "");
    const code = String(req.body.staffCode || "").trim();
    if (!EMAIL_RE.test(email) || email.length > 254) return fail(res, 400, "That email address doesn't look right.");
    if (password.length < 6) return fail(res, 400, "Password must be at least 6 characters.");
    if (password.length > 72) return fail(res, 400, "Password is too long.");
    // Same as before: a wrong or empty code just makes a citizen account.
    const role = code && staffCode && code === staffCode ? "staff" : "citizen";
    const id = crypto.randomUUID();
    const hash = await bcrypt.hash(password, 10);
    try {
      await pool.query("INSERT INTO users (id, email, password_hash, role) VALUES ($1, $2, $3, $4)", [id, email, hash, role]);
    } catch (err) {
      if (err.code === "23505") return fail(res, 409, "An account with that email already exists.");
      throw err;
    }
    const user = { id, email, role };
    res.status(201).json({ token: signToken(user), user: userJson(user) });
  }));

  app.post("/auth/login", wrap(async (req, res) => {
    const email = String(req.body.email || "").trim().toLowerCase();
    const password = String(req.body.password || "");
    const { rows } = await pool.query("SELECT * FROM users WHERE email = $1", [email]);
    const user = rows[0];
    const ok = await bcrypt.compare(password, user ? user.password_hash : DUMMY_HASH);
    // Same message for wrong password and unknown email, to avoid revealing
    // which emails have accounts.
    if (!user || !ok) return fail(res, 401, "Incorrect email or password.");
    res.json({ token: signToken(user), user: userJson(user) });
  }));

  app.get("/me", requireAuth, (req, res) => res.json({ user: userJson(req.user) }));

  // ---- reports ----
  app.get("/reports", requireAuth, wrap(async (_req, res) => {
    res.json({ reports: await loadReports() });
  }));

  app.post("/reports", requireAuth, upload.single("photo"), wrap(async (req, res) => {
    const { category, description } = req.body;
    const latitude = Number(req.body.latitude);
    const longitude = Number(req.body.longitude);
    if (!CATEGORIES.includes(category)) return fail(res, 400, "Invalid category.");
    if (typeof description !== "string" || description.length > 2000) return fail(res, 400, "Invalid description.");
    if (!Number.isFinite(latitude) || Math.abs(latitude) > 90 || !Number.isFinite(longitude) || Math.abs(longitude) > 180) {
      return fail(res, 400, "Invalid location.");
    }
    const id = crypto.randomUUID();
    let photoPath = null;
    if (req.file) {
      // Only accept real JPEG/PNG bytes; never trust the client's filename.
      const b = req.file.buffer;
      const isJpeg = b[0] === 0xff && b[1] === 0xd8;
      const isPng = b[0] === 0x89 && b[1] === 0x50 && b[2] === 0x4e && b[3] === 0x47;
      if (!isJpeg && !isPng) return fail(res, 400, "Photo must be a JPEG or PNG.");
      photoPath = `${id}.${isPng ? "png" : "jpg"}`;
      await fs.promises.writeFile(path.join(uploadDir, photoPath), b);
    }
    // Status and reporter are set by the server, never taken from the client.
    await pool.query(
      `INSERT INTO reports (id, category, description, latitude, longitude, status, reporter_id, photo_path)
       VALUES ($1, $2, $3, $4, $5, 'reported', $6, $7)`,
      [id, category, description, latitude, longitude, req.user.id, photoPath]
    );
    res.status(201).json({ report: (await loadReports(id))[0] });
  }));

  // Toggles the caller's upvote; each user counts at most once.
  app.post("/reports/:id/upvote", requireAuth, wrap(async (req, res) => {
    const { id } = req.params;
    const found = await pool.query("SELECT id FROM reports WHERE id = $1", [id]);
    if (!found.rows[0]) return fail(res, 404, "Report not found.");
    const removed = await pool.query("DELETE FROM upvotes WHERE report_id = $1 AND user_id = $2", [id, req.user.id]);
    if (removed.rowCount === 0) {
      await pool.query("INSERT INTO upvotes (report_id, user_id) VALUES ($1, $2) ON CONFLICT DO NOTHING", [id, req.user.id]);
    }
    res.json({ report: (await loadReports(id))[0] });
  }));

  app.patch("/reports/:id/status", requireAuth, requireStaff, wrap(async (req, res) => {
    const { status } = req.body;
    if (!STATUSES.includes(status)) return fail(res, 400, "Invalid status.");
    const updated = await pool.query("UPDATE reports SET status = $1 WHERE id = $2", [status, req.params.id]);
    if (updated.rowCount === 0) return fail(res, 404, "Report not found.");
    res.json({ report: (await loadReports(req.params.id))[0] });
  }));

  // Public: filenames are unguessable UUIDs, and AsyncImage can't send a token.
  app.get("/photos/:file", (req, res) => {
    const file = req.params.file;
    if (!/^[0-9a-f-]{36}\.(jpg|png)$/.test(file)) return fail(res, 404, "Not found.");
    res.sendFile(path.join(path.resolve(uploadDir), file), (err) => {
      if (err && !res.headersSent) fail(res, 404, "Not found.");
    });
  });

  app.use((err, _req, res, _next) => {
    if (err instanceof multer.MulterError) return fail(res, 400, "Photo is too large or invalid.");
    if (err.type === "entity.parse.failed") return fail(res, 400, "Invalid JSON.");
    console.error(err);
    fail(res, 500, "Something went wrong, try again.");
  });

  return app;
}

module.exports = { createApp };
