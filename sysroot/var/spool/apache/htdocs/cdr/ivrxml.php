<?php
header('Content-type: text/xml');
include_once "/var/spool/apache/htdocs/cdr/auth.inc";

print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<options>\n";

if ($_GET['search'] == 'Extension' || $_POST['search'] == 'Extension') {
  $contq="SELECT name,fullname||'('||name||')' AS fname from users
           left outer join astdb on (substr(name,0,3)=key)
           LEFT OUTER JOIN astdb AS bgrp ON (name=bgrp.family AND  bgrp.key='BGRP')
         WHERE length(name) = 4 AND astdb.family = 'LocalPrefix' AND astdb.value=1 ORDER BY fname";
} else if ($_GET['search'] == 'Queue' || $_POST['search'] == 'Queue') {
  $contq="SELECT name,description||'('||name||')' AS qname FROM queue_table";
} else if ($_GET['search'] == 'Voicemail' || $_POST['search'] == 'Voicemail') {
  $contq="SELECT name,fullname||'('||name||')' AS fname from users
           left outer join astdb on (substr(name,0,3)=key)
           LEFT OUTER JOIN astdb AS bgrp ON (name=bgrp.family AND  bgrp.key='BGRP')
         WHERE length(name) = 4 AND astdb.family = 'LocalPrefix' AND astdb.value=1 ORDER BY fname";
} else if ($_GET['search'] == 'Speeddial' || $_POST['search'] == 'Speeddial') {
  $contq="SELECT number,number||' ('||discrip||' - '||dest||')' from speed_dial";
} else if ($_GET['search'] == 'Reception' || $_POST['search'] == 'Reception') {
  $contq="SELECT REPLACE('oper','string','function') AS value, REPLACE('Operator','string','function') AS Name";
} else if ($_GET['search'] == 'Hangup' || $_POST['search'] == 'Hangup') {
  $contq="SELECT REPLACE('hangup','string','function') AS value, REPLACE('Hangup','string','function') AS Name";
} else if (($_GET['search'] == 'Background') || ($_POST['search'] == 'Background')) {
  print "  <option value=\"Background\">Play message again</option>\n";
  print "</options>\n";
  exit;
}

$contacts=pg_query($db,$contq);

//print $contq . "\n";

for($rcnt=0;$rcnt<pg_num_rows($contacts);$rcnt++) {
  $r=pg_fetch_array($contacts,$rcnt,PGSQL_NUM);
  print "  <option value=\"" . $r[0] .  "\">" . htmlspecialchars($r[1]) . "</option>\n";
}
print "</options>\n";
pg_close($db);
?>
