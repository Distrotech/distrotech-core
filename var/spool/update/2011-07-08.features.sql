ALTER TABLE features ADD repeatdial varchar(32);
UPDATE features SET repeatdial=value FROM astdb WHERE (family=exten AND key='RepeatDial');
DELETE FROM astdb WHERE key='RepeatDial';
