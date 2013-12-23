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

include_once "auth.inc";

if (isset($pbxupdate)) {
  if ($h323gwid != "") {
    pg_query($db,"INSERT INTO users (defaultuser,name,password,h323permit,h323gkid,ipaddr,h323neighbor) VALUES ('" . $h323gwid . "','" . $h323gwid . "','','allow','" . $h323gkid . "','" . $h323permip . "','t')");
  } else if ($key != "") {
    pg_query($db,"DELETE FROM users WHERE name='$key'");
  }
}
$qgetdata=pg_query($db,"SELECT name,h323gkid,ipaddr FROM users WHERE h323neighbor='t'");

%>

<CENTER>
<FORM METHOD=POST NAME=h323neigh onsubmit="ajaxsubmit(this.name);return false;">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2><%print _("Asterisk PBX H323 Neighbor Configuration");%></TH>
</TR>
<TR CLASS=list-color1>
<TD onmouseover="myHint.show('DA0')" onmouseout="myHint.hide()"><%print _("Select Neighbor To Delete");%></TD>
<TD><SELECT NAME=key>
<OPTION VALUE=""><%print _("Add New Neighbor Below");%></OPTION>
<%
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[0] . " (" . $getdata[1] . " [" . $getdata[2] . "])</OPTION>"; 
}
%>
</SELECT>
</TR>
<TR CLASS=list-color2>
<TD onmouseover="myHint.show('DA1')" onmouseout="myHint.hide()"><%print _("H323 Gateway ID");%></TD>
<TD><INPUT TYPE=TEXT NAME=h323gwid></TD>
</TR>
<TR CLASS=list-color1>
<TD onmouseover="myHint.show('DA2')" onmouseout="myHint.hide()"><%print _("H323 Gatekeeper ID");%></TD>
<TD><INPUT TYPE=TEXT NAME=h323gkid></TD>
</TR>
<TR CLASS=list-color2>
<TD onmouseover="myHint.show('DA2')" onmouseout="myHint.hide()"><%print _("H323 Gatekeeper IP Addr");%></TD>
<TD><INPUT TYPE=TEXT NAME=h323permip></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT NAME=pbxupdate VALUE="<%print _("Save Changes");%>">
  </TD>
</TR>
</TABLE>
</FORM>
