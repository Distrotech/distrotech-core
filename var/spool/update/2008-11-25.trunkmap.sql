ALTER TABLE ONLY public.trunkmap DROP CONSTRAINT trunkmap_pkey;
ALTER TABLE public.trunkmap ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE public.trunkmap_id_seq;
DROP TABLE public.trunkmap;
CREATE TABLE trunkmap (
    id integer NOT NULL,
    "match" character varying(255),
    prefix character varying(16) DEFAULT ''::character varying NOT NULL,
    strip integer DEFAULT 0 NOT NULL,
    trunk character varying(32) DEFAULT ''::character varying NOT NULL
);
ALTER TABLE public.trunkmap OWNER TO asterisk;
CREATE SEQUENCE trunkmap_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.trunkmap_id_seq OWNER TO asterisk;
ALTER SEQUENCE trunkmap_id_seq OWNED BY trunkmap.id;
ALTER TABLE trunkmap ALTER COLUMN id SET DEFAULT nextval('trunkmap_id_seq'::regclass);
ALTER TABLE ONLY trunkmap
    ADD CONSTRAINT trunkmap_pkey PRIMARY KEY (id);
