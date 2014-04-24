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

$getdata="SELECT distinct ivr,officehours from ivrconf";
$qgetdata=pg_query($db,$getdata);

%>

<CENTER>
<FORM METHOD=POST NAME=ivrform onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2><%print _("Asterisk PBX IVR Configuration");%></TH>
</TR>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cdr/ivrconf.php">

<TR CLASS=list-color1>
<TD onmouseover="myHint.show('DA0')" onmouseout="myHint.hide()"><%print _("Select DDI Route To Delete");%></TD>
<TD><SELECT NAME=ivr>
<OPTION VALUE=""><%print _("Add New IVR DDI");%></OPTION>
<%
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[0] . "->" . $getdata[1] . "(" . $getdata[2] . ")</OPTION>"; 
}
%>
</SELECT>
</TR>
<TR CLASS=list-color2>
<TD onmouseover="myHint.show('DA1')" onmouseout="myHint.hide()">New IVR DDI</TD>
<TD><INPUT TYPE=TEXT NAME=newivr></TD>
</TR>
<TR CLASS=list-color1>
  <TD>Service Time</TD><TD><SELECT NAME=hours>
     <OPTION VALUE="">Not Specified</OPTION>
     <OPTION VALUE=1 SELECTED>Office Hours</OPTION>
     <OPTION VALUE=2>After Hours</OPTION>
     <OPTION VALUE=3>Public Holiday</OPTION>
     </SELECT>
  </TD>  
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=MIDDLE COLSPAN=2>
     <INPUT TYPE=SUBMIT VALUE="<%print _("Add/Edit");%>">
  </TD>
</TR>
</TABLE>
</FORM>
