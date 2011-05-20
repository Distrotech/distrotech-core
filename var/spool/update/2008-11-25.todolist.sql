ALTER TABLE ONLY public.todolist DROP CONSTRAINT todolist_pkey;
ALTER TABLE public.todolist ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE public.todolist_id_seq;
DROP TABLE public.todolist;
CREATE TABLE todolist (
    id integer NOT NULL,
    assignedto character varying(15) DEFAULT ''::character varying NOT NULL,
    createby character varying(16) DEFAULT ''::character varying NOT NULL,
    todolist character varying(255) DEFAULT ''::character varying NOT NULL,
    date timestamp with time zone DEFAULT now()
);
ALTER TABLE public.todolist OWNER TO asterisk;
CREATE SEQUENCE todolist_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.todolist_id_seq OWNER TO asterisk;
ALTER SEQUENCE todolist_id_seq OWNED BY todolist.id;
ALTER TABLE todolist ALTER COLUMN id SET DEFAULT nextval('todolist_id_seq'::regclass);
ALTER TABLE ONLY todolist
    ADD CONSTRAINT todolist_pkey PRIMARY KEY (id);
