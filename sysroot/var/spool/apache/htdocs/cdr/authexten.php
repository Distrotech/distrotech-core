<CENTER>
<FORM METHOD=POST NAME=authexten onsubmit="ajaxsubmit(this.name);return false;">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<?php
include_once "auth.inc";
$extens=pg_query($db,"SELECT name,fullname,ipaddr,snommac from users left outer join features on (name = exten) 
			left outer join astdb as lpre on (lpre.family = 'LocalPrefix' and lpre.key = substr(name,0,3))
                         where autoauth = '1' AND lpre.value='1' order by name");

if ($_POST['print'] != "1") {
  $colspan=5;
} else {
  $colspan=4;
}

print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=" . $colspan . ">" . _("Unauthorised Auto Extensions") . "</TH></TR>";
print "<TR CLASS=list-color1>";

if ($_POST['print'] != "1") {
  print "<TH>Auth.</TH>";
}

print "<TH>Extension</TH><TH>Fullname</TH><TH>IP Address</TH><TH>MAC Address</TH></TR>";

$rcol=1;
for($tcnt=0;$tcnt<pg_num_rows($extens);$tcnt++) {
  $r=pg_fetch_array($extens,$tcnt);
  
  $toauth="auth" . $r[0];
  if ($$toauth == "on") {
    pg_query($db,"UPDATE features SET autoauth='0' WHERE exten='" . $r[0] . "'");
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

  if ($_POST['print'] != "1") {
    print "<TD><INPUT TYPE=CHECKBOX NAME=auth" . $r[0] . "></TD>";
  }

  print "<TD><A HREF=javascript:openextenedit('" . $r[0] . "')>" . $r[0] . "</A></TD><TD>" . $r[1] . "</TD><TD>" . $r[2] . "</TD><TD>" . $r[3] . "</TD></TR>";
  $rcol++;
}

if ($_POST['print'] != "1") {
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD COLSPAN=" . $colspan . " ALIGN=LEFT>" . ($rcol - 1) . " Extensions Affected</TH></TR>";
  $rcol++;
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $colspan . " CLASS=heading-body>
<INPUT TYPE=SUBMIT onclick=this.name='authexten' VALUE=\"Authorise\"><INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\"></TH></TR>";
}
?>
</FORM>
</TABLE>
<FORM NAME=ppage METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
</FORM>

