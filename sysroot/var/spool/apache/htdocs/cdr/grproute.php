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

if (isset($pbxupdate)) {
  $eddi=pg_query($db,"SELECT id FROM astdb WHERE family='BROUTE' AND key='" . $bgroup . "'");
  if (pg_num_rows($eddi)) {
    if ($newroute != "") {
      pg_query($db,"UPDATE astdb SET value='" . $newroute . "' WHERE family='BROUTE' AND key='" . $bgroup . "'");
    } else {
      pg_query($db,"DELETE FROM astdb WHERE family='BROUTE' AND key='" . $bgroup . "'");
    }
  } else if ($newroute != "") {
    pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('BROUTE','$bgroup','$newroute')");
  }
}
?>

<CENTER>
<FORM METHOD=POST NAME=grrtform onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2><?php print _("Asterisk PBX Group Routing Configuration");?></TH>
</TR>
<TR CLASS=list-color1>
<?php
$bgroups=pg_query("SELECT DISTINCT grp.value,route.value FROM astdb AS grp LEFT OUTER JOIN astdb AS route ON (route.key=grp.value AND route.family='BROUTE') WHERE grp.key='BGRP' AND grp.value != '' ORDER BY grp.value;");
$bgnum=pg_num_rows($bgroups);

if ($bgnum > 0) {
  print "<TD WIDTH=50% onmouseover=\"myHint.show('DA0')\" onmouseout=\"myHint.hide()\">" . _("Select Group To Configure") . "</TD><TD><SELECT NAME=bgroup>\n";
  for($i=0;$i<$bgnum;$i++){
    $getbgdata=pg_fetch_array($bgroups,$i);
    print "<OPTION VALUE=" . $getbgdata[0] . ">" . $getbgdata[0];
    if ($getbgdata[1] != "") {
      print " -> " . $getbgdata[1];
    }
    print "</OPTION>\n";
  }
?>
  </SELECT>
  </TD></TR>
  <TR CLASS=list-color2><TD><?php print _("Destination To Route To When Reception Is Not Available/After Hours");?></TD><TD>
    <INPUT TYPE=TEXT NAME=newroute></TD></TR>
  </TR>
  <TR CLASS=list-color1>
    <TD ALIGN=MIDDLE COLSPAN=2>
      <INPUT TYPE=RESET>
      <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='pbxupdate' VALUE="<?php print _("Save Changes");?>">
    </TD>
<?php
} else {
  print "<TH COLSPAN=2>" . _("There Are No Groups Configured") . "</TH>";
}
?>
</TR>
</TABLE>
</FORM>
