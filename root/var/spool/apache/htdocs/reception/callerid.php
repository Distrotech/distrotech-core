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
include "auth.inc";


if (isset($pbxupdate)) {
  if ($newval != "") {
    pg_query($db,"INSERT INTO callerid (cid,username) VALUES ('" . $newval . "','" . $PHP_AUTH_USER . "')");
  }
} else if (isset($pbxdelete)) {
  if ($key != "") {
    pg_query($db,"DELETE FROM callerid WHERE cid='$key' AND username = '" . $PHP_AUTH_USER . "'");
  }
}

$qgetdata=pg_query($db,"SELECT cid FROM callerid WHERE username='" . $PHP_AUTH_USER . "'");

%>

<CENTER>
<link rel="stylesheet" type="text/css" href="/style.php">
<FORM METHOD=POST ACTION=/reception/callerid.php>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH COLSPAN=2>Call Back CLI Configuration</TH>
</TR>
<TR CLASS=list-color1>
<TD>Select CLI Map To Delete</TD>
<TD><SELECT NAME=key>
<OPTION VALUE="">Add New Caller ID Bellow</OPTION>
<%
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[0] . "</OPTION>"; 
}
%>
</SELECT>
</TR>
<TR CLASS=list-color2>
<TD>Caller ID To Authenticate</TD>
<TD><INPUT TYPE=TEXT NAME=newval></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=SUBMIT NAME=pbxdelete VALUE="Delete Selected">
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT NAME=pbxupdate VALUE="Save Changes">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=MIDDLE COLSPAN=2>
  <A HREF=/reception/vladmin.php>Account Configuration</A>
  </TD>
</TR>
</TABLE>
</FORM>
