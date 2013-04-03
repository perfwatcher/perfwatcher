
CREATE TABLE tree (
  id        bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  parent_id bigint(20) unsigned NOT NULL,
  position  bigint(20) unsigned NOT NULL,
  title     varchar(255) DEFAULT NULL,
  type      varchar(255) DEFAULT NULL,
  datas     text NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY id (id,parent_id),
  KEY title (title),
  KEY type (type),
  KEY id_2 (id,title)
);
