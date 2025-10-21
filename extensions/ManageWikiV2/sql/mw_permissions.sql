-- ManageWiki permissions table
CREATE TABLE IF NOT EXISTS /*_*/mw_permissions (
  perm_dbname VARCHAR(64) NOT NULL,
  perm_group VARCHAR(64) NOT NULL,
  perm_permissions TEXT DEFAULT NULL,
  perm_addgroups TEXT DEFAULT NULL,
  perm_removegroups TEXT DEFAULT NULL,
  PRIMARY KEY (perm_dbname, perm_group)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/perm_dbname ON /*_*/mw_permissions (perm_dbname);
CREATE INDEX /*i*/perm_group ON /*_*/mw_permissions (perm_group);
