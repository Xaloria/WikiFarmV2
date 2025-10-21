-- CreateWiki wikis table
CREATE TABLE IF NOT EXISTS /*_*/cw_wikis (
  wiki_dbname VARCHAR(64) NOT NULL PRIMARY KEY,
  wiki_sitename VARCHAR(128) NOT NULL,
  wiki_language VARCHAR(12) NOT NULL,
  wiki_private TINYINT(1) NOT NULL DEFAULT 0,
  wiki_closed TINYINT(1) NOT NULL DEFAULT 0,
  wiki_inactive TINYINT(1) NOT NULL DEFAULT 0,
  wiki_category VARCHAR(64) NOT NULL DEFAULT 'uncategorised',
  wiki_url VARCHAR(255) NOT NULL,
  wiki_creation BINARY(14) NOT NULL,
  wiki_settings MEDIUMBLOB DEFAULT NULL
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/wiki_category ON /*_*/cw_wikis (wiki_category);
CREATE INDEX /*i*/wiki_closed ON /*_*/cw_wikis (wiki_closed);
CREATE INDEX /*i*/wiki_inactive ON /*_*/cw_wikis (wiki_inactive);
CREATE INDEX /*i*/wiki_creation ON /*_*/cw_wikis (wiki_creation);
