CREATE INDEX trunkcost_cost ON trunkcost using btree (cost);
CREATE INDEX cdr_calldate ON cdr using btree (calldate);
CREATE INDEX cdr_dstdate ON cdr using btree (calldate,dst);
CREATE INDEX cdr_dstdispdate ON cdr using btree (calldate,dst,disposition);
