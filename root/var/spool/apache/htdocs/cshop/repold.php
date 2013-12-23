<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
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

if (($year != "" ) && ($month == "")) {
  $report=pg_query($db,"SELECT DISTINCT date_part('month',saletime) AS month,
                           saletype,sum(credit) AS credit,avg(credit),count(credit) 
                         FROM sale
                         LEFT OUTER JOIN resellercard ON (resellercard.cardid=sale.cardid)
                         WHERE date_part('year',saletime) = '" . $year . "' 
                         AND resellerid = " . $_SESSION['resellerid'] . "
                         GROUP BY month,saletype ORDER BY month,credit DESC");
  $num=pg_num_rows($report); 
  $bcolor[0]="list-color1";
  $bcolor[1]="list-color2";%>

<TR CLASS=list-color2>
<TH ALIGN=LEFT>Month</TH>
<TH ALIGN=LEFT>Trans. Type</TH>
<TH ALIGN=LEFT>Credit</TH>
<TH ALIGN=LEFT>Average</TH>
<TH ALIGN=LEFT>Number</TH>
</TR><%

  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_row($report,$i);
    $rem=$i % 2; 
    print "<TR CLASS=" . $bcolor[$rem] . ">";
    $r[0]="<A HREF=/index.php?navpage=cshop/navbar.php&disppage=cshop/report.php&resellerid=" . $_SESSION['resellerid'] . "&year=" . 
           $year . "&month=" . $r[0] . ">" . $r[0] . "</A>";
    for ($j=0;$j < count($r);$j++) {
      print  "<TD>";
      if (($j == 2) || ($j == 3)) {
        if ($r[$j] < 0) {
          print "<FONT COLOR=RED>";
	  $r[$j]=sprintf("R%0.2f",abs($r[$j])/100);
        } else {
          $r[$j]=sprintf("R%0.2f",$r[$j]/100);
        }
      }
      print $r[$j];
      print "</TD>";
    }
    print "</TR>\n";
  }
} else if (($month != "") && ($day == "")) {
  $report=pg_query($db,"SELECT DISTINCT date_part('day',saletime) AS day,
                           saletype,sum(credit) AS credit,avg(credit),count(credit) 
                         FROM sale
                         LEFT OUTER JOIN resellercard ON (resellercard.cardid=sale.cardid)
                         WHERE date_part('year',saletime) = '" . $year . "' AND 
                               date_part('month',saletime) = '" . $month . "' AND 
                               resellerid = " . $_SESSION['resellerid'] . "
                         GROUP BY day,saletype ORDER BY day,credit DESC");
  $num=pg_num_rows($report); 
  $bcolor[0]="list-color1";
  $bcolor[1]="list-color2";%>

<TR CLASS=list-color2>
<TH ALIGN=LEFT>Day</TH>
<TH ALIGN=LEFT>Trans. Type</TH>
<TH ALIGN=LEFT>Credit</TH>
<TH ALIGN=LEFT>Average</TH>
<TH ALIGN=LEFT>Number</TH>
</TR><%

  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_row($report,$i);
    $rem=$i % 2; 
    print "<TR CLASS=" . $bcolor[$rem] . ">";
    $r[0]="<A HREF=/index.php?navpage=cshop/navbar.php&disppage=cshop/report.php&resellerid=" . $_SESSION['resellerid'] . "&year=" . 
           $year . "&month=" . $month .  "&day=" . $r[0] . ">" . $r[0] . "</A>";
    for ($j=0;$j < count($r);$j++) {
      print  "<TD>";
      if (($j == 2) || ($j == 3)) {
        if ($r[$j] < 0) {
          print "<FONT COLOR=RED>";
	  $r[$j]=sprintf("R%0.2f",abs($r[$j])/100);
        } else {
          $r[$j]=sprintf("R%0.2f",$r[$j]/100);
        }
      }
      print $r[$j];
      print "</TD>";
    }
    print "</TR>\n";
  }
} else if ($day != "") {
  $report=pg_query($db,"SELECT DISTINCT date_part('hour',saletime) AS hour,
                           saletype,sum(credit) AS credit,avg(credit),count(credit) 
                         FROM sale
                         LEFT OUTER JOIN resellercard ON (resellercard.cardid=sale.cardid)
                         WHERE date_part('year',saletime) = '" . $year . "' AND 
                               date_part('month',saletime) = '" . $month . "' AND
                               date_part('day',saletime) = '" . $day . "' AND
                               resellerid = " . $_SESSION['resellerid'] . "
                         GROUP BY hour,saletype ORDER BY hour,credit DESC");
  $num=pg_num_rows($report); 
  $bcolor[0]="list-color1";
  $bcolor[1]="list-color2";%>

<TR CLASS=list-color2>
<TH ALIGN=LEFT>Hour</TH>
<TH ALIGN=LEFT>Trans. Type</TH>
<TH ALIGN=LEFT>Credit</TH>
<TH ALIGN=LEFT>Average</TH>
<TH ALIGN=LEFT>Number</TH>
</TR><%

  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_row($report,$i);
    $rem=$i % 2; 
    print "<TR CLASS=" . $bcolor[$rem] . ">";
/*
    $r[0]="<A HREF=/index.php?navpage=cshop/navbar.php&disppage=cshop/report.php&resellerid=" . $_SESSION['resellerid'] . "&year=" . 
           $year . "&month=" . $month .  "&day=" . $r[0] . ">" . $r[0] . "</A>";
*/
    for ($j=0;$j < count($r);$j++) {
      print  "<TD>";
      if (($j == 2) || ($j == 3)) {
        if ($r[$j] < 0) {
          print "<FONT COLOR=RED>";
	  $r[$j]=sprintf("R%0.2f",abs($r[$j])/100);
        } else {
          $r[$j]=sprintf("R%0.2f",$r[$j]/100);
        }
      }
      print $r[$j];
      print "</TD>";
    }
    print "</TR>\n";
  }
} else {
  $report=pg_query($db,"SELECT DISTINCT date_part('year',saletime) AS year,
                           saletype,sum(credit) AS credit,avg(credit),count(credit) 
                         FROM sale 
                         LEFT OUTER JOIN resellercard ON (resellercard.cardid=sale.cardid)
                           WHERE resellerid = " . $_SESSION['resellerid'] . "
                         GROUP BY year,saletype ORDER BY year,credit DESC");
  $num=pg_num_rows($report); 
  $bcolor[0]="list-color1";
  $bcolor[1]="list-color2";%>

<TR CLASS=list-color2>
<TH ALIGN=LEFT>Year</TH>
<TH ALIGN=LEFT>Trans. Type</TH>
<TH ALIGN=LEFT>Credit</TH>
<TH ALIGN=LEFT>Average</TH>
<TH ALIGN=LEFT>Number</TH>
</TR><%

  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_row($report,$i);
    $rem=$i % 2; 
    print "<TR CLASS=" . $bcolor[$rem] . ">";
    $r[0]="<A HREF=/index.php?navpage=cshop/navbar.php&disppage=cshop/report.php&resellerid=" . $_SESSION['resellerid'] . "&year=" . 
           $r[0] . ">" . $r[0] . "</A>";
    for ($j=0;$j < count($r);$j++) {
      print  "<TD>";
      if (($j == 2) || ($j == 3)) {
        if ($r[$j] < 0) {
          print "<FONT COLOR=RED>";
	  $r[$j]=sprintf("R%0.2f",abs($r[$j])/100);
        } else {
          $r[$j]=sprintf("R%0.2f",$r[$j]/100);
        }
      }
      print $r[$j];
      print "</TD>";
    }
    print "</TR>\n";
  }
}
%>
</TABLE>
