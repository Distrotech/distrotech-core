<%
header('Content-type: text/xml');
include_once "/var/spool/apache/htdocs/cshop/auth.inc";

print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<users>\n";

$contq="SELECT id,description||' ('||username||')' FROM reseller WHERE admin AND (owner='" . $_SESSION['resellerid'] . "' OR id='" . $_SESSION['resellerid'] . "') AND (username ~* '" . $_POST['search'] . "' OR description ~* '" . $_POST['search'] . "') ORDER BY description,username LIMIT 20";
$contacts=pg_query($db,$contq);

for($rcnt=0;$rcnt<pg_num_rows($contacts);$rcnt++) {
  $r=pg_fetch_array($contacts,$rcnt,PGSQL_NUM);
  print "  <user id=\"" . $r[0] .  "\">" . htmlspecialchars($r[1]) . "</user>\n";
}
print "</users>\n";
pg_close($db);
%>
