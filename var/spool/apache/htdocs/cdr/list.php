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
  $month=split("/",$date);

  if ($ADMIN_USER != "admin") {
    return;
  }
%>
<CENTER>
<FORM METHOD=POST NAME=crepform onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>Call Reporting</TH></TR>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cdr/allext.php">
<TR CLASS=list-color1><TD>
  Month
</TD><TD>
<SELECT NAME=date>
<%
  $getcdr=pg_query($db,"SELECT date_part('month',calldate) AS month,
                               date_part('year',calldate) AS year
                             from cdr where 
                               userfield != '' AND dstchannel != '' AND disposition='ANSWERED' AND 
                               length(accountcode) = 4
                             group by year,month
                             order by year,month");

  for($i=0;$i<pg_num_rows($getcdr);$i++) {
    $r=pg_fetch_row($getcdr, $i);
    print "<OPTION VALUE=\"" . $r[0] . "/" . $r[1] . "\"";
    if ((($dtime['year'] == $r[1]) && ($dtime['mon'] == $r[0]) && (! isset($date))) ||
        (($month[0] == $r[0]) && ($month[1] == $r[1]) && (isset($date)))) {
      print " SELECTED";
      $mqset="1";
    } else if (($dtime['year'] == $r[1]) && ($dtime['mon'] == $r[0]) && ($mqset != "1")) {
      print " SELECTED";
    }
    print ">" . $r[0] . "/" . $r[1] . "\n"; 
  }
%>
</SELECT></TD></TR>
<TR CLASS=list-color2><TD>
  Query
</TD><TD>
<SELECT NAME=type>
<OPTION VALUE=0<%if ($type == "0") {print " SELECTED";}%>>All Calls
<OPTION VALUE=3<%if ($type == "3") {print " SELECTED";}%>>Internal Calls
<OPTION VALUE=4<%if (($type == "4") || ($type == "")) {print " SELECTED";}%>>National Calls
<OPTION VALUE=9<%if ($type == "9") {print " SELECTED";}%>>Cellular Calls
<OPTION VALUE=8<%if ($type == "8") {print " SELECTED";}%>>International Calls
<OPTION VALUE=1<%if ($type == "1") {print " SELECTED";}%>>Inter Branch Calls
<OPTION VALUE=7<%if ($type == "7") {print " SELECTED";}%>>Prepaid Users Calls
<OPTION VALUE=5<%if ($type == "5") {print " SELECTED";}%>>Incoming Calls
<OPTION VALUE=6<%if ($type == "6") {print " SELECTED";}%>>Call Queues
</SELECT></TD></TR>
<TR CLASS=list-color1><TD>
Order Results By
</TD><TD>
<SELECT NAME=morder>
<OPTION VALUE="accountcode"<%if ($morder == "accountcode") {print " SELECTED";}%>>Source
<OPTION VALUE="callcnt"<%if ($morder == "callcnt") {print " SELECTED";}%>>Number Of Calls
<OPTION VALUE="tottime"<%if (($morder == "tottime") || ($morder == "")){print " SELECTED";}%>>Total Time
<OPTION VALUE="avtime"<%if ($morder == "avtime") {print " SELECTED";}%>>Average Time
<OPTION VALUE="dv8"<%if ($morder == "dv8") {print " SELECTED";}%>>Standard Deviation
<OPTION VALUE="holdtime"<%if ($morder == "holdtime") {print " SELECTED";}%>>Av. Hold Time
<OPTION VALUE="disposition"<%if ($morder == "disposition") {print " SELECTED";}%>>Call State
</SELECT></TD></TR>
<TR CLASS=list-color2><TD>
Highlight Results Differing By
</TD><TD>
<SELECT NAME=exep>
<OPTION VALUE="0">No Exeptions
<%
if (! isset($exep)) {
  $exep=35;
}
for ($pct=5;$pct <= 100;$pct=$pct+5) {
  print "<OPTION VALUE=" . $pct;
  if (($exep == $pct) || (1 + $pct/100 == $exep)){
    print " SELECTED";
  }
  print ">" . $pct . "%\n";
}
%>
</SELECT></TD></TR>
<TR CLASS=list-color1><TD>
Descending Order</TD><TD>
<INPUT TYPE=CHECKBOX NAME=mweight CHECKED>
</TD></TR>
<TR CLASS=list-color2><TD>
Exceptions Only</TD><TD>
<INPUT TYPE=CHECKBOX NAME=xexep<%if ($xexep == "on") {print " CHECKED";}%>>
</TD></TR>
<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=MIDDLE>
<INPUT TYPE=SUBMIT>
</TD></TR>
</FORM>
</td></tr>
</table>
