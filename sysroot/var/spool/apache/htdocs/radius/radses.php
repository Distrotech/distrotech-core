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
?>
<html>
<head>
<title>Ldap Admin</title>
<base target="_self">
<?php
  include "opendb.inc";
  if (! $rdn) {
    include "../ldap/auth.inc";
  }
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=$ldn)(cn=Admin Access))");

  if ((ldap_count_entries($ds,$sr) != 1) && ($PHP_AUTH_USER != "admin")) {
    $ulimit=" AND UserName = \"$PHP_AUTH_USER\"";
  } else if ($closeses != "") {
    pg_query("UPDATE radacct SET AcctStopTime=NOW() WHERE AcctUniqueID='$closeses'");
  }

  $queryq="SELECT UserName,AcctSessionId,NASIPAddress,FramedIPAddress,NASPortId,
                             NASPortType,to_char(acctstarttime,'YY-MM-DD HH24:MI:SS'),CalledStationId,CallingStationId,
                             to_char(NOW()-AcctStartTime,'HH24:MI:SS') AS Tonline,
                             AcctUniqueID
                      FROM radacct WHERE AcctStartTime IS NOT NULL AND AcctStopTime is NULL $ulimit
                      ORDER BY Tonline";

  $query=pg_query($db,$queryq); 

  print "<CENTER>\n<TABLE WIDTH=90% cellspacing=0 cellpadding=0>\n";
  print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=8>Open Radius Sesions</TH></TR>";
  print "<TR CLASS=list-color1><TH CLASS=heading-body2>User Name</TH><TH CLASS=heading-body2>Session ID";
  if ($ulimit == "") {
    print "<BR>Click To Close";
  }
  print "</TH><TH CLASS=heading-body2>Local/Rem. IP</TH><TH CLASS=heading-body2>Port</TH><TH CLASS=heading-body2>";
  print "Type</TH><TH CLASS=heading-body2>Start Time</TH><TH CLASS=heading-body2>Called Station ID.</TH><TH CLASS=heading-body2>Caller ID.</TH></TR>\n";

  $rowcol[0]="list-color2";  
  $rowcol[1]="list-color1";  

  $rcnt=0;
  while(list($user,$sesid,$nasip,$frameip,$nasport,$ptype,$stime,$callid,$callerid,$etime,$uniqid)=pg_fetch_row($query)) {
    $rcolsel=$rcnt % 2;
    print "<TR CLASS=" . $rowcol[$rcolsel] . "><TD><FONT SIZE=1>" . $user  . "</TD><TD><FONT SIZE=1>";
    if ($ulimit == "") {
      print "<A HREF=/auth/index.php?disppage=radius/radses.php&closeses=$uniqid>" . $sesid . "</A>";
    } else {
      print $sesid;
    }
    print "</TD><TD><FONT SIZE=1>" . $nasip  . "<BR>" . $frameip . "</TD><TD><FONT SIZE=1>" . $nasport . "</TD><TD><FONT SIZE=1>" . $ptype  . "</TD><TD ALIGN=MIDDLE><FONT SIZE=1>" . $stime . "<BR>" . $etime . "</TD>";
    print "<TD><FONT SIZE=1>" . $callid . "</TD><TD><FONT SIZE=1>" . $callerid  . "</TD>";
    print "</TD></TR>\n";
    $rcnt++;
  }
  print "</TABLE>\n";
?>
