
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
include "../cdr/auth.inc";

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

$mona=explode(":",$_POST['bmon']);

$date=$mona[0] . "-" . $mona[1] . "-" . ($_POST['bday']);

$yesdate=date('Y-m-j', strtotime('-1 day' , strtotime($date)));
$daybefyes=date('Y-m-j', strtotime('-2 day' , strtotime($date)));

$cdrq="SELECT users.fullname,o.username,count(o.uniqueid) as sescount, count(CASE WHEN terminatecause = 'ANSWER' THEN terminatecause END) as anscnt, 
count(CASE WHEN terminatecause = 'NO ANSWER' THEN terminatecause WHEN terminatecause = 'CANCEL' THEN terminatecause  WHEN terminatecause = 'CHANUNAVAIL' THEN terminatecause  
WHEN terminatecause = 'FAILED' THEN terminatecause WHEN terminatecause = 'BUSY' THEN terminatecause END) as failcalls, (100 * count(CASE WHEN terminatecause = 'ANSWER' 
THEN terminatecause END)) / count(o.uniqueid)  as asnratio, avg((sessiontime - stopdelay)) as avg, (SELECT count(uniqueid) from call i LEFT OUTER JOIN resellercall ON 
(calluid=i.uniqueid) LEFT OUTER JOIN reseller ON (reseller.id=resellercall.resellerid) where starttime < '" . $yesdate . "' AND starttime > '". $daybefyes . "' AND 
reseller.id=" . $_SESSION['resellerid'] . " AND o.username=i.username GROUP BY users.fullname,i.username ORDER BY users.fullname,i.username) as precount FROM call o LEFT 
OUTER JOIN resellercall ON (calluid=o.uniqueid) LEFT OUTER JOIN reseller ON (reseller.id=resellercall.resellerid) LEFT OUTER JOIN country ON 
(o.calledcountry=country.countrycode) LEFT OUTER JOIN users ON (o.username=users.name) WHERE terminatecause != '' AND starttime < '" . $date . "' AND starttime > '" . 
$yesdate . "' AND reseller.id=" . $_SESSION['resellerid'] . " GROUP BY users.fullname,o.username ORDER BY users.fullname,o.username";

//print $cdrq . "<P>";
$cdr=pg_query($db,$cdrq);

$bcolor[0]="list-color1";
$bcolor[1]="list-color2";

%>
<link rel=stylesheet type=text/css href=/style.php>
<DIV CLASS=popup>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH ALIGN=LEFT><FONT SIZE=1>Company</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Account</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Total Calls</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Answered</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Failed Calls</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Answer Ratio</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Avg Call length</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Previos Date Count</TH>
</tr>
<%

$num=pg_num_rows($cdr);
$total="0";
$totmin="0";

for ($i=0; $i < $num; $i++) 
{
	$r = pg_fetch_row($cdr);
  	$rem=$i % 2; 
	print "<TR CLASS=" . $bcolor[$rem] . ">";

  	$total=$total+$r[2];
	$totalans=$totalans+$r[3];
	$totalfail=$totalfail+$r[4];
	$totalratio=$totalratio+$r[5];
  	$totmin=$totmin+$r[6];
	$totprecount=$totprecount+$r[7];
  
	for ($j=0;$j < count($r);$j++) 
	{
		if (($j == "6"))
		{
      			$r[$j]=gtime($r[$j]);
    		}
		print  "<TD><FONT SIZE=1>" . $r[$j] . "</TD>";
 	}
  
	print "</TR>\n";
}

$rem=$i % 2; 
print "<TR CLASS=" . $bcolor[$rem] . ">";
%>
<TD COLSPAN=2><FONT SIZE=1>&nbsp;</TD>
<TD ALIGN=LEFT><FONT SIZE=1><%print $total%></TD>
<TD ALIGN=LEFT><FONT SIZE=1><%print $totalans%></TD>
<TD ALIGN=LEFT><FONT SIZE=1><%print $totalfail%></TD>
<TD ALIGN=LEFT><FONT SIZE=1><%print ($totalratio/$num)%></TD>
<TD ALIGN=LEFT><FONT SIZE=1><%$totmin=gtime($totmin);print $totmin;%></TD>
<TD ALIGN=LEFT><FONT SIZE=1><%print $totprecount;%></TD>
</tr>
</TABLE>
</DIV>
