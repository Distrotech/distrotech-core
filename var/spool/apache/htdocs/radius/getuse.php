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

  include "../radius/opendb.inc";
  if ($rdn == "") {
    include "../ldap/auth.inc";
  }

  $curtime=localtime();
  $curtime[4]++;
  $curyear=$curtime[5]+1900;

  $curmon=$curyear . "," . $curtime[4];

%>
<FORM METHOD=POST NAME=raduse onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE WIDTH=90% CELLSPACING=0 CELLPADDING=0>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="radius/raduse.php">
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>Display User Usage</TH></TR>
<%

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=$ldn)(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) != 1) && ($PHP_AUTH_USER != "admin")) {
    $ulimit=" AND (UserName LIKE '$PHP_AUTH_USER@%' OR UserName = '$PHP_AUTH_USER')";
    print "<INPUT TYPE=HIDDEN NAME=sort VALUE=UserName>\n";
    print "<TR CLASS=list-color1><TH COLSPAN=2>Only Displaying User " . $PHP_AUTH_USER . "</TH></TR>";
    
  } else {
%>
      <TR CLASS=list-color1><TD WIDTH=50% ALIGN=LEFT>Order By</TH><TD>
      <SELECT NAME=sort>
        <OPTION VALUE=UserName>User Name</OPTION>
        <OPTION VALUE="TONLINE DESC">Total Time On Line</OPTION>
        <OPTION VALUE="SESCOUNT DESC">Session Count</OPTION>
        <OPTION VALUE="BYTESIN DESC">Bytes In</OPTION>
        <OPTION VALUE="BYTESOUT DESC">Bytes Out</OPTION>
      </SELECT></TD></TR>
<%
  }
  $queryq="SELECT DISTINCT date_part('year',AcctStartTime) AS Year,date_part('month',AcctStartTime) AS Month 
                      FROM radacct WHERE AcctStartTime != 0 AND AcctStopTime != 0$ulimit
                      ORDER BY Year,Month";
%>
      <TR CLASS=list-color2><TD ALIGN=LEFT WIDTH=50%>Month To View</TH><TD>
      <SELECT NAME=time><BR>
<%
  $query=pg_query($db,$queryq);
  $num=pg_num_rows($query);
  for($i=0;$i < $num;$i++) {
    $res=pg_fetch_row($query,$i);
    $stime=$res[0] . "," . $res[1];
    if ($stime != $curmon) {
      print "<OPTION VALUE=\"" . $stime . "\"";
      if ($stime == $time) {
        print " SELECTED";
        $curmfound=1;
      }
      print ">" . $res[0] . "/" . $res[1] . "\n"; 
    }
  }
  if (! $curmfound) {
    print "<OPTION VALUE=\"" . $curmon . "\" SELECTED>" . $curyear . "/" . $curtime[4]; 
  } else {
    print "<OPTION VALUE=\"" . $curmon . "\">" . $curyear . "/" . $curtime[4]; 
  }
%>

      </SELECT></TD></TR>
      <TR CLASS=list-color1><TD ALIGN=LEFT WIDTH=50%>Rows To Show</TH><TD>
      <SELECT NAME=showrows>
        <OPTION VALUE=20>20
        <OPTION VALUE=40>40
        <OPTION VALUE=80>80
        <OPTION VALUE=100>100
        <OPTION VALUE=ALL>All
      </SELECT></TD></TR>
      <TR CLASS=list-color2><TD COLSPAN=2 ALIGN=MIDDLE>
      <INPUT TYPE=SUBMIT NAME="Show Usage"></TD></TR>
      </FORM>
</table>
