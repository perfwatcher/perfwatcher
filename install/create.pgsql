
DROP TABLE IF EXISTS tree;

DROP SEQUENCE IF EXISTS tree_id_seq;
CREATE SEQUENCE tree_id_seq START 2;

CREATE TABLE tree (
  id        integer NOT NULL DEFAULT nextval('tree_id_seq') UNIQUE CHECK (id > 1),
  view_id   numeric(20) NOT NULL,
  parent_id numeric(20) NOT NULL,
  position  numeric(20) NOT NULL,
  title     varchar(255) DEFAULT NULL,
  pwtype    varchar(255) DEFAULT NULL,
  agg_id    numeric(20) DEFAULT NULL,
  cdsrc     varchar(255) DEFAULT NULL,
  datas     text DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE (id,parent_id)
);
ALTER SEQUENCE tree_id_seq OWNED BY tree.id;

CREATE INDEX view_id ON tree (view_id);
CREATE INDEX title ON tree (title);
CREATE INDEX pwtype ON tree (pwtype);
CREATE INDEX id_title ON tree (id,title);

DROP TABLE IF EXISTS selections;
CREATE TABLE selections (
  id           SERIAL UNIQUE,
  tree_id      numeric(20) NOT NULL,
  title        varchar(255) DEFAULT NULL,
  sortorder    numeric(20) NOT NULL,
  deleteafter  numeric(20) NOT NULL,
  data         text                NOT NULL,
  PRIMARY KEY  (id)
);

CREATE INDEX tree_id ON selections (tree_id);

DROP TABLE IF EXISTS config;
CREATE TABLE config (
  confkey      varchar(255) NOT NULL,
  value        text         NOT NULL,
  PRIMARY KEY  (confkey)
);

INSERT INTO tree (view_id, parent_id,position,title, pwtype) VALUES (1,1,0,'Default view', 'container');
INSERT INTO config (confkey, value) VALUES ('schema_version', '1.1');

