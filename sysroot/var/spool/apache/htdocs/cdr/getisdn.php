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

if (! isset($agi)) {
  require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
  $agi=new AGI_AsteriskManager();
  $agi->connect("127.0.0.1","admin","admin");
}

if (($iport == 0) && ($iblock == "reload")) {
  $agi->command("misdn reload");
} else if (($iport > 0) && ($iblock == "reset")) {
  $agi->command("misdn restart port " . $iport);
} else if (($iport > 0) && ($iblock != "")) {
  if ($iblock) {
    $agi->command("misdn port block " . $iport);
  } else {
    $agi->command("misdn port unblock " . $iport);
  }
}

$isdns=$agi->command("misdn show stacks");
?>

<CENTER>
<FORM METHOD=POST NAME=isdninf>
<INPUT TYPE=HIDDEN NAME=iport>
<INPUT TYPE=HIDDEN NAME=iblock>

<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body COLSPAN=5><?php print _("Asterisk ISDN Channel Status");?></TH>
  </TR>

<TR CLASS=list-color1>
<TH CLASS=heading-body2><?php print _("Port");?></TH>
<TH CLASS=heading-body2><?php print _("Active");?></TH>
<TH CLASS=heading-body2><?php print _("Link Status");?></TH>
<TH CLASS=heading-body2><?php print _("Blocked");?></TH><TH CLASS=heading-body2><?php print _("Reset Port");?></TH></TR>
<?php
$cnt=1;
foreach(explode("\n",$isdns['data']) as $line) {
  if (! ereg("(^Privilege: Command)|(^BEGIN STACK_LIST)|(^No such command)|(^$)",$line)) {
    print "<TR CLASS=list-color" . (($cnt % 2) + 1). ">\n";
    ereg("^ +\* Port ([0-9]+) Type (TE|NT) Prot. (PMP) L2Link (UP|DOWN) L1Link:(UP|DOWN) Blocked:(0|1)",$line,$data);
    if ($data[6] == "0") {
      $data[6]="<A HREF=\"javascript:blockisdn('" . $data[1] . "','1')\">" . _("No") . "</A>";
    } else {
      $data[6]="<A HREF=\"javascript:blockisdn('" . $data[1] . "','0')\">" . _("Yes") . "</A>";
    }
    if ($data[4] == "DOWN") {
      $data[4]=_("No");
    } else {
      $data[4]=_("Yes");
    }
    print "<TD ALIGN=MIDDLE>" . $data[1] . "</TD><TD ALIGN=MIDDLE>" . $data[4] . "</TD><TD ALIGN=MIDDLE>" . $data[5] . "</TD><TD ALIGN=MIDDLE>" . $data[6] . "</TD>";
    print "<TD ALIGN=MIDDLE><INPUT TYPE=BUTTON VALUE=\"" . _("Reset Port") . "\" onclick=\"javascript:resetisdn('" . $data[1] . "')\"></TD>";
    print "</TR>\n";
    $cnt++;
  }
}
$agi->disconnect();
?>
<TR CLASS=list-color<?php print (($cnt % 2) +1 );?>><TD ALIGN=MIDDLE COLSPAN=5>
<?php if ($cnt > 1) {?>
  <INPUT TYPE=BUTTON VALUE="<?php print _("Reload ISDN");?>" onclick="javascript:blockisdn('0','reload')">
<?php }?>
</TD></TR>
</TABLE>
</FORM>
