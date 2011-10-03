<%
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
//    print_r($_POST);
    pg_query($db,"UPDATE cc_callerid SET userid='" . $_POST['userid'] . "' WHERE callerid='" . $_POST['newddinum'] . "'");
  } else if ($_SESSION['resellerid'] == 0) {
    pg_query($db,"INSERT INTO cc_callerid (callerid,userid,ddifwd,reseller) VALUES ('" . $_POST['newddinum'] . "','" . $_POST['userid'] . "','" . $_POST['newddifwd'] . "','0')");
  } else {
//    print_r($_POST);
  }
}

if ($_SESSION['resellerid'] > 0) {
  $searchlim="WHERE reseller='" . $_SESSION['resellerid'] . "'";
  $max=3;
} else {
  $searchlim="WHERE reseller='" . $_SESSION['resellerid'] . "'";
//  $searchlim="WHERE reseller IS NOT NULL";
  $max=3;
}

$users=pg_query($db,"SELECT users.fullname||' ('||users.name||')' AS user,cc_callerid.callerid,
                         CASE WHEN (ddifwd IS NOT NULL) THEN ddifwd ELSE  cc_callerid.callerid END AS ddifwd,
                         reseller.description||' ('||reseller.username||')',reseller FROM cc_callerid 
                       left outer join users on (userid=users.uniqueid)
                       left outer join reseller on (reseller=reseller.id)" . $searchlim . " 
                         AND userid IS NOT NULL
                       order by fullname,cc_callerid.callerid");

$num=pg_num_rows($users); 

$bcolor[0]="list-color2";
$bcolor[1]="list-color1";
%>
<CENTER>
<FORM NAME=editac METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<SCRIPT>
  var contsearch=new TextComplete(document.editac.userid,ldapautodata,'contactxml.php',setautosearchurl,document.editac,contsearch);
  var ddisearch=new TextComplete(document.editac.newddinum,ldapautodata,'ddixml.php',setautosearchurl,document.editac,ddisearch);
</SCRIPT>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<INPUT TYPE=HIDDEN NAME=number VALUE="">
<INPUT TYPE=HIDDEN NAME=newddi VALUE="">

<TR CLASS=list-color2>
<TH ALIGN=LEFT CLASS=heading-body2>User</TH>
<TH ALIGN=LEFT CLASS=heading-body2>DDI</TH>
<TH ALIGN=LEFT CLASS=heading-body2>DDI Forward</TH>
<%
if ($_SESSION['resellerid'] == 0) {
//  print "<TH ALIGN=LEFT CLASS=heading-body2>Reseller</TH>";
}
print "</TR>";

print "<TR CLASS=list-color1><TD><INPUT NAME=userid autocomplete=off SIZE=40></TD>";
print "<TD><INPUT NAME=newddinum autocomplete=off SIZE=15></TD>";
print "<TD><INPUT NAME=newddifwd SIZE=15></TD>";
print "</TR>";
//$i++;
//$rem=$i % 2; 


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
$rem=$i % 2;
print "<TR CLASS=" . $bcolor[$rem] . "><TD ALIGN=MIDDLE COLSPAN=3><INPUT TYPE=submit NAME=setddi VALUE=Save></TD></TR>";
print "</FORM>";

%>
</TABLE>
