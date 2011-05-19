<%
include "cisco.inc";
include "auth.inc";

pg_query($db,"SELECT name,password,mac.value,reg.value FROM users 
                 LEFT OUTER JOIN astdb AS reg ON (reg.family=name ANd reg.key='REGISTRAR')
                 LEFT OUTER JOIN astdb AS mac ON (mac.family=name ANd mac.key='SNOMMAC') 
                 LEFT OUTER JOIN astdb AS ptype ON (ptype.family=name ANd ptype.key='PTYPE') 
               WHERE ptype.value='SNOM' ANd reg.value != '' ANd  mac.value != ''";

ciscoxml("0463","1925","192.168.2.1","0022555DE1C3");
ciscoxml("0464","1864","192.168.2.1","002290036AEE");
%>
