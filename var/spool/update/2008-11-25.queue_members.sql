ALTER TABLE queue_members ADD paused int default 0;
ALTER TABLE queue_members ALTER paused set default 0;
ALTER TABLE queue_members ALTER membername type varchar(128);
ALTER TABLE queue_members ALTER interface type varchar(36);

