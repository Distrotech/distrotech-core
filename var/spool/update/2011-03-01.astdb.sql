UPDATE astdb SET value='(^08[1-47][0-9]{7}$)|(^07[1-4689][0-9]{7}$)' WHERE family='Setup' AND key='GSMPat' and value='(^08[2-47][0-9]{7}$)|(^07[1-4689][0-9]{7}$)';
UPDATE astdb set value=value||'|(081[0-9]{7}$)' WHERE family='Setup' AND key='GSMPat' AND '0811234567' !~ value;
