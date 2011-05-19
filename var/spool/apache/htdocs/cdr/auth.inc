<%
  date_default_timezone_set("Africa/Johannesburg");
  $dtime=getdate();
  $syshname="pbx";
  $db=pg_connect("host=localhost dbname=asterisk user=asterisk password=asterisk sslmode=allow");
%>
