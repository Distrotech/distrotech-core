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

if (! $db) {
  include "auth.inc";
}
%>
<FORM METHOD=POST>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<%
if ((isset($selext)) || (isset($pbxupdate))) {
  if ($h323permit == "0.0.0.0") {
    $h323permit="allow";
  } else if ($h323permit == "") {
    $h323permit="deny";
  }
  if (isset($pbxupdate)) {
    pg_query($db,"UPDATE users SET h323permit='$h323permit',h323gkid='$h323gkid',h323prefix='$h323prefix'
                                   WHERE name='$exten'");
  }
  $qgetudata=pg_query($db,"SELECT h323permit,h323gkid,h323prefix FROM users WHERE name='" . $exten . "'");
  $udata=pg_fetch_array($qgetudata,0);

  if (($udata[0] == "allow") || ($udata[0] == "")){
    $h323permit="0.0.0.0";
  } else if ($udata[0] == "deny") {
    $h323permit="";
  } else {
    $h323permit=$udata[0];
  }

  if ($udata[1] != "") {
    $h323gkid=$udata[1];
  } else {
    $h323gkid=$exten;
  }
  if ($udata[2] != "") {
    $h323prefix=$udata[2];
  } else {
    $h323prefix="";
  }
%>
<INPUT TYPE=HIDDEN NAME=exten VALUE=<%print $exten;%>>
  <TH COLSPAN=2>H.323 Configuration For Extension <%print $exten%></TH>
</TR>
<TR CLASS=list-color1>
  <TD>Gatekeeper ID</TD>
  <TD><INPUT TYPE=TEXT NAME=h323gkid VALUE="<%print $h323gkid;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD>Recived Prefix</TD>
  <TD><INPUT TYPE=TEXT NAME=h323prefix VALUE="<%print $h323prefix;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD>Gatekeeper IP<BR>0.0.0.0 For Any IP Or Blank To Deny Access</TD>
  <TD><INPUT TYPE=TEXT NAME=h323permit VALUE="<%print $h323permit;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT NAME=pbxupdate VALUE="Activate/Save">
  </TD>
</TR>
</TABLE>
</FORM>
<%
} else if (!isset($selext)){
  if ((isset($delext)) && ($exten != "")) {
    pg_query($db,"DELETE FROM users WHERE name='" . $exten . "'");
  }
%>
  <TH COLSPAN=2>Select Extension</TH>
</TR>
<TR CLASS=list-color1>
  <TH ALIGN=LEFT WIDTH=50%>Account To Configure</TH>
  <TD WIDTH=50% ALIGN=LEFT><INPUT NAME=exten>
  </TD></TR>
  <TR CLASS=list-color2>
  <TH COLSPAN=2>
    <INPUT TYPE=SUBMIT NAME=selext VALUE="Edit H.323 Settings">
  </TABLE>
  </FORM>
<%
  exit;
}
