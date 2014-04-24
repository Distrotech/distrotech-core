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
$act[t]="Yes";
$act[f]="No";

$usert[1]="user";
$usert[2]="booth";

if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}
$users=pg_query($db,"SELECT users.name,users.secret,users.password,users.activated,users.credit,users.fullname,
                            tariff.tariffname
                     FROM users LEFT OUTER JOIN tariff ON (users.tariff = tariff.tariffcode)
                     WHERE usertype = " . $_SESSION['classi'] . " AND agentid = ". $_SESSION['resellerid'] . "
                     ORDER BY fullname");
$num=pg_num_rows($users); 

$bcolor[0]="list-color1";
$bcolor[1]="list-color2";
$_SESSION['disppage']="cshop/edit";
if ($_SESSION['classi'] == 1) { 
  $_SESSION['disppage'].="user.php";
} else {
  $_SESSION['disppage'].="booth.php";
}

if ($_SESSION['classi'] != "") {
  unset($_SESSION['classi']);
}
%>
<CENTER>
<FORM NAME=editac METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<INPUT TYPE=HIDDEN NAME=number VALUE="">

<TR CLASS=list-color2>
<TH ALIGN=LEFT CLASS=heading-body2>Number</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Line Pass.</TH>
<TH ALIGN=LEFT CLASS=heading-body2>VM Pass.</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Act.</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Credit</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Name</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Rate Plan</TH></TR>
<%
for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($users,$i);
  $rem=$i % 2; 
  print "<TR CLASS=" . $bcolor[$rem] . ">";
  for ($j=0;$j < count($r);$j++) {
    if ($j == "3") {
      $r[$j]=$act[$r[$j]];
    } else if ($j == "4") {
      $r[$j]=sprintf("%0.2f",($_SESSION['rexrate']*$r[$j])/10000);
//      $r[$j]=sprintf("%0.2f",$r[$j]/100);;
    } else if ($j == "0") {
      $r[$j]="<A HREF=javascript:voipacedit('" . $r[$j] . "')>" . $r[$j] . "</A>";
    }
    print  "<TD>" . $r[$j] . "</TD>";
  }
  print "</TR>\n";
}
%>
</FORM>
</TABLE>
