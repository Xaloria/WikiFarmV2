-- ManageWiki namespaces table
CREATE TABLE IF NOT EXISTS /*_*/mw_namespaces (
  ns_dbname VARCHAR(64) NOT NULL,
  ns_namespace_id INT NOT NULL,
  ns_namespace_name VARCHAR(255) NOT NULL,
  ns_searchable TINYINT(1) NOT NULL DEFAULT 1,
  ns_subpages TINYINT(1) NOT NULL DEFAULT 1,
  ns_content TINYINT(1) NOT NULL DEFAULT 0,
  ns_protection VARCHAR(64) DEFAULT NULL,
  ns_aliases TEXT DEFAULT NULL,
  PRIMARY KEY (ns_dbname, ns_namespace_id)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/ns_dbname ON /*_*/mw_namespaces (ns_dbname);
CREATE INDEX /*i*/ns_namespace_id ON /*_*/mw_namespaces (ns_namespace_id);
