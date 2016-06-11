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
  include "../radius/opendb.inc";

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

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=$ldn)(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) != 1) && ($PHP_AUTH_USER != "admin")) {
    $ulimit=" AND UserName = \"$PHP_AUTH_USER\"";
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


  if ($sort != "UserName") {
    $sort=$sort . ",UserName,realm";
  }

  list($year,$month)=preg_split("/,/",$time);



  $queryq="SELECT case when (realm != '') then substring(username from 0 for position('@' in username)) else username end,realm,
                      SUM(AcctInputOctets),count(acctsessionid),
                      SUM(AcctOutputOctets) AS BYTESOUT ,SUM(AcctSessionTime) AS TONLINE,
                      AVG(AcctSessionTime),SUM(AcctOutputOctets+AcctInputOctets)
                      FROM radacct WHERE AcctStartTime IS NOT NULL AND AcctStopTime IS NOT NULL AND
                           date_part('month',AcctStartTime) = $month AND date_part('Year',AcctStopTime) = $year$ulimit
                      GROUP BY UserName,realm 
                      ORDER BY $sort"; 
  $query=pg_query($queryq);

?>
<FORM NAME=openrdata METHOD=POST onsubmit="ajaxsubmit(this.name);return false;">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<?php print $_SESSION['disppage']?>">
<INPUT TYPE=HIDDEN NAME=username>
<INPUT TYPE=HIDDEN NAME=year>
<INPUT TYPE=HIDDEN NAME=month>
<INPUT TYPE=HIDDEN NAME=day>
<CENTER>
<TABLE WIDTH=90% cellspacing=0 cellpadding=0>
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=7>Select User</TH></TR>
<TR CLASS=list-color1>
  <TH CLASS=heading-body2>User Name<BR>(Connection/Traffic)</TH>
  <TH CLASS=heading-body2>No.</TH>
  <TH CLASS=heading-body2>Time<BR>Online</TH>
  <TH CLASS=heading-body2>Avg.<BR>Time/Ses</TH>
  <TH CLASS=heading-body2>In</TH>
  <TH CLASS=heading-body2>Out</TH>
  <TH CLASS=heading-body2>Total</TH>
</TR>
<?php

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

  for($i=$startrow;$i < $maxcnt;$i++) {
    list($user,$realm,$bytein,$sescount,$byteout,$timeol,$avgtime,$totalb)=pg_fetch_row($query,$i);
    $timeol=gtime($timeol);
    $avgtime=gtime($avgtime);
    $rcolsel=$i % 2;
    if ($realm != "") {
      $fqun=$user . "@" . $realm;
    } else {
      $fqun=$user;
    }
    $uname = urlencode($fqun);
    $uinfo="<FONT SIZE=1><A HREF=javascript:openraddata('" . $uname . "','" . urlencode($year) . "','" . urlencode($month) . "','0')>" . $user;
    if ($realm != "") {
      $uinfo.=" (" . $realm . ")";
    }
    $uinfo.="</A> <A HREF=/radius/cgraph.php?username=" . $uname . "&month=" . urlencode($month) . "&year=" . urlencode($year) . " TARGET=_blank>C</A>";
    $uinfo=$uinfo . "/<A HREF=/radius/tgraph.php?username=" . $uname . "&month=" . urlencode($month) . "&year=" . urlencode($year) . " TARGET=_blank>t</A>";
    $uinfo=$uinfo . "/<A HREF=/radius/tgraph.php?username=" . $uname . "&month=" . urlencode($month) . "&year=" . urlencode($year) . " TARGET=_blank>T</A>";
    print "<TR CLASS=" . $rowcol[$rcolsel] . "><TD>" . $uinfo  . "</TD><TD><FONT SIZE=1>" . $sescount . "</TD>" .
          "</TD><TD ALIGN=MIDDLE><FONT SIZE=1>" . $timeol . "</TD><TD ALIGN=MIDDLE><FONT SIZE=1>" . $avgtime . "</TD>" .
          "<TD ALIGN=RIGHT><FONT SIZE=1>";
    print gbytes($bytein) . "</TD><TD ALIGN=RIGHT><FONT SIZE=1>" . gbytes($byteout) . "</TD><TD ALIGN=RIGHT><FONT SIZE=1>";
    print gbytes($totalb) . "</TD></TR>\n";
  }

?>
</TABLE>
</FORM>
