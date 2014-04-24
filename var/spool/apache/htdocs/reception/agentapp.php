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


  if (isset($agentup)) {
    if ($action == "login")  {
      $aact="penalty=defpenalty,paused=0";
    } else if ($action == "logout")  {
      $aact="penalty=-1,paused=0";
    } else if ($action == "pause")  {
      $aact="penalty=defpenalty,paused=1";
    } else if ($action == "unpause")  {
      $aact="penalty=defpenalty,paused=0";
    }
    if ($agentname != "") {
      $aact.=",membername='" . $agentname . "'";
    }
    if (($action == "pause") || ($action == "logout")) {
      $queueq="INSERT INTO queue_log (time,queuename,agent,event,data,callid) SELECT 
                 cast(date_part('epoch',now()) as int),queue_name,membername,'AGENTCALLBACKLOGOFF',interface||'|','" . $_SERVER['REMOTE_ADDR'] . "'
                 from queue_members where interface ~ '/" . $PHP_AUTH_USER . "($|@)'";
    } else {
      $queueq="INSERT INTO queue_log (time,queuename,agent,event,data,callid) SELECT 
                 cast(date_part('epoch',now()) as int),queue_name,membername,'AGENTCALLBACKLOGIN',membername||'@intext','" . $_SERVER['REMOTE_ADDR'] . "'
                 from queue_members where interface ~ '/" . $PHP_AUTH_USER . "($|@)'";
    }
    pg_query($db,$queueq);
    $aqery=pg_query($db,"UPDATE queue_members SET " . $aact . " WHERE interface ~ '/" . $PHP_AUTH_USER . "($|@)'");
  }
  $ainfqr=pg_query($db,"SELECT membername,penalty,paused FROM queue_members WHERE interface ~ '/" . $PHP_AUTH_USER . "($|@)' LIMIT 1");
  list($agentname,$penalty,$paused)=pg_fetch_array($ainfqr,0);
  if ($paused == "1") {
    $status="-1";
  } else if ($penalty >= 0) {
    $status=1;
  } else if ($penalty < 0) {
    $status=0;
  } else {
    $status="2";
  }
/*
    <script language="JavaScript" src="/hints.js" type="text/javascript"></script>
    <script language="JavaScript" src="/hints_cfg.php?disppage=reception%2Fc2c.php" type="text/javascript"></script>
*/
%>
    <link rel="stylesheet" type="text/css" href="/style.php?style=<%print $style;%>">
    <CENTER>
    <FORM METHOD=POST>
    <TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
      <TR CLASS=list-color2>
        <TH COLSPAN=2 ALIGN=CENTER CLASS=heading-body><%print _("Agent Application Pannel");%></TH>
      </TR><TR  CLASS=list-color1>
        <TD ALIGN=LEFT onmouseover="myHint.show('QC0')" onmouseout="myHint.hide()" WIDTH=50%><%print _("Action");%></TD>
        <TD>
          <SELECT NAME=action>
            <OPTION VALUE=login <%if ($status == 0) {print " SELECTED";}%>>Log In</OPTION>
            <OPTION VALUE=logout>Log Out</OPTION>
            <OPTION VALUE=pause <%if ($status > 0) {print " SELECTED";}%>>Pause</OPTION>
            <OPTION VALUE=unpause <%if ($status == -1) {print " SELECTED";}%>>Resume</OPTION>
          </SELECT>
        </TD>
      </TR><TR  CLASS=list-color2>
        <TD ALIGN=LEFT onmouseover="myHint.show('QC1')" onmouseout="myHint.hide()"><%print _("Reason ...");%></TD>
        <TD><INPUT TYPE=TEXT NAME=reason></TD>
      </TR><TR  CLASS=list-color1>
        <TD ALIGN=LEFT onmouseover="myHint.show('QC1')" onmouseout="myHint.hide()"><%print _("Agent Name");%></TD>
        <TD><INPUT TYPE=TEXT NAME=agentname VALUE="<%print $agentname;%>"></TD>
      </TR><TR CLASS=list-color2>
        <TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT NAME=agentup></TD>
      </TR>
    </TABLE>
    </FORM>

