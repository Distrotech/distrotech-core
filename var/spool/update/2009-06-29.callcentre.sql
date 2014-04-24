DROP INDEX public.lead_lastcontact;
DROP INDEX public.lead_contact;
DROP INDEX public.contact_uniqueid;
DROP INDEX public.agentlist_link;
DROP INDEX public.agent_exten;
DROP INDEX public.agent_channel;
ALTER TABLE ONLY public.list DROP CONSTRAINT list_key;
ALTER TABLE ONLY public.lead DROP CONSTRAINT lead_pkey;
ALTER TABLE ONLY public.field_names DROP CONSTRAINT key_field_names;
ALTER TABLE ONLY public.contact DROP CONSTRAINT contact_key;
ALTER TABLE ONLY public.campaign DROP CONSTRAINT campaign_key;
ALTER TABLE ONLY public.agentlist DROP CONSTRAINT agentlist_pkey;
ALTER TABLE ONLY public.agent DROP CONSTRAINT agent_pkey;
ALTER TABLE public.list ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.lead ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.field_names ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.contact ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.campaign ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.agent ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE public.list_id_seq;
DROP TABLE public.list;
DROP SEQUENCE public.lead_id_seq;
DROP TABLE public.lead;
DROP SEQUENCE public.field_names_id_seq;
DROP TABLE public.field_names;
DROP SEQUENCE public.contact_id_seq;
DROP TABLE public.contact;
DROP SEQUENCE public.campaign_id_seq;
DROP TABLE public.campaign;
DROP TABLE public.camp_admin;
DROP TABLE public.agentlist;
DROP SEQUENCE public.agent_id_seq;
DROP TABLE public.agent;
CREATE TABLE agent (
    exten character varying(16) NOT NULL,
    channel character varying(80),
    id bigint NOT NULL
);
ALTER TABLE public.agent OWNER TO asterisk;
CREATE SEQUENCE agent_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.agent_id_seq OWNER TO asterisk;
ALTER SEQUENCE agent_id_seq OWNED BY agent.id;
CREATE TABLE agentlist (
    listid bigint NOT NULL,
    agentid bigint NOT NULL,
    active boolean DEFAULT true NOT NULL
);
ALTER TABLE public.agentlist OWNER TO asterisk;
CREATE TABLE camp_admin (
    campaign bigint,
    userid character varying(32)
);
ALTER TABLE public.camp_admin OWNER TO asterisk;
CREATE TABLE campaign (
    id bigint NOT NULL,
    description character varying(64) NOT NULL,
    active boolean,
    automethod character varying(16),
    name character varying(16),
    priority integer DEFAULT 0
);
ALTER TABLE public.campaign OWNER TO asterisk;
CREATE SEQUENCE campaign_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.campaign_id_seq OWNER TO asterisk;
ALTER SEQUENCE campaign_id_seq OWNED BY campaign.id;
CREATE TABLE contact (
    datetime timestamp with time zone DEFAULT now() NOT NULL,
    status character varying(15) DEFAULT 'INIT'::character varying NOT NULL,
    followup boolean DEFAULT true,
    feedback text DEFAULT ''::text NOT NULL,
    id bigint NOT NULL,
    uniqueid character varying(32),
    lead bigint,
    channel character varying(80),
    nextcall timestamp with time zone,
    agent bigint
);
ALTER TABLE public.contact OWNER TO asterisk;
CREATE SEQUENCE contact_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.contact_id_seq OWNER TO asterisk;
ALTER SEQUENCE contact_id_seq OWNED BY contact.id;
CREATE TABLE field_names (
    tablename character varying(64),
    field character varying(64),
    fname character varying(64),
    id bigint NOT NULL
);
ALTER TABLE public.field_names OWNER TO asterisk;
CREATE SEQUENCE field_names_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.field_names_id_seq OWNER TO asterisk;
ALTER SEQUENCE field_names_id_seq OWNED BY field_names.id;
CREATE TABLE lead (
    list bigint,
    availfrom time without time zone DEFAULT '00:00:00'::time without time zone,
    availtill time without time zone DEFAULT '00:00:00'::time without time zone,
    number character varying(32) NOT NULL,
    fname character varying(32),
    sname character varying(32),
    title character varying(8),
    active boolean DEFAULT true,
    lastcontact bigint,
    id bigint NOT NULL
);
ALTER TABLE public.lead OWNER TO asterisk;
CREATE SEQUENCE lead_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.lead_id_seq OWNER TO asterisk;
ALTER SEQUENCE lead_id_seq OWNED BY lead.id;
CREATE TABLE list (
    loaded timestamp with time zone DEFAULT now() NOT NULL,
    callbefore timestamp with time zone DEFAULT now() NOT NULL,
    priority integer DEFAULT 0,
    campaign bigint NOT NULL,
    id bigint NOT NULL,
    description character varying(64) NOT NULL,
    "owner" character varying(32) NOT NULL,
    information text NOT NULL,
    active boolean DEFAULT true
);
ALTER TABLE public.list OWNER TO asterisk;
CREATE SEQUENCE list_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.list_id_seq OWNER TO asterisk;
ALTER SEQUENCE list_id_seq OWNED BY list.id;
ALTER TABLE agent ALTER COLUMN id SET DEFAULT nextval('agent_id_seq'::regclass);
ALTER TABLE campaign ALTER COLUMN id SET DEFAULT nextval('campaign_id_seq'::regclass);
ALTER TABLE contact ALTER COLUMN id SET DEFAULT nextval('contact_id_seq'::regclass);
ALTER TABLE field_names ALTER COLUMN id SET DEFAULT nextval('field_names_id_seq'::regclass);
ALTER TABLE lead ALTER COLUMN id SET DEFAULT nextval('lead_id_seq'::regclass);
ALTER TABLE list ALTER COLUMN id SET DEFAULT nextval('list_id_seq'::regclass);
ALTER TABLE ONLY agent
    ADD CONSTRAINT agent_pkey PRIMARY KEY (id);
ALTER TABLE ONLY agentlist
    ADD CONSTRAINT agentlist_pkey PRIMARY KEY (listid, agentid);
ALTER TABLE ONLY campaign
    ADD CONSTRAINT campaign_key PRIMARY KEY (id);
ALTER TABLE ONLY contact
    ADD CONSTRAINT contact_key PRIMARY KEY (id);
ALTER TABLE ONLY field_names
    ADD CONSTRAINT key_field_names PRIMARY KEY (id);
ALTER TABLE ONLY lead
    ADD CONSTRAINT lead_pkey PRIMARY KEY (id);
ALTER TABLE ONLY list
    ADD CONSTRAINT list_key PRIMARY KEY (id);
CREATE INDEX agent_channel ON agent USING btree (channel);
CREATE INDEX agent_exten ON agent USING btree (exten);
CREATE UNIQUE INDEX agentlist_link ON agentlist USING btree (listid, agentid);
CREATE INDEX contact_uniqueid ON contact USING btree (uniqueid);
CREATE UNIQUE INDEX lead_contact ON lead USING btree (title, fname, sname, number);
CREATE INDEX lead_lastcontact ON lead USING btree (lastcontact);
