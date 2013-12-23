CREATE TABLE cc_climap (
    userid bigserial,
    prefix character varying(16),
    "match" character varying(64),
    defcli character varying(32),
    strip integer
);

ALTER TABLE users ADD resetcredit bigint;
ALTER TABLE reseller add resetcredit bigint;
ALTER TABLE reseller add resetallocated bigint;

ALTER TABLE users ALTER resetcredit SET DEFAULT '0';
ALTER TABLE reseller ALTER resetcredit SET DEFAULT '0';
ALTER TABLE reseller ALTER resetallocated SET DEFAULT '0';

