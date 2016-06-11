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
include_once "auth.inc";


$cclist=pg_query($db,"SELECT DISTINCT ON (countryname,subcode) countryname,countrycode,prefix,subcode,peaksec*60/100000.00,offpeaksec*60/100000.00 from countryprefix left outer join country using (countrycode) left outer join intrates using (subcode,countrycode) WHERE validfrom is null OR (validfrom < now() and validto > now()) order by countryname,subcode,length(prefix)");

//prefix=substr('2676211260',1,length(prefix)) 
//DISTINCT on (countryname) countryname,countrycode,prefix from country left outer join countryprefix using (countrycode) where prefix is not null order by countryname,length(prefix)");

$bcolor[0]="list-color2";
$bcolor[1]="list-color1";

?>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH CLASS=heading-body COLSPAN=5><?php print _("Country Code List");?></TH>
</TR>
<TR CLASS=list-color1>
<TH CLASS=heading-body2 WIDTH=60%><?php print _("Country");?></TH>
<TH CLASS=heading-body2 WIDTH=10%><?php print _("Code");?></TH>
<TH CLASS=heading-body2 WIDTH=10%><?php print _("Prefix");?></TH>
<TH CLASS=heading-body2 WIDTH=10%><?php print _("Peak");?></TH>
<TH CLASS=heading-body2 WIDTH=10%><?php print _("Offpeak");?></TH>
</TR>
<?php
$num=pg_num_rows($cclist);
for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($cclist,$i);
  $rem=$i % 2; 
  print "<TR CLASS=" . $bcolor[$rem] . ">";
  print "<TD>";
  if ($ADMIN_USER == "admin") {
    print "<A HREF=\"javascript:openirate('" . $r[1] . "','" . $r[3] . "')\">" . $r[0] . " - " . $r[3] . "</A>";
  } else {
    print $r[0] . " - " . $r[3];
  }
  print "</TD><TD>" . $r[1] . "</TD><TD>00" . $r[2];
  print "</TD><TD ALIGN=RIGHT>" . sprintf("%0.2f",$r[4]) . "</TD><TD ALIGN=RIGHT>" . sprintf("%0.2f",$r[5]) . "</TD>";
  print "</TR>\n";
}
?>
</TABLE>
<FORM NAME=irateform METHOD=POST>
  <INPUT TYPE=HIDDEN NAME=countrycode>
  <INPUT TYPE=HIDDEN NAME=subcode>
  <INPUT TYPE=HIDDEN NAME=nomenu VALUE=1>
  <INPUT TYPE=HIDDEN NAME=disppage VALUE=cdr/intrate.php>
</FORM>
