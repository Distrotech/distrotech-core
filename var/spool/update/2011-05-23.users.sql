ALTER TABLE users ALTER encryption TYPE varchar(16);
UPDATE users set encryption = 'yes' where useragent ~ '^snom3';
UPDATE users set encryption = 'yes' where useragent ~ '^PolycomSoundPointIP';
UPDATE users set encryption = 'no' where useragent ~ '^snom-m9';

