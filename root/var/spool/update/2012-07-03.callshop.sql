CREATE INDEX tariffrate_rate ON tariffrate USING btree (rate);
CREATE INDEX tariffrate_ratecode ON tariffrate USING btree (rate,tariffcode);
CREATE INDEX countryprefix_breakout ON countryprefix USING btree (countrycode,subcode,trunkprefix);
CREATE INDEX tariffrate_breakout ON tariffrate USING btree (countrycode,subcode,trunkprefix);
CREATE INDEX trunk_prefix ON trunk USING btree (trunkprefix);
CREATE INDEX trunk_prefixgk ON trunk USING btree (trunkprefix,h323reggk);
CREATE INDEX cc_route_breakout ON cc_route USING btree (countrycode,subcode,trunkprefix);
CREATE INDEX cc_route_all ON cc_route USING btree (countrycode,subcode,trunkprefix,userid);
CREATE INDEX cc_climap_userid ON cc_climap USING btree (userid);
CREATE INDEX countryprefix_prefix ON countryprefix USING btree (prefix);
CREATE INDEX users_active ON users USING btree (name,activated);
CREATE INDEX companysites_companyid ON companysites USING btree (companyid);
CREATE INDEX companysites_companypool ON companysites USING btree (companyid,creditpool);
CREATE INDEX companysites_source ON companysites USING btree (source);
CREATE INDEX creditpool_companypool ON creditpool USING btree (companyid,poolid);
CREATE INDEX intersite_pkey ON intersite USING btree (isiteid);
CREATE INDEX intersite_dest ON intersite USING btree (dest);
CREATE INDEX creditpool_pkey ON creditpool USING btree (poolid);
CREATE TABLE virtualcompany (
    resellerid bigserial,
    companyid bigint NOT NULL,
    description character varying(80),
    email character varying(80),
    contact character varying(80),
    altnumber character varying(80)
);
CREATE INDEX virtualcompany_pkey ON virtualcompany USING btree (companyid);
CREATE INDEX virtualcompany_resleller ON virtualcompany USING btree (resellerid);
CREATE INDEX tariffrate_tprefix ON tariffrate USING btree (trunkprefix);
CREATE INDEX package_user ON package USING btree (userid);
