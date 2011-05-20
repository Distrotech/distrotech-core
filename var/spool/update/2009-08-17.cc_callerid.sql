CREATE TABLE cc_callerid (
    callerid character varying(20) NOT NULL,
    reseller bigint,
    userid bigint
);
ALTER TABLE public.cc_callerid OWNER TO asterisk;
ALTER TABLE ONLY cc_callerid ADD CONSTRAINT cc_callerid_pkey PRIMARY KEY (callerid);
CREATE UNIQUE INDEX cc_callerid_all ON cc_callerid USING btree (callerid, reseller, userid);
CREATE UNIQUE INDEX cc_callerid_cid ON cc_callerid USING btree (callerid);
CREATE UNIQUE INDEX cc_callerid_cidr ON cc_callerid USING btree (callerid, reseller);
CREATE UNIQUE INDEX cc_callerid_cidu ON cc_callerid USING btree (callerid, userid);
