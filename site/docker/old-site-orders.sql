-- The old Node site's table, with one order, as it is in the Railway database.
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
INSERT INTO preorders (code, name, phone, delivery, items, total_grams, total_price, currency, note, created_at)
VALUES ('BED-OLD22', 'Ion Rusu', '+37369000111', 'delivery',
        '[{"flavour":"beef-honey-sriracha","grams":100,"qty":2,"price":130},{"flavour":"taster-box","grams":300,"qty":1,"price":285}]',
        500, 545, 'lei', 'From the old site', now() - interval '2 days')
ON CONFLICT DO NOTHING;
