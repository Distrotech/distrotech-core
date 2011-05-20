ALTER TABLE public.dynspan ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE public.dynspan_id_seq;
DROP TABLE public.dynspan;
CREATE TABLE dynspan (
    driver character varying(12) DEFAULT 'eth'::character varying NOT NULL,
    address character varying(64) NOT NULL,
    channels integer DEFAULT 0 NOT NULL,
    timing integer DEFAULT 0 NOT NULL,
    dchannel integer DEFAULT 0 NOT NULL,
    id bigint NOT NULL,
    commaname character varying(64) DEFAULT ''::character varying NOT NULL,
    commaip character varying(16) DEFAULT '0.0.0.0'::character varying NOT NULL
);
CREATE SEQUENCE dynspan_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
SELECT pg_catalog.setval('dynspan_id_seq', 1, true);
ALTER TABLE dynspan ALTER COLUMN id SET DEFAULT nextval('dynspan_id_seq'::regclass);
