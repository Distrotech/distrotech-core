CREATE TABLE ivrconf (
    ivr character varying(24) NOT NULL,
    officehours boolean DEFAULT true NOT NULL,
    "option" character varying(8) NOT NULL,
    "action" character varying(16) NOT NULL,
    data character varying(24) NOT NULL,
    command character varying(48) NOT NULL
);
ALTER TABLE ONLY ivrconf ADD CONSTRAINT ivrconf_pkey PRIMARY KEY (ivr, officehours, "option");
CREATE INDEX ivrconf_ivr ON ivrconf USING btree (ivr, officehours);
