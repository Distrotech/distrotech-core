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

$virtc=pg_query($db,"SELECT companyid, description,contact,email,altnumber FROM virtualcompany WHERE resellerid='" . $_SESSION['resellerid'] . "'");
$num=pg_num_rows($virtc); 

$bcolor[0]="list-color1";
$bcolor[1]="list-color2";
$_SESSION['disppage']="cshop/editvirt.php";
%>
<CENTER>
<FORM NAME=editvc METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<INPUT TYPE=HIDDEN NAME=number VALUE="">

<TR CLASS=list-color2>
<TH ALIGN=LEFT CLASS=heading-body2>Company</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Contact</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Email</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Number</TH></TR>
<%
for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($virtc,$i);
  $rem=$i % 2; 
  print "<TR CLASS=" . $bcolor[$rem] . ">";
  $vcid=array_shift($r);
  for ($j=0;$j < count($r);$j++) {
    if ($j == "0") {
      $r[$j]="<A HREF=javascript:voipvcedit('" . $vcid . "')>" . $r[$j] . "</A>";
    }
    print  "<TD>" . $r[$j] . "</TD>";
  }
  print "</TR>\n";
}
%>
</FORM>
</TABLE>
