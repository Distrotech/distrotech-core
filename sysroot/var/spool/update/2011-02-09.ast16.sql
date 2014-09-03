ALTER TABLE users RENAME id TO uniqueid;
ALTER TABLE queue_members RENAME id TO uniqueid;
UPDATE astdb set value='DAHDI'||substr(value,4) where value ~ '^Zap/';
UPDATE queue_members set interface='DAHDI'||substr(interface,4) where interface ~ '^Zap/';
ALTER TABLE queue_table ADD setinterfacevar varchar(8) default 'yes';
ALTER TABLE queue_table ADD ringinuse varchar(8) default 'yes';
ALTER TABLE users ALTER useragent TYPE varchar(128);
