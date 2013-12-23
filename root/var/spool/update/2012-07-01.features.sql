ALTER TABLE features ALTER dfeat SET default '0';
UPDATE features set dfeat = '0' where dfeat is NULL OR dfeat ='';
