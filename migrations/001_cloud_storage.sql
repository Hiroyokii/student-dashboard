-- Required for Vercel: shared PHP sessions and persistent uploaded images.
CREATE TABLE IF NOT EXISTS app_sessions (
  session_id varchar(128) NOT NULL,
  session_data mediumblob NOT NULL,
  last_activity int unsigned NOT NULL,
  PRIMARY KEY (session_id),
  KEY idx_app_sessions_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_media (
  media_key varchar(100) NOT NULL,
  mime_type varchar(50) NOT NULL,
  file_data mediumblob NOT NULL,
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (media_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
