<?php
header('Content-type: text/xml');
include_once "/var/spool/apache/htdocs/cshop/auth.inc";

print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<users>\n";

$contq="SELECT callerid FROM cc_callerid WHERE reseller='" . $_SESSION['resellerid'] . "' AND userid IS NULL AND callerid ~ '" . $_POST['search'] . "' ORDER BY callerid LIMIT 20";
$contacts=pg_query($db,$contq);

for($rcnt=0;$rcnt<pg_num_rows($contacts);$rcnt++) {
  $r=pg_fetch_array($contacts,$rcnt,PGSQL_NUM);
  print "  <user id=\"" . $r[0] .  "\">" . htmlspecialchars($r[0]) . "</user>\n";
}
print "</users>\n";
pg_close($db);
?>
