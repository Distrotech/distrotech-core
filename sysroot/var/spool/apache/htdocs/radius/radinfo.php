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
    $ulimit=" AND UserName = '$PHP_AUTH_USER'";
    $unme=$PHP_AUTH_USER;
  } else {
    $ulimit=" AND UserName = '$username'";
    $unme=$username;
  }

  function gtime($secin) {
    $secin=abs($secin);
    $rem=$secin % 3600;
    $hours=sprintf("%02d",($secin-$rem)/3600);
    $rem2=$rem % 60;
    $mins=sprintf("%02d",($rem-$rem2)/60);
    $secs=sprintf("%02d",$rem2);
    $timeout="$hours:$mins:$secs";
    return $timeout;
  }

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

  $queryq="SELECT AcctSessionId,AcctUniqueId,Realm,NASIPAddress,NASPortId,NASPortType,
                             to_char(AcctStartTime,'HH24:MI:SS'),to_char(AcctStopTime,'HH24:MI:SS'),
                             AcctSessionTime,AcctInputOctets,AcctOutputOctets,
                             CalledStationId,CallingStationId,AcctTerminateCause,ServiceType,
                             FramedProtocol,FramedIPAddress,connectinfo_start,connectinfo_stop
                           FROM radacct WHERE AcctStartTime IS NOT NULL AND AcctStopTime IS NOT NULL AND
                           date_part('month',AcctStartTime) = $month AND date_part('Year',AcctStopTime) = $year$ulimit AND
                           date_part('day',AcctStopTime) = $day
                      ORDER BY AcctStartTime"; 
  $query=pg_query($queryq);

  print "<CENTER>\n<TABLE WIDTH=90% cellspacing=0 cellpadding=0>\n";
  print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=7>Daily Usage For " . $unme . " (" . $year . "/" . $month . "/" . $day . ")</TH></TR>";
  print "<TR CLASS=list-color1><TH CLASS=heading-body2>Session Info</TH><TH CLASS=heading-body2>Address</TH>";
  print "<TH CLASS=heading-body2>Start</TH><TH CLASS=heading-body2>End</TH><TH CLASS=heading-body2>Ses. Time</TH>";
  print "<TH CLASS=heading-body2>In</TH><TH CLASS=heading-body2>Out</TH></TR>\n";

  $rowcol[0]="list-color2";
  $rowcol[1]="list-color1";  

  if ($startrow == ""){
    $startrow=0;
  }

  if ($startrow >= pg_num_rows($query)) {
    $startrow=0;
  }

  if (($showrows != "") && (pg_num_rows($query) > ($showrows+$startrow)) && ($showrows != "ALL")) {
    $maxcnt=$showrows+$startrow;
  } else {
    $maxcnt=pg_num_rows($query);
  }

  for($rcnt=$startrow;$rcnt < $maxcnt;$rcnt++) {
    list($sesid,$uniqueid,$realm,$nasip,$nasport,$nasptype,$tstart,$tend,$online,
         $bytesin,$bytesout,$stationid,$callerid,$term,$ptype,$protocol,$ipaddr,
         $cinfstart,$cinfstop)=pg_fetch_row($query,$rcnt);
    $rcolsel=$rcnt % 2;
    $cinfstart=str_replace(" ","&nbsp;",$cinfstart);
    $cinfstop=str_replace(" ","&nbsp;",$cinfstop);
    print "<TR CLASS=" . $rowcol[$rcolsel] . "><TD><FONT SIZE=1>" .
          "<A HREF=javascript:alert('Unique&nbsp;ID:&nbsp;$uniqueid\\nSession&nbsp;ID:&nbsp;$sesid\\nRealm:&nbsp;$realm\\nNAS&nbsp;IP:&nbsp;$nasip\\nNAS&nbsp;Port:&nbsp;$nasport\\nPort&nbsp;Type:&nbsp;$nasptype\\nCalled&nbsp;Station&nbsp;ID:&nbsp;$stationid\\nCaller&nbsp;ID:&nbsp;$callerid\\nTermination&nbsp;Reason:&nbsp;$term\\nSession&nbsp;Type:&nbsp;$ptype\\nProtocol:&nbsp;$protocol\\nAddress:&nbsp;$ipaddr\\nConnect-Info-Start:&nbsp;" . $cinfstart . "\\nConnect-Info-Stop:&nbsp;" . $cinfstop . "')>$sesid</A>" .
          "</TD><TD ALIGN=LEFT><FONT SIZE=1>" . $ipaddr . "</TD><TD ALIGN=MIDDLE><FONT SIZE=1>" . 
          $tstart . "</TD><TD ALIGN=MIDDLE><FONT SIZE=1>" . $tend . "</TD><TD ALIGN=MIDDLE><FONT SIZE=1>" . gtime($online) . 
          "</TD><TD ALIGN=RIGHT><FONT SIZE=1>" .
          gbytes($bytesin) . "</TD><TD ALIGN=RIGHT><FONT SIZE=1>" . gbytes($bytesout) . "</TD></TR>\n";
  }
  print "</TABLE>\n";

?>
