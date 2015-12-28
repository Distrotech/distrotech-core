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
  include "auth.inc";
}
?>
<FORM METHOD=POST NAME=mkzform onsubmit="ajaxsubmit(this.name);return false;">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<?php
if ((isset($pbxupdate)) && ($zaptrunk != "")) {
  $zedit=pg_query($db,"SELECT zaptrunk FROM zapgroup WHERE zaptrunk='" . $zaptrunk . "'");
  if (pg_num_rows($zedit) == 0) {
    pg_query($db,"INSERT INTO zapgroup (zaptrunk) VALUES ('" . $zaptrunk . "')");
  }
  include "zapadmin.php";
} else {
?>
  <TH CLASS=heading-body COLSPAN=2><?php print _("Select Trunk Group");?></TH>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('Z0')" onmouseout="myHint.hide()"><?php print _("Trunk Group To Configure");?></TH>
  <TD WIDTH=50% ALIGN=LEFT>
  <INPUT TYPE=HIDDEN NAME=pbxupdate VALUE=1>
  <SELECT NAME=zaptrunk onchange="ajaxsubmit(this.form.name)">
  <OPTION VALUE=""></OPTION>
<?php
  for($i=1;$i <= 4;$i++) {
    print "    <OPTION VALUE=\"" .  $i . "\">" . _("Digium Trunk Group") . " " . $i . "</OPTION>\n";
  }
?>
  </SELECT>
  </TABLE>
  </FORM>
<?php
}
?>
