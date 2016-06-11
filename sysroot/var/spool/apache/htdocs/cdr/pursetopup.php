<CENTER>
<CENTER>
<FORM NAME=ppage METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<FORM METHOD=POST NAME=topupexten onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<INPUT TYPE=HIDDEN NAME=print>
<INPUT TYPE=HIDDEN NAME=account>
<INPUT TYPE=HIDDEN NAME=ammount>
<?php
include_once "auth.inc";

if (($_POST['account'] != "") && ($_POST['ammount'] != "")) {
  $_POST['ammount']=sprintf("%d",$_POST['ammount']*100);
  pg_query($db,"INSERT INTO purse_limit (name,ammount) VALUES ('" . $_POST['account'] . "','" . $_POST['ammount'] . "')");
}

$extensq="SELECT users.name,fullname,purse,ammount,sum(cost) FROM cdr
 LEFT OUTER JOIN trunkcost USING (uniqueid) 
 LEFT OUTER JOIN users ON (cdr.accountcode=users.name)
 LEFT OUTER JOIN features ON (users.name=exten)
 LEFT OUTER JOIN purse_update ON (users.name=purse_update.name)
 LEFT OUTER join astdb AS lpre ON (lpre.family = 'LocalPrefix' and lpre.key = substr(users.name,0,3))";
if ($SUPER_USER != 1) {
  $extensq.=" LEFT OUTER JOIN astdb AS bgrp ON (users.name=bgrp.family AND bgrp.key='BGRP')";
}
$extensq.=" WHERE cdr.calldate > date_trunc('month',now()) AND cdr.disposition='ANSWERED' AND lpre.value='1' AND purse IS NOT NULL AND purse != ''";
if ($SUPER_USER != 1) {
  $extensq.=" AND " . $clogacl;
}
$extensq.=" GROUP BY users.name,users.fullname,purse,ammount order by users.name";

//print $extensq . "<p>";

$extens=pg_query($db,$extensq);

if ($_POST['print'] != "1") {
  $colspan=5;
} else {
  $colspan=5;
}

print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=" . $colspan . ">" . _("Extension Monthly Limit") . "</TH></TR>";
print "<TR CLASS=list-color1>";

if ($_POST['print'] != "1") {
  print "<TH CLASS=heading-body2 ALIGN=LEFT>Extension/Topup</TH>";
} else {
  print "<TH CLASS=heading-body2 ALIGN=LEFT>Extension</TH>";
}

print "</TH><TH CLASS=heading-body2 ALIGN=RIGHT>Limit</TH><TH CLASS=heading-body2 ALIGN=RIGHT>Added This Month</TH>";
print "<TH CLASS=heading-body2 ALIGN=RIGHT>Used This Month</TH><TH CLASS=heading-body2 ALIGN=RIGHT>Available</TH></TR>";

$rcol=1;
for($tcnt=0;$tcnt<pg_num_rows($extens);$tcnt++) {
  $r=pg_fetch_array($extens,$tcnt);
  
  if ($r[1] == "") {
    $r[1]="&nbsp;";
  }

  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";

  if ($_POST['print'] != "1") {
    print "<TD><A HREF=javascript:toplimit('" . $r[0] . "')>" . $r[1] . " (" . $r[0] . ")</TD>";
  } else {
    print "<TD>" . $r[1] . " (" . $r[0]  . ")</TD>";
  }

  $r[2]=sprintf("%0.2f",$r[2]);
  $r[3]=sprintf("%0.2f",$r[3]/100);
  $r[4]=sprintf("%0.2f",$r[4]/100000);
  $tavail=sprintf("%0.2f",($r[2]+$r[3])-$r[4]);
  print "<TD ALIGN=RIGHT>" . $r[2] . "</TD><TD ALIGN=RIGHT>" . $r[3] . "</TD><TD ALIGN=RIGHT>" . $r[4] . "</TD><TD ALIGN=RIGHT>" . $tavail . "</TD>";
  $rcol++;
}

if ($_POST['print'] != "1") {
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD COLSPAN=" . $colspan . " ALIGN=LEFT>" . ($rcol - 1) . " Extensions Affected</TH></TR>";
  $rcol++;
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $colspan . " CLASS=heading-body><INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\"></TH></TR>";
}
?>
</FORM>
</TABLE>
