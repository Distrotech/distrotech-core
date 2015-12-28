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

$descrip['channels']=_("No Off Channels");
$descrip['timing']=_("Timing Priority");
$descrip['dchannel']=_("D Channel If Required");


if ((isset($pbxupdate)) && ($update == "seen")) {
  if ($dchannel == "") {
    $dchannel="0";
  }
  if ($channels < 0) {
    $channels="0";
  }
  pg_query($db,"UPDATE dynspan SET channels=" . $channels . ",dchannel=" . $dchannel . ",timing='" . $timing . "' WHERE address='" . $zapspan . "'");
}

$qgetzdata=pg_query($db,"SELECT channels,timing,dchannel FROM dynspan where address='" . $zapspan . "'");
$zdata=pg_fetch_array($qgetzdata,0,PGSQL_ASSOC);

?>
<INPUT TYPE=HIDDEN NAME=zapspan VALUE=<?php print $zapspan;?>>
<INPUT TYPE=HIDDEN NAME=update VALUE=seen>
  <TH CLASS=heading-body COLSPAN=2><?php print _("Configuration For Span") . " " . $zapspan?></TH>
</TR>
<?php
$col=0;
while(list($zapopt,$zapval)=each($zdata)) {
  if ($label[$zapopt] != "") {
    print "<TR CLASS=list-color" . (($col % 2) +1) . "><TH COLSPAN=2 CLASS=heading-body2>" . $label[$zapopt] . "</TH></TR>\n";
    $col++;
  }
  print "<TR CLASS=list-color" . (($col % 2) +1) . ">\n  <TD WIDTH=50% onmouseover=\"myHint.show('" . $zapopt . "')\" onmouseout=\"myHint.hide()\">\n    ";
  if ($descrip[$zapopt] != "") {
    print $descrip[$zapopt];
  } else {
    print $zapopt;
  }
  print "  </TD>\n  <TD>\n    ";
  print "<INPUT TYPE=TEXT NAME=\"" . $zapopt . "\" VALUE=\"" . $zapval . "\">";
  print "\n  </TD>\n</TR>";
  $col++;
}
?>
<TR CLASS=list-color<?php print (($col % 2) +1);?>>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxupdate' VALUE="<?php print _("Save");?>">
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxdelete' VALUE="<?php print _("Delete");?>">
  </TD>
</TR>
</TABLE>
</FORM>
