ALTER TABLE cdr ADD linkedid varchar(80);
CREATE INDEX cdr_linkedid ON cdr USING BTREE (linkedid);
