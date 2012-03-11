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

  include "func.inc";
  if ($ADMIN_USER != "admin") {
    return;
  }

  if ($fqueue != "") {
    $dfiltgb=",date_trunc('hour',calldate)";
    $dfilt=" AND users.name='" . $fqueue . "'";
    $dfield="date_trunc('hour',calldate)";
  } else { 
    $dfilt="";
    $dfiltgb="";
    $dfield="users.name";
  }

  if ($fqueue != "") {
    $qdisc=pg_query($db,"SELECT fullname FROM users WHERE name='" . $fqueue .  "'");
    $r=pg_fetch_array($qdisc,0);
    $subrep=$r[0] . " (" . $fqueue . ") ";
    $rcol++;
  } else {
    $subrep="";
  }

  $reptitle="Usage Report For " . $subrep . "Period (" . $time_year . "-" . str_pad($time_month,2,"0",STR_PAD_LEFT) . "-" . str_pad($time_day,2,"0",STR_PAD_LEFT) .  " " . str_pad($time_hour,2,"0",STR_PAD_LEFT) . ":" . str_pad($time_min,2,"0",STR_PAD_LEFT) . ":" . str_pad($time_sec,2,"0",STR_PAD_LEFT) . " To " . $mtime_year . "-" . str_pad($mtime_month,2,"0",STR_PAD_LEFT) . "-" . str_pad($mtime_day,2,"0",STR_PAD_LEFT) . " " . str_pad($mtime_hour,2,"0",STR_PAD_LEFT) . ":" . str_pad($mtime_min,2,"0",STR_PAD_LEFT) . ":" . str_pad($mtime_sec,2,"0",STR_PAD_LEFT). ")";

  if ($_POST['print'] < 2) {
  $colspan=16;
%>
<CENTER>
<FORM NAME=pform METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $_SESSION['disppage']%>">
<INPUT TYPE=HIDDEN NAME=time_year VALUE="<%print $time_year;%>">
<INPUT TYPE=HIDDEN NAME=time_month VALUE="<%print $time_month;%>">
<INPUT TYPE=HIDDEN NAME=time_day VALUE="<%print $time_day;%>">
<INPUT TYPE=HIDDEN NAME=time_hour VALUE="<%print $time_hour;%>">
<INPUT TYPE=HIDDEN NAME=time_min VALUE="<%print $time_min;%>">
<INPUT TYPE=HIDDEN NAME=time_sec VALUE="<%print $time_sec;%>">
<INPUT TYPE=HIDDEN NAME=mtime_year VALUE="<%print $mtime_year;%>">
<INPUT TYPE=HIDDEN NAME=mtime_month VALUE="<%print $mtime_month;%>">
<INPUT TYPE=HIDDEN NAME=mtime_day VALUE="<%print $mtime_day;%>">
<INPUT TYPE=HIDDEN NAME=mtime_hour VALUE="<%print $mtime_hour;%>">
<INPUT TYPE=HIDDEN NAME=mtime_min VALUE="<%print $mtime_min;%>">
<INPUT TYPE=HIDDEN NAME=mtime_sec VALUE="<%print $mtime_sec;%>">
<INPUT TYPE=HIDDEN NAME=exten VALUE="<%print $exten;%>">
<INPUT TYPE=HIDDEN NAME=fqueue VALUE="<%print $fqueue;%>">
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
</FORM>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=<%print $colspan;%> CLASS=heading-body>
<%print $reptitle;%>
</TH>
</TR>
<TR CLASS=list-color1>
<TH WIDTH=25%>&nbsp;</TH>
<TH COLSPAN=3 CLASS=heading-body2>Total</TH>
<TH COLSPAN=3 CLASS=heading-body2>Inbound</TH>
<TH COLSPAN=4 CLASS=heading-body2>Outbound</TH>
<TH COLSPAN=5 CLASS=heading-body2>ACD</TH>
</TR>
<TR CLASS=list-color2>
<TH ALIGN=LEFT CLASS=heading-body2 WIDTH=25%><%print ($fqueue == "")?"Agent":"Time";%></TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Calls</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Time</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Avg.</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Calls</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Time</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Avg</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Calls</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Time</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Avg</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Cost</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Calls</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Time</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Avg</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>RNA</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>A. Dis.</TH>
</TR>
<%
} else {
  print printcsv(array($reptitle));
  print printcsv(array(($fqueue == "")?"Agent":"Time","Tot. Calls","Tot. Time","Tot. Avg.","In Calls","In Time","In Avg.",
                       "Out Calls","Out Time","Out Avg.","Out Cost","ACD Calls","ACD Time","ACD Avg.","RNA","A. Dis"));
}

  $usersq=pg_query($db,"SELECT name,fullname||' ('||users.name||')' FROM users 
		LEFT OUTER JOIN astdb AS local ON  (local.family='LocalPrefix' AND local.key=substr(name,1,2)) WHERE local.value=1 ORDER BY fullname,name");
  for($qcnt=0;$qcnt < pg_num_rows($usersq);$qcnt++) {
    $r=pg_fetch_array($usersq,$qcnt,PGSQL_NUM);
    $userinf[$r[0]]['name']=$r[1];
  }

  $perstart="'" . $time_year . "-" . $time_month . "-" . $time_day ." " . $time_hour . ":" . $time_min . ":" . $time_sec . "'";
  $perstop="'" . $mtime_year . "-" . $mtime_month . "-" . $mtime_day ." " . $mtime_hour . ":" . $mtime_min . ":" . $mtime_sec . "'";

  $acdcallqsql="SELECT membername,count(CASE WHEN (event = 'CONNECT') THEN agent ELSE null END),
                    sum(CASE WHEN (event='COMPLETECALLER' OR event='COMPLETEAGENT') THEN CAST(data2 AS integer) ELSE
                      CASE WHEN (event='TRANSFER') THEN CAST(data4 AS integer) ELSE NULL END END),NULL,
                    count(CASE WHEN (event = 'RINGNOANSWER') THEN agent ELSE null END),
                    count(CASE WHEN (event = 'COMPLETEAGENT') THEN agent ELSE null END)
                  FROM queue_log
                    LEFT OUTER JOIN queue_members ON (queuename=queue_name AND (agent=membername or agent=interface))
                  WHERE time > " . $perstart . " AND time < " . $perstop . " AND membername IS NOT NULL AND
                        (event='CONNECT' OR event='RINGNOANSWER' OR event='COMPLETEAGENT' OR event='COMPLETECALLER' OR event='TRANSFER')
                  GROUP BY membername";

//  print $acdcallqsql . ";<P>";
  $acdcallq=pg_query($db,$acdcallqsql);

  for($qcnt=0;$qcnt < pg_num_rows($acdcallq);$qcnt++) {
    $r=pg_fetch_array($acdcallq,$qcnt,PGSQL_NUM);
    if (is_array($userinf[$r[0]])) {
      $user=array_shift($r);
      $r[2]=$r[1]/$r[0];
      $userinf[$user]['acd']=$r;
    }
  }

  $cdrcallqsql="SELECT users.name,
                     count(CASE WHEN (cdr.dst = users.name) THEN cdr.uniqueid ELSE NULL END),
                     sum(CASE WHEN (cdr.dst = users.name) THEN billsec ELSE NULL END),NULL,
                     count(CASE WHEN (cdr.accountcode = users.name) THEN cdr.uniqueid ELSE NULL END),
                     sum(CASE WHEN (cdr.accountcode = users.name) THEN billsec ELSE NULL END),NULL,
                     sum(CASE WHEN (cdr.accountcode = users.name) THEN cost ELSE NULL END)
                   FROM cdr
                     LEFT OUTER JOIN trunkcost ON (cdr.uniqueid = trunkcost.uniqueid)
                     LEFT OUTER JOIN users ON (dst=users.name OR cdr.accountcode=users.name)
                   WHERE calldate > " . $perstart . " AND calldate < " . $perstop . " AND disposition='ANSWERED' AND
                     users.name IS NOT NULL GROUP BY fullname,users.name";

//  print $cdrcallqsql . ";<P>";
  $cdrcallq=pg_query($db,$cdrcallqsql);

  for($qcnt=0;$qcnt < pg_num_rows($cdrcallq);$qcnt++) {
    $r=pg_fetch_array($cdrcallq,$qcnt,PGSQL_NUM);
    if (is_array($userinf[$r[0]])) {
      $user=array_shift($r);
      $r[1]=($r[1] != "") ? $r[1] : "0";
      $r[4]=($r[4] != "") ? $r[4] : "0";
      $r[6]=($r[6] != "") ? $r[6] : "0";
      $r[2]=($r[1] > 0) ? ($r[1]/$r[0]) : "0";
      $r[5]=($r[4] > 0) ? ($r[4]/$r[3]) : "0";
      $userinf[$user]['cdr']=$r;
    }
  }

  $ccnt=0;
  $toc=0;
  $rcol=0;

  $totcol=array(1,2,4,5,7,8,10,11,12,14,15);
  while(list($user) = each($userinf)) {
    $udata=$userinf[$user];
    if ((!is_array($udata['acd'])) && (!is_array($udata['cdr']))) {
      continue;
    }
    if (!is_array($udata['cdr'])) {
      $udata['cdr']=array("0","0","0","0","0","0","0");
    }
    if (!is_array($udata['acd'])) {
      $udata['acd']=array("0","0","0","0","0");
    }
    $udata['name']=($udata['name'] == "") ? $user : $udata['name'];
    $tot=$udata['cdr'][0]+$udata['cdr'][3]+$udata['acd'][0];
    $totdur=$udata['cdr'][1]+$udata['cdr'][4]+$udata['acd'][1];
    $totavg=$totdur/$tot;
    $r=array_merge(array($user,$udata['name'],$tot,$totdur,$totavg),$udata['cdr'],$udata['acd']);

/*
    if ($fqueue == "") {
      if ($_POST['print'] < 1) {
        $r[1]="<A HREF=javascript:openqueue('" . $r[0] . "')>" . $r[1] . "</A></TD>";
      }
    } else {
      $r[1]=$r[0];
    }
*/
    array_shift($r);
    for($icol=0;$icol < count($totcol);$icol++) {
      $colidx=$totcol[$icol];
      $totals[$colidx]=$totals[$colidx]+$r[$colidx];
    }

    $r[2]=gtime($r[2]);
    $r[3]=gtime($r[3]);
    $r[5]=gtime($r[5]);
    $r[6]=gtime($r[6]);
    $r[8]=gtime($r[8]);
    $r[9]=gtime($r[9]);
    $r[10]=sprintf("%0.2f",$r[10]/100000);
    $r[12]=gtime($r[12]);
    $r[13]=gtime($r[13]);
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($rcol % 2)+1) . ">";
      $rcol++;
      for($cnt=0;$cnt<count($r);$cnt++) {
        if ($cnt < 1) {
          print "<TD ALIGN=LEFT>" . $r[$cnt] . "</TD>";
        } else {
          print "<TD ALIGN=RIGHT>" . $r[$cnt] . "</TD>";
        }
      }
      print "</TR>";
    } else {
      $r[0]="'" . $r[0];
      print printcsv($r);
    }
  }

  $totals[0]="";
  $totals[3]=($totals[1] > 0)?gtime($totals[2]/$totals[1]):"00:00:00";
  $totals[6]=($totals[4] > 0)?gtime($totals[5]/$totals[4]):"00:00:00";
  $totals[9]=($totals[7] > 0)?gtime($totals[8]/$totals[7]):"00:00:00";
  $totals[13]=($totals[11] > 0)?gtime($totals[12]/$totals[11]):"00:00:00";
  $totals[2]=gtime($totals[2]);
  $totals[5]=gtime($totals[5]);
  $totals[8]=gtime($totals[8]);
  $totals[12]=gtime($totals[12]);
  $totals[10]=sprintf("%0.2f",$totals[10]/100000);
  ksort($totals);

  if ($_POST['print'] < 2) {
    print "<TR CLASS=list-color" . (($rcol % 2)+1) . ">";
    $rcol++;
    for($cnt=0;$cnt<count($totals);$cnt++) {
      if ($cnt < 1) {
        print "<TD ALIGN=LEFT>&nbsp;</TD>";
      } else {
        print "<TD ALIGN=RIGHT>" . $totals[$cnt] . "</TD>";
      }
    }
    print "</TR>";
  } else {
    $totals[0]="'" . $totals[0];
    print printcsv($totals);
  }

  if ($_POST['print'] < 2) {
    if ($_POST['print'] != "1") {
      print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $colspan . " CLASS=heading-body>";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.pform)\">";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.pform)\">";
      print "</TH></TR>";
    }
    print "</TABLE>";
  }
%>
