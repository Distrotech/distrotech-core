CREATE INDEX queue_log_queuename ON queue_log USING BTREE (queuename);
CREATE INDEX queue_log_event ON queue_log USING BTREE (event);
CREATE INDEX queue_log_callid ON queue_log USING BTREE (callid);
