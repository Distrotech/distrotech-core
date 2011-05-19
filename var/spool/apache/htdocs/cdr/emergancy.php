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
  if ($newkey != "") {
    $eddi=pg_query($db,"SELECT id FROM astdb WHERE family='EMERG' AND key='" . $newkey . "'");
    if (pg_num_rows($eddi)) {
      $ddiid=pg_fetch_row($eddi,0);
      pg_query($db,"UPDATE astdb SET value='1' WHERE family='EMERG' AND key='" . $newkey . "'");
    } else {
      pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('EMERG','$newkey','1')");
    }
  } else if ($key != "") {
    pg_query($db,"DELETE FROM astdb WHERE family='EMERG' AND key='$key'");
  }
}

$qgetdata=pg_query($db,"SELECT astdb.key FROM astdb  WHERE astdb.family='EMERG'");


%>

<CENTER>
<FORM METHOD=POST NAME=e911form onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2><%print _("Asterisk PBX Emergancy Numbers");%></TH>
</TR>
<TR CLASS=list-color1>
<TD onmouseover="myHint.show('DA0')" onmouseout="myHint.hide()"><%print _("Select Emergancy Number To Delete");%></TD>
<TD><SELECT NAME=key>
<OPTION VALUE=""><%print _("Add New Emergancy Number Below");%></OPTION>
<%
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[0];
  print "</OPTION>"; 
}
%>
</SELECT>
</TR>
<TR CLASS=list-color2>
<TD onmouseover="myHint.show('DA1')" onmouseout="myHint.hide()"><%print _("Emergancy Number");%></TD>
<TD><INPUT TYPE=TEXT NAME=newkey></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='pbxupdate' VALUE="<%print _("Save Changes");%>">
  </TD>
</TR>
</TABLE>
</FORM>
