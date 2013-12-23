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
  $getcdr=pg_query($db,"SELECT date_part('month',calldate) AS month,
                               count(accountcode) AS callcnt,
                               sum(billsec),avg(billsec)
                             from cdr where 
                               userfield != '' AND $chan AND disposition='ANSWERED' AND 
                               length(accountcode) = 4  AND date_part('year',calldate) = '$date'
                             group by month 
                             order by month,callcnt DESC");
  print "<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>\n<TR CLASS=list-color2>";
  print "<TH ALIGN=LEFT>Month</TH>";
  print "<TH ALIGN=LEFT>Number Of Calls</TH>";
  print "<TH ALIGN=LEFT>Total Length Of Calls</TH>";
  print "<TH ALIGN=LEFT>Average</TH>";
  print "</TR>\n<TR CLASS=list-color1>";
  $ccnt=0;
 
  while($r = pg_fetch_row($getcdr, $i)) {
    $rem=$ccnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color2";
    }
 

    print "<TD><A HREF=/auth/index.php?disppage=cdr/allext.php&date=" . $r[0] . "%2F" . $date . "&type=" . $type . ">" . $r[0] . "</A></TD>";
    print "<TD>" . $r[1] . "</TD>";
    print "<TD>" . gtime($r[2]) . "</TD>";
    print "<TD>" . gtime($r[3]) . "</TD>";
    print "</TR>\n<TR $bcolor>";
    $ccnt++;
  }
  print "</TABLE>";
%>
