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

require_once "auth.inc";

if ($ADMIN_USER != "admin") {
  return;
}

  $qlistq="SELECT name,CASE WHEN (description != '') THEN description ELSE name END FROM queue_table";
  if ($SUPER_USER != 1) {
    $qlistq.=" LEFT OUTER JOIN astdb AS bgrp ON ('Q'||name=bgrp.family AND bgrp.key='BGRP') WHERE " . $clogacl;
  }
 $qlistq.=" ORDER by description";
%>
<CENTER>
<FORM METHOD=POST NAME=acdrep onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cdr/acdstat.php">
<INPUT TYPE=HIDDEN NAME=slog VALUE=time>
<TABLE WIDTH=30% CELLPADDING=0 CELLSPACING=0>
<TR><TD><FONT SIZE=1>
<TABLE WIDTH=100% CELLSPACING=0 CELLPADING=0>
<TR CLASS=list-color2>
<TH COLSPAN=3 CLASS=heading-body>ACD Queue Status</TH></TR>
<TR CLASS=list-color1>
<TH COLSPAN=3 CLASS=heading-body2>Display Queue Status From</TH></TR>
<TR CLASS=list-color2>
<TH><FONT SIZE=1>Day</TH>
<TH><FONT SIZE=1>Month</TH>
<TH><FONT SIZE=1>Year</TH></TR>
<TR CLASS=list-color1><TD><FONT SIZE=1>
  <SELECT NAME=time_day>
<%
  if (! isset($slog)) {
    $cur_date=getdate();

    if ($cur_date['hours'] < 1) {
      $cur_date['mday']--;
    }
  }
  for($dom=1;$dom <= 31;$dom++) {
    if (($dom != $cur_date['mday']) && ($time_day != $dom)){
      print "    <OPTION VALUE=$dom>$dom\n";
    } else {
      print "    <OPTION SELECTED VALUE=$dom>$dom\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=time_month>
<%
  for($month=1;$month <= 12;$month++) {
    if (($month != $cur_date['mon']) && ($time_month != $month)){
      print "    <OPTION VALUE=$month>$month\n";
    } else {
      print "    <OPTION SELECTED VALUE=$month>$month\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=time_year>
<%
  for($year=2000;$year <= 2050;$year++) {
    if (($year != $cur_date['year']) && ($year != $time_year)){
      print "    <OPTION VALUE=$year>$year\n";
    } else {
      print "    <OPTION SELECTED VALUE=$year>$year\n";
    }
  }
%>
  </SELECT>
</TD></TR>
<TR CLASS=list-color2 WIDTH=100%>
<TH><FONT SIZE=1>Hour</TH>
<TH><FONT SIZE=1>Minute</TH>
<TH><FONT SIZE=1>Second</TH></TR>
<TR CLASS=list-color1><TD><FONT SIZE=1>
  <SELECT NAME=time_hour>
<%
  for($hour=0;$hour < 24;$hour++) {
    print "    <OPTION VALUE=$hour";
    if ($hour == $time_hour) {
      print " SELECTED";
    }
    print ">$hour\n";
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=time_min>
<%
  for($minute=0;$minute < 60;$minute++) {
    print "    <OPTION VALUE=$minute";
    if ($minute == $time_min) {
      print " SELECTED";
    }
    print ">$minute\n";
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=time_sec>
<%
  for($second=0;$second < 60;$second++) {
    print "    <OPTION VALUE=$second";
    if ($second == $time_sec) {
      print " SELECTED";
    }
    print ">$second\n";
  }
%>
  </SELECT>
</TABLE></TD></TR>
<TR><TD><FONT SIZE=1>
<TABLE WIDTH=100% CELLSPACING=0 CELLPADING=0>
<TR CLASS=list-color2>
<TH COLSPAN=3 CLASS=heading-body2>To</TH></TR>
<TR CLASS=list-color1>
<TH><FONT SIZE=1>Day</TH>
<TH><FONT SIZE=1>Month</TH>
<TH><FONT SIZE=1>Year</TH></TR>
<TR CLASS=list-color2><TD><FONT SIZE=1>
  <SELECT NAME=mtime_day>
<%
  if (! isset($slog)) {
    $cur_date=getdate();
  }
  for($dom=1;$dom <= 31;$dom++) {
    if (($dom != $cur_date['mday']) && ($dom != $mtime_day)){
      print "    <OPTION VALUE=$dom>$dom\n";
    } else {
      print "    <OPTION SELECTED VALUE=$dom>$dom\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=mtime_month>
<%
  for($month=1;$month <= 12;$month++) {
    if (($month != $cur_date['mon']) && ($mtime_month != $month)){
      print "    <OPTION VALUE=$month>$month\n";
    } else {
      print "    <OPTION SELECTED VALUE=$month>$month\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=mtime_year>
<%
  for($year=2000;$year <= 2050;$year++) {
    if (($year != $cur_date['year']) && ($year != $mtime_year)){
      print "    <OPTION VALUE=$year>$year\n";
    } else {
      print "    <OPTION SELECTED VALUE=$year>$year\n";
    }
  }
%>
  </SELECT>
</TD></TR>
<TR CLASS=list-color1 WIDTH=100%>
<TH><FONT SIZE=1>Hour</TH>
<TH><FONT SIZE=1>Minute</TH>
<TH><FONT SIZE=1>Second</TH></TR>
<TR CLASS=list-color2><TD><FONT SIZE=1>
  <SELECT NAME=mtime_hour>
<%
  for($hour=0;$hour < 24;$hour++) {
    if (($hour != $cur_date['hours']) && ($mtime_hour != $hour)){
      print "    <OPTION VALUE=$hour>$hour\n";
    } else {
      print "    <OPTION SELECTED VALUE=$hour>$hour\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=mtime_min>
<%
  for($minute=0;$minute < 60;$minute++) {
    if (($minute != $cur_date['minutes']) && ($mtime_min != $minute)) {
      print "    <OPTION VALUE=$minute>$minute\n";
    } else {
      print "    <OPTION SELECTED VALUE=$minute>$minute\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=mtime_sec>
<%
  for($second=0;$second < 60;$second++) {
    if (($second != $cur_date['seconds']) && ($mtime_sec != $second)){
      print "    <OPTION VALUE=$second>$second\n";
    } else {
      print "    <OPTION SELECTED VALUE=$second>$second\n";
    }
  }
%>
  </SELECT>
</TABLE></TD></TR>


<TR><TD><FONT SIZE=1><TABLE WIDTH=100% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color1>
<TH COLSPAN=2 CLASS=heading-body2>Options</TH></TR>

<TR CLASS=list-color2>
<TD>ACD To Check</TD>
<TD><SELECT NAME=fqueue>
<OPTION VALUE="">Any</OPTION>
<%
  if ($SUPER_USER == 1) {
    print "<OPTION VALUE=799>Default Ring All Queue</OPTION>\n";
  }
  $qlist=pg_query($db,$qlistq);

  for($i=0;$i < pg_num_rows($qlist);$i++) {
    $r=pg_fetch_row($qlist, $i);
    print "<OPTION VALUE=\"" . $r[0] . "\">" . $r[1] . "</OPTION>\n";
  }
%>
</SELECT>
</TD></TR>

</TABLE>

<TR CLASS=list-color1><TD ALIGN=MIDDLE><INPUT TYPE=SUBMIT VALUE="Display Data"></TD></TR>
</FORM>
</TABLE>
