CREATE TABLE qfeatures (
    id bigint NOT NULL,
    queue character varying(16) NOT NULL,
    penalty character varying(32) DEFAULT ''::character varying,
    dopts character varying(32) DEFAULT 't'::character varying,
    novmail character varying(32) DEFAULT '0'::character varying,
    ohonly character varying(32) DEFAULT '0'::character varying,
    rdelay character varying(32) DEFAULT '4'::character varying,
    record character varying(32) DEFAULT '1'::character varying,
    timeout character varying(32) DEFAULT ''::character varying,
    vmfwd character varying(32) DEFAULT 'NONE'::character varying
);
CREATE SEQUENCE qfeatures_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE qfeatures ALTER COLUMN id SET DEFAULT nextval('qfeatures_id_seq'::regclass);
ALTER TABLE ONLY qfeatures ADD CONSTRAINT qfeatures_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX qfeat_queue ON qfeatures USING btree (queue);

INSERT INTO qfeatures (queue) SELECT name from queue_table ;

UPDATE qfeatures SET vmfwd=value FROM astdb WHERE (family='Q'||queue AND key='QVMFWD');
UPDATE qfeatures SET penalty=value FROM astdb WHERE (family='Q'||queue AND key='QAPENALTY');
UPDATE qfeatures SET dopts=value FROM astdb WHERE (family='Q'||queue AND key='QDOPTS');
UPDATE qfeatures SET novmail=value FROM astdb WHERE (family='Q'||queue AND key='QNOVMAIL');
UPDATE qfeatures SET ohonly=value FROM astdb WHERE (family='Q'||queue AND key='QOHONLY');
UPDATE qfeatures SET rdelay=value FROM astdb WHERE (family='Q'||queue AND key='QRDELAY');
UPDATE qfeatures SET record=value FROM astdb WHERE (family='Q'||queue AND key='QRECORD');
UPDATE qfeatures SET timeout=value FROM astdb WHERE (family='Q'||queue AND key='QTIMEOUT');
