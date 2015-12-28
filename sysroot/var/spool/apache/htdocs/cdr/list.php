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
  $month=split("/",$date);

  if ($ADMIN_USER != "admin") {
    return;
  }
?>
<CENTER>
<FORM METHOD=POST NAME=crepform onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>Call Reporting</TH></TR>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cdr/allext.php">
<TR CLASS=list-color1><TD>
  Month
</TD><TD>
<SELECT NAME=date>
<?php
  $getcdr=pg_query($db,"SELECT date_part('year', min(calldate)),date_part('month', min(calldate)),
                               date_part('year', max(calldate)),date_part('month', max(calldate)) from cdr");

  $r=pg_fetch_row($getcdr, $i);
  for ($year=$r[0];$year <= $r[2];$year++) {
    for($mon=1;$mon <= 12;$mon++) {
      if ((($year == $r[0]) && ($mon < $r[1])) ||
          (($year == $r[2]) && ($mon > $r[3]))) {
        continue;
      }
      print "<OPTION VALUE=\"" . $mon . "/" . $year . "\"";
      if ((($dtime['year'] == $year) && ($dtime['mon'] == $mon) && (! isset($date))) ||
          (($month[0] == $year) && ($month[1] == $mon) && (isset($date)))) {
        print " SELECTED";
        $mqset="1";
      } else if (($dtime['year'] == $year) && ($dtime['mon'] == $mon) && ($mqset != "1")) {
        print " SELECTED";
      }
      print ">" . $year . "/" . $mon . "\n"; 
    }
  }
?>
</SELECT></TD></TR>
<TR CLASS=list-color2><TD>
  Query
</TD><TD>
<SELECT NAME=type>
<OPTION VALUE=0<?php if ($type == "0") {print " SELECTED";}?>>All Calls
<OPTION VALUE=3<?php if ($type == "3") {print " SELECTED";}?>>Internal Calls
<OPTION VALUE=4<?php if (($type == "4") || ($type == "")) {print " SELECTED";}?>>National Calls
<OPTION VALUE=9<?php if ($type == "9") {print " SELECTED";}?>>Cellular Calls
<OPTION VALUE=8<?php if ($type == "8") {print " SELECTED";}?>>International Calls
<OPTION VALUE=1<?php if ($type == "1") {print " SELECTED";}?>>Inter Branch Calls
<OPTION VALUE=7<?php if ($type == "7") {print " SELECTED";}?>>Prepaid Users Calls
<OPTION VALUE=5<?php if ($type == "5") {print " SELECTED";}?>>Incoming Calls
<OPTION VALUE=6<?php if ($type == "6") {print " SELECTED";}?>>Call Queues
</SELECT></TD></TR>
<TR CLASS=list-color1><TD>
Order Results By
</TD><TD>
<SELECT NAME=morder>
<OPTION VALUE="accountcode"<?php if ($morder == "accountcode") {print " SELECTED";}?>>Source
<OPTION VALUE="callcnt"<?php if ($morder == "callcnt") {print " SELECTED";}?>>Number Of Calls
<OPTION VALUE="tottime"<?php if (($morder == "tottime") || ($morder == "")){print " SELECTED";}?>>Total Time
<OPTION VALUE="avtime"<?php if ($morder == "avtime") {print " SELECTED";}?>>Average Time
<OPTION VALUE="dv8"<?php if ($morder == "dv8") {print " SELECTED";}?>>Standard Deviation
<OPTION VALUE="holdtime"<?php if ($morder == "holdtime") {print " SELECTED";}?>>Av. Hold Time
<OPTION VALUE="disposition"<?php if ($morder == "disposition") {print " SELECTED";}?>>Call State
</SELECT></TD></TR>
<TR CLASS=list-color2><TD>
Highlight Results Differing By
</TD><TD>
<SELECT NAME=exep>
<OPTION VALUE="0">No Exeptions
<?php
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
?>
</SELECT></TD></TR>
<TR CLASS=list-color1><TD>
Descending Order</TD><TD>
<INPUT TYPE=CHECKBOX NAME=mweight CHECKED>
</TD></TR>
<TR CLASS=list-color2><TD>
Exceptions Only</TD><TD>
<INPUT TYPE=CHECKBOX NAME=xexep<?php if ($xexep == "on") {print " CHECKED";}?>>
</TD></TR>
<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=MIDDLE>
<INPUT TYPE=SUBMIT>
</TD></TR>
</FORM>
</td></tr>
</table>
