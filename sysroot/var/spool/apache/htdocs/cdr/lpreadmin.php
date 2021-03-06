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
    pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('LocalPrefix','$newkey','1')");
  } else if ($key != "") {
    pg_query($db,"DELETE FROM astdb WHERE family='LocalPrefix' AND value='1' AND key='$key'");
    pg_query($db,"DELETE FROM users WHERE name ~ '^" . $key . "[0-9]{2}$'");
    pg_query($db,"DELETE FROM astdb WHERE family ~ '^" . $key . "[0-9]{2}$'");
  }
}

$qgetdata=pg_query($db,"SELECT key FROM astdb WHERE family='LocalPrefix' AND value='1' ORDER BY key;");

?>

<CENTER>
<FORM METHOD=POST NAME=lpreform onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2><?php print _("Asterisk PBX Local Prefix Configuration");?></TH>
</TR>
<TR CLASS=list-color1>
<TD onmouseover="myHint.show('LP0')" onmouseout="myHint.hide()"><?php print _("Select Prefix To Delete");?></TD>
<TD><SELECT NAME=key>
<OPTION VALUE=""><?php print _("Add New Prefix Bellow");?></OPTION>
<?php
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[0] . "</OPTION>"; 
}
?>
</SELECT>
</TR>
<TR CLASS=list-color2>
<TD onmouseover="myHint.show('LP1')" onmouseout="myHint.hide()"><?php print _("New Prefix");?></TD>
<TD><INPUT TYPE=TEXT NAME=newkey></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='pbxupdate' VALUE="<?php print _("Save Changes");?>">
  </TD>
</TR>
</TABLE>
</FORM>
