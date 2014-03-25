
ALTER TABLE tree ADD view_id   bigint(20) unsigned NOT NULL AFTER id;
UPDATE tree set view_id=1;
ALTER TABLE tree ADD INDEX view_id (view_id);

