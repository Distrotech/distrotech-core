<CENTER>
<CENTER>
<FORM NAME=ppage METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<FORM METHOD=POST NAME=deletexten onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<INPUT TYPE=HIDDEN NAME=print>
<?php
include_once "autoadd.inc";
$extensq="SELECT name,fullname,secret,
            CASE WHEN (ptype = 'SNOM') THEN 'snom' ELSE CASE WHEN (ptype ~ '(^POLYCOM$)|(^IP_[0-9]+$)') THEN 'polycom' END END from users 
           LEFT OUTER JOIN features ON (name=exten) ";
if ($SUPER_USER != 1) {
  $extensq.=" LEFT OUTER JOIN astdb AS bgrp ON (name=bgrp.family AND bgrp.key='BGRP')";
}
$extensq.=" where (name=secret or secret='' or secret is null or length(secret) < 8 or secret !~ '[a-z]' OR secret !~ '[A-Z]') AND name !~ '^001[0-9]{5}$' ";
if ($SUPER_USER != 1) {
  $extensq.=" AND " . $clogacl;
}
$extensq.=" order by name";

$extens=pg_query($db,$extensq);

if ($_POST['print'] != "1") {
  $colspan=4;
} else {
  $colspan=3;
}

print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=" . $colspan . ">" . _("Extension Pin Code List") . "</TH></TR>";
print "<TR CLASS=list-color1>";

if ($_POST['print'] != "1") {
  print "<TH>Reset</TH>";
}

print "<TH>Extension</TH><TH>Fullname</TH><TH>Line Pass.</TH></TH></TR>";


if (isset($_POST['delexten'])) {
  require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
  $agi=new AGI_AsteriskManager();
  $agi->connect("127.0.0.1","admin","admin");
}

$rcol=1;
for($tcnt=0;$tcnt<pg_num_rows($extens);$tcnt++) {
  $r=pg_fetch_array($extens,$tcnt);
  
  $uppin="newpin" . $r[0];
  if ($$uppin == "on") {
    $r[2]=randpwgen(8);
    if ($r[3] != "") {
      $agi->command("sip notify reboot-" . $r[3]. " " . $r[0]);
    }
    pg_query($db,"UPDATE users SET secret='" . $r[2] . "' WHERE name='" . $r[0] . "'");
    $agi->command("sip prune realtime user " . $r['0']);
    $agi->command("sip prune realtime peer " . $r['0']);
    $agi->command("sip show peer " . $r[0] . " load");
    continue;
  }

  if ($r[1] == "") {
    $r[1]="&nbsp;";
  }

  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";

  if ($_POST['print'] != "1") {
    print "<TD><INPUT TYPE=CHECKBOX NAME=newpin" . $r[0] . "></TD>";
  }

  print "<TD>";
  if ($_POST['print'] < 1) {
    print "<A HREF=javascript:openextenedit('" . $r[0] . "')>";
  }
  print $r[0]; 

  if ($_POST['print'] < 1) {
    print "</A>";
  }
  print "</TD><TD>" . $r[1] . "</TD><TD>" . $r[2];
  print "</TD></TR>";
  $rcol++;
}

if ($_POST['print'] != "1") {
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD COLSPAN=" . $colspan . " ALIGN=LEFT>" . ($rcol - 1) . " Extensions Affected</TH></TR>";
  $rcol++;
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $colspan . " CLASS=heading-body><INPUT TYPE=SUBMIT NAME=delexten VALUE=\"" . _("Update") . "\"><INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\"></TH></TR>";
}
?>
</FORM>
</TABLE>
<?php
if (isset($_POST['delexten'])) {
  $agi->disconnect();
}
?>
