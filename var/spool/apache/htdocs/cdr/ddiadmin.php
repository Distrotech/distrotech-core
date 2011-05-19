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

/*
AREAGATEWAY
*/

if (isset($pbxupdate)) {
  if ($newkey != "") {
    if ($newbgroup != "") {
      $bgroup=$newbgroup;
    }
    $eddi=pg_query($db,"SELECT id FROM astdb WHERE family='BGRP' AND family='" . $newkey . "'");
    if (pg_num_rows($eddi)) {
      $ddiid=pg_fetch_row($eddi,0);
      pg_query($db,"UPDATE astdb SET value='" . $bgroup . "' WHERE family='BGRP' AND family='" . $newkey . "'");
    } else {
      pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('$newkey','BGRP','$bgroup')");
    }

    $eddi=pg_query($db,"SELECT id FROM astdb WHERE family='DDI' AND key='" . $newkey . "'");
    if (pg_num_rows($eddi)) {
      $ddiid=pg_fetch_row($eddi,0);
      pg_query($db,"UPDATE astdb SET value='" . $newval . "' WHERE family='DDI' AND key='" . $newkey . "'");
    } else {
      pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('DDI','$newkey','$newval')");
    }
  } else if ($key != "") {
    pg_query($db,"DELETE FROM astdb WHERE family='DDI' AND key='$key'");
    pg_query($db,"DELETE FROM astdb WHERE key='BGRP' AND family='$key'");
  }
}

$qgetdata=pg_query($db,"SELECT astdb.key,astdb.value,grp.value FROM astdb LEFT OUTER JOIN astdb AS grp ON (astdb.key=grp.family AND grp.key='BGRP') WHERE astdb.family='DDI'");


%>

<CENTER>
<FORM METHOD=POST NAME=ddiform onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2><%print _("Asterisk PBX DDI Configuration");%></TH>
</TR>
<TR CLASS=list-color1>
<TD onmouseover="myHint.show('DA0')" onmouseout="myHint.hide()"><%print _("Select DDI Route To Delete");%></TD>
<TD><SELECT NAME=key>
<OPTION VALUE=""><%print _("Add New DDI Route Below");%></OPTION>
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
<TD onmouseover="myHint.show('DA1')" onmouseout="myHint.hide()">New DDI</TD>
<TD><INPUT TYPE=TEXT NAME=newkey></TD>
</TR>
<TR CLASS=list-color1>
<TD onmouseover="myHint.show('DA2')" onmouseout="myHint.hide()"><%print _("Destination To Route To");%></TD>
<TD><INPUT TYPE=TEXT NAME=newval></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES29') ONMOUSEOUT=myHint.hide()><%print _("Billing Group");%></TD>
  <TD>
    <SELECT NAME=bgroup>
      <OPTION VALUE=""><%print _("Select Existing Group/Add New Group Bellow");%></OPTION>
<%
      $bgroups=pg_query("SELECT DISTINCT value FROM astdb WHERE key='BGRP' AND value != '' ORDER BY value;");
      $bgnum=pg_num_rows($bgroups);

      for($i=0;$i<$bgnum;$i++){
        $getbgdata=pg_fetch_array($bgroups,$i);
        print "<OPTION VALUE=" . $getbgdata[0] . ">" . $getbgdata[0] . "</OPTION>\n";
      }
%>
    </SELECT><BR>
    <INPUT TYPE=TEXT NAME=newbgroup>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='pbxupdate' VALUE="<%print _("Save Changes");%>">
  </TD>
</TR>
</TABLE>
</FORM>
