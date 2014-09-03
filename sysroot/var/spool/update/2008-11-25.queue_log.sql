ALTER TABLE public.queue_log ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE public.queue_log_id_seq;
DROP TABLE public.queue_log;
CREATE TABLE queue_log (
    id integer NOT NULL,
    "time" bigint,
    callid character varying(32),
    queuename character varying(32),
    agent character varying(32),
    event character varying(32),
    data character varying(255)
);
ALTER TABLE public.queue_log OWNER TO asterisk;
CREATE SEQUENCE queue_log_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.queue_log_id_seq OWNER TO asterisk;
ALTER SEQUENCE queue_log_id_seq OWNED BY queue_log.id;
ALTER TABLE queue_log ALTER COLUMN id SET DEFAULT nextval('queue_log_id_seq'::regclass);
