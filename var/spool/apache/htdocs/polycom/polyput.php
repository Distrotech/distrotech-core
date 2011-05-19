<%
$putdata = fopen("php://input", "r");

$ruripath= explode("/", $REQUEST_URI);
$fp = fopen("/var/spool/apache/htdocs/polycom/logs/" . $ruripath[2], "a+");

while ($data = fread($putdata, 1024))
  fwrite($fp, $data);

fclose($fp);
fclose($putdata);
%>
