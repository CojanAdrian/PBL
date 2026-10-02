CREATE TABLE IF NOT EXISTS preorders (
  code TEXT PRIMARY KEY,
  name TEXT NOT NULL,
  phone TEXT NOT NULL,
  email TEXT NOT NULL DEFAULT '',
  delivery TEXT NOT NULL,
  address TEXT NOT NULL DEFAULT '',
  wanted_date TEXT NOT NULL DEFAULT '',
  note TEXT NOT NULL DEFAULT '',
  items TEXT NOT NULL,
  total_grams INTEGER NOT NULL,
  total_price INTEGER NOT NULL,
  currency TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'new',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
