-- CreateWiki comments table
CREATE TABLE IF NOT EXISTS /*_*/cw_comments (
  cw_id INT UNSIGNED NOT NULL,
  cw_comment TEXT NOT NULL,
  cw_user INT UNSIGNED NOT NULL,
  cw_timestamp BINARY(14) NOT NULL
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/cw_id ON /*_*/cw_comments (cw_id);
CREATE INDEX /*i*/cw_timestamp ON /*_*/cw_comments (cw_timestamp);
