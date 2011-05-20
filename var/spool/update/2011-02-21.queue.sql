ALTER TABLE queue_table ALTER autopausedelay set default 0;
ALTER TABLE queue_table ADD setqueuevar varchar(8) default 'yes';
ALTER TABLE queue_table RENAME autologout TO autopausedelay;
ALTER TABLE cdr ADD linkedid varchar(80);
UPDATE cdr SET linkedid=uniqueid WHERE linkedid is NULL;
