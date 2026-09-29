# RoadFix API

Express + Postgres REST API used by the iOS app. Deployed on Railway.

## Railway setup

1. In the Railway project, **New → Database → PostgreSQL**.
2. **New → GitHub Repo** (this repo) and set **Root Directory** to `/server`. This is a separate service from `web-sim`.
3. On the API service, add a **Volume** mounted at `/data` (photos are saved here; without it they vanish on every deploy).
4. Set the API service's variables:

   | Variable | Value |
   | --- | --- |
   | `DATABASE_URL` | `${{Postgres.DATABASE_URL}}` (reference to the Postgres service) |
   | `JWT_SECRET` | a long random string (32+ chars) |
   | `STAFF_INVITE_CODE` | the code people enter at sign-up to become staff |
   | `UPLOAD_DIR` | `/data` |

5. **Settings → Networking → Generate Domain**, then put that URL in `APIConfig.baseURL` in `RoadFix/Services/APIClient.swift`.

Tables are created automatically on startup (`src/schema.sql`).

## Local development

Needs a local Postgres.

```
cd server
npm install
DATABASE_URL=postgres://localhost/roadfix JWT_SECRET=dev-secret-dev-secret-1 STAFF_INVITE_CODE=STAFF npm start
```

`npm test` runs the API tests against an in-memory Postgres, so it needs no database.

## Endpoints

| Method | Path | Notes |
| --- | --- | --- |
| POST | `/auth/signup` | `{email, password, staffCode?}` |
| POST | `/auth/login` | `{email, password}` |
| GET | `/me` | current user |
| GET | `/reports` | all reports, newest first |
| POST | `/reports` | multipart: `category, description, latitude, longitude, photo?` |
| POST | `/reports/:id/upvote` | toggles the caller's upvote |
| PATCH | `/reports/:id/status` | staff only, `{status}` |
| GET | `/photos/:file` | public |
| GET | `/health` | for uptime checks |

Everything except `/auth/*`, `/photos/*` and `/health` needs `Authorization: Bearer <token>`.
