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
include "/var/spool/apache/htdocs/cshop/mkuser.inc";

if (isset($_POST['addbooth'])) {
  $_POST['cno']=cardnum();	
  $pass=cardpin();
  pg_query($db,"INSERT INTO users (name,defaultuser,fromuser,mailbox,secret,credit,tariff,
                                   activated,usertype,fullname,agentid) VALUES (
                                   '" . $_POST['cno'] . "','" . $_POST['cno'] . "','" . $_POST['cno'] . "','','$pass','0','$tariff','f',
                                   '2','" . $_POST['bname'] . "'," . $_SESSION['resellerid'] . ")");
?>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>New Phone Booth Created</TH>
</TR>
<TR CLASS=list-color1>
<TD WIDTH=50%>Number</TD>
<TD WIDTH=50% ALIGN=LEFT><?php print $_POST['cno'];?></TD>
<TR CLASS=list-color2>
<TD WIDTH=50%>Password</TD>
<TD WIDTH=50% ALIGN=LEFT><?php print $pass;?></TD>
</TR>
</TABLE>
<?php
} else {
  $tplan=pg_query($db,"SELECT tariffname,tariffcode FROM tariff WHERE tariffcode LIKE '" . $_SESSION['resellerid'] . "-%' ORDER BY tariffname");
?>
<FORM METHOD=POST NAME=mkbooth onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Create A New Phone Booth</TH>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50%>Tariff Plan</TD>
  <TD WIDTH=50% ALIGN=LEFT><SELECT NAME=tariff><?php
  $num=pg_num_rows($tplan);
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_array($tplan,$i,PGSQL_NUM);
    print "<OPTION VALUE=\"" . $r[1] . "\">" . $r[0] . "</OPTION>\n";
  }?>
</SELECT></TD></TR>
<TR CLASS=list-color2>
  <TD>Booth Name/Number</TD>
  <TD><INPUT TYPE=TEXT NAME=bname></TD>
<TR CLASS=list-color1>
<TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT NAME=addbooth VALUE="Add Booth"></TD>
</TABLE>
</FORM><?php
}?>
