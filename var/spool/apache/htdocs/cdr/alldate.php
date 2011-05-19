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
  $db=pg_connect("host=127.0.0.1 dbname=asterisk user=asterisk password=asterisk");
  $getcdr=pg_query($db,"SELECT date_part('year',calldate) AS year,
                               date_part('month',calldate) AS month,
                               count(accountcode) AS callcnt,accountcode  
                             from cdr where 
                               userfield != '' AND $chan AND disposition='ANSWERED'
                             group by year,month,accountcode 
                             order by year,month,callcnt DESC");

  print "<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>\n<TR CLASS=list-color2>";
  print "<TH ALIGN=LEFT>Year</TH><TH ALIGN=LEFT>Month</TH>";
  print "<TH ALIGN=LEFT>Number Of Calls</TH><TH ALIGN=LEFT>Extension</TH>";
  print "</TR>\n<TR CLASS=list-color1>";
  $ccnt=0;
 
  while($r = pg_fetch_row($getcdr, $i)) {
    $rem=$ccnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color2";
    }
 

    print "<TD>" . $r[0] . "</TD><TD>" . $r[1] . "</TD><TD>" . $r[2];
    print "</TD><TD><A HREF=/auth/index.php?disppage=cdr/getrep.php&month=" . $r[1] . "&year=" . $r[0] . "&exten=" . $r[3] . ">";
    print $r[3] . "</A></TD>";
    print "</TR>\n<TR $bcolor>";
    $ccnt++;
  }
  print "</TABLE>";
%>
