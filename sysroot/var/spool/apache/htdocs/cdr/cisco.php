<%
include "cisco.inc";
include "auth.inc";

//pg_query($db,"SELECT name,password,snommac,registrar FROM users 
//                 LEFT OUTER JOIN features ON (exten=name) 
//               WHERE ptype='SNOM' ANd registrar != '' ANd  snommac != ''";

ciscoxml("0463","1925","192.168.2.1","0022555DE1C3");
ciscoxml("0464","1864","192.168.2.1","002290036AEE");
%>
