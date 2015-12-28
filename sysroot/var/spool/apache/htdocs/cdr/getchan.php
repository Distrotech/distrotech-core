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

if (isset($hangup)) {
  for($ccnt=1;$ccnt <= $callcnt;$ccnt++) {
    $call="del" . $ccnt;
    if ($$call) {
      $chan="chan" . $ccnt;
      $chans=$agi->command("soft hangup " . $$chan);
    }
  }
  sleep(2);

}

$chans=$agi->command("core show channels concise");
?>

<CENTER>
<FORM METHOD=POST NAME=hangupchan onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body COLSPAN=6><?php print _("Active Channels");?></TH>
  </TR>

<?php

print "<TR CLASS=list-color1><TH>" . _("Hangup") . "</TH><TH>" . _("Caller ID") . "</TH><TH>"  . _("Account") . "</TH><TH>" . _("Status") . "</TH><TH>" . _("Duration") . "</TH><TH>" . _("Channel(s)") . "</TH></TR>\n";

$cnt=1;
foreach(explode("\n",$chans['data']) as $line) {
  if (! ereg("(^Privilege: Command)|(^[0-9]*[ ]*active)|(^$)",$line)) {
    print "<TR CLASS=list-color" . (($cnt % 2) + 1). ">\n";
    $chan=explode("!",$line);
    print "<TD><INPUT TYPE=CHECKBOX NAME=\"del" . $cnt . "\"><INPUT TYPE=HIDDEN NAME=chan" . $cnt . " VALUE=\"" . $chan[0] . "\"></TD>";
    print "<TD>" . $chan[7] . "</TD>";
    print "<TD>" . $chan[8] . "</TD>";
    print "<TD>" . $chan[4] . "</TD>";
    print "<TD>" . $chan[10] . "</TD>";
    if ($chan[11] != "(None)") {
      $chaninf=$chan[0] . " <-> " . $chan[11];
    } else {
      $chaninf=$chan[0];
    }
    print "<TD><A HREF=\"javascript:alert('";
    print "Channel: " . $chan[0] . "\\n";
    print "Exten: " . $chan[2] . "\\n";
    print "Context: " . $chan[1] . "\\n";
    print "Priority: " . $chan[3] . "\\n";
    print "Application: " . $chan[5] . "(" . $chan[6] . ")\\n";
    print "Status: " . $chan[4] . "\\n";
    print "')\">" . $chaninf . "</A></TD>";
    $cnt++;
  }
}

print "\n<INPUT TYPE=HIDDEN NAME=callcnt VALUE=" . ($cnt - 1) . ">\n";
print "\n<TR CLASS=list-color" . (($cnt % 2) + 1). "><TH COLSPAN=6><INPUT TYPE=SUBMIT VALUE=\"" . _("Hangup Selected Calls") . "\" NAME=hangup></TD></TR>\n";
$agi->disconnect();
?>
</TABLE>
</FORM>
