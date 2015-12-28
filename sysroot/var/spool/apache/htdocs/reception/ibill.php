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
include "auth.inc";

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

$mona=explode(":",$bmon);

if ($bday != "") {
  $bday=" AND date_part('day',starttime) = " . $bday;
} 

$cdrq="SELECT to_char(call.starttime,'YY/MM/DD HH24:MI:SS'),country.countryname,
                          call.calledstation,call.totaltime,call.sessiontime,call.calledrate,call.sessionbill / 100,
                          calledsub 
                  FROM call LEFT OUTER JOIN country ON (call.calledcountry = country.countrycode) 
                  WHERE call.username='" . $exten . "' AND call.sessionbill > '0' AND terminatecause='ANSWER' AND 
			date_part('year',starttime) = " . $mona[0] . " AND 
                        date_part('month',starttime) = " . $mona[1] . $bday . "
                  ORDER BY starttime";  

//print $cdrq . "<P>";
$cdr=pg_query($db,$cdrq);

$bcolor[0]="list-color1";
$bcolor[1]="list-color2";

if ($csvout == "") {
?>
<link rel=stylesheet type=text/css href=/style.php>
<DIV CLASS=popup>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH ALIGN=LEFT><FONT SIZE=1>Start Time</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Country</TH><TH ALIGN=LEFT><FONT SIZE=1>Number</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Total Time</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Bill. Time</TH><TH ALIGN=LEFT><FONT SIZE=1>Rate</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Cost</TH></TR>
<?php
} else {
  header("Content-type: application/ms-excel");
  $stdout=fopen("php://output","w");
}
$num=pg_num_rows($cdr);
$total="0";
$totmin="0";
for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($cdr,$i);
  $rem=$i % 2; 
  if ($csvout == "") {
    print "<TR CLASS=" . $bcolor[$rem] . ">";
  }
  $total=$total+$r[6];
  $totmin=$totmin+$r[4];
  for ($j=0;$j < count($r)-1;$j++) {
    if (($j == "3") || ($j == "4")){
      $r[$j]=gtime($r[$j]);
    } else if ($j == "1") {
      $r[$j]=$r[$j] . " (" . $r[7] . ")";
    } else if ($j > "4") {
      $r[$j]=sprintf("R%0.2f",$r[$j]);
    }
    if ($csvout == "") {
      print  "<TD><FONT SIZE=1>" . $r[$j] . "</TD>";
    }
  }
  if ($csvout == "") {
    print "</TR>\n";
  } else {
    fputcsv($stdout,$r);
  }
}
if ($csvout == "") {
  $rem=$i % 2; 
  print "<TR CLASS=" . $bcolor[$rem] . ">";
?>
<TD COLSPAN=4><FONT SIZE=1>&nbsp;</TD>
<TD ALIGN=LEFT><FONT SIZE=1><?php $totmin=gtime($totmin);print $totmin;?></TD>
<TD><FONT SIZE=1>&nbsp;</TD>
<TD ALIGN=LEFT><FONT SIZE=1><?php printf("R%0.2f",$total);?></TD></TR>
</TABLE>
</DIV>
<?php
} else {
  fclose($stdout);
}
?>
