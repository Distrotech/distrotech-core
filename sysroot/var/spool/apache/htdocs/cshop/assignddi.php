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

if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}

if (isset($_POST['setddi'])) {
  $isclid=pg_query($db,"SELECT callerid FROM cc_callerid WHERE callerid='" . $_POST['newddinum'] . "'");
  if (pg_num_rows($isclid) > 0) {
    pg_query($db,"UPDATE cc_callerid SET reseller='" . $_POST['userid'] . "' WHERE callerid='" . $_POST['newddinum'] . "'");
  } else if ($_SESSION['resellerid'] == 0) {  
    pg_query($db,"INSERT INTO cc_callerid (callerid,reseller) VALUES ('" . $_POST['newddinum'] . "','" . $_POST['userid'] . "')");
  }
}

if ($_SESSION['resellerid'] > 0) {
  $searchlim="WHERE (cc_callerid.reseller='" . $_SESSION['resellerid'] . "' OR reseller.owner='" . $_SESSION['resellerid'] . "')";
  $max=2;
} else {
//  $searchlim="WHERE cc_callerid.reseller='" . $_SESSION['resellerid'] . "'";
  $searchlim="WHERE (cc_callerid.reseller='" . $_SESSION['resellerid'] . "' OR reseller.owner='" . $_SESSION['resellerid'] . "')";
  $max=2;
}

$usersq="SELECT reseller.description||' ('||reseller.username||')',cc_callerid.callerid 
                       FROM cc_callerid LEFT OUTER JOIN reseller ON (reseller.id=cc_callerid.reseller) " . $searchlim . " 
                       ORDER BY reseller.description,reseller.username";

//print $usersq . "<P>";
$users=pg_query($db,$usersq);

$num=pg_num_rows($users); 

$bcolor[1]="list-color1";
$bcolor[0]="list-color2";
?>
<CENTER>
<FORM NAME=editac METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<SCRIPT>
  var contsearch=new TextComplete(document.editac.userid,ldapautodata,'resellerxml.php',setautosearchurl,document.editac,contsearch);
  var ddisearch=new TextComplete(document.editac.newddinum,ldapautodata,'ddixml.php',setautosearchurl,document.editac,ddisearch);
</SCRIPT>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>

<TR CLASS=list-color2>
<TH ALIGN=LEFT CLASS=heading-body2>Reseller</TH>
<TH ALIGN=LEFT CLASS=heading-body2>DDI</TH>
<?php
if ($_SESSION['resellerid'] == 0) {
//  print "<TH ALIGN=LEFT CLASS=heading-body2>Reseller</TH>";
}
print "</TR>";
print "<TR CLASS=" . $bcolor[1] . "><TD><INPUT NAME=userid autocomplete=off SIZE=40></TD>";
print "<TD><INPUT NAME=newddinum autocomplete=off SIZE=15></TD>";
print "</TR>";


for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($users,$i);
  $rem=$i % 2; 
  print "<TR CLASS=" . $bcolor[$rem] . ">";
  for ($j=0;$j < $max;$j++) {
    if (($j == "2") && ($r[4] == $_SESSION['resellerid'])) {
      $r[$j]="<DIV ID=\"edit_" . $r[1] . "\"><A HREF=javascript:voipddiedit('" . $r[1] . "','" . $r[2] . "')>" . $r[$j] . "</A></DIV>";
    }
    print  "<TD>" . $r[$j] . "</TD>";
  }
  print "</TR>\n";
}

//$i++;
$rem=$i % 2; 
print "<TR CLASS=" . $bcolor[$rem] . "><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=submit NAME=setddi VALUE=Save></TD></TR>";
?>
</FORM>
</TABLE>
