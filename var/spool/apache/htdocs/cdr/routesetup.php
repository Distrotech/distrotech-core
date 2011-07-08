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
  if (($newkey != "") && ($dhost != "")) {
    pg_query($db,"INSERT INTO interbranch (prefix,dprefix,proto,address) VALUES ('" . $newkey . "','" . $prefix . "',
                              '" . $lineproto . "','" . $dhost . "')");
  } else if ($key != "") {
    pg_query($db,"DELETE FROM interbranch WHERE prefix='" . $key . "'");
  }
}

$qgetdata=pg_query($db,"SELECT prefix,prefix||' -> '||dprefix||' -> '||address||' ('||proto||')' FROM interbranch ORDER BY prefix");

%>

<CENTER>
<FORM NAME=ibroute METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2><%print _("Inter Branch Routing Configuration");%></TH>
</TR>
<TR CLASS=list-color1>
<TD WIDTH=50% onmouseover="myHint.show('IB0')" onmouseout="myHint.hide()"><%print _("Select Route To Delete");%></TD>
<TD><SELECT NAME=key>
<OPTION VALUE=""><%print _("Add New Line Below");%></OPTION>
<%
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  $iszap[$getdata[0]]=1;
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[1] . "</OPTION>"; 
}
%>
</SELECT>
</TR>
<TR CLASS=list-color2>
<TD onmouseover="myHint.show('IB1')" onmouseout="myHint.hide()"><%print _("Dialed Prefix");%></TD>
<TD>
<INPUT TYPE=TEXT NAME=newkey>
</TD>
</TR>
<TR CLASS=list-color1>
<TD onmouseover="myHint.show('IB2')" onmouseout="myHint.hide()"><%print _("Hostname To Call (IAX/SIP)/Trunk Prefix (H323)");%></TD>
<TD>
<INPUT TYPE=TEXT NAME=dhost></TD>
</TR>
<TR CLASS=list-color2>
<TD onmouseover="myHint.show('IB3')" onmouseout="myHint.hide()"><%print _("Prefix To Call");%></TD>
<TD>
<INPUT TYPE=TEXT NAME=prefix></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('IB4')" onmouseout="myHint.hide()"><%print _("Protocol To Use");%></TD>
  <TD><SELECT NAME=lineproto>
    <OPTION VALUE=IAX2><%print _("Inter Asterisk eXchange");%>
    <OPTION VALUE=SIP><%print _("Session Initiation Protocol");%>
    <OPTION VALUE=OH323><%print _("H.323");%>
  </SELECT></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxupdate' VALUE="<%print _("Add/Delete");%>">
  </TD>
</TR>
</TABLE>
</FORM>
