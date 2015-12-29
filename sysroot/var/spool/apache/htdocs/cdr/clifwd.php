<CENTER>
<CENTER>
<FORM NAME=ppage METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
</FORM><FORM NAME=clifwdf METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=index VALUE="">
<INPUT TYPE=HIDDEN NAME=timerange VALUE="">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<?php
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
  $cspan="4";
} else {
  $cspan="3";
}

print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TH class=heading-body COLSPAN=" . $cspan . ">" . _("Inbound CLI Maping") . 
"</TH></TR>\n";
$rcol++;
print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";
if (($_POST['print'] != "1") && ($ADMIN_USER != "pleb")) {
  print "<TH class=heading-body2>" . _("Delete") . "</TH>";
}
print "<TH class=heading-body2>" . _("Prefix") . "</TH><TH class=heading-body2>" . _("Digits To Strip") . "</TH>";
print "<TH class=heading-body2>" . _("Match") . "</TH>\n</TR>";
$rcol++;

if ($match != "") {
  if ($strip == "") {
    $strip=0;
  }
  pg_query($db,"INSERT INTO climap (prefix,strip,match) VALUES ('" . $prefix . "'," . $strip . ",'" . $match . "')");
}

$tmap=pg_query($db,"SELECT prefix,strip,match,id FROM climap ORDER by length(prefix)");

for($tcnt=0;$tcnt<pg_num_rows($tmap);$tcnt++) {
  $r=pg_fetch_array($tmap,$tcnt);
  $todel="del" . $r[3];
  if (${$todel} == "on") {
    pg_query("DELETE FROM climap WHERE id=" . $r[3]);
    continue;
  }

  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";
  if (($_POST['print'] != "1") && ($ADMIN_USER != "pleb")) {
    print "<TD><INPUT TYPE=CHECKBOX NAME=del" . $r[3] . "></TD>";
  }
  print "<TD>" . $r[0] . "</TD><TD>" . $r[1] . "</TD><TD>" . $r[2];
  print "</TD></TR>\n";
  $rcol++;
}

print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TH ALIGN=LEFT class=heading-body2>New</TD>";

print "<TD><INPUT TYPE=TEXT NAME=prefix MAXLEN=5 SIZE=5 VALUE=></TD>";
print "<TD><INPUT TYPE=TEXT NAME=strip MAXLEN=5 SIZE=5 VALUE=></TD>";
print "<TD><INPUT TYPE=TEXT NAME=match MAXLEN=200 SIZE=30 VALUE=></TD>";

print "</TR>\n";

$rcol++;

if ($_POST['print'] != "1") {
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $cspan . " CLASS=heading-body>";
  if ($ADMIN_USER == "admin") {
    print "<INPUT TYPE=SUBMIT VALUE=\"" . _("Update") . "\">";
  }
  print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\"></TH></TR>\n";
}
?>
</FORM>
</TABLE>
