-- Bank of YR Maps — initial schema (MySQL / MariaDB)

CREATE TABLE users (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username       VARCHAR(40)  NOT NULL,
  email          VARCHAR(190) NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,
  display_name   VARCHAR(80)      DEFAULT NULL,
  role           VARCHAR(20)  NOT NULL DEFAULT 'member',
  bio            TEXT             DEFAULT NULL,
  created_at     DATETIME     NOT NULL,
  last_login_at  DATETIME         DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE maps (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug             VARCHAR(120) NOT NULL,
  title            VARCHAR(160) NOT NULL,
  version          VARCHAR(20)  NOT NULL DEFAULT '1.0',
  game             VARCHAR(10)  NOT NULL DEFAULT 'yr',
  players          TINYINT UNSIGNED NOT NULL DEFAULT 2,
  size_x           SMALLINT UNSIGNED DEFAULT NULL,
  size_y           SMALLINT UNSIGNED DEFAULT NULL,
  theater          VARCHAR(20)  NOT NULL DEFAULT 'temperate',
  summary          VARCHAR(300)     DEFAULT NULL,
  designer_notes   MEDIUMTEXT       DEFAULT NULL,
  install_notes    MEDIUMTEXT       DEFAULT NULL,
  tech_structures  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  oil_derricks     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  ore_density      VARCHAR(20)      DEFAULT NULL,
  gem_count        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  cncnet_ready     TINYINT(1)   NOT NULL DEFAULT 1,
  preview_image    VARCHAR(255)     DEFAULT NULL,
  author_id        INT UNSIGNED     DEFAULT NULL,
  author_name      VARCHAR(80)      DEFAULT NULL,
  status           VARCHAR(20)  NOT NULL DEFAULT 'draft',
  featured         TINYINT(1)   NOT NULL DEFAULT 0,
  download_count   INT UNSIGNED NOT NULL DEFAULT 0,
  view_count       INT UNSIGNED NOT NULL DEFAULT 0,
  published_at     DATETIME         DEFAULT NULL,
  created_at       DATETIME     NOT NULL,
  updated_at       DATETIME     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_maps_slug (slug),
  KEY idx_maps_listing (status, published_at),
  KEY idx_maps_players (players),
  KEY idx_maps_theater (theater),
  KEY idx_maps_featured (featured),
  CONSTRAINT fk_maps_author FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE map_files (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  map_id         INT UNSIGNED NOT NULL,
  stored_name    VARCHAR(255) NOT NULL,
  original_name  VARCHAR(255) NOT NULL,
  bytes          INT UNSIGNED NOT NULL DEFAULT 0,
  kind           VARCHAR(20)  NOT NULL DEFAULT 'map',
  created_at     DATETIME     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_map_files_stored (stored_name),
  KEY idx_map_files_map (map_id),
  CONSTRAINT fk_map_files_map FOREIGN KEY (map_id) REFERENCES maps (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE map_images (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  map_id      INT UNSIGNED NOT NULL,
  path        VARCHAR(255) NOT NULL,
  kind        VARCHAR(20)  NOT NULL DEFAULT 'screenshot',
  alt         VARCHAR(200)     DEFAULT NULL,
  sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_map_images_map (map_id, sort_order),
  CONSTRAINT fk_map_images_map FOREIGN KEY (map_id) REFERENCES maps (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE map_versions (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  map_id       INT UNSIGNED NOT NULL,
  version      VARCHAR(20)  NOT NULL,
  released_at  DATE             DEFAULT NULL,
  notes        TEXT             DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_map_versions_map (map_id, id),
  CONSTRAINT fk_map_versions_map FOREIGN KEY (map_id) REFERENCES maps (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tags (
  id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug  VARCHAR(60)  NOT NULL,
  name  VARCHAR(60)  NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE map_tag (
  map_id  INT UNSIGNED NOT NULL,
  tag_id  INT UNSIGNED NOT NULL,
  PRIMARY KEY (map_id, tag_id),
  KEY idx_map_tag_tag (tag_id),
  CONSTRAINT fk_map_tag_map FOREIGN KEY (map_id) REFERENCES maps (id) ON DELETE CASCADE,
  CONSTRAINT fk_map_tag_tag FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE modes (
  id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug  VARCHAR(40)  NOT NULL,
  name  VARCHAR(60)  NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_modes_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE map_mode (
  map_id   INT UNSIGNED NOT NULL,
  mode_id  INT UNSIGNED NOT NULL,
  PRIMARY KEY (map_id, mode_id),
  KEY idx_map_mode_mode (mode_id),
  CONSTRAINT fk_map_mode_map FOREIGN KEY (map_id) REFERENCES maps (id) ON DELETE CASCADE,
  CONSTRAINT fk_map_mode_mode FOREIGN KEY (mode_id) REFERENCES modes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  map_id       INT UNSIGNED NOT NULL,
  user_id      INT UNSIGNED     DEFAULT NULL,
  author_name  VARCHAR(80)      DEFAULT NULL,
  body         TEXT         NOT NULL,
  status       VARCHAR(20)  NOT NULL DEFAULT 'pending',
  ip_hash      CHAR(64)         DEFAULT NULL,
  created_at   DATETIME     NOT NULL,
  PRIMARY KEY (id),
  KEY idx_comments_map (map_id, status, created_at),
  CONSTRAINT fk_comments_map FOREIGN KEY (map_id) REFERENCES maps (id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE download_events (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  map_id      INT UNSIGNED NOT NULL,
  file_id     INT UNSIGNED     DEFAULT NULL,
  ip_hash     CHAR(64)         DEFAULT NULL,
  created_at  DATETIME     NOT NULL,
  PRIMARY KEY (id),
  KEY idx_download_events_map (map_id, created_at),
  KEY idx_download_events_ip (ip_hash, created_at),
  CONSTRAINT fk_download_events_map FOREIGN KEY (map_id) REFERENCES maps (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
