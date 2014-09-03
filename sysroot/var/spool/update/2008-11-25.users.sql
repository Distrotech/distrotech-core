ALTER TABLE users ADD regserver varchar(64);
ALTER TABLE users ADD deletevoicemail varchar(8) default 'no';
ALTER TABLE users ADD rpid varchar(64);
CREATE UNIQUE INDEX users_username ON users USING btree (username);
ALTER TABLE users rename cancallforward to faxdetect;
