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

  $reptitle="Routing Report For " . $subrep . "Period (" . $time_year . "-" . str_pad($time_month,2,"0",STR_PAD_LEFT) . "-" . str_pad($time_day,2,"0",STR_PAD_LEFT) .  " " . str_pad($time_hour,2,"0",STR_PAD_LEFT) . ":" . str_pad($time_min,2,"0",STR_PAD_LEFT) . ":" . str_pad($time_sec,2,"0",STR_PAD_LEFT) . " To " . $mtime_year . "-" . str_pad($mtime_month,2,"0",STR_PAD_LEFT) . "-" . str_pad($mtime_day,2,"0",STR_PAD_LEFT) . " " . str_pad($mtime_hour,2,"0",STR_PAD_LEFT) . ":" . str_pad($mtime_min,2,"0",STR_PAD_LEFT) . ":" . str_pad($mtime_sec,2,"0",STR_PAD_LEFT). ")";

  if ($_POST['print'] < 2) {
  $colspan=10;
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
<TH COLSPAN=2>&nbsp;</TH>
<TH COLSPAN=4 CLASS=heading-body2 STYLE="border: solid black 1px">LCR</TH>
<TH COLSPAN=4 CLASS=heading-body2 STYLE="border: solid black 1px">TDM</TH>
</TR>
<TR CLASS=list-color2>
<TH ALIGN=LEFT CLASS=heading-body2 WIDTH=5%>Day</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Total</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>No.</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Time</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Avg.</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Cost</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>No.</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Time</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Avg.</TH>
<TH ALIGN=RIGHT CLASS=heading-body2 WIDTH=5%>Cost</TH>
</TR>
<%
} else {
  print printcsv(array($reptitle));
  print printcsv(array(($fqueue == "")?"Agent":"Time","Tot. Calls","Tot. Time","Tot. Avg.","In Calls","In Time","In Avg.",
                       "Out Calls","Out Time","Out Avg.","Out Cost","ACD Calls","ACD Time","ACD Avg.","RNA","A. Dis"));
}
  $ccnt=0;
  $toc=0;
  $rcol=0;

  $acdstatq="SELECT date_part('year',calldate),date_part('month',calldate),date_part('day',calldate),count(uniqueid) as total,
               count(CASE WHEN (dstchannel ~ '(^SIP/parent)|(^IAX2/parent)') THEN uniqueid ELSE NULL END) AS voip,
               sum(CASE WHEN (dstchannel ~ '(^SIP/parent)|(^IAX2/parent)') THEN billsec ELSE 0 END) AS voipsec,
               avg(CASE WHEN (dstchannel ~ '(^SIP/parent)|(^IAX2/parent)') THEN billsec ELSE NULL END) AS voipavg,
               sum(CASE WHEN (dstchannel ~ '(^SIP/parent)|(^IAX2/parent)') THEN cost ELSE 0 END) AS voipcost,
               count(CASE WHEN (dstchannel !~ '(^SIP/parent)|(^IAX2/parent)') THEN uniqueid ELSE NULL END) AS tdm,
               sum(CASE WHEN (dstchannel !~ '(^SIP/parent)|(^IAX2/parent)') THEN billsec ELSE 0 END) AS tdmsec,
               avg(CASE WHEN (dstchannel !~ '(^SIP/parent)|(^IAX2/parent)') THEN billsec ELSE NULL END) AS tdmavg,
               sum (CASE WHEN (dstchannel !~ '(^SIP/parent)|(^IAX2/parent)') THEN cost ELSE 0 END) AS tdmcost 
             FROM cdr LEFT OUTER JOIN trunkcost USING (uniqueid)";
  if ($TMS_USER == 1) {
    $acdstatq.=" LEFT OUTER JOIN astdb AS bgrp ON (accountcode=bgrp.family AND bgrp.key='BGRP')";
  }
  $acdstatq.=" WHERE disposition='ANSWERED' AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's' AND dst ~ '^[0-9]{4}[0-9]+')) AND 
                 calldate > '" . $time_year . "-" . $time_month . "-" . $time_day ." " . $time_hour . ":" . $time_min . ":" . $time_sec . "' AND
                 calldate < '" . $mtime_year . "-" . $mtime_month . "-" . $mtime_day ." " . $mtime_hour . ":" . $mtime_min . ":" . $mtime_sec . "'";

  if (($TMS_USER == 1) && ($SUPER_USER != 1)) {
    $acdstatq.=" AND $clogacl";
  }

  $acdstatq.=" GROUP BY date_part('year',calldate),date_part('month',calldate),date_part('day',calldate) 
             ORDER BY date_part('year',calldate),date_part('month',calldate),date_part('day',calldate)";

  $getcdr=pg_query($db,$acdstatq);
  $lastqueue="";
  for($i=0;$i < pg_num_rows($getcdr);$i++) {
    $r=pg_fetch_row($getcdr, $i);
/*
    if ($fqueue == "") {
      if ($_POST['print'] < 1) {
        $r[1]="<A HREF=javascript:openqueue('" . $r[0] . "')>" . $r[1] . "</A></TD>";
      }
    } else {
      $r[1]=$r[0];
    }
*/
    $r[4]=sprintf("%s (%0.2f%%)",$r[4],($r[4]/$r[3])*100);
    $r[8]=sprintf("%s (%0.2f%%)",$r[8],($r[8]/$r[3])*100);
    $r[5]=gtime($r[5]);
    $r[6]=gtime($r[6]);
    $r[9]=gtime($r[9]);
    $r[10]=gtime($r[10]);
    $r[7]=sprintf("%0.2f",$r[7]/100000);
    $r[11]=sprintf("%0.2f",$r[11]/100000);
    $r[2]=sprintf("%s/%s/%s",$r[0],$r[1],$r[2]);
    array_shift($r);
    array_shift($r);

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
