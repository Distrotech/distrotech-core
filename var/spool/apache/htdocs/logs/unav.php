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
%>
  <CENTER>
  <FORM METHOD=POST NAME=ulog onsubmit="ajaxsubmit(this.name);return false;">
  <INPUT TYPE=HIDDEN NAME=disppage VALUE="logs/ulog.php">
  <INPUT TYPE=HIDDEN NAME=slog VALUE=time>
  <TABLE WIDTH=30% CELLPADDING=0 CELLSPACING=0>
    <TR>
      <TD>
        <TABLE WIDTH=100% CELLSPACING=0 CELLPADING=0>
          <TR CLASS=list-color2>
            <TH COLSPAN=3 CLASS=heading-body>
              Display Exceptions From
            </TH>
          </TR>
          <TR CLASS=list-color1>
            <TH CLASS=heading-body2>Day</TH>
            <TH CLASS=heading-body2>Month</TH>
            <TH CLASS=heading-body2>Year</TH></TR>
          <TR CLASS=list-color2><TD>
            <SELECT NAME=time_day>
<%
  include "uloginc.php";
  if (! isset($slog)) {
    $cur_date=getdate();
    if ($cur_date['hours'] < 1) {
      $cur_date['mday']--;
    }
  }

  for($dom=1;$dom <= 31;$dom++) {
    if (($dom != $cur_date['mday']) && ($time_day != $dom)) {
      print "    <OPTION VALUE=$dom>$dom\n";
    } else {
      print "    <OPTION SELECTED VALUE=$dom>$dom\n";
    }
  }
%>
  </SELECT>
</TD><TD>
  <SELECT NAME=time_month>
<%
  $mon_name['1']="January";
  $mon_name['2']="Febuary";
  $mon_name['3']="March";
  $mon_name['4']="April";
  $mon_name['5']="May";
  $mon_name['6']="June";
  $mon_name['7']="July";
  $mon_name['8']="August";
  $mon_name['9']="September";
  $mon_name['10']="October";
  $mon_name['11']="November";
  $mon_name['12']="December";

  $mon_days['1']="31";
  $mon_days['2']="28";
  $mon_days['3']="31";
  $mon_days['4']="30";
  $mon_days['5']="31";
  $mon_days['6']="30";
  $mon_days['7']="31";
  $mon_days['8']="31";
  $mon_days['9']="30";
  $mon_days['10']="31";
  $mon_days['11']="30";
  $mon_days['12']="31";

  for($month=1;$month <= 12;$month++) {
    if (($month != $cur_date['mon']) && ($time_month != $month)){
      print "    <OPTION VALUE=$month>$month\n";
    } else {
      print "    <OPTION SELECTED VALUE=$month>$month\n";
    }
  }
%>
  </SELECT>
</TD><TD>
  <SELECT NAME=time_year>
<%
  for($year=2000;$year <= 2050;$year++) {
    if (($year != $cur_date['year']) && ($time_year != $year)){
      print "    <OPTION VALUE=$year>$year\n";
    } else {
      print "    <OPTION SELECTED VALUE=$year>$year\n";
    }
  }
%>
  </SELECT>
</TD></TR>
<TR CLASS=list-color1 WIDTH=100%>
<TH CLASS=heading-body2>Hour</TH>
<TH CLASS=heading-body2>Minute</TH>
<TH CLASS=heading-body2>Second</TH></TR>
<TR CLASS=list-color2><TD><FONT SIZE=1>
  <SELECT NAME=time_hour>
<%
  for($hour=0;$hour < 24;$hour++) {
    if (($hour != $cur_date['hours']-1) && ($hour != $time_hour)){
      print "    <OPTION VALUE=$hour>$hour\n";
    } else {
      print "    <OPTION SELECTED VALUE=$hour>$hour\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=time_min>
<%
  for($minute=0;$minute < 60;$minute++) {
    if (($minute != $cur_date['minutes']) && ($minute != $time_min)){
      print "    <OPTION VALUE=$minute>$minute\n";
    } else {
      print "    <OPTION SELECTED VALUE=$minute>$minute\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=time_sec>
<%
  for($second=0;$second < 60;$second++) {
    if (($second != $cur_date['seconds']) && ($second != $time_sec)){
      print "    <OPTION VALUE=$second>$second\n";
    } else {
      print "    <OPTION SELECTED VALUE=$second>$second\n";
    }
  }
%>
  </SELECT>
</TABLE></TD></TR>
<TR><TD>
<TABLE WIDTH=100% CELLSPACING=0 CELLPADING=0>
<TR CLASS=list-color1>
<TH COLSPAN=3 CLASS=heading-body2>To</TH></TR>
<TR CLASS=list-color2>
<TH CLASS=heading-body2>Day</TH>
<TH CLASS=heading-body2>Month</TH>
<TH CLASS=heading-body2>Year</TH></TR>
<TR CLASS=list-color1><TD>
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
</TD><TD>
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
</TD><TD>
  <SELECT NAME=mtime_year>
<%
  for($year=2000;$year <= 2050;$year++) {
    if (($year != $cur_date['year']) && ($mtime_year != $year)){
      print "    <OPTION VALUE=$year>$year\n";
    } else {
      print "    <OPTION SELECTED VALUE=$year>$year\n";
    }
  }
%>
  </SELECT>
</TD></TR>
<TR CLASS=list-color2 WIDTH=100%>
<TH CLASS=heading-body2>Hour</TH>
<TH CLASS=heading-body2>Minute</TH>
<TH CLASS=heading-body2>Second</TH></TR>
<TR CLASS=list-color1><TD>
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
</TD><TD>
  <SELECT NAME=mtime_min>
<%
  for($minute=0;$minute < 60;$minute++) {
    if (($minute != $cur_date['minutes']) && ($mtime_min != $minute)){
      print "    <OPTION VALUE=$minute>$minute\n";
    } else {
      print "    <OPTION SELECTED VALUE=$minute>$minute\n";
    }
  }
%>
  </SELECT>
</TD><TD>
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
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body2>Options</TH></TR>
<TR CLASS=list-color1>
<TH CLASS=heading-body2>Direction</TH>
<TH CLASS=heading-body2>Protocol</TH></TR>
<TR CLASS=list-color2><TD><SELECT NAME=direction>
  <OPTION VALUE="">Any
  <OPTION VALUE="in" <%if ($direction == "in") {print selected;}%>>In
  <OPTION VALUE="out"<%if ($direction == "out") {print selected;}%>>Out
  <OPTION VALUE="fwd"<%if ($direction == "fwd") {print selected;}%>>Fwd
</SELECT></TD><TD><FONT SIZE=1><SELECT NAME=sproto>
  <OPTION VALUE="">Any
<%
  if ($sproto == "") {
    $sproto="";
  }
  $parr=$proto;
  while(list($idx,$val)=each($parr)) {
    print "<OPTION VALUE=$idx ";
    if (($sproto == $idx) && ($sproto != "")) {
      print "selected";
    }
    print ">$val\n";
  }
%>  
</SELECT>
</TD></TR>
<TR CLASS=list-color1>
<TH CLASS=heading-body2>Report</TH>
<TH CLASS=heading-body2>I.D. Shows</TH></TR>
<TR CLASS=list-color2>
<TD><SELECT NAME="type">
  <OPTION VALUE="">Both
  <OPTION VALUE="rep" <%if (($type == "rep") || (! isset($slog))){print selected;}%>>I.D.
  <OPTION VALUE="ip"<%if ($type == "ip") {print selected;}%>>Log
</SELECT></TD>

<TD><SELECT NAME="idtype">
  <OPTION VALUE="">Both
  <OPTION VALUE="0" <%if ($idtype == "0")  {print selected;}%>>Source
  <OPTION VALUE="1"<%if ($idtype == "1") {print selected;}%>>Dest.
</SELECT></TD>

</TR>
</TABLE></TD></TR>


<TR CLASS=list-color1><TD ALIGN=MIDDLE><INPUT TYPE=SUBMIT VALUE="Display Exceptions"></TD></TR>
</FORM>
<FORM METHOD=POST ACTION=/auth/index.php?disppage=logs/ulogdel.php NAME=DelForm>
<TR><TD>
<TABLE WIDTH=100% CELLSPACING=0 CELLPADING=0>
<TR CLASS=list-color2>
<TH COLSPAN=3>Delete Entries Before</TH></TR>
<TR CLASS=list-color1>
<TH><FONT SIZE=1>Day</TH>
<TH><FONT SIZE=1>Month</TH>
<TH><FONT SIZE=1>Year</TH></TR>
<TR CLASS=list-color2><TD><FONT SIZE=1>
  <SELECT NAME=dtime_day>
<%
  $cur_date=getdate();
  $dday=$cur_date['mday'];
  $dmon=$cur_date['mon']-1;
  if ($mon_days[$dmon] < $dday) {
    $dday=$mon_days[$dmon];
  }
  for($dom=1;$dom <= 31;$dom++) {
    if ($dom != $dday) {
      print "    <OPTION VALUE=$dom>$dom\n";
    } else {
      print "    <OPTION SELECTED VALUE=$dom>$dom\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=dtime_month>
<%
  for($month=1;$month <= 12;$month++) {
    if ($month != $cur_date['mon'] -1) {
      print "    <OPTION VALUE=$month>$month\n";
    } else {
      print "    <OPTION SELECTED VALUE=$month>$month\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=dtime_year>
<%
  for($year=2000;$year <= 2050;$year++) {
    if ($year != $cur_date['year']) {
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
  <SELECT NAME=dtime_hour>
<%
  for($hour=0;$hour < 24;$hour++) {
    if ($hour != $cur_date['hours']) {
      print "    <OPTION VALUE=$hour>$hour\n";
    } else {
      print "    <OPTION SELECTED VALUE=$hour>$hour\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=dtime_min>
<%
  for($minute=0;$minute < 60;$minute++) {
    if ($minute != $cur_date['minutes']) {
      print "    <OPTION VALUE=$minute>$minute\n";
    } else {
      print "    <OPTION SELECTED VALUE=$minute>$minute\n";
    }
  }
%>
  </SELECT>
</TD><TD><FONT SIZE=1>
  <SELECT NAME=dtime_sec>
<%
  for($second=0;$second < 60;$second++) {
    if ($second != $cur_date['seconds']) {
      print "    <OPTION VALUE=$second>$second\n";
    } else {
      print "    <OPTION SELECTED VALUE=$second>$second\n";
    }
  }
%>
  </SELECT>
</TABLE></TD></TR>
<SCRIPT>
function DelOld() {
  UserCheck=confirm("Warning\n\nYou Are About To Delete All Entries Before\n\n"+
                    document.DelForm.time_hour.value+":"+document.DelForm.time_min.value+":"+document.DelForm.time_sec.value+
                    " "+
                    document.DelForm.time_day.value+"/"+document.DelForm.time_month.value+"/"+document.DelForm.time_year.value+
                    "\n\nPress OK To Continue.");
  if (UserCheck) {
    document.DelForm.submit();
  }
}
</SCRIPT>
<TR CLASS=list-color1><TD ALIGN=MIDDLE><INPUT TYPE=BUTTON VALUE="Delete Entries" onclick=DelOld()></TD></TR>
</FORM>
</TABLE>
