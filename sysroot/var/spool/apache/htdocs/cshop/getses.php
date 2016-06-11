<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
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
if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}

if (($_POST['year'] != "" ) || ($_POST['month'] != "") || ($_POST['day'] != "")) {
  $sesions=pg_query($db,"SELECT to_char(saletime,'YY/MM/DD HH24:MI:SS'),
                                sale.username,fullname,users.username,saletime FROM sale
                         LEFT OUTER JOIN users ON (sale.cardid=users.uniqueid) 
                         LEFT OUTER JOIN reseller ON (reseller.id= " . $_SESSION['resellerid'] . " OR reseller.owner= " . $_SESSION['resellerid'] . ") 
                         WHERE saletype = 'Session Start' AND 
                               ((agentid=reseller.id AND admin = 't' AND reseller.id=" . $_SESSION['resellerid'] . ") OR
                                (agentid=owner AND admin = 'f' AND reseller.id=" . $_SESSION['resellerid'] . ")) AND
                               date_part('year',saletime) = '" . $_POST['year'] . "' AND
                               date_part('month',saletime) = '" . $_POST['month'] . "' AND
                               date_part('day',saletime) = '" . $_POST['day'] . "'");

  $num=pg_num_rows($sesions); 
  $bcolor[0]="list-color2";
  $bcolor[1]="list-color1";?>

<TR CLASS=list-color2>
<TH COLSPAN=4 CLASS=heading-body>All Sessions For <?php print $_POST['year'] . "/" . $_POST['month'] . "/" . $_POST['day'];?></TH></TR>
<TR CLASS=list-color1>
<TH ALIGN=LEFT CLASS=heading-body2>Time</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Session ID</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Booth</TH>
<TH ALIGN=LEFT CLASS=heading-body2>Number</TH>
</TR><?php

  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_row($sesions,$i);
    $rem=$i % 2; 
    print "<TR CLASS=" . $bcolor[$rem] . ">";
    $r[0]="<A HREF=/end.php?sesname=" . 
           urlencode($r[1]) . "&cno=" . urlencode($r[3]) . "&stime=" . urlencode($r[4]) . " TARGET=_blank>" . $r[0] . "</A>";
    for ($j=0;$j < count($r)-1;$j++) {
      print  "<TD>" . $r[$j] . "</TD>";
    }
    print "</TR>\n";
  }
} else {
  $today=getdate();
  $_POST['year']=$today['year'];
  $_POST['day']=$today['mday'];
  $_POST['month']=$today['mon'];
?>
<FORM METHOD=post>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Show All Sessions For Day</TH></TR>
<TR CLASS=list-color1>
<TD ALIGN=LEFT WIDTH=50%>Year</TD><TD><SELECT NAME=year><?php
  $yearq=pg_query($db,"SELECT DISTINCT date_part('year',saletime) AS year FROM sale ORDER BY year DESC");
  for($i=0;$i < pg_num_rows($yearq);$i++) {
    $r=pg_fetch_row($yearq,$i);
    print "<OPTION VALUE=" . $r[0];
    if ($year == $r[0]) {
      print " SELECTED";
    }
    print ">" . $r[0] . "</OPTION>\n";
  }
?></SELECT></TD></TR>

<TR CLASS=list-color2>
<TD ALIGN=LEFT>Month</TD><TD><SELECT NAME=month><?php
  for($i=1;$i <= 12;$i++) {
    print "<OPTION VALUE=" . $i;
    if ($_POST['month'] == $i) {
      print " SELECTED";
    }
    print ">" . $i . "</OPTION>\n";
  }
?></SELECT></TD></TR>

<TR CLASS=list-color1>
<TD ALIGN=LEFT>Day</TD><TD><SELECT NAME=day><?php
  for($i=1;$i <= 31;$i++) {
    print "<OPTION VALUE=" . $i;
    if ($_POST['day'] == $i) {
      print " SELECTED";
    }
    print ">" . $i . "</OPTION>\n";
  }
?></SELECT></TD>
</TR>
<TR CLASS=list-color2>
<TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT VALUE="Show Sessions"></TH></TR>
</FORM><?php
}
?>
</TABLE>
