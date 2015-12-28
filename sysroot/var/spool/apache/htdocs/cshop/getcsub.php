<CENTER>
<FORM NAME=getbreak METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<?php
/*
#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2005  <ZA Telecomunications>
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


if ((isset($_POST['addcountry'])) && ($_SESSION['resellerid'] == 0)) {
  if (( $_POST['newcountrycode'] != "") && ($_POST['newcountryname'] != "")) {
    pg_query($db,"INSERT INTO country (countrycode,countryname) VALUES ('" . $_POST['newcountrycode'] . "','" . $_POST['newcountryname'] . "')");
    $_SESSION['country']=$_POST['newcountrycode'];
    $_SESSION['reload']=true;
  } else {
    include "/var/spool/apache/htdocs/cshop/getcountry.php";
    return;
  }
} else if (($_POST['delcountry'] == "1") && ($_SESSION['resellerid'] == 0)) {
  pg_query($db,"DELETE FROM country WHERE countrycode='" . $_SESSION['country'] . "'");
  pg_query($db,"DELETE FROM tariffrate WHERE countrycode='" . $_SESSION['country'] . "'");
  pg_query($db,"DELETE FROM countryprefix WHERE countrycode='" . $_SESSION['country'] . "'");
  include "/var/spool/apache/htdocs/cshop/getcountry.php";
  return;
}

$act[t]="Yes";
$act[f]="No";

if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}

if (($_SESSION['tariffcode'] != "A") || ($_SESSION['resellerid'] != "0")) {
  $tarcode=$_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'];
} else {
  $tarcode=$_SESSION['tariffcode'];
}

$cousub=pg_query($db,"SELECT DISTINCT subcode,trunkprefix,id,trunk.description FROM tariffrate LEFT OUTER JOIN trunk USING (trunkprefix) WHERE countrycode = '" . $_SESSION['country'] . "' AND tariffcode='" . $tarcode . "' ORDER BY subcode");
$num=pg_num_rows($cousub); 

$bcolor[0]="list-color2";
$bcolor[1]="list-color1";

$_SESSION['disppage']="cshop/editrate.php";

?>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Alter/Create A Break Out (Subcode)
</TR>
<TR CLASS=list-color1>
<TD WIDTH=50%>Select Break Out To Alter</TH>
<TH ALIGN=CENTER VALIGN=MIDDLE>
<SELECT NAME=breakout>
<?php

for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($cousub,$i);
//  print  "<OPTION VALUE=\"" . $r[0] . "\">" . $r[0] . "(R" . sprintf("%0.2f",$r[1]) . "/m via " . $r[2] . ")</OPTION>\n";
  print  "<OPTION VALUE=\"" . $r[2] . "\"";
  if (($r[0] == $_SESSION['subcode']) && ($r[1] == $_SESSION['trunkprefix'])) {
    print " SELECTED";
  }
  print ">" . $r[0] . " (" . $r[3] . ")</OPTION>\n";
}
?>
</SELECT></TH></TR>
<TR CLASS=list-color2>
<TH COLSPAN=2>
  <INPUT TYPE=BUTTON ONCLICK=openpage('cshop/getcountry.php','tariffs') VALUE="<<">
  <INPUT TYPE=SUBMIT VALUE="Alter Breakout Rate">
<?php
  if ($_SESSION['resellerid'] == 0) {?>
    <INPUT TYPE=BUTTON ONCLICK="deleteconf('This Breakout',document.getbreak,document.getbreak.delbreak)" VALUE="Delete Breakout">
    <INPUT TYPE=HIDDEN NAME=delbreak>
<?php
  }
  if (isset($_SESSION['subcode'])) {?>
    <INPUT TYPE=BUTTON ONCLICK=openpage('cshop/editrate.php','tariffs') VALUE=">>">
<?php
  }
?>
</TH></TR>
<?php
if ($_SESSION['resellerid'] == 0) {
?>
  <TR CLASS=list-color1 CLASS=heading-body2><TH COLSPAN=2>Add Breakout</TH></TR>
  <TR CLASS=list-color2><TD>Breakout Name</TD><TD>
  <INPUT TYPE=TEXT NAME=newbreakout></TD></TR>
  <TR CLASS=list-color1><TD>Rate (R/m)</TD><TD>
  <INPUT TYPE=TEXT NAME=newrate></TD></TR>
  <TR CLASS=list-color2>
  <TD>Provider</TD><TD><SELECT NAME=trunkprefix>
<?php
  $tplan=pg_query($db,"SELECT distinct trunkprefix,name from provider ORDER BY name");
  $num=pg_num_rows($tplan);
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_array($tplan,$i,PGSQL_NUM);
    print "<OPTION VALUE=\"" . $r[0] . "\"";
    print ">" . $r[1] . "</OPTION>\n";
  }
?>
</SELECT>
</TD></TR>
<TR CLASS=list-color1><TD ALIGN=MIDDLE COLSPAN=2>
<INPUT TYPE=SUBMIT onclick=this.name='addbreak' VALUE="Add Breakout"></TD></TR>
<?php }?>
</FORM>
</TABLE>
