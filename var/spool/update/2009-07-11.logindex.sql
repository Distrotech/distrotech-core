CREATE INDEX cdr_calllogjoin ON cdr using btree (uniqueid,channel);
CREATE INDEX calllog_cdrjoin ON calllog using btree (uniqueid,dstchannel);
