CREATE TABLE climap (
    id serial,
    "match" character varying(255),
    prefix character varying(16) DEFAULT ''::character varying NOT NULL,
    strip integer DEFAULT 0 NOT NULL,
    trunk character varying(32) DEFAULT ''::character varying NOT NULL
);
ALTER TABLE ONLY climap ADD CONSTRAINT climap_pkey PRIMARY KEY (id);
