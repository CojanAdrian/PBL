const { test, before, after } = require("node:test");
const assert = require("node:assert/strict");
const fs = require("fs");
const os = require("os");
const path = require("path");
const { newDb } = require("pg-mem");
const { createApp } = require("../src/app");

let server, base, uploadDir;

before(async () => {
  const db = newDb();
  db.public.none(fs.readFileSync(path.join(__dirname, "..", "src", "schema.sql"), "utf8"));
  const { Pool } = db.adapters.createPg();
  uploadDir = fs.mkdtempSync(path.join(os.tmpdir(), "roadfix-"));
  const app = createApp({ pool: new Pool(), jwtSecret: "test-secret-test-secret", staffCode: "STAFF123", uploadDir });
  await new Promise((resolve) => {
    server = app.listen(0, "127.0.0.1", resolve);
  });
  base = `http://127.0.0.1:${server.address().port}`;
});

after(() => {
  server.close();
  fs.rmSync(uploadDir, { recursive: true, force: true });
});

async function call(method, url, { token, json, form } = {}) {
  const headers = {};
  if (token) headers.Authorization = `Bearer ${token}`;
  let body;
  if (json) {
    headers["Content-Type"] = "application/json";
    body = JSON.stringify(json);
  } else if (form) {
    body = form;
  }
  const res = await fetch(base + url, { method, headers, body });
  const data = res.headers.get("content-type")?.includes("json") ? await res.json() : null;
  return { status: res.status, data, res };
}

const signup = (email, extra = {}) => call("POST", "/auth/signup", { json: { email, password: "secret1", ...extra } });

function reportForm(fields = {}, photo) {
  const form = new FormData();
  const all = { category: "pothole", description: "Big hole", latitude: "47.02", longitude: "28.83", ...fields };
  for (const [k, v] of Object.entries(all)) form.append(k, v);
  if (photo) form.append("photo", new Blob([photo], { type: "image/jpeg" }), "x.jpg");
  return form;
}

test("signup makes a citizen, staff code makes staff, wrong code stays citizen", async () => {
  const citizen = await signup("Citizen@Example.com");
  assert.equal(citizen.status, 201);
  assert.equal(citizen.data.user.role, "citizen");
  assert.equal(citizen.data.user.email, "citizen@example.com");

  const staff = await signup("staff@example.com", { staffCode: "STAFF123" });
  assert.equal(staff.data.user.role, "staff");

  const wrong = await signup("wrong@example.com", { staffCode: "nope" });
  assert.equal(wrong.data.user.role, "citizen");
});

test("signup validates input and rejects duplicates", async () => {
  assert.equal((await signup("not-an-email")).status, 400);
  assert.equal((await call("POST", "/auth/signup", { json: { email: "a@b.co", password: "123" } })).status, 400);
  await signup("dupe@example.com");
  const again = await signup("DUPE@example.com");
  assert.equal(again.status, 409);
});

test("login works and gives the same error for wrong password and unknown email", async () => {
  await signup("login@example.com");
  const ok = await call("POST", "/auth/login", { json: { email: "login@example.com", password: "secret1" } });
  assert.equal(ok.status, 200);
  assert.ok(ok.data.token);

  const badPw = await call("POST", "/auth/login", { json: { email: "login@example.com", password: "wrong" } });
  const noUser = await call("POST", "/auth/login", { json: { email: "ghost@example.com", password: "secret1" } });
  assert.equal(badPw.status, 401);
  assert.equal(noUser.status, 401);
  assert.equal(badPw.data.error, noUser.data.error);
});

test("protected routes need a valid token", async () => {
  assert.equal((await call("GET", "/reports")).status, 401);
  assert.equal((await call("GET", "/reports", { token: "garbage" })).status, 401);
  const { data } = await signup("me@example.com");
  const me = await call("GET", "/me", { token: data.token });
  assert.equal(me.data.user.email, "me@example.com");
});

test("create report ignores client-supplied status and reporter", async () => {
  const { data: user } = await signup("reporter@example.com");
  const res = await call("POST", "/reports", {
    token: user.token,
    form: reportForm({ status: "resolved", reporterId: "someone-else" }),
  });
  assert.equal(res.status, 201);
  assert.equal(res.data.report.status, "reported");
  assert.equal(res.data.report.reporterId, user.user.id);
  assert.deepEqual(res.data.report.upvoterIds, []);
  assert.equal(res.data.report.photoURL, null);
});

test("create report validates fields", async () => {
  const { data: user } = await signup("validator@example.com");
  const bad = (fields) => call("POST", "/reports", { token: user.token, form: reportForm(fields) });
  assert.equal((await bad({ category: "alien" })).status, 400);
  assert.equal((await bad({ latitude: "abc" })).status, 400);
  assert.equal((await bad({ latitude: "123" })).status, 400);
  assert.equal((await bad({ description: "x".repeat(2001) })).status, 400);
});

test("photo upload is saved, served, and non-images are rejected", async () => {
  const { data: user } = await signup("photo@example.com");
  const jpeg = Buffer.from([0xff, 0xd8, 0xff, 0xe0, 1, 2, 3, 4]);
  const created = await call("POST", "/reports", { token: user.token, form: reportForm({}, jpeg) });
  assert.equal(created.status, 201);
  const url = created.data.report.photoURL;
  assert.match(url, /^\/photos\/[0-9a-f-]{36}\.jpg$/);

  const fetched = await fetch(base + url);
  assert.equal(fetched.status, 200);
  assert.deepEqual(Buffer.from(await fetched.arrayBuffer()), jpeg);

  const notImage = await call("POST", "/reports", { token: user.token, form: reportForm({}, Buffer.from("<html>")) });
  assert.equal(notImage.status, 400);

  assert.equal((await fetch(base + "/photos/..%2F..%2Fetc%2Fpasswd")).status, 404);
});

test("upvote toggles once per user", async () => {
  const { data: a } = await signup("a-upvote@example.com");
  const { data: b } = await signup("b-upvote@example.com");
  const { data: created } = await call("POST", "/reports", { token: a.token, form: reportForm() });
  const id = created.report.id;

  let r = await call("POST", `/reports/${id}/upvote`, { token: a.token });
  assert.deepEqual(r.data.report.upvoterIds, [a.user.id]);
  r = await call("POST", `/reports/${id}/upvote`, { token: b.token });
  assert.equal(r.data.report.upvoterIds.length, 2);
  r = await call("POST", `/reports/${id}/upvote`, { token: a.token });
  assert.deepEqual(r.data.report.upvoterIds, [b.user.id]);

  assert.equal((await call("POST", "/reports/nope/upvote", { token: a.token })).status, 404);
});

test("only staff can change status", async () => {
  const { data: citizen } = await signup("c-status@example.com");
  const { data: staff } = await signup("s-status@example.com", { staffCode: "STAFF123" });
  const { data: created } = await call("POST", "/reports", { token: citizen.token, form: reportForm() });
  const id = created.report.id;

  const denied = await call("PATCH", `/reports/${id}/status`, { token: citizen.token, json: { status: "resolved" } });
  assert.equal(denied.status, 403);

  const ok = await call("PATCH", `/reports/${id}/status`, { token: staff.token, json: { status: "inProgress" } });
  assert.equal(ok.status, 200);
  assert.equal(ok.data.report.status, "inProgress");

  assert.equal((await call("PATCH", `/reports/${id}/status`, { token: staff.token, json: { status: "bogus" } })).status, 400);
});

test("list returns newest first with upvotes attached", async () => {
  const { data: user } = await signup("list@example.com");
  const first = await call("POST", "/reports", { token: user.token, form: reportForm({ description: "first" }) });
  await new Promise((r) => setTimeout(r, 15));
  await call("POST", "/reports", { token: user.token, form: reportForm({ description: "second" }) });
  await call("POST", `/reports/${first.data.report.id}/upvote`, { token: user.token });

  const { data } = await call("GET", "/reports", { token: user.token });
  const mine = data.reports.filter((r) => r.reporterId === user.user.id);
  assert.deepEqual(mine.map((r) => r.description), ["second", "first"]);
  assert.equal(mine[1].upvoterIds.length, 1);
});
