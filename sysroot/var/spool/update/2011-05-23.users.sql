ALTER TABLE users ALTER encryption TYPE varchar(16);
UPDATE users set encryption = 'yes' where useragent ~ '^snom3';
UPDATE users set encryption = 'yes' where useragent ~ '^PolycomSoundPointIP';
UPDATE users set encryption = 'no' where useragent ~ '^snom-m9';
ALTER TABLE users add encryption_taglen varchar(8) default '80';
UPDATE users set encryption_taglen='32',encryption=substr(encryption,1,position(',' in encryption)-1) where encryption ~ ',32bit$';
