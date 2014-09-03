CREATE TABLE dynspan (
    driver character varying(12) DEFAULT 'eth'::character varying NOT NULL,
    address character varying(64) NOT NULL,
    channels integer DEFAULT 0 NOT NULL,
    timing integer DEFAULT 0 NOT NULL,
    dchannel integer DEFAULT 0 NOT NULL,
    id bigint NOT NULL
);

CREATE SEQUENCE dynspan_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
