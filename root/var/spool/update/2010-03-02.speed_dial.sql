DROP TABLE IF EXISTS speed_dial;
CREATE TABLE speed_dial (
    number character varying(20) NOT NULL,
    dest character varying(32),
    discrip character varying(64)
);
ALTER TABLE ONLY speed_dial ADD CONSTRAINT speed_dial_pkey PRIMARY KEY (number);
CREATE INDEX speed_dial_num ON speed_dial USING btree (number);
INSERT INTO speed_dial (number,dest) SELECT key,value FROM astdb LEFT OUTER JOIN speed_dial ON (number=key) WHERE family='SDIAL' AND number IS NULL ORDER BY key;
