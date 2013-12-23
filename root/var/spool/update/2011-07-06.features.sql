CREATE TABLE features (
    id bigint NOT NULL,
    exten character varying(16) NOT NULL,
    cdnd character varying(32) DEFAULT '0'::character varying,
    cfbu character varying(32) DEFAULT '0'::character varying,
    cfim character varying(32) DEFAULT '0'::character varying,
    cfna character varying(32) DEFAULT '0'::character varying,
    cffax character varying(32) DEFAULT '0'::character varying,
    altc character varying(32) DEFAULT ''::character varying,
    office character varying(32) DEFAULT ''::character varying,
    wait character varying(32) DEFAULT '0'::character varying,
    record character varying(32) DEFAULT ''::character varying,
    alock character varying(32) DEFAULT ''::character varying,
    nopres character varying(32) DEFAULT '0'::character varying,
    dfeat character varying(32) DEFAULT ''::character varying,
    novoip character varying(32) DEFAULT '0'::character varying,
    crmpop character varying(32) DEFAULT '0'::character varying,
    novmail character varying(32) DEFAULT ''::character varying,
    faxmail character varying(32) DEFAULT '0'::character varying,
    snomlock character varying(32) DEFAULT '1'::character varying,
    polydirln character varying(32) DEFAULT '0'::character varying,
    efaxd character varying(32) DEFAULT '0'::character varying,
    tout character varying(32) DEFAULT ''::character varying,
    dgroup character varying(32) DEFAULT ''::character varying,
    zapline character varying(32) DEFAULT '0'::character varying,
    ddipass character varying(32) DEFAULT '0'::character varying,
    zapproto character varying(32) DEFAULT 'fxo_ks'::character varying,
    zaprxgain character varying(32) DEFAULT '0'::character varying,
    zaptxgain character varying(32) DEFAULT '0'::character varying,
    cli character varying(32) DEFAULT '0'::character varying,
    trunk character varying(32) DEFAULT ''::character varying,
    "access" character varying(32) DEFAULT ''::character varying,
    authaccess character varying(32) DEFAULT ''::character varying,
    iaxline character varying(32) DEFAULT '0'::character varying,
    h323line character varying(32) DEFAULT '0'::character varying,
    fwdu character varying(32) DEFAULT ''::character varying,
    locked character varying(32) DEFAULT '0'::character varying,
    snommac character varying(32) DEFAULT ''::character varying,
    vlan character varying(32) DEFAULT ''::character varying,
    registrar character varying(32) DEFAULT ''::character varying,
    ptype character varying(32) DEFAULT 'OTHER'::character varying,
    purse character varying(32) DEFAULT ''::character varying,
    dring character varying(32) DEFAULT '1'::character varying,
    sring0 character varying(32) DEFAULT '6'::character varying,
    sring1 character varying(32) DEFAULT '3'::character varying,
    sring2 character varying(32) DEFAULT '1'::character varying,
    sring3 character varying(32) DEFAULT '6'::character varying,
    roampass character varying(32) DEFAULT ''::character varying,
    noclid character varying(32) DEFAULT 'NULL'::character varying,
    isdnline character varying(32) DEFAULT '0'::character varying,
    autoauth character varying(32) DEFAULT '0'::character varying
);
ALTER TABLE public.features OWNER TO asterisk;
CREATE SEQUENCE features_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER TABLE public.features_id_seq OWNER TO asterisk;
ALTER SEQUENCE features_id_seq OWNED BY features.id;
ALTER TABLE features ALTER COLUMN id SET DEFAULT nextval('features_id_seq'::regclass);
ALTER TABLE ONLY features ADD CONSTRAINT features_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX feat_exten ON features USING btree (exten);
INSERT INTO features (exten) SELECT DISTINCT astdb.family AS exten 
                FROM astdb 
                LEFT OUTER JOIN astdb as lpre ON (substr(astdb.family,1,2) = lpre.key AND lpre.family='LocalPrefix')
                LEFT OUTER JOIN users ON (name=astdb.family) WHERE (lpre.value='1' OR name ~ '^001[0-9]{5}$') AND name IS NOT NULL;
INSERT INTO features (exten) SELECT DISTINCT astdb.family AS exten 
                FROM astdb 
                LEFT OUTER JOIN users ON (name=astdb.family) WHERE (name ~ '^001[0-9]{5}$') AND name IS NOT NULL;


UPDATE features SET CDND=value FROM astdb WHERE (family=exten AND key='CDND');
UPDATE features SET CFBU=value FROM astdb WHERE (family=exten AND key='CFBU');
UPDATE features SET CFIM=value FROM astdb WHERE (family=exten AND key='CFIM');
UPDATE features SET CFNA=value FROM astdb WHERE (family=exten AND key='CFNA');
UPDATE features SET CFFAX=value FROM astdb WHERE (family=exten AND key='CFFAX');
UPDATE features SET ALTC=value FROM astdb WHERE (family=exten AND key='ALTC');
UPDATE features SET OFFICE=value FROM astdb WHERE (family=exten AND key='OFFICE');
UPDATE features SET WAIT=value FROM astdb WHERE (family=exten AND key='WAIT');
UPDATE features SET RECORD=value FROM astdb WHERE (family=exten AND key='RECORD');
UPDATE features SET ALOCK=value FROM astdb WHERE (family=exten AND key='ALOCK');
UPDATE features SET NOPRES=value FROM astdb WHERE (family=exten AND key='NOPRES');
UPDATE features SET DFEAT=value FROM astdb WHERE (family=exten AND key='DFEAT');
UPDATE features SET NOVOIP=value FROM astdb WHERE (family=exten AND key='NOVOIP');
UPDATE features SET CRMPOP=value FROM astdb WHERE (family=exten AND key='CRMPOP');
UPDATE features SET NOVMAIL=value FROM astdb WHERE (family=exten AND key='NOVMAIL');
UPDATE features SET FAXMAIL=value FROM astdb WHERE (family=exten AND key='FAXMAIL');
UPDATE features SET SNOMLOCK=value FROM astdb WHERE (family=exten AND key='SNOMLOCK');
UPDATE features SET POLYDIRLN=value FROM astdb WHERE (family=exten AND key='POLYDIRLN');
UPDATE features SET EFAXD=value FROM astdb WHERE (family=exten AND key='EFAXD');
UPDATE features SET TOUT=value FROM astdb WHERE (family=exten AND key='TOUT');
UPDATE features SET DGROUP=value FROM astdb WHERE (family=exten AND key='DGROUP');
UPDATE features SET ZAPLine=value FROM astdb WHERE (family=exten AND key='ZAPLine');
UPDATE features SET DDIPASS=value FROM astdb WHERE (family=exten AND key='DDIPASS');
UPDATE features SET ZAPProto=value FROM astdb WHERE (family=exten AND key='ZAPProto');
UPDATE features SET ZAPRXGain=value FROM astdb WHERE (family=exten AND key='ZAPRXGain');
UPDATE features SET ZAPTXGain=value FROM astdb WHERE (family=exten AND key='ZAPTXGain');
UPDATE features SET CLI=value FROM astdb WHERE (family=exten AND key='CLI');
UPDATE features SET TRUNK=value FROM astdb WHERE (family=exten AND key='TRUNK');
UPDATE features SET ACCESS=value FROM astdb WHERE (family=exten AND key='ACCESS');
UPDATE features SET AUTHACCESS=value FROM astdb WHERE (family=exten AND key='AUTHACCESS');
UPDATE features SET IAXLine=value FROM astdb WHERE (family=exten AND key='IAXLine');
UPDATE features SET H323Line=value FROM astdb WHERE (family=exten AND key='H323Line');
UPDATE features SET FWDU=value FROM astdb WHERE (family=exten AND key='FWDU');
UPDATE features SET Locked=value FROM astdb WHERE (family=exten AND key='Locked');
UPDATE features SET SNOMMAC=value FROM astdb WHERE (family=exten AND key='SNOMMAC');
UPDATE features SET VLAN=value FROM astdb WHERE (family=exten AND key='VLAN');
UPDATE features SET REGISTRAR=value FROM astdb WHERE (family=exten AND key='REGISTRAR');
UPDATE features SET PTYPE=value FROM astdb WHERE (family=exten AND key='PTYPE');
UPDATE features SET PURSE=value FROM astdb WHERE (family=exten AND key='PURSE');
UPDATE features SET DRING=value FROM astdb WHERE (family=exten AND key='DRING');
UPDATE features SET SRING0=value FROM astdb WHERE (family=exten AND key='SRING0');
UPDATE features SET SRING1=value FROM astdb WHERE (family=exten AND key='SRING1');
UPDATE features SET SRING2=value FROM astdb WHERE (family=exten AND key='SRING2');
UPDATE features SET SRING3=value FROM astdb WHERE (family=exten AND key='SRING3');
UPDATE features SET RoamPass=value FROM astdb WHERE (family=exten AND key='RoamPass');
UPDATE features SET NoCLID=value FROM astdb WHERE (family=exten AND key='NoCLID');
UPDATE features SET ISDNLine=value FROM astdb WHERE (family=exten AND key='ISDNLine');
UPDATE features SET AUTOAUTH=value FROM astdb WHERE (family=exten AND key='AUTOAUTH');

