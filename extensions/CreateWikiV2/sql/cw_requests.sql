-- CreateWiki requests table
CREATE TABLE IF NOT EXISTS /*_*/cw_requests (
  cw_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  cw_dbname VARCHAR(64) NOT NULL,
  cw_sitename VARCHAR(128) NOT NULL,
  cw_language VARCHAR(12) NOT NULL,
  cw_private TINYINT(1) NOT NULL DEFAULT 0,
  cw_category VARCHAR(64) NOT NULL DEFAULT 'uncategorised',
  cw_url VARCHAR(255) NOT NULL,
  cw_user INT UNSIGNED NOT NULL,
  cw_reason TEXT NOT NULL,
  cw_status VARCHAR(16) NOT NULL DEFAULT 'pending',
  cw_timestamp BINARY(14) NOT NULL,
  cw_comment TEXT DEFAULT NULL
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/cw_status ON /*_*/cw_requests (cw_status);
CREATE INDEX /*i*/cw_user ON /*_*/cw_requests (cw_user);
CREATE INDEX /*i*/cw_timestamp ON /*_*/cw_requests (cw_timestamp);
CREATE INDEX /*i*/cw_dbname ON /*_*/cw_requests (cw_dbname);
