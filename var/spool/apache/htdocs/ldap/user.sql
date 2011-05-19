BEGIN;
    CREATE TABLE ${user}_Obj (
	name		varchar(64)   NOT NULL, PRIMARY KEY (name),
	parent		varchar(64)   NOT NULL,
	type		int2          DEFAULT 0,
	title		varchar(128)  DEFAULT '',
	flags		int4          DEFAULT 0,
	status		int4          DEFAULT 0,
	persist		int2          DEFAULT 0,
        created		int4          DEFAULT xnow(),
        modified	int4          DEFAULT xnow(),
        serial		int4          DEFAULT 1,
        seq_no		int4          DEFAULT 1,
        next_no		int4          DEFAULT 1,
	itemclass	varchar(64)   DEFAULT '',
	path		varchar(1020) DEFAULT '/',
	comment		varchar(64)   DEFAULT ''
    );

    CREATE INDEX ${user}_parent_idx ON ${user}_Obj (parent);
    CREATE INDEX ${user}_type_idx   ON ${user}_Obj (type);
    CREATE INDEX ${user}_path_idx   ON ${user}_Obj (path);
    CREATE INDEX ${user}_seq_no_idx ON ${user}_Obj (seq_no);
    CREATE INDEX ${user}_class_idx  ON ${user}_Obj (itemclass);
END;
BEGIN;
    INSERT INTO ${user}_Obj VALUES
	('','',3,'DBRoot',1,0,1,xnow(),xnow(),1,1,1,'','/');
    INSERT INTO ${user}_obj VALUES
	('SEID::MsgStore','',1,'exchange4linux Server',1,0,1,xnow(),xnow(),1,1,1,'','/');
    INSERT INTO ${user}_obj VALUES
	('SEID::RootFolder','SEID::RootFolder',3,'Root Folder',1,0,1,xnow(),xnow(),1,1,1,'','/');
    INSERT INTO ${user}_obj VALUES
	('SEID::IPMSubtreeFolder','SEID::RootFolder',3,'Top of Personal Folders',1,0,1,xnow(),xnow(),1,1,1,'','/SEID::RootFolder');
    INSERT INTO ${user}_obj VALUES
	('SEID::InboxFolder','SEID::IPMSubtreeFolder',3,'Inbox',1,0,1,xnow(),xnow(),1,1,1,'IPM.','/SEID::RootFolder/SEID::IPMSubtreeFolder');
    INSERT INTO ${user}_obj VALUES
	('SEID::TempFolder','SEID::RootFolder',3,'Temporary Objects Folder',1,0,1,xnow(),xnow(),1,1,1,'IPM.','/SEID::RootFolder');
    INSERT INTO ${user}_obj VALUES
	('SEID::NetFolderInbox','SEID::RootFolder',3,'Net Folder Inbox',1,0,1,xnow(),xnow(),1,1,1,'IPM.Note.FolderPub.MDO','/SEID::RootFolder');
    INSERT INTO ${user}_obj VALUES
	('SEID::NetMessageInNIF','SEID::NetFolderInbox',5,'IPM.Note.FolderPub.MDO',64,0,1,xnow(),xnow(),65,1,1,'IPM.Note.FolderPub.MDO','/SEID::RootFolder/SEID::NetFolderInbox');
END;
BEGIN;
    CREATE TABLE ${user}_Props (
	name	varchar(64),
        pn	varchar(64),
        pv	varchar(2040),
        pb	oid,
        pt	int2
    );

    CREATE UNIQUE INDEX ${user}_oid_pname_idx ON ${user}_Props (name, pn);
    CREATE        INDEX ${user}_oid_idx       ON ${user}_Props (name);
    CREATE        INDEX ${user}_pname_idx     ON ${user}_Props (pn);
    CREATE        INDEX ${user}_value_idx     ON ${user}_Props (pv);
END;
BEGIN;
    INSERT INTO ${user}_Props VALUES ('','PR_IPM_PUBLIC_FOLDERS_ENTRYID','PUB::',0,4);
    INSERT INTO ${user}_Props VALUES ('SEID::MsgStore','PR_RECEIVE_FOLDERS','{''IPM'': ''SEID::InboxFolder''}',0,7);
    INSERT INTO ${user}_Props VALUES ('SEID::MsgStore','PR_STORE_STATE','0',0,1);
    INSERT INTO ${user}_Props VALUES ('SEID::MsgStore','PR_TEMP_FOLDER','SEID::TempFolder',0,3);
    INSERT INTO ${user}_Props VALUES ('SEID::MsgStore','PR_VALID_FOLDER_MASK','3',0,1);
    INSERT INTO ${user}_Props VALUES ('SEID::MsgStore','PR_IPM_SUBTREE_ENTRYID','SEID::IPMSubtreeFolder',0,4);
    INSERT INTO ${user}_Props VALUES ('SEID::NetMessageInNIF','PR_0x68210102', '~0~0~0~0',0, 4);
END;
