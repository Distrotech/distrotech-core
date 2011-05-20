CREATE INDEX queue_log_qagent ON queue_log USING btree (queuename,agent);
CREATE INDEX queue_log_agent ON queue_log USING btree (agent);
CREATE INDEX queue_log_queuename ON queue_log USING btree (queuename);
CREATE INDEX queue_log_time ON queue_log USING btree (time);
CREATE INDEX queue_log_event ON queue_log USING btree (event);
