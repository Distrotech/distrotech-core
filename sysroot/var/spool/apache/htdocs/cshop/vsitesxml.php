<?php
header('Content-type: text/xml');
include_once "/var/spool/apache/htdocs/cshop/auth.inc";

print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<users>\n";

$contq="SELECT name,fullname||' ('||name||')' FROM users LEFT OUTER JOIN companysites ON (name=source) WHERE " .
		"agentid='" . $_SESSION['resellerid'] . "' AND usertype > 0 AND companysites IS NULL AND " .
		"(name ~ '" . $_POST['search'] . "' OR fullname ~* '" . $_POST['search'] . "') ORDER BY fullname,name LIMIT 20";

print $contq;
$contacts=pg_query($db,$contq);

for($rcnt=0;$rcnt<pg_num_rows($contacts);$rcnt++) {
  $r=pg_fetch_array($contacts,$rcnt,PGSQL_NUM);
  print "  <user id=\"" . $r[0] .  "\">" . htmlspecialchars($r[1]) . "</user>\n";
}
print "</users>\n";
pg_close($db);
?>
