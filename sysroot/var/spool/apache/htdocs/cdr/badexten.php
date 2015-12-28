<CENTER>
<FORM NAME=delunexten METHOD=POST onsubmit="ajaxsubmit(this.name);return false;">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<INPUT TYPE=HIDDEN NAME=ajax value=1>
<?php
include_once "auth.inc";
$extensq="SELECT name,fullname,ipaddr,snommac,useragent from users 
 left outer join features on (name = exten)
 left outer join astdb as lpre on (lpre.family = 'LocalPrefix' and lpre.key = substr(name,0,3))";
if ($SUPER_USER != 1) {
  $extensq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=name AND bgrp.key='BGRP')";
}
$extensq.=" where (fwdu = '0' OR fwdu is null) AND (zapline = '0' OR zapline is null) AND (extract(epoch from now()) - 3600 > CAST(regseconds AS int) OR regseconds is null ) AND (not h323neighbor  OR h323neighbor is null) AND lpre.value='1'";
if ($SUPER_USER != 1) {
  $extensq.=" AND " . $clogacl;
}
$extensq.=" order by name";

$extens=pg_query($db,$extensq);


if ($_POST['print'] != "1") {
  $colspan=6;
} else {
  $colspan=5;
}

print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=" . $colspan . ">Unused/Unregistered Extensions</TH></TR>\n";
print "<TR CLASS=list-color1>\n";

if (($_POST['print'] != "1") && ($SUPER_USER == 1)) {
  print "<TH>Del.</TH>";
}

print "<TH>Extension</TH><TH>Fullname</TH><TH>IP Address</TH><TH>MAC Address</TH><TH>User Agent</TH></TR>\n";

$rcol=1;
for($tcnt=0;$tcnt<pg_num_rows($extens);$tcnt++) {
  $r=pg_fetch_array($extens,$tcnt);
  
  $todel="del" . $r[0];
  if ($$todel == "on") {
    pg_query($db,"DELETE FROM users WHERE name='" . $r[0] . "'");
    pg_query($db,"DELETE FROM features WHERE exten='" . $r[0] . "'");
    pg_query($db,"DELETE FROM astdb WHERE family='" . $r[0] . "'");
    pg_query($db,"DELETE FROM console WHERE mailbox='" . $r[0] . "'");

    $delpre=pg_query($db,"SELECT name from users where name ~ '^" . substr($r[0],0,2) . "'");
    if (pg_num_rows($delpre) <= 0) {
      $delpre2=pg_query($db,"SELECT value from astdb where family = 'Setup' AND key = 'DefaultPrefix' AND value = '" . substr($r[0],0,2) . "'");
      if (pg_num_rows($delpre2) <= 0) {
        pg_query($db,"DELETE FROM astdb WHERE family = 'LocalPrefix' and key = '" . substr($r[0],0,2) . "'");
      }
    }
    continue;
  }

  if ($r[1] == "") {
    $r[1]="&nbsp;";
  }
  if ($r[2] == "") {
    $r[2]="&nbsp;";
  }
  if ($r[3] == "") {
    $r[3]="&nbsp;";
  }
  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";

  if (($_POST['print'] != "1") && ($SUPER_USER == 1)) {
    print "<TD><INPUT TYPE=CHECKBOX NAME=del" . $r[0] . "></TD>";
  }

  print "<TD><A HREF=javascript:openextenedit('" . $r[0] . "')>" . $r[0] . "</A></TD><TD>" . $r[1] . "</TD><TD>" . $r[2] . "</TD><TD>" . $r[3] . "</TD><TD>" . $r[4] . "</TD></TR>\n";
  $rcol++;
}

if ($_POST['print'] != "1") {
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD COLSPAN=" . $colspan . " ALIGN=LEFT>" . ($rcol - 1) . " Extensions Affected</TH></TR>\n";
  $rcol++;
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $colspan . " CLASS=heading-body>";
  if ($SUPER_USER == 1) {?>
    <INPUT TYPE=SUBMIT VALUE="Delete"><?php
  }
  print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\">";
  print "</TH></TR>";
}
?>
<FORM NAME=ppage METHOD=POST><INPUT TYPE=HIDDEN NAME=print></FORM>
</TABLE>
</FORM>

