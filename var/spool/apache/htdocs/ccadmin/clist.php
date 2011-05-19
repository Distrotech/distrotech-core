SELECT campaign.name,lead.title,lead.fname,lead.sname,lead.number,campaign.priority,list.priority,contact.status,contact.datetime from campaign left outer join list on 
(list.campaign=campaign.id) left outer join lead on (lead.list=list.id) left outer join contact ON (contact.lead=lead.id) WHERE lead.number IS NOT NULL AND 
campaign.active AND list.active AND list.callbefore > now() AND lead.availfrom < now() AND lead.availtill > now();
