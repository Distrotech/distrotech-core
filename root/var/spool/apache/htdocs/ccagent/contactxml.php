<%
header('Content-type: text/xml');
include_once "/var/spool/apache/htdocs/reception/auth.inc";

include_once "/var/spool/apache/htdocs/session.inc";
$sessid=session_id();
if ($sessid == "") {
  ob_start('ob_gzhandler');
  session_name("agent_" . $_SERVER['PHP_AUTH_USER']);
  session_set_cookie_params(28800);
  session_start();
}

$dbname="inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];

print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<users>\n";
//  print "  <contact id=\"" . $_SESSION['listid'] .  "\">" . print_r($_SESSION,TRUE) . "</contact>\n";

$contq="SELECT " . $_POST['fname'] .",lead.title||' '||lead.fname||' '||lead.sname,lead.id 
          FROM list
            LEFT OUTER JOIN lead ON (lead.list = list.id)
            LEFT OUTER JOIN " . $dbname . " AS input ON (lead.id=input.leadid) 
            LEFT OUTER JOIN agentlist ON (list.id=agentlist.listid)
            LEFT OUTER JOIN agent ON (agent.id=agentlist.agentid)
            LEFT OUTER JOIN  campaign ON (list.campaign = campaign.id)
          WHERE agent.exten = '" . $_SERVER['PHP_AUTH_USER'] . "' AND lead.list=" . $_SESSION['listid'] . " AND " . $_POST['fname'] ." ~* '" . $_POST['fvalue'] . "' AND
                lead.active AND list.active AND campaign.active AND agentlist.active
            ORDER BY lead.sname,lead.fname,lead.title LIMIT 20";
$contacts=pg_query($db,$contq);

for($rcnt=0;$rcnt<pg_num_rows($contacts);$rcnt++) {
  $r=pg_fetch_array($contacts,$rcnt,PGSQL_NUM);
  print "  <contact id=\"" . $r[2] .  "\">" . $r[1] . " (" . $r[0] . ")</contact>\n";
}
print "</users>\n";
pg_close($db);
%>
