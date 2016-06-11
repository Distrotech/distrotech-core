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

include "auth.inc";

/*
AREAGATEWAY
*/

if (isset($pbxupdate)) {
  if ($newkey != "") {
    pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('CLI','$newkey','$newval')");
  } else if ($key != "") {
    pg_query($db,"DELETE FROM astdb WHERE family='CLI' AND key='$key'");
  }
}

$qgetdata=pg_query($db,"SELECT key,value FROM astdb WHERE family='CLI'");

?>

<CENTER>
<FORM METHOD=POST>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH COLSPAN=2>Asterisk PBX CLI Configuration</TH>
</TR>
<TR CLASS=list-color1>
<TD>Select CLI Map To Delete</TD>
<TD><SELECT NAME=key>
<OPTION VALUE="">Add New CLI Map Below</OPTION>
<?php
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[0] . "->" . $getdata[1] . "</OPTION>"; 
}
?>
</SELECT>
</TR>
<TR CLASS=list-color2>
<TD>Extension</TD>
<TD><INPUT TYPE=TEXT NAME=newkey></TD>
</TR>
<TR CLASS=list-color1>
<TD>CLI TO Display</TD>
<TD><INPUT TYPE=TEXT NAME=newval></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT NAME=pbxupdate VALUE="Save Changes">
  </TD>
</TR>
</TABLE>
</FORM>
