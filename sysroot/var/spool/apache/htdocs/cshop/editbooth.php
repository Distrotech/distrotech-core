<?php
if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}

if ($_POST['delbooth'] == "1") {
  $boothq=pg_query($db,"SELECT id,credit
                     FROM users 
                     WHERE usertype=2 AND name='" . $_SESSION['number'] . "'
                           AND agentid = " . $_SESSION['resellerid'] . " LIMIT 1");
  $booth=pg_fetch_row($boothq,0);
  pg_query($db,"UPDATE reseller set rcallocated=rcallocated-" . $booth[1] . " WHERE id = " . $_SESSION['resellerid']);
  pg_query("DELETE FROM users WHERE name='" . $_SESSION['number'] . "'");
  include "getbooth.php";
  return;
}

if (isset($_POST['savebooth'])) {
  pg_query("UPDATE users SET secret='" . $_POST['userpass'] . "',fullname='" . $_POST['firstname'] . 
        "',tariff='" . $_POST['tariff'] . "' WHERE name='" . $_SESSION['number'] . "'");
}

$boothq=pg_query($db,"SELECT fullname,tariff
                     FROM users
                     WHERE usertype=2 AND name='" . $_SESSION['number'] . "'
                           AND agentid = " . $_SESSION['resellerid'] . " LIMIT 1");
$booth=pg_fetch_row($boothq,0);
?>
<CENTER>
<FORM NAME=edituser METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%> 
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Editing <?php print $_SESSION['number'];?></TH></TR>
<TR CLASS=list-color1>
<TD ALIGN=LEFT>Booth Name</TD><TD><INPUT TYPE=TEXT NAME=firstname VALUE="<?php print $booth[0];?>"></TD></TR>
<TR CLASS=list-color2>
<TD ALIGN=LEFT>Rate Plan</TD><TD><SELECT NAME=tariff><?php
  $tplan=pg_query($db,"SELECT tariffname,tariffcode FROM tariff WHERE tariffcode LIKE '" .
                       $_SESSION['resellerid'] . "-%' ORDER BY tariffname");
  $num=pg_num_rows($tplan);
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_array($tplan,$i,PGSQL_NUM);
    print "<OPTION VALUE=\"" . $r[1] . "\"";
    if ($booth[1] == "$r[1]") {
      print " SELECTED";
    }
    print ">" . $r[0] . "</OPTION>\n";
  }?>
<?php
?>
</SELECT>
</TD></TR>
<TR CLASS=list-color1>
<TD ALIGN=MIDDLE COLSPAN=2>
<INPUT TYPE=SUBMIT NAME=savebooth VALUE="Save Changes">
<INPUT TYPE=BUTTON ONCLICK="deleteconf('This Booth',document.edituser,document.edituser.delbooth)" VALUE="Delete">
<INPUT TYPE=HIDDEN NAME=delbooth>
</TD></TR>
</TABLE>
</FORM>
