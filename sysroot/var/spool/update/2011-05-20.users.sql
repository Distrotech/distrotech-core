ALTER TABLE users ADD encryption varchar(8) default 'no';
UPDATE users set encryption = 'try,32bit' where useragent ~ '^snom';
