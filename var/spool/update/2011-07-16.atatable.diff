CREATE TABLE atatable (
    id bigint NOT NULL,
    mac character varying(18),
    profile character varying(64),
    nat character varying(12),
    rxgain character varying(8) DEFAULT '-3'::character varying,
    txgain character varying(8) DEFAULT '-3'::character varying,
    hostname character varying(32),
    stunsrv character varying(64),
    vlan character varying(8)
);
CREATE SEQUENCE atatable_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE atatable ALTER COLUMN id SET DEFAULT nextval('atatable_id_seq'::regclass);
ALTER TABLE ONLY atatable ADD CONSTRAINT atatable_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX atatable_mac ON atatable USING btree (mac);

ALTER TABLE atatable ALTER mac set not null;
ALTER TABLE atatable ALTER stunsrv SET DEFAULT '';
ALTER TABLE atatable ALTER rxgain SET DEFAULT '-3';
ALTER TABLE atatable ALTER txgain SET DEFAULT '-3';
ALTER TABLE atatable ALTER vlan SET DEFAULT '1';
ALTER TABLE atatable ALTER nat SET DEFAULT 'Bridge';

INSERT INTO atatable (mac,stunsrv,profile,hostname,rxgain,txgain,vlan,nat)
SELECT stun.family,stun.value,pserv.value,descrip.value,rxgain.value,txgain.value,vlanid.value,nat.value
    FROM astdb AS stun
    LEFT OUTER JOIN astdb AS descrip ON (descrip.family=stun.family AND descrip.key='LINKSYS')
    LEFT OUTER JOIN astdb AS pserv ON (pserv.family=stun.family AND pserv.key='PROFILE')
    LEFT OUTER JOIN astdb AS vlanid ON (vlanid.family=stun.family AND vlanid.key='VLAN')
    LEFT OUTER JOIN astdb AS rxgain ON (rxgain.family=stun.family AND rxgain.key='LSYSRXGAIN')
    LEFT OUTER JOIN astdb AS txgain ON (txgain.family=stun.family AND txgain.key='LSYSTXGAIN')
    LEFT OUTER JOIN astdb AS nat ON (nat.family=stun.family AND nat.key='NAT')
      WHERE stun.key='STUNSRV';

DELETE FROM astdb WHERE id IN (SELECT astdb.id from astdb left outer join atatable on (mac=family) WHERE atatable.id is not null);

