CREATE TABLE interbranch (
    id bigint NOT NULL,
    prefix character varying(4) NOT NULL,
    dprefix character varying(4) NOT NULL,
    proto character varying(8) NOT NULL,
    address character varying(64) NOT NULL
);
CREATE SEQUENCE interbranch_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE interbranch ALTER COLUMN id SET DEFAULT nextval('interbranch_id_seq'::regclass);
ALTER TABLE ONLY interbranch ADD CONSTRAINT interbranch_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX interbranch_prefix ON interbranch USING btree (prefix);

INSERT INTO interbranch (prefix,dprefix,proto,address) SELECT astdb.key,astdb.value,proto.value,addr.value FROM astdb 
		LEFT OUTER JOIN astdb AS proto ON (proto.family='LocalRouteProto' AND proto.key=astdb.key) 
		LEFT OUTER JOIN astdb AS addr ON (addr.family='LocalRoute' AND addr.key=astdb.key) WHERE astdb.family='LocalRewrite';

DELETE FROM astdb WHERE family='LocalRewrite' OR family='LocalRouteProto' OR family='LocalRoute';
