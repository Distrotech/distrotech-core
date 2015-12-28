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


if ((isset($pbxupdate)) && ($pbname != "") && ($pbnumber != "")) {
  pg_query($db,"INSERT INTO snom_pbook VALUES ('" . $PHP_AUTH_USER . "','" . $pbname . "','" . $pbtype . "','" . $pbnumber . "')");
}

$gdbquery="SELECT name,number,type FROM snom_pbook WHERE exten='" . $PHP_AUTH_USER . "' ORDER BY name";
$qgetdata=pg_query($db,$gdbquery);

if (! $qgetdata) {
  pg_query($db,"CREATE TABLE snom_pbook (
                             exten character varying(12) DEFAULT ''::character varying NOT NULL,
                             name character varying(64) DEFAULT ''::character varying NOT NULL,
                             type character varying(12) DEFAULT ''::character varying NOT NULL,
                             number character varying(27) DEFAULT ''::character varying NOT NULL)");
  pg_query($db,"CREATE UNIQUE INDEX snom_pbook_uniqe ON snom_pbook USING btree (exten, number)");
  $qgetdata=pg_query($db,$gdbquery);
}
?>

<CENTER>
<link rel="stylesheet" type="text/css" href="/style.php?style=<?php print $style;?>">
<FORM METHOD=POST>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=4>Snom Address Book</TH>
</TR>
<TR CLASS=list-color1>
<TH CLASS=heading-body2 WIDTH=10%>Delete</TH>
<TH CLASS=heading-body2 WIDTH=50%>Name</TH>
<TH CLASS=heading-body2 WIDTH=30%>Number</TH>
<TH CLASS=heading-body2 WIDTH=30%>Type</TH>
</TR>
<?php

$rcnt=0;
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  $pbtodel="pbdel" . $getdata[1];
  if ((isset($pbxupdate)) && ($$pbtodel == "on")) {
    pg_query($db,"DELETE FROM snom_pbook WHERE exten='" . $PHP_AUTH_USER . "' AND number='" . $getdata[1] . "'");
  } else {
    if ($rcnt % 2 == 0) {
      $bcolor="CLASS=list-color2";
    } else {
      $bcolor="CLASS=list-color1";
    }
    print "<TR " . $bcolor . "><TD>";
    print "<INPUT TYPE=CHECKBOX NAME=\"pbdel" . $getdata[1]  . "\">";
    print "</TD><TD>" . $getdata[0] . "</TD><TD>" . $getdata[1] . "</TD><TD>" . $getdata[2] . "</TD></TR>\n";
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
  <TH COLSPAN=4 CLASS=heading-body2>Add New Entry</TH></TR>
  <TR <?php print $bcol[1];?>>
  <TD>&nbsp;</TD>
  <TD><INPUT NAME=pbname SIZE=50 VALUE=""></TD>
  <TD><INPUT NAME=pbnumber></TD>
  <TD><SELECT NAME=pbtype>
  <option value="None">None</option>
  <option value="Friends">Friends</option>
  <option value="Family">Family</option>
  <option value="Colleagues">Colleagues</option>
  <option value="VIP">VIP</option>
  <option value="Deny">Deny List</option>
  </SELECT></TD></TR>
<?php
}
?>
<TR <?php print $bcol[0];?>>
  <TD ALIGN=MIDDLE COLSPAN=4>
    <INPUT TYPE=SUBMIT NAME=pbxupdate VALUE="Save Changes">
    <INPUT TYPE=RESET>
  </TD>
</TR>
<TR <?php print $bcol[1];?>>
  <TD ALIGN=MIDDLE COLSPAN=4>
  <A HREF=/reception/vladmin.php?style=<?php print $style;?>>Account Configuration</A>
</TD>
</TR>
</TABLE>
</FORM>
