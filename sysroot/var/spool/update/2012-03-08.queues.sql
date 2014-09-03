CREATE UNIQUE INDEX queue_table_name ON queue_table USING btree (name);
CREATE INDEX queue_table_sl ON queue_table USING btree (servicelevel);
CREATE INDEX queue_table_descrip ON queue_table USING btree (description);
CREATE INDEX queue_log_eventid ON queue_log USING btree (callid,event);
CREATE INDEX queue_log_qeventid ON queue_log USING btree (callid,event,queuename);
