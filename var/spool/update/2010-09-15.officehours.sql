ALTER TABLE officehours ADD bgroup varchar(32) DEFAULT '' NOT NULL;
insert INTO officehours (dayrange,monthday,month,year,pubhol,description,starttime,stoptime,bgroup) SELECT DISTINCT dayrange,monthday,month,year,pubhol,description,starttime,stoptime,value from officehours left outer join astdb on (key='BGRP') where (bgroup is null OR bgroup='') AND (SELECT count(*) = 0 from officehours WHERE bgroup=value) AND value is not null and value !='';
CREATE FUNCTION holidaytime(timestamp with time zone,bgroup varchar(32)) RETURNS character varying
    AS $_$SELECT CASE WHEN (starttime = 0 AND stoptime = 1440) THEN '*' ELSE
           (starttime-(starttime % 60))/60||':'||rpad((starttime % 60),2,0)||'-'||(stoptime-((stoptime-1) % 60)-1)/60||':'||rpad(((stoptime-1) % 60),2,0)
            END
            FROM officehours WHERE (year = '' OR year=date_part('year',$1)) AND
       (month=date_part('month',$1) OR (month=date_part('month',$1-interval '1 day') AND date_part('dow',$1) = 1 AND monthday > 1)) AND
       ((monthday = date_part('day',$1) AND date_part('dow',$1) > 0) OR (monthday = date_part('day',$1-interval '1 day') AND date_part('dow',$1) = 1))
       AND pubhol = 't' AND bgroup=$2;$_$
    LANGUAGE sql IMMUTABLE STRICT;
CREATE FUNCTION officehours(timestamp with time zone,bgroup varchar(32)) RETURNS character varying
    AS $_$SELECT CAST('1' AS character varying) FROM officehours WHERE NOT pubhol AND
            date_part('dow',$1) ~ dayrange AND date_part('hour',$1)*60+date_part('min',$1) > starttime AND
            date_part('hour',$1)*60+date_part('min',$1) < stoptime-1 AND bgroup=$2;$_$
    LANGUAGE sql IMMUTABLE STRICT;
