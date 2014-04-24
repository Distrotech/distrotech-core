ALTER TABLE users ALTER secret SET default substr(md5(random()),0,8);
UPDATE users set secret=password||substr(md5(random()),0,8-length(password))  where name ~ '^5[0-9]{2}$' OR secret is null or secret='';
