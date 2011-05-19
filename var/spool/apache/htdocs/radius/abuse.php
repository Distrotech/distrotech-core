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
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<FORM METHOD=POST>
<INPUT TYPE=HIDDEN NAME=disppage VALUE=radius/radabuse.php>
  <tr CLASS=list-color2><TH CLASS=heading-body COLSPAN=3>Radius Abuse Tracking</TH></TR>
  <tr CLASS=list-color1><TH COLSPAN=3 CLASS=heading-body2>Select Date</TH></TR>
  <TR CLASS=list-color2>
      <TD WIDTH=33% ALIGN=MIDDLE>Year</TH>
      <TD WIDTH=33% ALIGN=MIDDLE>Month</TH>
      <TD WIDTH=33% ALIGN=MIDDLE>Day</TH>
 </TR> 
 <TR CLASS=list-color1>
      <TD ALIGN=MIDDLE><SELECT NAME=year>
<%
  include "opendb.inc";
  if (! $rdn) {
    include "../ldap/auth.inc";
  }
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=$ldn)(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) != 1) && ($PHP_AUTH_USER != "admin")) {
    $ulimit=" AND UserName = \"$PHP_AUTH_USER\"";
  }

  $curtime=localtime();
  $curtime[4]++;
  $curtime[3]--;
  $curyear=$curtime[5]+1900;

  $query=mysql_query("SELECT DISTINCT YEAR(AcctStartTime) AS Year
                      FROM radacct WHERE AcctStartTime != 0 AND AcctStopTime != 0$ulimit
                      ORDER BY Year");

  while(list($syear)=mysql_fetch_row($query)) {
    if ((($syear == $curyear) && ($year == "")) || ($year == $syear)){
      print "<OPTION VALUE=\"" . $syear . "\" SELECTED>" . $syear; 
      $curyset=true;
    } else {
      print "<OPTION VALUE=\"" . $syear . "\">" . $syear; 
    }
  }
  if (!$curyset) {
      print "<OPTION VALUE=\"" . $curyear . "\" SELECTED>" . $curyear; 
  }
%>

      </SELECT></TD>
      <TD ALIGN=MIDDLE><SELECT NAME=month>
<%
  for($smnt=1;$smnt <= 12;$smnt++) {
    if ((($smnt == $curtime[4]) && ($month == "")) || ($month == $smnt)){
      print "<OPTION VALUE=" . $smnt . " SELECTED>" . $smnt;
    } else {
      print "<OPTION VALUE=" . $smnt . ">" . $smnt;
    }
  }
%>
      </SELECT></TD>
      <TD ALIGN=MIDDLE><SELECT NAME=day>
<%
  for($sday=1;$sday <= 31;$sday++) {
    if ((($sday == $curtime[3]) && ($day == "")) || ($day == $sday)) {
      print "<OPTION VALUE=" . $sday . " SELECTED>" . $sday;
    } else {
      print "<OPTION VALUE=" . $sday . ">" . $sday;
    }
  }
%>
      </SELECT></TD></TR>

      <tr CLASS=list-color2><TH CLASS=heading-body2 COLSPAN=3>Select Time</TH></TR>
      <TR CLASS=list-color1><TD ALIGN=MIDDLE>Hour</TD><TD ALIGN=MIDDLE>Min</TD><TD ALIGN=MIDDLE>Sec.</TH></TR>
      <TR CLASS=list-color2>

      <TD ALIGN=MIDDLE><SELECT NAME=hour>
<%
  for($shour=0;$shour <= 23;$shour++) {
    print "<OPTION VALUE=" . $shour;
    if ($shour == $hour) {
      print " SELECTED";
    }
    print ">" . $shour;
  }
%>
      </SELECT></TD>
      <TD ALIGN=MIDDLE><SELECT NAME=min>
<%
  for($smin=0;$smin <= 59;$smin++) {
    print "<OPTION VALUE=" . $smin;
    if ($smin == $min) {
      print " SELECTED";
    }
    print ">" . $smin;
  }
%>
      </SELECT></TD>
      <TD ALIGN=MIDDLE><SELECT NAME=sec>
<%
  for($ssec=0;$ssec <= 59;$ssec++) {
    print "<OPTION VALUE=" . $ssec;
    if ($ssec == $sec) {
      print " SELECTED";
    }
    print ">" . $ssec;
  }
%>
      </SELECT></TD></TR>



      <TR CLASS=list-color1><TD ALIGN=MIDDLE>IP Address</TD><TD>&nbsp;</TD><TD ALIGN=MIDDLE>
      <INPUT NAME="ipaddr" VALUE="<% print $ipaddr;%>"></TD></TR>
      <TR CLASS=list-color2><TD COLSPAN=3 ALIGN=MIDDLE>
      <INPUT TYPE=SUBMIT NAME="Show Usage" VALUE="Search For User"></TD></TR>
      </FORM>
      </TABLE>
