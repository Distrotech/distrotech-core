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

?>

<CENTER>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<?php

$cnt=1;
$astcmd=array("version","uptime","license","warranty");

$heading["version"]=_("PBX Version");
$heading["uptime"]=_("System Uptime");
$heading["license"]=_("PBX License");
$heading["warranty"]=_("PBX Warranty");

for($ccmd=0;$ccmd < count($astcmd);$ccmd++) {
  $chans=$agi->command("core show " . $astcmd[$ccmd]);
  print "<TR CLASS=list-color" . (($cnt % 2) + 1). "><TH CLASS=heading-body ALIGN=LEFT>" . $heading[$astcmd[$ccmd]] . "</TH></TR>";
  $cnt++;
  print "<TR CLASS=list-color" . (($cnt % 2) + 1). "><TD";
  print "><PRE>";
  $cnt++;
  if ($ccmd == 0) {
    system("uname -a");
  } else if ($ccmd == 1) {
    print "Server Uptime:";
    system("uptime");
  }
  foreach(explode("\n",$chans['data']) as $line) {
    if (! preg_match("/(^Privilege: Command)|(^[0-9]*[ ]*active)|(^$)/",$line)) {
      print $line . "\n";
    }
  }
}

print "</PRE></TD></TR>\n";
$agi->disconnect();
?>
</TABLE>
