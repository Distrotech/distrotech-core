CREATE TABLE intersite (
    isiteid bigserial NOT NULL,
    companyid integer NOT NULL,
    dest character varying(16) NOT NULL,
    destmatch character varying(64) NOT NULL,
    destpre character varying(8) NOT NULL,
    deststrip integer DEFAULT 2 NOT NULL
);

CREATE TABLE companysites (
    companyid bigserial,
    source character varying(16),
    creditpool bigint
);

CREATE TABLE creditpool (
    poolid bigserial,
    companyid bigint,
    description character varying(80)
);
