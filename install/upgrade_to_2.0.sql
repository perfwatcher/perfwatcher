
ALTER TABLE tree ADD pwtype varchar(255) DEFAULT NULL AFTER type;
UPDATE tree set pwtype = "server" where type = "default";
UPDATE tree set pwtype = "container" where type <> "default";
ALTER TABLE tree ADD INDEX pwtype (pwtype);

