ALTER TABLE users ADD encryption varchar(8) default 'no';
UPDATE users set encryption = 'yes' where useragent ~ '^snom';
