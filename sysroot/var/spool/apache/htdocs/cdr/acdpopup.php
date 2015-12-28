<?php
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


  include "func.inc";
  if ($ADMIN_USER != "admin") {
    return;
  }

  $frmtime=$time_year . "-" . $time_month . "-" . $time_day ." " . $time_hour . ":" . $time_min . ":" . $time_sec;
  $totime=$mtime_year . "-" . $mtime_month . "-" . $mtime_day ." " . $mtime_hour . ":" . $mtime_min . ":" . $mtime_sec;
 
  if (($_POST['poptype'] == "AGENTCALLBACKLOGIN") || ($_POST['poptype'] == "REMOVEMEMBER") || ($_POST['poptype'] == "ADDMEMBER")) {
    $_POST['poptype']="AGENTCALLBACKLOGOFF";
  }

  $popinf['AGENTCALLBACKLOGOFF']['descrip']=_("Agent Login/Logoff");
  $popinf['AGENTCALLBACKLOGOFF']['report']="SELECT date_trunc('second',time),CASE WHEN (event = 'AGENTCALLBACKLOGOFF' OR event = 'REMOVEMEMBER') THEN 'Off' ELSE 'On' END,
                         users.fullname||' ('||membername||')',extract('epoch' from time)
                       FROM queue_log 
                       LEFT OUTER JOIN queue_members ON (queue_name = queuename AND (membername = agent OR interface=agent))
                       LEFT OUTER JOIN users ON (name=membername) WHERE
                        queue_log.time > '" . $frmtime . "' AND queue_log.time < '" . $totime . "' AND 
                        (queue_log.event='AGENTCALLBACKLOGOFF' OR queue_log.event='AGENTCALLBACKLOGIN' OR
                        queue_log.event='REMOVEMEMBER' OR queue_log.event='ADDMEMBER') AND 
                        queue_log.queuename='" . $fqueue . "' AND NAME IS NOT NULL ORDER BY agent,time";
  $popinf['AGENTCALLBACKLOGOFF']['title']=array("Date","Time");

  $popinf['ABANDON']['report']="SELECT DISTINCT ON (queue_log.time,conlog.callid) queue_log.time,conlog.data2,CAST (queue_log.data1 AS integer),
                             CAST (queue_log.data2 AS integer),CAST (queue_log.data3 AS integer) from queue_log 
                      LEFT OUTER JOIN queue_log AS conlog USING (callid) WHERE 
                        queue_log.time > '" . $frmtime . "' AND queue_log.time < '" . $totime . "' AND 
                        queue_log.event='ABANDON' AND queue_log.queuename='" . $_POST['fqueue'] . "' AND conlog.event='ENTERQUEUE' ORDER BY queue_log.time,conlog.callid";
  $popinf['ABANDON']['title']=array("Date","Caller ID","Final Pos","Initial Pos.","Hold Time");
  $popinf['ABANDON']['descrip']=_("Calls Abandonded By Caller");

  $popinf['CONNECT']['report']="SELECT DISTINCT ON (callid) time,src,agent,duration,billsec,duration-billsec 
                        from queue_log left outer join cdr ON (uniqueid=callid) where 
                        queue_log.time > '" . $frmtime . "' AND queue_log.time < '" . $totime . "' AND 
                        queue_log.queuename='" . $_POST['fqueue'] . "' AND queue_log.event='CONNECT' ORDER BY callid,cdr.calldate,queue_log.time";
  $popinf['CONNECT']['title']=array("Date","Caller ID","Agent","Total Time","Talk Time","Ring Time");
  $popinf['CONNECT']['descrip']=_("All Connected Calls For Queue");


  $reptitle=$popinf[$_POST['poptype']]['descrip'] . " Report For Period (" .  $time_year . "-" . str_pad($time_month,2,"0",STR_PAD_LEFT) . "-" . str_pad($time_day,2,"0",STR_PAD_LEFT) .  " " . str_pad($time_hour,2,"0",STR_PAD_LEFT) . ":" . str_pad($time_min,2,"0",STR_PAD_LEFT) . ":" . str_pad($time_sec,2,"0",STR_PAD_LEFT) . " To " . $mtime_year . "-" . str_pad($mtime_month,2,"0",STR_PAD_LEFT) . "-" . str_pad($mtime_day,2,"0",STR_PAD_LEFT) . " " . str_pad($mtime_hour,2,"0",STR_PAD_LEFT) . ":" . str_pad($mtime_min,2,"0",STR_PAD_LEFT) . ":" . str_pad($mtime_sec,2,"0",STR_PAD_LEFT) . ")";

  if ($_POST['print'] < 2) {
?>
<CENTER>
<FORM NAME=pform METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cdr/acdpopup.php">
<INPUT TYPE=HIDDEN NAME=time_year VALUE="<?php print $time_year;?>">
<INPUT TYPE=HIDDEN NAME=time_month VALUE="<?php print $time_month;?>">
<INPUT TYPE=HIDDEN NAME=time_day VALUE="<?php print $time_day;?>">
<INPUT TYPE=HIDDEN NAME=time_hour VALUE="<?php print $time_hour;?>">
<INPUT TYPE=HIDDEN NAME=time_min VALUE="<?php print $time_min;?>">
<INPUT TYPE=HIDDEN NAME=time_sec VALUE="<?php print $time_sec;?>">
<INPUT TYPE=HIDDEN NAME=mtime_year VALUE="<?php print $mtime_year;?>">
<INPUT TYPE=HIDDEN NAME=mtime_month VALUE="<?php print $mtime_month;?>">
<INPUT TYPE=HIDDEN NAME=mtime_day VALUE="<?php print $mtime_day;?>">
<INPUT TYPE=HIDDEN NAME=mtime_hour VALUE="<?php print $mtime_hour;?>">
<INPUT TYPE=HIDDEN NAME=mtime_min VALUE="<?php print $mtime_min;?>">
<INPUT TYPE=HIDDEN NAME=mtime_sec VALUE="<?php print $mtime_sec;?>">
<INPUT TYPE=HIDDEN NAME=exten VALUE="<?php print $exten;?>">
<INPUT TYPE=HIDDEN NAME=fqueue VALUE="<?php print $fqueue;?>">
<INPUT TYPE=HIDDEN NAME=date VALUE="<?php print $date;?>">
<INPUT TYPE=HIDDEN NAME=nomenu>
<INPUT TYPE=HIDDEN NAME=poptype VALUE=<?php print $_POST['poptype'];?>>
</FORM>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=<?php print count($popinf[$_POST['poptype']]['title']);?> CLASS=heading-body>
<?php print $reptitle;?>
</TH>
</TR>
<TR CLASS=list-color1><?php
  for($cnt=0;$cnt<count($popinf[$_POST['poptype']]['title']);$cnt++) {
    print "<TH CLASS=heading-body2 ALIGN=LEFT>" . $popinf[$_POST['poptype']]['title'][$cnt] . "</TD>";
  }
?>
</TR>
<?php
} else {
  $arrout=array();
  for($cnt=0;$cnt<count($popinf[$_POST['poptype']]['title']);$cnt++) {
    array_push($arrout,$popinf[$_POST['poptype']]['title'][$cnt]);
  }
  print printcsv(array($reptitle));
  print printcsv($arrout);
}
  $ccnt=0;
  $toc=0;
  $rcol=1;


//  if ($SUPER_USER != 1) {
//    $getcdrq.=" LEFT OUTER JOIN astdb AS bgrp ON ((accountcode=bgrp.family  OR src=bgrp.family OR exten=bgrp.family OR dst=bgrp.family) AND bgrp.key='BGRP')";
//  }
//  if ($SUPER_USER != 1) {
//    $getcdrq.=" AND $clogacl";
//  }
//  $getcdrq.=" GROUP BY calldate,calllog.uniqueid,clid,billsec,src,dst,userfield,accountcode,cdr.uniqueid 
//                ORDER BY calldate,cdr.uniqueid;";

//  print $popinf[$_POST['poptype']]['report'] . "<P>";


  $starttime=strtotime($frmtime);


  $getcdr=pg_query($db,$popinf[$_POST['poptype']]['report']);
  for($i=0;$i < pg_num_rows($getcdr);$i++) {
    $r=pg_fetch_row($getcdr, $i);
    if ($_POST['poptype'] == "AGENTCALLBACKLOGOFF") {
      $time=array_pop($r);
      $agent=array_pop($r);
      $state=array_pop($r);
      if (($lastagent == "") || ($lastagent != $agent)) {
        if ($_POST['print'] < 2) {
          print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=3 CLASS=heading-body2>" . $agent . "</TH></TR>\n";
          $rcol++;
        } else {
          print "\n" . printcsv(array($agent));
        }
        $lastagent=$agent;
      }
      if ($agentinf[$agent] == "") {
        $agentinf[$agent]=$starttime;
      }
      $tdiff=$time-$agentinf[$agent];
      $agentinf[$agent]=$time;
      if ($state == "On") {
        array_push($r,"<DIV CLASS=option-red>" . gtime($tdiff) . "</DIV>");
      } else {
        array_push($r,"<DIV CLASS=option-green>" . gtime($tdiff) . "</DIV>");
      }
      $ext[$i][1]=" ALIGN=RIGHT WIDTH=1%";
    }
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($rcol % 2)+1) . ">";
      $rcol++;
    }
    if ($_POST['print'] < 2) {
      for($cnt=0;$cnt<count($r);$cnt++) {
        print "<TD" . $ext[$i][$cnt] . ">" . (($r[$cnt] == "")?"&nbsp;":$r[$cnt]) . "</TD>";
      }
    }
    if ($_POST['print'] < 2) {
      print "</TR>\n";
    } else {
      $r[1]="'" . $r[1];
      print printcsv($r);
    }
  }
  if ($_POST['print'] < 2) {
    if ($_POST['print'] != "1") {
      print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=8 CLASS=heading-body>";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.pform)\">";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.pform)\">";
      print "</TH></TR>";
    }
    print "</TABLE>";
  }
?>
