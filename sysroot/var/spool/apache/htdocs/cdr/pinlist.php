<CENTER>
<CENTER>
<FORM NAME=ppage METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<FORM METHOD=POST NAME=deletexten onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<INPUT TYPE=HIDDEN NAME=print>
<%
include_once "auth.inc";
$extensq="SELECT name,users.fullname,secret,voicemail.password,roampass from users
 left outer join features on (name = exten)
 left outer join voicemail on (name = voicemail.mailbox)
 left outer join astdb as lpre on (lpre.family = 'LocalPrefix' and lpre.key = substr(name,0,3))";
if ($SUPER_USER != 1) {
  $extensq.=" LEFT OUTER JOIN astdb AS bgrp ON (name=bgrp.family AND bgrp.key='BGRP')";
}
$extensq.=" where lpre.value='1'";
if ($SUPER_USER != 1) {
  $extensq.=" AND " . $clogacl;
}
$extensq.=" order by name";
$extens=pg_query($db,$extensq);

function newpin($exten) {
  global $db;

  $pincnt=1;
  $pintry=1;

  while (($pintry <= 10) && ($pincnt > 0)) {
    $randpin=rand(0,9999);
    $randpin=str_pad($randpin,4,"0",STR_PAD_LEFT);
    $pincntq=pg_query($db,"SELECT count(id) FROM features WHERE roampass='" . $randpin . "'");
    list($pincnt)=pg_fetch_array($pincntq,0);
    $pintry++;
  }
  if ($pincnt == 0) {
    $ud=pg_query($db,"UPDATE features SET roampass='" . $randpin . "' WHERE exten='" . $exten . "'");
  }
  return $randpin;
}


if ($_POST['print'] != "1") {
  $colspan=7;
} else {
  $colspan=6;
}

print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=" . $colspan . ">" . _("Extension Pin Code List") . "</TH></TR>";
print "<TR CLASS=list-color1>";

if ($_POST['print'] != "1") {
  print "<TH>Reset</TH>";
}

print "<TH>Extension</TH><TH>Fullname</TH><TH>Line Pass.</TH><TH>VM Pass.</TH><TH>Pin Code</TH></TH></TR>";

$rcol=1;
for($tcnt=0;$tcnt<pg_num_rows($extens);$tcnt++) {
  $r=pg_fetch_array($extens,$tcnt);
  
  $uppin="newpin" . $r[0];
  if (($$uppin == "on") || ($r[4] == "") || ($r[4] == $r[0])) {
    $r[4]=newpin($r[0]);
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
  print "</TD><TD>" . $r[1] . "</TD><TD>" . $r[2] . "</TD><TD>" . $r[3] . "</TD><TD>" . $r[4] . "</TD></TR>";
  $rcol++;
}

if ($_POST['print'] != "1") {
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD COLSPAN=" . $colspan . " ALIGN=LEFT>" . ($rcol - 1) . " Extensions Affected</TH></TR>";
  $rcol++;
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $colspan . " CLASS=heading-body><INPUT TYPE=SUBMIT NAME=delexten VALUE=\"" . _("Update") . "\"><INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\"></TH></TR>";
}
%>
</FORM>
</TABLE>
