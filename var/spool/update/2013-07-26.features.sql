CREATE INDEX call_calledcountry ON call USING btree (calledcountry);
CREATE INDEX cdr_intcheck ON cdr USING btree (uniqueid,accountcode,disposition);
ALTER TABLE features ADD intblocked timestamp with time zone;
ALTER TABLE features ADD noint boolean default 'f';
ALTER TABLE features ADD intlimit int default '100';
