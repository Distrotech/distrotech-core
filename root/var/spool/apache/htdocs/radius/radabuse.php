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
%>
<%
  include "../radius/opendb.inc";

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=$ldn)(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) != 1) && ($PHP_AUTH_USER != "admin")) {
    $ulimit=" AND (UserName LIKE '$PHP_AUTH_USER@%' OR UserName = '$PHP_AUTH_USER')";
  }

  if ($ipaddr != "") {
    $iplimit=" AND FramedIPAddress = '$ipaddr'";
  }
  $adate="$year-$month-$day $hour:$min:$sec";

  function gbytes($bytes) {
    if ($bytes >= 1073741824) {
      $bout=$bytes/1073741824;
      $bout=round($bout,2);
      $bout="$bout GB";
    } elseif ($bytes >= 1048576) {
      $bout=$bytes/1048576;
      $bout=round($bout,2);
      $bout="$bout MB";
    } elseif ($bytes >= 1024) {
      $bout=$bytes/1024;
      $bout=round($bout,2);
      $bout="$bout KB";
    } else {
      $bout="$bytes B";
    }
    return $bout;
  }


  $pgquery="SELECT AcctSessionId,AcctUniqueId,Realm,NASIPAddress,NASPortId,NASPortType,
                             date_trunc('second',AcctStartTime  at time zone INTERVAL '+2'),
                             date_trunc('second',AcctStopTime  at time zone INTERVAL '+2'),
                             interval '1 second' * AcctSessionTime,AcctInputOctets,AcctOutputOctets,
                             CalledStationId,CallingStationId,AcctTerminateCause,ServiceType,
                             FramedProtocol,FramedIPAddress,
                             date_trunc('seconds',AcctStopTime)-date_trunc('seconds',AcctStartTime) AS CalcTime,
                             case when (realm != '') then substring(username from 0 for position('@' in username)) else username end
                           FROM radacct WHERE AcctStartTime < '$adate' AND AcctStopTime > '$adate'$ulimit$iplimit
                      ORDER BY AcctStartTime"; 

  $query=pg_query($db,$pgquery);

  print "<CENTER>\n<TABLE WIDTH=90% cellspacing=0 cellpadding=0>\n";
  print "<TR CLASS=list-color2><TH>User</TH><TH>Session Info</TH><TH>Address</TH>";
  print "<TH>Start Time</TH><TH>End Time</TH><TH>Accounted Time</TH>";
  print "<TH>Session Time</TH><TH>Bytes In</TH><TH>Bytes Out</TH></TR>\n";

  $rowcol[0]="list-color1";  
  $rowcol[1]="list-color2";  

  if ($startrow == ""){
    $startrow=0;
  }

  $maxrows=pg_num_rows($query);

  if ($startrow >= $maxrows) {
    $startrow=0;
  }

  if (($showrows != "") && ($maxrows > ($showrows+$startrow)) && ($showrows != "ALL")) {
    $maxrows=$showrows+$startrow;
  }

  mysql_data_seek($query,$startrow);

  for($rcnt=$startrow;$rcnt < $maxrows;$rcnt++) {
    list($sesid,$uniqueid,$realm,$nasip,$nasport,$nasptype,$tstart,$tend,$online,
         $bytesin,$bytesout,$stationid,$callerid,$term,$ptype,$protocol,$ipaddr,
         $sestime,$uname)=pg_fetch_row($query);
    $rcolsel=$rcnt % 2;
    print "<TR CLASS=" . $rowcol[$rcolsel] . "><TD><A HREF=javascript:edituser('" . urlencode($uname) . "','system')>" . $uname . "</A></TD><TD>" .
          "<A HREF=javascript:alert('Unique&nbsp;ID:&nbsp;$uniqueid\\nSession&nbsp;ID:&nbsp;$sesid\\nRealm:&nbsp;$realm\\nNAS&nbsp;IP:&nbsp;$nasip\\nNAS&nbsp;Port:&nbsp;$nasport\\nPort&nbsp;Type:&nbsp;$nasptype\\nCalled&nbsp;Station&nbsp;ID:&nbsp;$stationid\\nCaller&nbsp;ID:&nbsp;$callerid\\nTermination&nbsp;Reason:&nbsp;$term\\nSession&nbsp;Type:&nbsp;$ptype\\nProtocol:&nbsp;$protocol\\nAddress:&nbsp;$ipaddr')>$sesid</A>" .
          "</TD><TD ALIGN=LEFT>" . $ipaddr . "</TD><TD ALIGN=MIDDLE>" . 
          $tstart . "</TD><TD ALIGN=MIDDLE>" . $tend . "</TD><TD ALIGN=MIDDLE>" . $online . 
          "</TD><TD ALIGN=MIDDLE>" . $sestime . "</TD><TD ALIGN=RIGHT>" .
          gbytes($bytesin) . "</TD><TD ALIGN=RIGHT>" . gbytes($bytesout) . "</TD></TR>\n";
  }
  print "</TABLE>\n";

%>
