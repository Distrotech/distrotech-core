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

require_once "/var/spool/apache/htdocs/cdr/uauth.inc";

include "apifunc.inc";
require_once "auth.inc";


$allmemqu="SELECT DISTINCT case when (fullname is not null AND fullname != '') then fullname||' ('||membername||')' else 
                             case when (membername is not null) then membername else interface end end,
                           interface
                           from queue_members left outer join users on (membername=name)";
if ($SUPER_USER != 1) {
  $allmemqu.=" LEFT OUTER JOIN astdb AS bgrp ON ('Q'||queue_name=bgrp.family AND bgrp.key='BGRP') WHERE " . $clogacl;
}

$allmemq=pg_query($db,$allmemqu);

//print $ldn . "<P>" . $allmemqu . "\n";

for($cnt=0;$cnt < pg_num_rows($allmemq);$cnt++) {
 $qmema=pg_fetch_array($allmemq,$cnt,PGSQL_NUM);
 $qmem[$qmema[1]]=$qmema[0];
}


$qnme['All']="All Queues";
$qnameq="SELECT name,description||' ('||name||')',count(case when (CAST(penalty AS INT) <= 0) then 1 else null end) as inactive,
           count(case when (paused != 0) then paused else null end) as paused
 FROM queue_table LEFT OUTER JOIN queue_members ON (name=queue_name) ";
if ($SUPER_USER != 1) {
  $qnameq.=" LEFT OUTER JOIN astdb AS bgrp ON ('Q'||name=bgrp.family AND bgrp.key='BGRP') WHERE " . $clogacl;
} else {
  $qnme['799']="Reception (799)";
}
$qnameq.=" GROUP BY queue_table.name,queue_table.description ORDER BY description";
$qname=pg_query($db,$qnameq);

for($qcnt=0;$qcnt < pg_num_rows($qname);$qcnt++) {
  $qdat=pg_fetch_row($qname,$qcnt);
  $qnme[$qdat[0]]=$qdat[1];
  $qstat[$qdat[0]]['Inactive']=$qdat[2];
  $qstat[$qdat[0]]['Paused']=$qdat[3];
}

$apiinf=apiquery("QueueStatus");

for ($pkt=0;$pkt < count($apiinf);$pkt++) {
  $queuename=$apiinf[$pkt]['Queue'];
  unset($apiinf[$pkt]['Queue']);
  if ($apiinf[$pkt]['Event'] == "QueueParams") {
    unset($apiinf[$pkt]['Event']);
    $quarr[$queuename]=$apiinf[$pkt];
    if ((isset($qnme[$queuename])) && ($quarr['All']['Holdtime'] < $quarr[$queuename]['Holdtime'])) {
      $quarr['All']['Holdtime']=$quarr[$queuename]['Holdtime'];
    }
  } else if ($apiinf[$pkt]['Event'] == "QueueMember") {
    unset($apiinf[$pkt]['Event']);
    $memname=$apiinf[$pkt]['Name'];
    unset($apiinf[$pkt]['Name']);
    $quarr[$queuename]['Members'][$memname]=$apiinf[$pkt];
    if (isset($qnme[$queuename])) {
      $quarr['All']['Members'][$memname]=$apiinf[$pkt];
    }
  } else if ($apiinf[$pkt]['Event'] == "QueueEntry") {
    unset($apiinf[$pkt]['Event']);
    $memname=$apiinf[$pkt]['Position'];
    unset($apiinf[$pkt]['Position']);
    $quarr[$queuename]['Entrys'][$memname]=$apiinf[$pkt];
    if (! is_array($quarr['All']['Entrys'])) {
      $quarr['All']['Entrys']=array();
    }
    if (isset($qnme[$queuename])) {
      array_push($quarr['All']['Entrys'],$apiinf[$pkt]);
    } 
//  } else {
//    print_r($apiinf[$pkt]);
  }
}

$status[0]="Unknown";
$status[1]="Not in use";
$status[2]="In use";
$status[3]="Busy";
$status[4]="Invalid";
$status[5]="Unavailable";
$status[6]="Ringing";

$rcnt=1;
//print "<PRE>";
//print_r($quarr);
//print "</PRE>";
?>
<CENTER>
<FORM METHOD=POST NAME=queuestat onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color<?php print ($rcnt % 2) + 1;$rcnt++;?>>
<TH CLASS=heading-body2 ALIGN=LEFT><?php print _("Queue");?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Avail");?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Unavail.");?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Unknown.");?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Logged Out.");?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Paused.");?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Holdtime");?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Waiting");?></TH>
</TR>
<?php
/*
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Completed");?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Abandoned");?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Service Lev.");?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT><?php print _("Service Time");?></TH>
*/

while(list($qnum,$qdisc)=each($qnme)) {
  $queue=$quarr[$qnum];
  print "<TR CLASS=list-color" . (($rcnt % 2) + 1) . " ID=QSTATS_" . ($rcnt-2) . ">\n";
  $rcnt++;
  if ($qdisc == "") {
    if ($SUPER_USER != 1) {
      continue;
    } else {
      $qdisc=$qnum;
    }
  }
  $afree=0;
  $abusy=0;
  $aunk=0;
  $fagents="Free Agents\\n\\r";
  $bagents="\\n\\rBusy Agents\\n\\r";
  while(list($memname,$member) = each($queue['Members'])) {
    if ($member['Status'] == 0) {
      $aunk++;
      if (!isset($done[$memname])) {
        $done[$memname]=TRUE;
        $total['unk']++;
      }
    } else if ($member['Status'] > 1) {
      $bagents.=$qmem[$member['Location']] . "\\n\\r";
      $abusy++;
      if (!isset($done[$memname])) {
        $done[$memname]=TRUE;
        $total['busy']++;
      }
    } else if ($member['Status'] == 1) {
      $fagents.=$qmem[$member['Location']] . "\\n\\r";
      $afree++;
      if (!isset($done[$memname])) {
        $done[$memname]=TRUE;
        $total['free']++;
      }
    }
  }

//  print "<SCRIPT>alert('" . $agents . "');</SCRIPT>";
  printf("  <TD><A HREF=\"javascript:alert('%s'+'%s')\">%s</TD><TD ALIGN=RIGHT>%d</TD><TD ALIGN=RIGHT>%d</TD><TD ALIGN=RIGHT>%d</TD>
<TD ALIGN=RIGHT>%d</TD><TD ALIGN=RIGHT>%d</TD><TD ALIGN=RIGHT>%d</TD><TD ALIGN=RIGHT>%d</TD>\n",$fagents,$bagents,$qdisc,$afree,$abusy,$aunk,
    $qstat[$qnum]['Inactive'],$qstat[$qnum]['Paused'],$queue['Holdtime'],count($queue['Entrys']));
//,$queue['Completed'],$queue['Abandoned'],$queue[ServicelevelPerf],$queue['ServiceLevel']);
  print "</TR>";
}

    
$total['queue']=count($quarr['All']['Entrys']);
$total['agents']=count($quarr['All']['Members']);
$total['wait']=0;
for($ent=0;$ent<$total['queue'];$ent++) {
  if ($quarr['All']['Entrys'][$ent]['Wait'] > $total['wait']) {
    $total['wait']=$quarr['All']['Entrys'][$ent]['Wait'];
  }
}
/*
print "<PRE>";
print_r($total);
print "</PRE>";
*/
?>
</TABLE>
</FORM>
<SCRIPT>
setTimeout("ajaxsubmit('queuestat')",10000);
</SCRIPT>
