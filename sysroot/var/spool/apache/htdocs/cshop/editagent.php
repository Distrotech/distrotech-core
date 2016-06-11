<CENTER>
<FORM METHOD=POST NAME=editagentf onsubmit="ajaxsubmit(this.name);return false">
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

if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}
$users=pg_query("SELECT username,description,admin,id FROM reseller WHERE
                     id = " . $_SESSION['resellerid'] . " OR owner = " . $_SESSION['resellerid'] . "ORDER BY description");

$num=pg_num_rows($users); 
$_SESSION['disppage']="cshop/addagent.php";
?>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Edit Reseller Or Operator</TH>
</TR>
<TR CLASS=list-color1>
<TD WIDTH=50%>Select Operator Or Reseller To Edit</TD>
<TD VALIGN=MIDDLE>
<SELECT NAME=edituser>
<?php

for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($users,$i);
  print  "<OPTION VALUE=" . $r[3] . ">" . $r[1] . " (";
  if ($r[2] == "t") {
    print "Reseller:";
  } else {
    print "Operator:";
  }
  print $r[0] . ")</OPTION>\n";
}
?>
</SELECT></TD></TR>
<TR CLASS=list-color2><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT></TH></TR>
</FORM>
</TABLE>
