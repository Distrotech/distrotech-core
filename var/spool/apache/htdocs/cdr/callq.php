<link rel="stylesheet" type="text/css" href="/style.php">
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

  if ($xexep == "on") {
    $exceptions=" AND (billsec > " . $avg . "*" . $exep;
    $exceptions.=" OR duration-billsec > " . $havg . "*" . $exep;
    $exceptions.=")";
  }

  $getcdrq="SELECT avg(billsec),sum(billsec),
                               stddev(billsec),count(accountcode),
                               avg(duration-billsec) AS holdtime,
                               substr(dstchannel,5,4) AS cagent
                             from cdr where
                               userfield = '$exten' AND
                               disposition = '$disp' AND
                               date_part('year',calldate) = '" . $month[1] . "' AND
                               date_part('month',calldate) = '" . $month[0] . "'$exceptions
                             group by 
                               cagent
                             order by cagent";
  $getcdr=pg_query($db,$getcdrq);

  print "<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>\n<TR CLASS=list-color2>";
  if ($disp == "ANSWERED") {
    print "<TH ALIGN=LEFT>Agent</TH>";
  }
  print "<TH ALIGN=LEFT>Calls</TH>";
  print "<TH ALIGN=LEFT>Hold Time</TH>";
  print "<TH ALIGN=LEFT>Time/Call</TH>";
  print "<TH ALIGN=LEFT>Tot. Time</TH>";
  print "<TH ALIGN=LEFT>Std. Dev</TH>";
  print "</TR>\n<TR CLASS=list-color1>";
  $ccnt=0;
 
  while($r = pg_fetch_row($getcdr, $i)) {
    $rem=$ccnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color2";
    }
 
    if ($disp == "ANSWERED") {
      print "</TD><TD><A HREF=/auth/index.php?disppage=cdr/getrep.php";
      print "&tavg=" . $avg . "&thold=" . $havg . "&queue=" . $exten;
      print "&disp=" . urlencode($disp) . "&exten=" . $r[5];
      print $baseurl;
      print ">" . $r[5] . "</A></TD>";
    }
    print "<TD>" . $r[3] . "</TD><TD>";

    if (($r[4] > $havg*$exep) && ($exep > 0)) {
      print "<FONT COLOR=RED>";
    }

    print gtime($r[4]);
    if ($havg != "") {
      print "&nbsp;(" . gtime(abs($r[4]-$havg)) . ")";
    }

    print "</TD><TD>";
    if (($r[0] > $avg*$exep) && ($exep > 0)) {
      print "<FONT COLOR=RED>";
    }
    print gtime($r[0]);
    if ($avg != "") {
      print "&nbsp;(" . gtime(abs($r[0]-$avg)) . ")";
    }
    print "</TD>";
    print "<TD>" . gtime($r[1]) . "</TD>";
    print "<TD>" . gtime($r[2]);
    if ($dev != "") {
      print "&nbsp;(" . gtime(abs($r[2]-$dev)) . ")";
    }
    print "</TD>";
    print "</TR>\n<TR $bcolor>";
    $ccnt++;
  }
  if ((pg_num_rows($getcdr) == "0") && ($xexep == "on")) {
    print "<TR CLASS=list-color1><TH COLSPAN=5>No Exeptions Found</TH></TR>";
  }

  print "</TABLE>";
%>
