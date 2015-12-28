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
  if ($newkey != "") {
    $eddi=pg_query($db,"SELECT number FROM speed_dial WHERE number='" . $newkey . "'");
    if (pg_num_rows($eddi)) {
      $ddiid=pg_fetch_row($eddi,0);
      pg_query($db,"UPDATE speed_dial SET dest='" . $newval . "',discrip='" . $newdiscrip . "' WHERE number='" . $newkey . "'");
    } else {
      pg_query($db,"INSERT INTO speed_dial (number,dest,discrip) VALUES ('$newkey','$newval','$newdiscrip')");
    }
  } else if ($key != "") {
    pg_query($db,"DELETE FROM speed_dial WHERE number='$key'");
  }
}

$qgetdata=pg_query($db,"SELECT number,dest,discrip FROM speed_dial ORDER by number");?>

<CENTER>
<FORM METHOD=POST NAME=printform>
<INPUT TYPE=HIDDEN NAME=print>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<?php print $_SESSION['disppage'];?>">
</FORM>

<FORM METHOD=POST NAME=sdialform onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<?php
if ($_POST['print'] == 0) {
  print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>" . _("Asterisk PBX Speed Dial Configuration") . "</TH></TR>";
?>
  <TR CLASS=list-color1>
  <TD onmouseover="myHint.show('DA0')" onmouseout="myHint.hide()"><?php print _("Select Speed Dial To Delete");?></TD>
  <TD>
  <SELECT NAME=key>
    <OPTION VALUE=""><?php print _("Add New Speed Dial Below");?></OPTION><?php
    for($i=0;$i<pg_num_rows($qgetdata);$i++){
      $getdata=pg_fetch_array($qgetdata,$i);
      print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[0] . "->" . $getdata[1];
      if ($getdata[2] != "") {
        print " (" . $getdata[2] . ")";
      }
      print "</OPTION>"; 
    }?>
  </SELECT>
  </TR>
  <TR CLASS=list-color2>
  <TD onmouseover="myHint.show('DA1')" onmouseout="myHint.hide()">New Speed Dial</TD>
  <TD><INPUT TYPE=TEXT NAME=newkey></TD>
  </TR>
  <TR CLASS=list-color1>
  <TD onmouseover="myHint.show('DA2')" onmouseout="myHint.hide()"><?php print _("Destination To Route To");?></TD>
  <TD><INPUT TYPE=TEXT NAME=newval></TD>
  </TR>
  <TR CLASS=list-color2>
  <TD onmouseover="myHint.show('DA3')" onmouseout="myHint.hide()"><?php print _("Discription");?></TD>
  <TD><INPUT TYPE=TEXT NAME=newdiscrip></TD>
  </TR>
  <TR CLASS=list-color1>
    <TD ALIGN=MIDDLE COLSPAN=2>
      <INPUT TYPE=RESET>
      <INPUT TYPE=BUTTON NAME=pbutton VALUE="<?php print _("Print");?>" ONCLICK="printpage(document.printform)">
      <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='pbxupdate' VALUE="<?php print _("Save Changes");?>">
    </TD>
  </TR><?php
} else {
  print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=3>" . _("Asterisk PBX Speed Dial Configuration") . "</TH></TR>";
  for($i=0;$i<pg_num_rows($qgetdata);$i++){
    $getdata=pg_fetch_array($qgetdata,$i);
    $getdata[2]=($getdata[2] == "")?"&nbsp":$getdata[2];
    print "<TR CLASS=list-color" . (($rcnt % 2) + 1) . "><TD WIDTH=50%>" . $getdata[0] . "</TD><TD>" . $getdata[1] . "</TD><TD>" . $getdata[2] . "</TD></TR>\n"; 
    $rcnt++;
  }
}
?>
</TABLE>
</FORM>
