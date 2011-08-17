INSERT INTO features (exten) SELECT DISTINCT astdb.family AS exten
                FROM astdb
                LEFT OUTER JOIN astdb as lpre ON (substr(astdb.family,1,2) = lpre.key AND lpre.family='LocalPrefix')
                LEFT OUTER JOIN users ON (name=astdb.family) 
                LEFT OUTER JOIN features ON (name=exten) 
              WHERE (lpre.value='1' OR name ~ '^001[0-9]{5}$') AND name IS NOT NULL AND features.id IS NULL;
ALTER TABLE features ALTER office TYPE varchar(64);
UPDATE features SET office=value FROM astdb WHERE (family=exten AND key='OFFICE');
