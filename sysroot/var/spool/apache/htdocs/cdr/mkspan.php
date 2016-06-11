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
<FORM METHOD=POST NAME=spanform onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<?php
if ((isset($pbxupdate)) && (($zapspan != "") || (($zapspan == "") && ($newspan != "")))) {
  if (($zapspan == "") && ($newspan != "")) {
    $zapspan=$newspan;

    $getsdef=pg_query($db,"SELECT substr(key,4),value from astdb where family = 'Setup' AND key ~ '^PRI'");
    for($i=0;$i < pg_num_rows($getsdef);$i++) {
      $sdrow=pg_fetch_array($getsdef);
      if ($sdrow[0] == "crc4") {
        $sdrow[1]=$sdrow[1]?"t":"f";
      }
      $spandef['fields'].="," . $sdrow[0];
      $spandef['values'].=",'" . $sdrow[1] . "'";
    }

    $getsdef=pg_query($db,"SELECT max(timingsource)+1 FROM zapspan");
    $tdat=pg_fetch_array($getsdef);
    if ($tdat[0] != "") {
      $times=$tdat[0];
    } else {
      $times=1;
    }

    pg_query($db,"INSERT INTO zapspan (spannum,timingsource" . $spandef['fields'] . ") VALUES ('" . $newspan . "','" . $times . "'" . $spandef['values'] . ")");
  }
  include "spanadmin.php";
} else {
  if ((isset($pbxdelete)) && ($zapspan != ""))  {
    pg_query($db,"DELETE FROM zapspan WHERE spannum='" . $zapspan . "'");
  }
?>
  <TH CLASS=heading-body COLSPAN=2><?php print _("Select TDM Span To Modify");?></TH>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('Z1')" onmouseout="myHint.hide()"><?php print _("Span To Configure");?></TH>
  <TD WIDTH=50% ALIGN=LEFT>
<?php
  $spanq=pg_query($db,"SELECT spannum,spannum||','||timingsource||','||lbo||','||framing||','||coding||case when (crc4) then ',crc4' else '' end||case when (yalarm) then ',yellow' else '' end||case when (dchannel is not null AND dchannel > 0) THEN ' - '||dchannel else '' end from zapspan ORDER BY spannum");
  print "  <SELECT NAME=zapspan onchange=this.form.subme.click()>\n    <OPTION VALUE=\"\">" . _("Add New Span Bellow") . "</OPTION>\n";
  for($i=0;$i < pg_num_rows($spanq);$i++) {
    $span=pg_fetch_array($spanq,$i);
    print "    <OPTION VALUE=\"" .  $span[0] . "\">" . $span[1] . "</OPTION>\n";
  }
?>
  </SELECT>
  </TD></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('Z2')" onmouseout="myHint.hide()"><?php print _("Span To Add");?></TH>
  <TD WIDTH=50% ALIGN=LEFT>
    <INPUT NAME=newspan VALUE="">
  </TD></TR>
<TR CLASS=list-color1>
  <TH COLSPAN=2>
    <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='pbxupdate' VALUE="<?php print _("Add/Modify Span");?>">
</TH>
</TR>
  </TABLE>
  </FORM>
<?php
}
?>
