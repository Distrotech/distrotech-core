CREATE TABLE user_shares (
    from_user character varying(100) NOT NULL,
    to_user character varying(100) NOT NULL,
    dummy character(1)
);

ALTER TABLE ONLY user_shares
    ADD CONSTRAINT user_shares_key PRIMARY KEY (from_user, to_user);
ALTER TABLE user_shares OWNER TO exchange;

CREATE TABLE group_shares (
    for_group character varying(100) NOT NULL,
    from_user character varying(100) NOT NULL,
    dummy character(1)
);

ALTER TABLE ONLY group_shares
    ADD CONSTRAINT group_shares_key PRIMARY KEY (for_group, from_user);
ALTER TABLE group_shares OWNER TO exchange;


CREATE TABLE anyone_shares (
    from_user character varying(100) NOT NULL,
    dummy character(1) DEFAULT '1'::bpchar
);

ALTER TABLE ONLY anyone_shares
    ADD CONSTRAINT anyone_shares_pkey PRIMARY KEY (from_user);
ALTER TABLE anyone_shares OWNER TO exchange;

CREATE TABLE sogo_sessions_folder (
    c_id character varying(255) NOT NULL,
    c_value character varying(255) NOT NULL,
    c_creationdate integer NOT NULL,
    c_lastseen integer NOT NULL
);

ALTER TABLE ONLY sogo_sessions_folder
    ADD CONSTRAINT sogo_sessions_folder_key PRIMARY KEY (c_id);
ALTER TABLE sogo_sessions_folder OWNER TO exchange;

CREATE TABLE sogolog (
    tstamp timestamp with time zone DEFAULT now(),
    "hour" integer,
    http200 integer,
    http304 integer,
    http502 integer,
    http503 integer,
    httpother integer,
    mailsent integer,
    mailimap integer
);
ALTER TABLE sogolog OWNER TO exchange;

