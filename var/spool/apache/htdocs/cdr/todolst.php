<CENTER>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<FORM NAME=ppage METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<FORM NAME=officehours METHOD=POST>
<INPUT TYPE=HIDDEN NAME=index VALUE="">
<INPUT TYPE=HIDDEN NAME=timerange VALUE="">
<%
include_once "cdr/auth.inc";
include_once "ldap/auth.inc";

$sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
  $ADMIN_USER="admin";
} else {
  $ADMIN_USER="pleb";
}

$rcol=1;
if (($_POST['print'] != "1") && ($ADMIN_USER != "pleb")) {
  $cspan="5";
} else {
  $cspan="4";
}

print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TH class=heading-body COLSPAN=" . $cspan . ">" . _("To Do List") . "</TH></TR>\n";
$rcol++;
print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";
if (($_POST['print'] != "1") && ($ADMIN_USER != "pleb")) {
  print "<TH class=heading-body2>" . _("Delete") . "</TH>";
}
print "<TH class=heading-body2>" . _("Created By") . "</TH><TH class=heading-body2>" . _("To Do List") . "</TH>";
print "<TH class=heading-body2>" . _("Assigned To") . "</TH><TH class=heading-body2>" . _("Created Date") . "</TH></TR>\n";
$rcol++;

if (($todolist != "") && ($assignedto != "")) {
  $dbins="INSERT INTO todolist (createby,todolist,assignedto) VALUES ('" . $createby . "','" . $todolist . "','" . $assignedto . "')";
  pg_query($db,$dbins);
}

$tmap=pg_query($db,"SELECT createby,todolist,assignedto,id,date_trunc('minute',date) FROM todolist ORDER BY date DESC");

for($tcnt=0;$tcnt<pg_num_rows($tmap);$tcnt++) {
  $r=pg_fetch_array($tmap,$tcnt);
  $todel="del" . $r[3];
  if ($$todel == "on") {
    pg_query("DELETE FROM todolist WHERE id=" . $r[3]);
    continue;
  }

  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";
  if (($_POST['print'] != "1") && ($ADMIN_USER != "pleb")) {
    print "<TD><INPUT TYPE=CHECKBOX NAME=del" . $r[3] . "></TD>";
  }
  print "<TD>" . $r[0] . "</TD><TD>" . $r[1] . "</TD><TD>" . $r[2] . "</TD><TD>" . $r[4];
  print "</TD></TR>\n";
  $rcol++;
}

print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TH ALIGN=LEFT class=heading-body2>New</TD>";

print "<TD><INPUT TYPE=TEXT NAME=createby MAXLEN=15 SIZE=10 VALUE=></TD>";
print "<TD><INPUT TYPE=TEXT NAME=todolist MAXLEN=200 SIZE=30 VALUE=></TD>";
print "<TD><INPUT TYPE=TEXT NAME=assignedto MAXLEN=15 SIZE=10 VALUE=></TD>";
print "<TD>Todo Due Date</TD>";

print "</TR>\n";

$rcol++;

if ($_POST['print'] != "1") {
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $cspan . " CLASS=heading-body>";
  if ($ADMIN_USER == "admin") {
    print "<INPUT TYPE=SUBMIT VALUE=\"" . _("Update") . "\">";
  }
  print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\"></TH></TR>\n";
}
%>
</FORM>
</TABLE>
