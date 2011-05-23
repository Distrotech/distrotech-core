ALTER TABLE users ALTER encryption TYPE varchar(16);
UPDATE users set encryption = 'try,32bit' where useragent ~ '^snom';
