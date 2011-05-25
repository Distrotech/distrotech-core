CREATE INDEX cdr_join ON calllog USING btree (uniqueid, dstchannel);
CREATE INDEX cdr_calllogjoin2 ON cdr USING btree (linkedid, dstchannel);
