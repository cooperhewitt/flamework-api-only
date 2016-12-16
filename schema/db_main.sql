CREATE TABLE users (
  id SERIAL,
  username varchar(255) DEFAULT NULL,
  email varchar(255) DEFAULT NULL,
  deleted int DEFAULT 0,
  created int DEFAULT 0,
  password char(64) DEFAULT NULL,
  conf_code char(24) DEFAULT NULL,
  confirmed int DEFAULT 0,
  cluster_id int DEFAULT 0
);
