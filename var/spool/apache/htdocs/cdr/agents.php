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

$yesno[0]=_("No");
$yesno[1]=_("Yes");


if ($_POST['mmap'] != "") {
  $queue=$_POST['mmap'];
} else if ((isset($queue)) && ($defqueue != "799")){
  $defqueue=$queue;
}

if (($queue == "799") || ($defqueue != $queue)) {
  $defqueue="799";
  $defqueueq=pg_query($db,"SELECT value FROM astdb WHERE family='Setup' AND key='AttendantQ' AND value != '-1'");
  if (pg_num_rows($defqueueq) > 0) {
    list($queue)=pg_fetch_array($defqueueq,0,PGSQL_NUM);
  }
} else {
  $defqueue=$queue;
}

if (isset($queue)) {
  $defpenalty=pg_query($db,"SELECT value FROM astdb WHERE family='Setup' AND key='QAPenalty'");
  if (pg_num_rows($defpenalty) > 0) {
    list($dqpenalty) = pg_fetch_array($defpenalty,0,PGSQL_NUM);
  } else {
    $dqpenalty=20;
  }
}

if (($queue == "799") || ($defqueue != $queue)) {
  if (isset($update)) {
    if ($AANOPROMPT == "on") {
      $AANOPROMPT="1";
    } else {
      $AANOPROMPT="0";
    }

    if ($AAMOH == "on") {
      $AAMOH="1";
    } else {
      $AAMOH="0";
    }

    if ($AAREC == "on") {
      $AAREC="1";
    } else {
      $AAREC="0";
    }

    pg_query($db,"UPDATE astdb SET value='" . $AATimeout . "' WHERE family='Setup' AND key='AATimeout'");
    pg_query($db,"UPDATE astdb SET value='" . $AANext . "' WHERE family='Setup' AND key='AANext'");
    pg_query($db,"UPDATE astdb SET value='" . $QAPENALTY . "' WHERE family='Q799' AND key='QAPENALTY'");
    pg_query($db,"UPDATE astdb SET value='" . $AANOPROMPT . "' WHERE family='Setup' AND key='AANOPROMPT'");
    pg_query($db,"UPDATE astdb SET value='" . $AAMOH . "' WHERE family='Setup' AND key='AAMOH'");
    pg_query($db,"UPDATE astdb SET value='" . $AAREC . "' WHERE family='Setup' AND key='AAREC'");
    pg_query($db,"UPDATE astdb SET value='" . $AADelay . "' WHERE family='Setup' AND key='AADelay'");


    $cdelvm=pg_query($db,"SELECT count(a.attname) FROM pg_catalog.pg_stat_user_tables AS t, pg_catalog.pg_attribute a WHERE t.relid = a.attrelid  AND t.relname='users' AND a.attname='deletevoicemail'");
    list($delvm)=pg_fetch_array($cdelvm,0);
    if ($delvm < 1) {
      pg_query($db,"ALTER TABLE users ADD deletevoicemail varchar(8) default 'no'");
    }
    pg_query($db,"UPDATE users SET mailbox=name,deletevoicemail='yes',email='" . $AAEMAIL . "' WHERE name='"  . $syshname . "'");
  }

  $aavm=pg_query($db,"SELECT email FROM users WHERE name='" . $syshname . "'");
  if (pg_num_rows($aavm) > 0) { 
    list($AAEMAIL)=pg_fetch_array($aavm,0);
  }

  $qgetdata=pg_query($db,"SELECT key,value FROM astdb WHERE (family='Setup' AND key LIKE 'AA%') OR (family = 'Q799' AND key='QAPENALTY')");
  $dnum=pg_num_rows($qgetdata);
  for($i=0;$i<$dnum;$i++){
    $getdata=pg_fetch_array($qgetdata,$i);
    $origdata[$getdata[0]]=$getdata[1];
  }
  if ($origdata["AATimeout"] == "") {
    $origdata["AATimeout"]="120";
    pg_query("INSERT INTO astdb (family,key,value) VALUES ('Setup','AATimeout','120')");
  }
  if ($origdata["AANext"] == "") {
    $origdata["AANext"]="";
    pg_query("INSERT INTO astdb (family,key,value) VALUES ('Setup','AANext','')");
  }
  if ($origdata["AANOPROMPT"] == "") {
    $origdata["AANOPROMPT"]="0";
    pg_query("INSERT INTO astdb (family,key,value) VALUES ('Setup','AANOPROMPT','0')");
  }
  if ($origdata["AAMOH"] == "") {
    $origdata["AAMOH"]="0";
    pg_query("INSERT INTO astdb (family,key,value) VALUES ('Setup','AAMOH','0')");
  }
  if ($origdata["AAREC"] == "") {
    $origdata["AAREC"]="0";
    pg_query("INSERT INTO astdb (family,key,value) VALUES ('Setup','AAREC','0')");
  }
  if ($origdata["AADelay"] == "") {
    $origdata["AADelay"]="2";
    pg_query("INSERT INTO astdb (family,key,value) VALUES ('Setup','AADelay','2')");
  }
  if ($origdata["QAPENALTY"] == "") {
    $origdata["QAPENALTY"]=$dqpenalty;
    pg_query("INSERT INTO astdb (family,key,value) VALUES ('Q799','QAPENALTY','". $dqpenalty ."')");
  }
}
%>
<FORM NAME=queuemod METHOD=POST onsubmit="ajaxsubmit(this.name);return false;">
<INPUT TYPE=HIDDEN NAME=delagent VALUE="">
<INPUT TYPE=HIDDEN NAME=agentlogon VALUE="">
<INPUT TYPE=HIDDEN NAME=agentpause VALUE="">
<INPUT TYPE=HIDDEN NAME=agentlogoff VALUE="">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE="">
<INPUT TYPE=HIDDEN NAME=agentwei VALUE="">
<INPUT TYPE=HIDDEN NAME=agentignore VALUE="">
<INPUT TYPE=HIDDEN NAME=agentchan VALUE="">
<INPUT TYPE=HIDDEN NAME=defqueue VALUE="<%print $defqueue;%>">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<%

if (isset($queue)) {
  $defpenalty=pg_query($db,"SELECT value FROM astdb WHERE family='Q" . $queue . "' AND key='QAPENALTY'");
  if (pg_num_rows($defpenalty) > 0) {
    list($dqpenalty) = pg_fetch_array($defpenalty,0,PGSQL_NUM);
  }  
  if (isset($agentignore)) {
    pg_query($db,"UPDATE queue_members SET ignorebusy=NOT ignorebusy WHERE queue_name='" .  $queue . "' AND interface='" . $agentignore . "'");
  }
  if (isset($delagent)) {
    pg_query($db,"DELETE FROM queue_members WHERE queue_name='" .  $queue . "' AND interface='" . $delagent . "'");
  }
  if (isset($agentwei)) {
    $getweight=pg_query($db,"SELECT penalty FROM queue_members WHERE queue_name='" .  $queue . "' AND interface='" . $agentchan . "'");
    $aweight=pg_fetch_array($getweight,0,PGSQL_NUM);
    if ($aweight[0] > 0) {
      pg_query($db,"UPDATE queue_members  set penalty='" . $agentwei . "',defpenalty='" . $agentwei . "' where queue_name='" .  $queue . "' AND interface='" . $agentchan . "'");
    } else {
      pg_query($db,"UPDATE queue_members  set defpenalty='" . $agentwei . "' where queue_name='" .  $queue . "' AND interface='" . $agentchan . "'");
    }
  }
  if (isset($agentlogon)) {
    pg_query($db,"UPDATE queue_members set penalty=defpenalty,paused='0' where queue_name='" .  $queue . "' AND interface='" . $agentlogon . "'");
    $getweight=pg_query($db,"SELECT penalty FROM queue_members where queue_name='" .  $queue . "' AND interface='" . $agentlogon . "'");
    $aweight=pg_fetch_array($getweight,0,PGSQL_NUM);
  }
  if (isset($agentpause)) {
    pg_query($db,"UPDATE queue_members set penalty=defpenalty,paused=0 where queue_name='" .  $queue . "' AND interface='" . $agentpause . "'");
    $getweight=pg_query($db,"SELECT penalty FROM queue_members where queue_name='" .  $queue . "' AND interface='" . $agentpause . "'");
    $aweight=pg_fetch_array($getweight,0,PGSQL_NUM);
  }
  if (isset($agentlogoff)) {
    pg_query($db,"UPDATE queue_members set penalty = '-1',paused='0' where queue_name='" .  $queue . "' AND interface='" . $agentlogoff . "'");
  }
  if ((isset($addagent)) && ($channel != "") && ($weight != "")) {
    $oweight=$weight;
    if ((!strpos($channel,"/")) && ($olagent == "")) {
      $origchan=$channel;
      $linetype=pg_query($db,"SELECT CASE WHEN (zapline > 0) THEN 'DAHDI/'||zapline ELSE 
                                       CASE WHEN (iaxline = '1') THEN 'IAX2/'||name ELSE 'SIP/'||name
                                       END 
                                     END
                                FROM users LEFT OUTER JOIN features ON (exten = name)
                                WHERE name='" . $channel . "'");
      if (pg_num_rows($linetype) > 0) {
        $ltype=pg_fetch_row($linetype,0);
        $channel=$ltype[0];
      } else {
        $channel="Local/" . $channel . "@6/n";
      }
    } else if ($olagent != "") {
      $origchan=$channel;
      $linetype=pg_query($db,"SELECT name from users where name='" . $channel . "'");
      if (pg_num_rows($linetype) > 0) {
        $channel="Agent/" . $channel;
        $weight="-1";
      } else {
        $channel="Local/" . $channel . "@6/n";
      }
    } else {
      $origchan=$queue;
    }
    if ($nobusy == "") {
      $nobusy="'f'";
    } else {
      $nobusy="'t'";
    }
    pg_query($db,"INSERT INTO queue_members (queue_name,interface,penalty,defpenalty,ignorebusy) VALUES ('" . $queue . "','" . $channel . "','" . $weight . "','" . $oweight . "'," . $nobusy . ")");
    $memup=pg_send_query($db,"UPDATE queue_members SET membername='" . $origchan . "' WHERE queue_name='" . $queue . "' AND interface='" . $channel . "'");
    $pgres=pg_get_result($db);
    if (pg_result_error($pgres)) {
      pg_query($db,"ALTER TABLE queue_members ADD membername character varying(8)");
      pg_query($db,"UPDATE queue_members set membername = substr(interface,5);");
      pg_send_query($db,"UPDATE queue_members SET membername='" . $origchan . "' WHERE queue_name='" . $queue . "' AND interface='" . $channel . "'");
    }
  }

  print "<TR CLASS=list-color2><TH class=heading-body COLSPAN=6>" . _("Editing Agents For Queue") . " " . $queue . "</TH></TR>";
  print "<TR CLASS=list-color1>";
  print "<TH CLASS=heading-body2 WIDTH=100>" . _("Active")  . "</TH><TH CLASS=heading-body2>" . _("Agent") . "</TH><TH CLASS=heading-body2>" . _("Channel") . "</TH><TH CLASS=heading-body2>" . _("Perm.") . "<TH CLASS=heading-body2>" . _("Weight") . "</TH><TH CLASS=heading-body2>" . _("Ignore Busy Status") . "</TH>";
  print "</TR>";
  $getagentq="SELECT penalty > 0 as active,CASE WHEN (users.uniqueid is not null) THEN users.fullname ELSE 'Unknown User' END as agent,
                     interface as channel,CASE WHEN (penalty is not null) THEN defpenalty ELSE '" . $dqpenalty. "' END as weight,
                     CASE WHEN (name is not null) THEN name ELSE interface END, ignorebusy,paused > 0 
                   from queue_members left outer join features ON (zapline=substr(interface,5)) 
                     left outer join users on (users.name=substring(interface from position('/' in interface)+1) OR exten=name)
                   where queue_name='" . $queue . "' ORDER BY defpenalty,name";
  $getagents=pg_query($getagentq);
  $num=pg_num_rows($getagents);
  $bcolor[1]=" CLASS=list-color1";
  $bcolor[0]=" CLASS=list-color2";
  for($i=0;$i < $num;$i++) {
    $rcol=$i % 2;
    print "<TR" . $bcolor[$rcol] . ">";
    $r = pg_fetch_array($getagents,$i,PGSQL_NUM);
    print "<TD ALIGN=MIDDLE onmouseover=\"myHint.show('AS1')\" onmouseout=myHint.hide()>";%>
    <INPUT TYPE=BUTTON VALUE="" CLASS="option-<%
    if (($r[0] == 't') && ($r[6] != 't')) {
      print "green";
    } else if ($r[6] == 't') {
      print "orange";
      $r[0]='p';
    } else {
      print "red";
    }%>" onClick=agentonoff('<%print $r[2] . "','" . $r[0];%>')></TD><TD onmouseover="myHint.show('AS2')" onmouseout=myHint.hide()><%
    print $r[1] . "</TD>\n<TD onmouseover=\"myHint.show('AS3')\" onmouseout=myHint.hide()>";
    if (strpos($r[4],"@")) {
      $r[4]=substr($r[4],6,strpos($r[4],"@")-6);
    }
    print "<A HREF=\"javascript:deleteagent('" . $r[2] . "')\">" . $r[4] . "</A></TD>";
    print "<TD ALIGN=MIDDLE>";
    if ($r[2] == "Agent/" . $r[4]) {
      $olagent=1;
    } else {
      $olagent=0;
    }
    print $yesno[$olagent] . "</TD></TD>";
//    print "<A HREF=javascript:permagent('" . $r[2] . "','" . $olagent . "')>" . $yesno[$olagent] . "</A></TD></TD>";
    print "<TD onmouseover=\"myHint.show('AS4')\" onmouseout=myHint.hide() ALIGN=MIDDLE><A HREF=javascript:agentweight('" . $r[2] . "')>" . $r[3] . "</A></TD>";
    print "<TD ALIGN=MIDDLE>";
    if ($r[5] == "f" ) {
      $r[5]=_("No");
    } else {
      $r[5]=_("Yes");
    }
    print "<A HREF=javascript:agentignorebusy('" . $r[2] . "')>" . $r[5] . "</A></TD></TD>";
    print "</TR>";
  }
  $rcol=$i % 2;
  print "<TR" . $bcolor[$rcol] . "><TH COLSPAN=2><INPUT TYPE=SUBMIT onclick=this.name='addagent' VALUE=\"" . _("ADD") . "\"></TH>";
  print "<TD onmouseover=\"myHint.show('AS5')\" onmouseout=myHint.hide()><INPUT NAME=channel SIZE=10></TD>";
  print "<TD ALIGN=MIDDLE><INPUT TYPE=CHECKBOX NAME=olagent></TD>";
  print "<TD onmouseover=\"myHint.show('AS4')\" onmouseout=myHint.hide()><INPUT NAME=weight VALUE=" . $dqpenalty . " SIZE=10></TD>";
  print "<TD ALIGN=MIDDLE><INPUT TYPE=CHECKBOX NAME=nobusy></TD></TR>";
  $i++;
  $rcol=$i % 2;
  if (($queue == "799") || ($queue != $defqueue)) {
%>
    <TR <%print $bcolor[$rcol];$i++;$rcol=$i % 2;%>><TH CLASS=heading-body COLSPAN=6><%print _("Reception Queue Config");%></TH></TR>
    <TR <%print $bcolor[$rcol];$i++;$rcol=$i % 2;%>>
      <TD COLSPAN=3 WIDTH=50% onmouseover="myHint.show('QS8')" onmouseout=myHint.hide()><%print _("Queue Timeout Checked Every 18s");%></TD>
      <TD COLSPAN=3 WIDTH=50%><INPUT TYPE=TEXT NAME=AATimeout VALUE="<%print $origdata["AATimeout"];%>"></TD></TR>
    <TR <%print $bcolor[$rcol];$i++;$rcol=$i % 2;%>>
      <TD COLSPAN=3 WIDTH=50% onmouseover="myHint.show('AS6')" onmouseout=myHint.hide()><%print _("Auto Attendant Mailbox/Forward On No Agent/Timeout");%></TD>
      <TD COLSPAN=3 WIDTH=50%><INPUT TYPE=TEXT NAME=AANext VALUE="<%print $origdata["AANext"];%>"></TD></TR>
    <TR <%print $bcolor[$rcol];$i++;$rcol=$i % 2;%>>
      <TD COLSPAN=3 WIDTH=50% onmouseover="myHint.show('AS6')" onmouseout=myHint.hide()><%print _("Default System V.Mail Email");%></TD>
      <TD COLSPAN=3 WIDTH=50%><INPUT TYPE=TEXT NAME=AAEMAIL VALUE="<%print $AAEMAIL;%>"></TD></TR>
    <TR <%print $bcolor[$rcol];$i++;$rcol=$i % 2;%>>
      <TD COLSPAN=3 WIDTH=50% onmouseover="myHint.show('QS9')" onmouseout=myHint.hide()><%print _("Default Agent Penalty");%></TD>
      <TD COLSPAN=3 WIDTH=50%><INPUT TYPE=TEXT NAME=QAPENALTY VALUE="<%print $origdata["QAPENALTY"];%>"></TD></TR>
    <TR <%print $bcolor[$rcol];$i++;$rcol=$i % 2;%>>
      <TD COLSPAN=3 WIDTH=50% onmouseover="myHint.show('QS12')" onmouseout=myHint.hide()><%print _("IVR Delay Between Digits");%></TD>
      <TD COLSPAN=3 WIDTH=50%><INPUT TYPE=TEXT NAME=AADelay VALUE="<%print $origdata["AADelay"];%>"></TD></TR>
    <TR <%print $bcolor[$rcol];$i++;$rcol=$i % 2;%>>
      <TD onmouseover="myHint.show('QS10')" onmouseout="myHint.hide()" COLSPAN=3><%print _("Disable Default Auto Attendant Prompts");%></TD>
      <TD COLSPAN=3><INPUT TYPE=CHECKBOX NAME=AANOPROMPT<%if ($origdata["AANOPROMPT"] == "1") {print " CHECKED";}%>></TD>
    </TR>
    <TR <%print $bcolor[$rcol];$i++;$rcol=$i % 2;%>>
      <TD onmouseover="myHint.show('QS11')" onmouseout="myHint.hide()" COLSPAN=3><%print _("Music On Hold When Calling Reception");%></TD>
      <TD COLSPAN=3><INPUT TYPE=CHECKBOX NAME=AAMOH<%if ($origdata["AAMOH"] == "1") {print " CHECKED";}%>></TD>
    </TR>
    <TR <%print $bcolor[$rcol];$i++;$rcol=$i % 2;%>>
      <TD onmouseover="myHint.show('QS11')" onmouseout="myHint.hide()" COLSPAN=3><%print _("Record Inbound Calls");%></TD>
      <TD COLSPAN=3><INPUT TYPE=CHECKBOX NAME=AAREC<%if ($origdata["AAREC"] == "1") {print " CHECKED";}%>></TD>
    </TR>
    <TR <%print $bcolor[$rcol];$i++;$rcol=$i % 2;%>>
      <TD COLSPAN=6 ALIGN=MIDDLE><INPUT TYPE=SUBMIT VALUE="<%print _("Save Settings");%>" onclick=this.name='update'></TD></TR>
<%
  }
  print "<INPUT TYPE=HIDDEN NAME=queue VALUE=\"" . $queue . "\">"; 
  print "<INPUT TYPE=HIDDEN NAME=defqueue VALUE=\"" . $defqueue . "\">";
} else if (!isset($selext)){
  if ((isset($delext)) && ($queue != "")) {
    pg_query($db,"DELETE FROM queue_table WHERE name='" . $queue . "'");
    pg_query($db,"DELETE FROM users WHERE mailbox='" . $queue . "'");
  }
%>
  <TH CLASS=heading-body COLSPAN=2>Select Queue To Edit Agents</TH>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('AS0')" onmouseout="myHint.hide()">Edit Agents From Queue ...</TH>
  <TD WIDTH=50% ALIGN=LEFT>
  <SELECT NAME=queue onchange=ajaxsubmit('queuemod')>
<%

  $actqueuesq="SELECT name,description FROM queue_table";
  if ($SUPER_USER != 1) {
    $actqueuesq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family='Q'||name AND bgrp.key='BGRP') WHERE " . $clogacl;
  }
  $actqueuesq.=" ORDER BY name";
  $actqueues=pg_query($db,$actqueuesq);

  $num=pg_num_rows($actqueues);
  print "    <OPTION VALUE=\"\">Select Bellow</OPTION>\n";
  for($i=0;$i < $num;$i++) {
    $r = pg_fetch_array($actqueues,$i,PGSQL_NUM);
    print "    <OPTION VALUE=\"" .  $r[0] . "\">" . $r[1] . " (" . $r[0] .")</OPTION>\n";
  }
%>
  </SELECT>
  </TD></TR>
<%
}
%>
</TABLE>
</FORM>

