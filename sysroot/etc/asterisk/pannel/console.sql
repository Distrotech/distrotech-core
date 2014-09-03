USE asterisk;
CREATE TABLE console (
  position smallint(5) unsigned NOT NULL default '0',
  mailbox varchar(10) NOT NULL default '',
  context varchar(64) NOT NULL default 'default',
  count tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (mailbox),
  UNIQUE KEY entry_position (context,position)
) TYPE=MyISAM;
