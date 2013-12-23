SET client_encoding = 'SQL_ASCII';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;
SET search_path = public, pg_catalog;
ALTER TABLE ONLY public."location" DROP CONSTRAINT location_pkey;
ALTER TABLE public."location" ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE public.location_id_seq;
DROP TABLE public."location";
SET default_tablespace = '';
SET default_with_oids = false;
CREATE TABLE "location" (
    id integer NOT NULL,
    username character varying(64) DEFAULT ''::character varying NOT NULL,
    "domain" character varying(64),
    contact character varying(255) DEFAULT ''::character varying NOT NULL,
    received character varying(128),
    path character varying(128),
    expires timestamp without time zone DEFAULT '2020-05-28 21:32:15'::timestamp without time zone NOT NULL,
    q real DEFAULT 1.0 NOT NULL,
    callid character varying(255) DEFAULT 'Default-Call-ID'::character varying NOT NULL,
    cseq integer DEFAULT 13 NOT NULL,
    last_modified timestamp without time zone DEFAULT '1900-01-01 00:00:01'::timestamp without time zone NOT NULL,
    flags integer DEFAULT 0 NOT NULL,
    cflags integer DEFAULT 0 NOT NULL,
    user_agent character varying(255) DEFAULT ''::character varying NOT NULL,
    socket character varying(64),
    methods integer
);
ALTER TABLE public."location" OWNER TO asterisk;
CREATE SEQUENCE location_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.location_id_seq OWNER TO asterisk;
ALTER SEQUENCE location_id_seq OWNED BY "location".id;
ALTER TABLE "location" ALTER COLUMN id SET DEFAULT nextval('location_id_seq'::regclass);
ALTER TABLE ONLY "location"
    ADD CONSTRAINT location_pkey PRIMARY KEY (id);
