
CREATE TABLE tree (
  id        bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  view_id   bigint(20) unsigned NOT NULL,
  parent_id bigint(20) unsigned NOT NULL,
  position  bigint(20) unsigned NOT NULL,
  title     varchar(255) DEFAULT NULL,
  pwtype    varchar(255) DEFAULT NULL,
  agg_id    bigint(20) unsigned DEFAULT NULL AUTO_INCREMENT,
  datas     text NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY id (id,parent_id),
  KEY view_id (view_id),
  KEY title (title),
  KEY pwtype (pwtype),
  KEY id_2 (id,title)
);

CREATE TABLE selections (
  id           bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  tree_id      bigint(20) unsigned NOT NULL,
  title        varchar(255) DEFAULT NULL,
  deleteafter  bigint(20) unsigned NOT NULL,
  data         text                NOT NULL,
  PRIMARY KEY  (id),
  KEY          (tree_id)
);

INSERT INTO tree (id,view_id, parent_id,position,title, pwtype) VALUES (2,1,1,0,'Default view', 'container');

