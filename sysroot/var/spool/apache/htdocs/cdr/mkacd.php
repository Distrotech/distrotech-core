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
<FORM METHOD=POST NAME=acdform onsubmit="ajaxsubmit(this.name);return false;">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<INPUT TYPE=HIDDEN NAME=defsub>
<SCRIPT>
document.onkeyup = KeyPressHandler;
document.onkeydown = KeyPressHandler;
function KeyPressHandler(event) {
  if (event.keyCode == 13) {
    event.returnValue=false;
    event.cancel = true;
    document.acdform.defsub.name="pbxupdate";
    ajaxsubmit('acdform');
  }
}
</SCRIPT>
<%
if ((isset($pbxupdate)) && ($queue == "") && ($qno != "") && (strlen($qno) == 2)) {
  $deftimeout=pg_query($db,"SELECT value FROM astdb WHERE family='Setup' AND key='QATimeout'");
  if (pg_num_rows($deftimeout) > 0) {
    list($timeout) = pg_fetch_array($deftimeout,$i,PGSQL_NUM);
  } else {
    $timeout=30;
  }
  include "autoadd.inc";
  $newlpass=randpwgen(8);
  $queue=$qpre . $qno;
  $exadd=pg_query($db,"INSERT INTO queue_table (name,timeout) VALUES ('" . $queue . "','" . $timeout . "')");
  $exadd=pg_query($db,"INSERT INTO voicemail (context,mailbox,password,fullname,email) VALUES ('6','" . $queue . "','" . $queue . "','','')");
  include "qadmin.php";
} else if (isset($pbxupdate)) {
  if ($queue != "") {
    $exedit=pg_query($db,"SELECT mailbox FROM voicemail WHERE mailbox='" . $queue . "'");
    if (pg_num_rows($exedit) > 0) {
      include "qadmin.php";
    }
  }
} else if (!isset($pbxupdate)){
  if ((isset($delext)) && ($queue != "")) {
    pg_query($db,"DELETE FROM queue_table WHERE name='" . $queue . "'");
    pg_query($db,"DELETE FROM astdb WHERE family='" . $queue . "' OR family='Q" . $queue . "'");
    pg_query($db,"DELETE FROM voicemail WHERE mailbox='" . $queue . "'");
  }
%>
  <TH CLASS=heading-body COLSPAN=2><%print _("Select Queue");%></TH>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('QS0')" onmouseout="myHint.hide()"><%print _("Queue To Configure");%></TH>
  <TD WIDTH=50% ALIGN=LEFT>
  <SELECT NAME=queue onchange="document.acdform.subme.name='pbxupdate';ajaxsubmit('acdform')">
<%
  $actqueuesq="SELECT name,description FROM queue_table";
  if ($SUPER_USER != 1) {
    $actqueuesq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family='Q'||name AND bgrp.key='BGRP') WHERE " . $clogacl;
  } else {
    print "<OPTION VALUE=\"\">" . _("Add New Queue Bellow");
  }
  $actqueuesq.=" ORDER BY name";
  $actqueues=pg_query($db,$actqueuesq);

  $num=pg_num_rows($actqueues);
  for($i=0;$i < $num;$i++) {
    $r = pg_fetch_array($actqueues,$i,PGSQL_NUM);
    print "    <OPTION VALUE=\"" .  $r[0] . "\">" . $r[1] . " (" . $r[0] .")</OPTION>\n";
  }
%>
  </SELECT>
  </TD></TR>
<%
  if ($SUPER_USER == 1) {%>
  <TR CLASS=list-color2>
    <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('QS1')" onmouseout="myHint.hide()">
<%
    $qpre=pg_query($db,"SELECT key FROM astdb WHERE family='ACDPrefix' AND value='1'");  
    if (pg_num_rows($qpre) == 0) {
      print "<INPUT TYPE=HIDDEN NAME=qpre VALUE=\"5\">";
      print _("New Queue 5(00-99)");
      print "</TH><TD WIDTH=50% ALIGN=LEFT>";
    } else {
      print _("New Queue");
      print "</TH><TD WIDTH=50% ALIGN=LEFT>";
      print "<SELECT NAME=qpre>\n<OPTION VALUE=5>5</OPTION>\n";
      for ($icnt=0;$icnt < pg_num_rows($qpre);$icnt++) {
        $r=pg_fetch_array($qpre,$icnt,PGSQL_NUM);
        print "<OPTION VALUE=" . $r[0] . ">" . $r[0] . "</OPTION>\n";
      }
      print "</SELECT>";
    }
%>
      <INPUT TYPE=TEXT NAME=qno MAXLENGTH=2 SIZE=2>
<TR CLASS=list-color1>
<%
  } else {
    print "<TR CLASS=list-color2>";
  }
%>
  <TH COLSPAN=2>
<%
  if ($SUPER_USER == 1) {
%>
    <INPUT TYPE=SUBMIT name=subme onclick=this.name='pbxupdate' VALUE="<%print _("Add/Edit");%>">
<%
  } else {
%>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxupdate' VALUE="<%print _("Edit");%>">
<%
  }
%>

  </TABLE>
  </FORM>
<%
}
%>
