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
include "auth.inc";


if ((isset($pbxupdate)) && ($sdpos != "") && ($sdnumber != "") && ((($sdpos >= 0) && ($sdpos < 30)) || ($sdpos == "*") || ($sdpos == "#"))) {
  pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $PHP_AUTH_USER . "','speed-" . $sdpos . "','" . $sdnumber . "')");
}

$gdbquery="SELECT lpad(substr(key,7),2,'0'),value FROM astdb WHERE family='" . $PHP_AUTH_USER . "' AND key ~ '^speed\-([0-9]+)|([*#])' ORDER BY lpad(substr(key,7),2,'0')";
$qgetdata=pg_query($db,$gdbquery);

?>

<CENTER>
<link rel="stylesheet" type="text/css" href="/style.php?style=<?php print $style;?>">
<FORM METHOD=POST>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=3>Snom Speed Dials</TH>
</TR>
<TR CLASS=list-color1>
<TH CLASS=heading-body2 WIDTH=10%>Delete</TH>
<TH CLASS=heading-body2 WIDTH=50%>Position</TH>
<TH CLASS=heading-body2 WIDTH=30%>Number</TH>
</TR>
<?php

$rcnt=0;
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  $sdtodel="sddel" . $getdata[0];
  if (($getdata[0][1] != "*") && ($getdata[0][1] != "#")) {
    $sdpos=sprintf("%d",$getdata[0]);
  } else {
    $sdpos=$getdata[0][1];
  }
  if ((isset($pbxupdate)) && ($$sdtodel == "on")) {
    pg_query($db,"DELETE FROM astdb WHERE family='" . $PHP_AUTH_USER . "' AND key='speed-" . $sdpos . "'");
  } else {
    if ($rcnt % 2 == 0) {
      $bcolor="CLASS=list-color2";
    } else {
      $bcolor="CLASS=list-color1";
    }
    print "<TR " . $bcolor . "><TD>";
    print "<INPUT TYPE=CHECKBOX NAME=\"sddel" . $getdata[0]  . "\">";
    print "</TD><TD>" . $sdpos . "</TD><TD>" . $getdata[1] . "</TD><TD>" . $getdata[2] . "</TD></TR>\n";
    $rcnt++;
  }
}
  if ($rcnt % 2 == 0) {
    $bcol[0]="CLASS=list-color2";
    $bcol[1]="CLASS=list-color1";
  } else {
    $bcol[0]="CLASS=list-color1";
    $bcol[1]="CLASS=list-color2";
  }
?>
</TR>

<?php
if ($rcnt < 100) {
?>
  <TR <?php print $bcol[0];?>>
  <TH COLSPAN=3 CLASS=heading-body2>Add New Entry</TH></TR>
  <TR <?php print $bcol[1];?>>
  <TD>&nbsp;</TD>
  <TD><INPUT NAME=sdpos VALUE=""></TD>
  <TD><INPUT NAME=sdnumber></TD></TR>
<?php
}
?>
<TR <?php print $bcol[0];?>>
  <TD ALIGN=MIDDLE COLSPAN=3>
    <INPUT TYPE=SUBMIT NAME=pbxupdate VALUE="Save Changes">
    <INPUT TYPE=RESET>
  </TD>
</TR>
<TR <?php print $bcol[1];?>>
  <TD ALIGN=MIDDLE COLSPAN=3>
  <A HREF=/reception/vladmin.php?style=<?php print $style;?>>Account Configuration</A>
</TD>
</TR>
</TABLE>
</FORM>
