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
<?php
  include "opendb.inc";

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


  $queryq="SELECT date_part('day',AcctStopTime) AS Day,COUNT(RadAcctId) AS SESCOUNT ,SUM(AcctInputOctets) AS BYTESIN ,
                      SUM(AcctOutputOctets) AS BYTESOUT ,SUM(AcctSessionTime) AS TONLINE,
                      AVG(AcctSessionTime),SUM(AcctOutputOctets+AcctInputOctets)
                      FROM radacct WHERE AcctStartTime IS NOT NULL AND AcctStopTime IS NOT NULL AND
                           date_part('month',AcctStartTime) = $month AND date_part('Year',AcctStopTime) = $year$ulimit
                      GROUP BY Day
                      ORDER BY Day"; 

  $query=pg_query($queryq);
?>
<FORM NAME=openrdata METHOD=post>
<INPUT TYPE=HIDDEN NAME=disppage>
<INPUT TYPE=HIDDEN NAME=username>
<INPUT TYPE=HIDDEN NAME=year>
<INPUT TYPE=HIDDEN NAME=month>
<INPUT TYPE=HIDDEN NAME=day>

<CENTER>
<TABLE WIDTH=90% cellspacing=0 cellpadding=0>
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=7>
<?php
  print "Daily Usage For " . $unme . " (" . $year . "/" . $month . ")</TH></TR>";
  print "<TR CLASS=list-color1><TH CLASS=heading-body2>Day</TH><TH CLASS=heading-body2>Conn.</TH>" .
        "<TH CLASS=heading-body2>Time<BR>Online</TH><TH CLASS=heading-body2>Avg.<BR>Time/Ses</TH><TH CLASS=heading-body2>In</TH><TH CLASS=heading-body2>Out</TH>" .
        "<TH CLASS=heading-body2>Total</TH></TR>\n";

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
    list($dayom,$concnt,$bytein,$byteout,$timeo,$tavgol,$btotal)=pg_fetch_row($query,$rcnt);
    $timeo=gtime($timeo);
    $tavgol=gtime($tavgol);
    $rcolsel=$rcnt % 2;
    print "<TR CLASS=" . $rowcol[$rcolsel] . "><TD><FONT SIZE=1><A HREF=javascript:openraddata('" . urlencode($username) . "','" . urlencode($year) . "','" . urlencode($month) . "','" . urlencode($dayom) . "')>" .$dayom  . 
          "</A></TD><TD ALIGN=MIDDLE><FONT SIZE=1>" . $concnt . "</TD><TD ALIGN=CENTER><FONT SIZE=1>" . $timeo . 
          "</TD><TD ALIGN=MIDDLE><FONT SIZE=1>" . $tavgol . "</TD><TD ALIGN=RIGHT><FONT SIZE=1>" . gbytes($bytein) . "</TD>" .
          "<TD ALIGN=RIGHT><FONT SIZE=1>" . gbytes($byteout) . "</TD><TD ALIGN=RIGHT><FONT SIZE=1>" . gbytes($btotal) . "</TD></TR>\n";
  }
  print "</TABLE>\n";

?>
</FORM>

