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
    $qfilt=" AND queue_log.queuename='" . $fqueue . "' ";
    $perhour=",date_trunc('hour',queue_log.time)";
  } else { 
    $qfilt="";
    $perhour="";
  }

  $reptitle="ACD SL Report For Period (" . $time_year . "-" . str_pad($time_month,2,"0",STR_PAD_LEFT) . "-" . str_pad($time_day,2,"0",STR_PAD_LEFT) .  " " . str_pad($time_hour,2,"0",STR_PAD_LEFT) . ":" . str_pad($time_min,2,"0",STR_PAD_LEFT) . ":" . str_pad($time_sec,2,"0",STR_PAD_LEFT) . " To " . $mtime_year . "-" . str_pad($mtime_month,2,"0",STR_PAD_LEFT) . "-" . str_pad($mtime_day,2,"0",STR_PAD_LEFT) . " " . str_pad($mtime_hour,2,"0",STR_PAD_LEFT) . ":" . str_pad($mtime_min,2,"0",STR_PAD_LEFT) . ":" . str_pad($mtime_sec,2,"0",STR_PAD_LEFT) . ")";

  if ($fqueue != "") {
    $qdisc=pg_query($db,"SELECT description,servicelevel FROM queue_table WHERE name='" . $fqueue .  "'");
    $r=pg_fetch_array($qdisc,0);
    $rephead=$r[0] . " (" . $fqueue . ") Service Level " . gtime($r[1]);
    $rcol++;
  }

  if ($_POST['print'] < 2) {
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
<TH COLSPAN=9 CLASS=heading-body>
<%print $reptitle;%>
</TH>
</TR>
<%
  $rcol=0;
  if ($fqueue != "") {
    print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=9>" . $rephead . "</TH>";
    $rcol++;
  }
%>
<TR CLASS=list-color<%print (($rcol % 2)+1);$rcol++%>>
<TH ALIGN=LEFT CLASS=heading-body2><%if ($fqueue == "") {print "Queue";} else {print "Start Hour";}%></TH>
<TH ALIGN=RIGHT CLASS=heading-body2>Total</TH>
<TH ALIGN=RIGHT CLASS=heading-body2>Not Con.</TH>
<TH ALIGN=RIGHT CLASS=heading-body2>25%</TH>
<TH ALIGN=RIGHT CLASS=heading-body2>50%</TH>
<TH ALIGN=RIGHT CLASS=heading-body2>100%</TH>
<TH ALIGN=RIGHT CLASS=heading-body2>200%</TH>
<TH ALIGN=RIGHT CLASS=heading-body2>400%</TH>
<TH ALIGN=RIGHT CLASS=heading-body2>+400%</TH>
</TR>
<%
} else {
  print printcsv(array($reptitle));
  if ($fqueue != "") {
    print printcsv(array($rephead));
  }
  print printcsv(array(($fqueue == "")?"Queue":"Start Hour","Total","Not Con.","Not Con%","25%","50%","100%","200%","400%","+400%"));
}
  $ccnt=0;
  $toc=0;

  $acdstatq="SELECT description,queue_log.queuename,servicelevel,count(distinct queue_log.callid) AS total,
                    count(distinct CASE WHEN (enterq.event='CONNECT' AND CAST (enterq.data1 AS integer) < servicelevel/4) THEN queue_log.callid ELSE NULL END) AS sl_25,
                    count(distinct CASE WHEN (enterq.event='CONNECT' AND CAST (enterq.data1 AS integer) < servicelevel/2) THEN queue_log.callid ELSE NULL END) AS sl_50,
                    count(distinct CASE WHEN (enterq.event='CONNECT' AND CAST (enterq.data1 AS integer) < servicelevel) THEN queue_log.callid ELSE NULL END) AS sl,
                    count(distinct CASE WHEN (enterq.event='CONNECT' AND CAST (enterq.data1 AS integer) < servicelevel*2) THEN queue_log.callid ELSE NULL END) AS sl_200,
                    count(distinct CASE WHEN (enterq.event='CONNECT' AND CAST (enterq.data1 AS integer) < servicelevel*4) THEN queue_log.callid ELSE NULL END) AS sl_400,
                    count(distinct CASE WHEN (enterq.event='CONNECT' AND CAST (enterq.data1 AS integer) >= servicelevel*4) THEN queue_log.callid ELSE NULL END) AS sl_over_400" . $perhour . " 
                  FROM queue_log LEFT OUTER JOIN queue_table ON (queue_log.queuename=name) 
                                 LEFT OUTER JOIN queue_log AS enterq ON (enterq.callid=queue_log.callid AND enterq.event='CONNECT' AND 
                                   queue_log.queuename=enterq.queuename) ";

  if ($SUPER_USER != 1) {
    $acdstatq.="LEFT OUTER JOIN astdb AS bgrp ON ('Q'||name=bgrp.family AND bgrp.key='BGRP') ";
  }
/*
  if ($SUPER_USER != 1) {
    $acdlistq.=" AND " . $clogacl;
  }
*/
  $acdstatq.="WHERE queue_log.event='ENTERQUEUE' AND
                     queue_log.time > '" . $time_year . "-" . $time_month . "-" . $time_day ." " . $time_hour . ":" . $time_min . ":" . $time_sec . "' AND
                     queue_log.time < '" . $mtime_year . "-" . $mtime_month . "-" . $mtime_day ." " . $mtime_hour . ":" . $mtime_min . ":" . $mtime_sec . "' AND
                     queue_table.name IS NOT NULL" . $qfilt;
  if ($SUPER_USER != 1) {
    $acdstatq.=" AND " . $clogacl;
  }
  $acdstatq.=" GROUP BY description,queue_log.queuename,servicelevel" . $perhour . " ORDER BY description" . $perhour;
//  print $acdstatq . "\n";
//  if ($SUPER_USER != 1) {
//    $getcdrq.=" LEFT OUTER JOIN astdb AS bgrp ON ((accountcode=bgrp.family  OR src=bgrp.family OR exten=bgrp.family OR dst=bgrp.family) AND bgrp.key='BGRP')";
//  }
//  $getcdrq.=" GROUP BY calldate,calllog.uniqueid,clid,billsec,src,dst,userfield,accountcode,cdr.uniqueid 
//                ORDER BY calldate,cdr.uniqueid;";
//    print $acdstatq . "<P>";

  $getcdr=pg_query($db,$acdstatq);
  for($i=0;$i < pg_num_rows($getcdr);$i++) {
    $r=pg_fetch_row($getcdr, $i);
    $noans=$r[3]-$r[8]-$r[9];
    $noap=sprintf("%0.2f%%",($noans/$r[3])*100);
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($rcol % 2)+1) . ">";
      $rcol++;
      if ($fqueue == "") {
        if ($_POST['print'] == 1) {
          print "<TD>" . $r[0] . "(" . $r[1] . ") [" . gtime($r[2]) . "]</TD><TD ALIGN=RIGHT>" . $r[3] . "</TD>";
        } else {
          print "<TD><A HREF=javascript:openqueue('" . $r[1] . "')>" . $r[0] . "(" . $r[1] . ") [" . gtime($r[2]) . "]</A></TD><TD ALIGN=RIGHT>" . $r[3] . "</TD>";
        }
      } else {
        print "<TD>" . $r[10] . "</TD><TD ALIGN=RIGHT>" . $r[3] . "</TD>";
      }
      print "<TD ALIGN=RIGHT>" . $noap . " (" . $noans . ")</TD>";
      for($cnt=4;$cnt<=9;$cnt++) {
        $r[$cnt]=sprintf("%0.2f%%",($r[$cnt]/$r[3])*100);
        print "<TD ALIGN=RIGHT>" . $r[$cnt] . "</TD>";
      }
    } else {
      $outarr=array();
      array_push($outarr,($fqueue == "")?$r[0] . "(" . $r[1] . ") [" . gtime($r[2]) . "]":$r[10]);
      array_push($outarr,$r[3]);
      array_push($outarr,$noans);
      array_push($outarr,$noap);
      for($cnt=4;$cnt<=9;$cnt++) {
        array_push($outarr,sprintf("%0.2f%%",($r[$cnt]/$r[3])*100));
      }
      print printcsv($outarr);
    }
  }
  if ($_POST['print'] < 2) {
    if ($_POST['print'] != "1") {
      print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=9 CLASS=heading-body>";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.pform)\">";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.pform)\">";
      print "</TH></TR>";
    }
    print "</TABLE>";
  }
%>
