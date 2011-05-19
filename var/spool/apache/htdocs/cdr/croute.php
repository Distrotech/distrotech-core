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
%>

<CENTER>
<FORM METHOD=POST NAME=crouteform onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <TR CLASS=list-color2>
    <TH COLSPAN=2 CLASS=heading-body><%print _("Asterisk PBX Inter Branch Configuration");%></TH>
  </TR>
  <TR CLASS=list-color1>

<%
if ((isset($modcompany)) || (isset($delbranch))){
  if ($newkey != "") {
    pg_query($db,"INSERT INTO astdb (family,value,key) VALUES ('Company','$newkey','$fbranch')");
    $key=$newkey;
  } elseif (isset($delbranch)) {
    pg_query($db,"DELETE FROM astdb WHERE (value='$key' AND family='$company') OR (family='Company' AND key='$key' AND value='$company')");
    $key=$company;
  }
  $qgetdata=pg_query($db,"SELECT key,fullname FROM astdb LEFT OUTER JOIN users ON (key=name) WHERE family='Company' AND value='$key' ORDER BY fullname");
%>
  <TD><%print _("Select Branch");%></TD>
  <TD><SELECT NAME=key>
  <%
  $dnum=pg_num_rows($qgetdata);
  for($i=0;$i<$dnum;$i++){
    $getdata=pg_fetch_array($qgetdata,$i);
    print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[1] . "(" . $getdata[0] . ")</OPTION>"; 
  }
  %>
  </SELECT> 
  <INPUT TYPE=HIDDEN NAME=company VALUE="<%print $key;%>">
  </TR>
  <TR CLASS=list-color2>
  <TD><%print _("New Branch");%></TD>
  <TD><INPUT TYPE=TEXT NAME=newkey></TD>
  </TR>
  <TR CLASS=list-color1>
  <TD><%print _("First Prefix For Branch");%></TD>
  <TD><INPUT TYPE=TEXT NAME=fbranch></TD>
  </TR>
  <TR CLASS=list-color2>
  <TD><%print _("Alias For Above Branch");%></TD>
  <TD><INPUT TYPE=TEXT NAME=alias></TD>
  </TR>
  <TR CLASS=list-color1>
  <TD><%print _("Send DTMF On Answer For This Branch");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=dtmf></TD>
  </TR>
  <TR CLASS=list-color2>
    <TD ALIGN=MIDDLE COLSPAN=2>
      <INPUT TYPE=RESET>
      <INPUT TYPE=SUBMIT onclick=this.name='modbranch' VALUE="<%print _("Modify");%>">
      <INPUT TYPE=SUBMIT onclick=this.name=delbranch' VALUE="<%print _("Delete");%>">
    </TD>
<%
} elseif ((isset($modbranch)) || (isset($delpre))) {
  if ($newkey != "") {
    pg_query($db,"INSERT INTO astdb (family,value,key) VALUES ('Company','$company','$newkey')");
    pg_query($db,"INSERT INTO astdb (family,value,key) VALUES ('$company','$newkey','$fbranch')");

    if ($dtmf == "on") {
      $dtmf=1;
    } else {
      $dtmf=0;
    }
    pg_query($db,"INSERT INTO astdb (family,value,key) VALUES ('$company','$alias','ALIAS$fbranch')");
    pg_query($db,"INSERT INTO astdb (family,value,key) VALUES ('$company','$dtmf','DTMF$fbranch')");
    $key=$newkey;
  } else { 
    if (($key != "") && (isset($delpre))) {
      pg_query($db,"DELETE FROM astdb WHERE family='$company' AND key='$key'");
      pg_query($db,"DELETE FROM astdb WHERE family='$company' AND key='ALIAS$key'");
      pg_query($db,"DELETE FROM astdb WHERE family='$company' AND key='DTMF$key'");
      $key=$branch;
    }
  }
  $qgetdata=pg_query($db,"SELECT key FROM astdb WHERE family='$company' AND value='$key' ORDER BY key");
%>
  <TD><%print _("Select Prefix To Modify");%></TD>
  <TD><SELECT NAME=key>
  <OPTION VALUE=""><%print _("Add New Prefix");%></OPTION>
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
  <TD><%print _("New Branch");%></TD>
  <TD><INPUT TYPE=TEXT NAME=newkey></TD>
  </TR>
  <TR CLASS=list-color1>
  <TD><%print _("Alias For Above Branch");%></TD>
  <TD><INPUT TYPE=TEXT NAME=alias></TD>
  </TR>
  <TR CLASS=list-color2>
  <TD><%print _("Send DTMF On Answer For This Branch");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=dtmf></TD>
  </TR>
  <INPUT TYPE=HIDDEN NAME=company VALUE="<%print $company;%>">
  <INPUT TYPE=HIDDEN NAME=branch VALUE="<%print $key;%>">
  <TR CLASS=list-color1>
    <TD ALIGN=MIDDLE COLSPAN=2>
      <INPUT TYPE=RESET>
      <INPUT TYPE=SUBMIT onclick=this.name='modpre' VALUE="<%print _("Modify/Add");%>">
      <INPUT TYPE=SUBMIT onclick=this.name='delpre' VALUE="<%print _("Delete");%>">
    </TD>
<%
} elseif ((isset($modpre)) || (isset($modup)))  {
  if ($newkey != "") {
    pg_query($db,"INSERT INTO astdb (family,value,key) VALUES ('$company','$branch','$newkey')");
    if ($dtmf == "on") {
      $dtmf=1;
    } else {
      $dtmf=0;
    }
    pg_query($db,"INSERT INTO astdb (family,value,key) VALUES ('$company','$alias','ALIAS$newkey')");
    pg_query($db,"INSERT INTO astdb (family,value,key) VALUES ('$company','$dtmf','DTMF$newkey')");
    $bpre=$newkey;
  } else if (isset($modup)) {
    if ($dtmf == "on") {
      $dtmf=1;
    } else {
      $dtmf=0;
    }
    pg_query($db,"UPDATE astdb SET value='$alias' WHERE family='$company' AND key='ALIAS$bpre'");
    pg_query($db,"UPDATE astdb SET value='$dtmf' WHERE family='$company' AND key='DTMF$bpre'");
    if ($dtmf == "1") {
      $dtmf=" CHECKED";
    } else {
      $dtmf="";
    }
  } else {
    $getbra=pg_query($db,"SELECT value FROM astdb WHERE family='$company' AND key LIKE 'ALIAS$key'");
    $getbrd=pg_query($db,"SELECT value FROM astdb WHERE family='$company' AND key LIKE 'DTMF$key'");
    $bra=pg_fetch_array($getbra,0);
    $brd=pg_fetch_array($getbrd,0);
    if ($brd == "1") {
      $dtmf=" CHECKED";
    } else {
      $dtmf="";
    }
    $bpre=$key;
  }
%>
  <TR CLASS=list-color1>
  <TD><%print _("Alias");%></TD>
  <TD><INPUT TYPE=TEXT NAME=alias VALUE="<%print $alias;%>"></TD>
  </TR>
  <TR CLASS=list-color2>
  <TD><%print _("Send DTMF On Answer");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=dtmf <%print $dtmf;%>></TD>
  </TR>
  <INPUT TYPE=HIDDEN NAME=company VALUE="<%print $company;%>">
  <INPUT TYPE=HIDDEN NAME=bpre VALUE="<%print $bpre;%>">
  <TR CLASS=list-color1>
    <TD ALIGN=MIDDLE COLSPAN=2>
      <INPUT TYPE=RESET>
      <INPUT TYPE=SUBMIT onclick=this.name='modup' VALUE="<%print _("Modify");%>">
    </TD>
<%
} else {
  if ((isset($delcompany)) && ($key != "")) {
    pg_query($db,"DELETE FROM astdb WHERE (family='$key') OR (family='Company' AND value='$key')");
  }
  $qgetdata=pg_query($db,"SELECT DISTINCT value FROM astdb WHERE family='Company' ORDER BY value");
%>
  <TD><%print _("Select Company To Edit");%></TD>
  <TD><SELECT NAME=key>
  <OPTION VALUE=""><%print _("Add New Company");%></OPTION>
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
  <TD><%print _("New Company Number");%></TD>
  <TD><INPUT TYPE=TEXT NAME=newkey></TD>
  </TR>
  <TR CLASS=list-color1>
  <TD><%print _("First Branch Number");%></TD>
  <TD><INPUT TYPE=TEXT NAME=fbranch></TD>
  </TR>
  <TR CLASS=list-color2>
    <TD ALIGN=MIDDLE COLSPAN=2>
      <INPUT TYPE=RESET>
      <INPUT TYPE=SUBMIT onclick=this.name='modcompany' VALUE="<%print _("Modify");%>">
      <INPUT TYPE=SUBMIT onclick=this.name='delcompany' VALUE="<%print _("Delete");%>">
    </TD>
<%
}
%>
</TR>
</TABLE>
</FORM>
