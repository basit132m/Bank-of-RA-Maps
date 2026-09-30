-- Bank of YR Maps — initial schema (SQLite, local development only)
-- Keep in sync with 001_initial.mysql.sql

CREATE TABLE users (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  username       TEXT NOT NULL UNIQUE,
  email          TEXT NOT NULL UNIQUE,
  password_hash  TEXT NOT NULL,
  display_name   TEXT,
  role           TEXT NOT NULL DEFAULT 'member',
  bio            TEXT,
  created_at     TEXT NOT NULL,
  last_login_at  TEXT
);

CREATE TABLE maps (
  id               INTEGER PRIMARY KEY AUTOINCREMENT,
  slug             TEXT NOT NULL UNIQUE,
  title            TEXT NOT NULL,
  version          TEXT NOT NULL DEFAULT '1.0',
  game             TEXT NOT NULL DEFAULT 'yr',
  players          INTEGER NOT NULL DEFAULT 2,
  size_x           INTEGER,
  size_y           INTEGER,
  theater          TEXT NOT NULL DEFAULT 'temperate',
  summary          TEXT,
  designer_notes   TEXT,
  install_notes    TEXT,
  tech_structures  INTEGER NOT NULL DEFAULT 0,
  oil_derricks     INTEGER NOT NULL DEFAULT 0,
  ore_density      TEXT,
  gem_count        INTEGER NOT NULL DEFAULT 0,
  cncnet_ready     INTEGER NOT NULL DEFAULT 1,
  preview_image    TEXT,
  author_id        INTEGER REFERENCES users (id) ON DELETE SET NULL,
  author_name      TEXT,
  status           TEXT NOT NULL DEFAULT 'draft',
  featured         INTEGER NOT NULL DEFAULT 0,
  download_count   INTEGER NOT NULL DEFAULT 0,
  view_count       INTEGER NOT NULL DEFAULT 0,
  published_at     TEXT,
  created_at       TEXT NOT NULL,
  updated_at       TEXT NOT NULL
);
CREATE INDEX idx_maps_listing  ON maps (status, published_at);
CREATE INDEX idx_maps_players  ON maps (players);
CREATE INDEX idx_maps_theater  ON maps (theater);
CREATE INDEX idx_maps_featured ON maps (featured);

CREATE TABLE map_files (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  map_id         INTEGER NOT NULL REFERENCES maps (id) ON DELETE CASCADE,
  stored_name    TEXT NOT NULL UNIQUE,
  original_name  TEXT NOT NULL,
  bytes          INTEGER NOT NULL DEFAULT 0,
  kind           TEXT NOT NULL DEFAULT 'map',
  created_at     TEXT NOT NULL
);
CREATE INDEX idx_map_files_map ON map_files (map_id);

CREATE TABLE map_images (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  map_id      INTEGER NOT NULL REFERENCES maps (id) ON DELETE CASCADE,
  path        TEXT NOT NULL,
  kind        TEXT NOT NULL DEFAULT 'screenshot',
  alt         TEXT,
  sort_order  INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX idx_map_images_map ON map_images (map_id, sort_order);

CREATE TABLE map_versions (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  map_id       INTEGER NOT NULL REFERENCES maps (id) ON DELETE CASCADE,
  version      TEXT NOT NULL,
  released_at  TEXT,
  notes        TEXT
);
CREATE INDEX idx_map_versions_map ON map_versions (map_id, id);

CREATE TABLE tags (
  id    INTEGER PRIMARY KEY AUTOINCREMENT,
  slug  TEXT NOT NULL UNIQUE,
  name  TEXT NOT NULL
);

CREATE TABLE map_tag (
  map_id  INTEGER NOT NULL REFERENCES maps (id) ON DELETE CASCADE,
  tag_id  INTEGER NOT NULL REFERENCES tags (id) ON DELETE CASCADE,
  PRIMARY KEY (map_id, tag_id)
);
CREATE INDEX idx_map_tag_tag ON map_tag (tag_id);

CREATE TABLE modes (
  id    INTEGER PRIMARY KEY AUTOINCREMENT,
  slug  TEXT NOT NULL UNIQUE,
  name  TEXT NOT NULL
);

CREATE TABLE map_mode (
  map_id   INTEGER NOT NULL REFERENCES maps (id) ON DELETE CASCADE,
  mode_id  INTEGER NOT NULL REFERENCES modes (id) ON DELETE CASCADE,
  PRIMARY KEY (map_id, mode_id)
);
CREATE INDEX idx_map_mode_mode ON map_mode (mode_id);

CREATE TABLE comments (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  map_id       INTEGER NOT NULL REFERENCES maps (id) ON DELETE CASCADE,
  user_id      INTEGER REFERENCES users (id) ON DELETE SET NULL,
  author_name  TEXT,
  body         TEXT NOT NULL,
  status       TEXT NOT NULL DEFAULT 'pending',
  ip_hash      TEXT,
  created_at   TEXT NOT NULL
);
CREATE INDEX idx_comments_map ON comments (map_id, status, created_at);

CREATE TABLE download_events (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  map_id      INTEGER NOT NULL REFERENCES maps (id) ON DELETE CASCADE,
  file_id     INTEGER,
  ip_hash     TEXT,
  created_at  TEXT NOT NULL
);
CREATE INDEX idx_download_events_map ON download_events (map_id, created_at);
CREATE INDEX idx_download_events_ip  ON download_events (ip_hash, created_at);
