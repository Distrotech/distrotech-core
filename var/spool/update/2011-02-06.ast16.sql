ALTER TABLE users ALTER lastms type character varying(11);
DROP VIEW voicemail ;
ALTER TABLE users ALTER password type character varying(10);
ALTER TABLE users ADD useragent character varying(32);
