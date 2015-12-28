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

if (is_file("auth.inc")) {
  include_once "auth.inc";
} else {
  include_once "reception/auth.inc";
}
?>
<CENTER>
<FORM METHOD=POST NAME=irateform onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=countrycode VALUE="<?php print $countrycode;?>">
<INPUT TYPE=HIDDEN NAME=subcode VALUE="<?php print $subcode;?>">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE=1>

<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body COLSPAN=2><?php print _("International Rate Setup");?></TH>
  </TR>
  <TR CLASS=list-color1>

<?php

if (isset($uprate)) {
  $peaksec=sprintf("%d",($peaksec*100000)/60);
  $offpeaksec=sprintf("%d",($offpeaksec*100000)/60);
  pg_query($db,"UPDATE intrates SET peaksec='" . $peaksec . "',offpeaksec='" . $offpeaksec . "' WHERE validfrom < now() AND validto > now() AND countrycode='" . $countrycode . "' AND subcode='" . $subcode . "'");
}

$qgetdata=pg_query($db,"SELECT peaksec*60/100000.0,offpeaksec*60/100000.0 FROM intrates WHERE validfrom < now() AND validto > now() AND countrycode='" . $countrycode . "' AND subcode='" . $subcode . "'");
if (pg_num_rows($qgetdata) > 0) {
  $getdata=pg_fetch_array($qgetdata,0);
} else {
  pg_query($db,"INSERT INTO intrates (countrycode,subcode,peakmin,peakperiod,offpeakmin,offpeakperiod) VALUES ('" . $countrycode . "','" . $subcode . "','63158','1','63158','1')");
  $qgetdata=pg_query($db,"SELECT peaksec*60/100000.0,offpeaksec*60/100000.0 FROM intrates WHERE validfrom < now() AND validto > now() AND countrycode='" . $countrycode . "' AND subcode='" . $subcode . "'");
  $getdata=pg_fetch_array($qgetdata,0);
}
?>
  <TR CLASS=list-color1>
  <TD onmouseover="myHint.show('GR5')" onmouseout="myHint.hide()"><?php print _("Peak Rate/m");?></TD>
  <TD><INPUT TYPE=TEXT SIZE=12 NAME=peaksec VALUE="<?php print sprintf("%0.4f",$getdata[0]);?>">
  </TR>
  <TR CLASS=list-color2>
  <TD onmouseover="myHint.show('GR6')" onmouseout="myHint.hide()"><?php print _("Off Peak Rate/m");?></TD>
  <TD><INPUT TYPE=TEXT SIZE=12 NAME=offpeaksec VALUE="<?php print sprintf("%0.4f",$getdata[1]);?>">
  </TR>
  <INPUT TYPE=HIDDEN NAME=router VALUE="<?php print $router;?>">
  <INPUT TYPE=HIDDEN NAME=channel VALUE="<?php print $channel;?>">
  <TR CLASS=list-color1>
    <TD ALIGN=MIDDLE COLSPAN=2>
      <INPUT TYPE=RESET>
      <INPUT TYPE=SUBMIT NAME=uprate VALUE="<?php print _("Modify");?>">
    </TD>
</TR>
</TABLE>
</FORM>
