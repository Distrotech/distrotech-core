CREATE TABLE inuse (
    userid character varying(16) NOT NULL,
    uniqueid character varying(32) NOT NULL,
    cleared boolean DEFAULT false,
    setuptime timestamp without time zone DEFAULT now(),
    callocated integer,
    setup timestamp without time zone DEFAULT now()
);
ALTER TABLE ONLY inuse ADD CONSTRAINT inuse_key PRIMARY KEY (uniqueid);
CREATE UNIQUE INDEX inuse_call ON inuse USING btree (userid, uniqueid);
CREATE UNIQUE INDEX inuse_clearcall ON inuse USING btree (userid, uniqueid, cleared);
CREATE INDEX inuse_uniqueid ON inuse USING btree (uniqueid);
CREATE INDEX inuse_userid ON inuse USING btree (userid);
