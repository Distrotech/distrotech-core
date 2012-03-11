ALTER TABLE users ADD sendrpid varchar(8) default 'no';
CREATE INDEX cdr_callrep ON cdr USING btree (userfield,dstchannel,disposition);
CREATE INDEX cdr_callrepdate ON cdr USING btree (userfield,dstchannel,disposition,calldate);
CREATE INDEX cdr_callrepdate2 ON cdr USING btree (userfield,dstchannel,calldate);
CREATE INDEX cdr_callrepdate3 ON cdr USING btree (userfield,channel,dstchannel,calldate);
CREATE INDEX cdr_callrepdate4 ON cdr USING btree (userfield,channel,dstchannel,calldate,disposition);
CREATE INDEX queue_log_queueuid ON queue_log USING btree (callid,queuename);
CREATE INDEX cdr_usagerep ON cdr USING btree (dst,accountcode);
CREATE INDEX queue_log_usaagerep ON queue_log USING btree (callid,agent,queuename);
CREATE INDEX queue_member_id ON queue_members USING btree (membername,interface);
CREATE INDEX queue_membername ON queue_members USING btree (membername);
CREATE INDEX queue_interface ON queue_members USING btree (interface);
CREATE INDEX queue_name ON queue_members USING btree (queue_name);
CREATE INDEX cdr_dstacode ON cdr USING btree (dst,accountcode);
CREATE INDEX cdr_datedisp ON cdr USING btree (calldate,disposition);
