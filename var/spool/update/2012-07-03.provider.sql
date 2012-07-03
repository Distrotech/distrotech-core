ALTER TABLE provider ALTER removeprefix set default '27';
ALTER TABLE provider ADD nationalprefix varchar(12) default '0';
ALTER TABLE provider ADD internationalprefix varchar(12) default '00';
ALTER TABLE provider ADD nationallen varchar(12) default '9';
