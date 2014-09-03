<%
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

$chans=$agi->command("core show translation");
%>

<CENTER>

<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body><%print _("Codec Translation Table");%></TH>
  </TR>

<%

$cnt=0;
print "<TR CLASS=list-color" . (($cnt % 2) + 1). "><TD ALIGN=MIDDLE><PRE>";
foreach(explode("\n",$chans['data']) as $line) {
  if (! ereg("(^Privilege: Command)|(^[0-9]*[ ]*active)|(^$)",$line)) {
    print $line . "\n";
    $cnt++;
  }
}
print "</PRE></TD></TR>\n";
$agi->disconnect();
%>
</TABLE>
