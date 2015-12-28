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
if (! $db) {
  print "<link rel=stylesheet type=text/css href=/netsentry.php>";
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}

$bcolor[0]="list-color1";
$bcolor[1]="list-color2";

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

if (isset($_POST['showrep'])) {
  $_POST['periode']=stripcslashes($_POST['periode']);
  $_POST['periods']=stripcslashes($_POST['periods']);
  if ($_POST['orderby'] == "dest" ) {
    $_POST['orderby']="countryname,calledsub";
    $title="Destination";
  } else if ($_POST['orderby'] == "username") {
    $_POST['orderby']="users.fullname,call.username";
    $title="User";
  } else if ($_POST['orderby'] == "day") {
    $_POST['orderby']="to_char(starttime,'Month \"Day\"'),to_char(starttime,'DD')";
    $title="Day Of Month";
  } else if ($_POST['orderby'] == "week") {
    $_POST['orderby']="to_char(starttime,'Month \"Week\"'),to_char(starttime,'WW')";
    $title="Week Of Year";
  } else if ($_POST['orderby'] == "month") {
    $_POST['orderby']="to_char(starttime,'Month'),to_char(starttime,'MM')";
    $title="Month Of Year";
  } else if ($_POST['orderby'] == "quater") {
    $_POST['orderby']="to_char(starttime,'YYYY'),to_char(starttime,'Qth \"Quater\"')";
    $title="Quater Of Year";
  } else if ($_POST['orderby'] == "half") {
    $_POST['orderby']="to_char(starttime,'YYYY'),CASE WHEN to_char(starttime,'Q') <= 2 THEN '1st Half'  WHEN to_char(starttime,'Q') > 2 THEN '2nd Half' END";
    $title="Half Of Year";
  }

  if ($_SESSION['repshow'] == "money") {
    $query="SELECT " . $_POST['orderby'] . ",count(call.uniqueid) as sescount,
              avg(resellercall.buyrate*resellercall.exchangerate)/10000,
              avg(sellrate*resellercall.exchangerate)/10000 as avcalledrate,
              avg(buycost*resellercall.exchangerate) / 10000,
              avg(sellcost*resellercall.exchangerate)/10000,
              sum(buycost*resellercall.exchangerate)/10000,
              sum(rsessionbill)/100,
              sum(outtax-intax),
              sum(rsessionbill- buycost*resellercall.exchangerate/100-outtax/100+intax/100)/100 ";
    $tcause="= 'ANSWER'";
  } else {
    $query="SELECT " . $_POST['orderby'] . ",count(call.uniqueid) as sescount,
         count(CASE WHEN terminatecause = 'ANSWER' THEN terminatecause END) as anscnt,
         count(CASE WHEN terminatecause = 'NOANSWER' THEN terminatecause END) as noanscnt,
         count(CASE WHEN terminatecause = 'CANCEL' THEN terminatecause END) as cancelcnt,
         count(CASE WHEN terminatecause = 'CHANUNAVAIL' THEN terminatecause END) as unavailcnt,
         (100 * count(CASE WHEN terminatecause = 'ANSWER' THEN terminatecause END)) / count(call.uniqueid) as asr,
         avg((sessiontime - stopdelay)) as mht,sum(sessiontime)-sum(stopdelay), sum(sessiontime) ";
    $tcause="!= ''";
  }

  if ($allcalls == "on") {
    $allcalls="(reseller.id=" . $_SESSION['resellerid'] . " OR reseller.owner=" . $_SESSION['resellerid'] . ")";
  } else {
    $allcalls="reseller.id=" . $_SESSION['resellerid'];
  }


  $query=$query . "FROM call LEFT OUTER JOIN resellercall ON (calluid=call.uniqueid)
          LEFT OUTER JOIN reseller ON (reseller.id=resellercall.resellerid)
          LEFT OUTER JOIN country ON (call.calledcountry=country.countrycode)
          LEFT OUTER JOIN users ON (call.username=users.name)
  WHERE terminatecause $tcause AND " . $_POST['periode'] . " AND " . $_POST['periods'] . " AND $allcalls
  GROUP BY " . $_POST['orderby'] . "
  ORDER BY " . $_POST['orderby'];

//  print $_POST['type'] . "<BR>" . $query . "<BR>";

  $crep=pg_query($query);
  $num=pg_num_rows($crep);
  print "<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>";
if ($_SESSION['repshow'] == "money") {
?>
<TR CLASS=list-color2>
<TH CLASS=heading-body2><?php print $title;?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;TA</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;BR</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;SR</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;AB</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;AS</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;B</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;S</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;NT</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;GP</TH>
</TR>
<?php
} else {
?>
<TR CLASS=list-color2>
<TH CLASS=heading-body2><?php print $title;?></TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;T</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;A</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;N</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;C</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;U</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;ASR</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;MHT</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;B</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>&nbsp;S</TH>
</TR>
<?php
}
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_row($crep,$i);
    $rem=$i % 2;
    print "<TR CLASS=" . $bcolor[$rem] . ">";
    for ($j=0;$j < count($r);$j++) {
      $total[$j]=$total[$j]+$r[$j];
      if ($j == "0") {
        $r[$j]=$r[$j] . " (" . $r[$j+1] . ")";
      } else if (($j >= "7") && ($j < 8) && ($_SESSION['repshow'] == "time")){
        $r[$j]=sprintf("%0.2f",$r[$j]);
      } else if (($j == "9") && ($_SESSION['repshow'] == "money")){
        $r[$j]=sprintf("%0.2f",$r[$j]/10000);
      } else if (($j >= "3") && ($_SESSION['repshow'] == "money")){
        $r[$j]=sprintf("%0.2f",$r[$j]);
      } else if (($j >= "8") && ($_SESSION['repshow'] == "time")){
        if ($j == "8") {
          $r[$j]=$r[$j]*$r[2];
          if ($r[3] > 0) {
            $r[$j]=$r[$j]/$r[3];
          } else {
            $r[$j]=0;
          }
        }
        $r[$j]=gtime($r[$j]);
      }
      if ($j != 1) {
        if ($j >= 2) {
          print  "<TD ALIGN=RIGHT>&nbsp;";
        } else {
          print  "<TD>";
        }
        print $r[$j] . "</TD>";
      }
    }
    print "</TR>\n";
  }
//  $i++;
  $r = $total;
  $rem=$i % 2;
  print "<TR CLASS=" . $bcolor[$rem] . ">";
  for ($j=0;$j < count($r);$j++) {
    $total[$j]=$total[$j]+$r[$j];
    if ($j == "0") {
      $r[$j]="Totals";
    } else if (($j >= "7") && ($j <= "8") && ($_SESSION['repshow'] == "time")){
      $r[$j]=$r[$j]/$i;
      if ($j == "7") {
	$r[$j]=sprintf("%0.2f",$r[$j]);
      } else {
	$r[$j]=gtime($r[$j]);
      }
    } else if (($j == "9") && ($_SESSION['repshow'] == "money")){
      $r[$j]=sprintf("%0.2f",$r[$j]/10000);
    } else if (($j >= "3") && ($_SESSION['repshow'] == "money")){
      if ($j <= "6") {
        $r[$j]=$r[$j]/$i;
      }
      $r[$j]=sprintf("%0.2f",$r[$j]);
    } else if (($j >= "8") && ($_SESSION['repshow'] == "time")){
      $r[$j]=gtime($r[$j]);
    }
    if ($j != 1) {
      if ($j >= 2) {
        print  "<TD ALIGN=RIGHT>&nbsp;";
      } else {
        print  "<TD>";
      }
      print $r[$j] . "</TD>";
    }
  }
  print "</TR>\n";
  $i++;
  $bcol[0]=$i % 2;
  $bcol[1]=($i+1) % 2;
  if ($_SESSION['repshow'] == "money") {
    $omargin=sprintf("%0.2f",(1-$total[7]/$total[8])*100);
    $omarkup=sprintf("%0.2f",($total[8]/$total[7]-1)*100);
    $pgp=sprintf("%0.2f",($total[10]/$total[8])*100);
    ?><TR CLASS=<?php print $bcolor[$bcol[0]];?>>
      <TD ALIGN=LEFT>Overall Margin</TD>
      <TD COLSPAN=9 ALIGN=RIGHT><?php print $omargin;?>%</TD></TR><?php
    $i++;
    $bcol[0]=$i % 2;
    $bcol[1]=($i+1) % 2;
    ?><TR CLASS=<?php print $bcolor[$bcol[0]];?>>
      <TD ALIGN=LEFT>Overall Markup</TD>
      <TD COLSPAN=9 ALIGN=RIGHT><?php print $omarkup;?>%</TD></TR><?php
    $i++;
    $bcol[0]=$i % 2;
    $bcol[1]=($i+1) % 2;
    ?><TR CLASS=<?php print $bcolor[$bcol[0]];?>>
      <TD ALIGN=LEFT>GP % Of Sales</TD>
      <TD COLSPAN=9 ALIGN=RIGHT><?php print $pgp;?>%</TD></TR><?php
    $i++;
    $bcol[0]=$i % 2;
    $bcol[1]=($i+1) % 2;
  }


if ($_SESSION['repshow'] == "time") {
?>	
<TR  CLASS=<?php print $bcolor[$bcol[0]];?>><TH COLSPAN=10 CLASS=heading-body2>Legend</TH></TR>
<TR  CLASS=<?php print $bcolor[$bcol[1]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>T</TH>
  <TD COLSPAN=9>Total Calls</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[0]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>A</TH>
  <TD COLSPAN=9>Answered Calls</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[1]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>N</TH>
  <TD COLSPAN=9>Unanswered Calls</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[0]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>C</TH>
  <TD COLSPAN=9>Canceled Calls</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[1]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>U</TH>
  <TD COLSPAN=9>Unavailable Route</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[0]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>ASR</TH>
  <TD COLSPAN=9>Average Strike Rate (Provider Quality)</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[1]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>MHT</TH>
  <TD COLSPAN=9>Average Call Time</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[0]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>B</TH>
  <TD COLSPAN=9>Bought (m)</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[1]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>S</TH>
  <TD COLSPAN=9>Sold (m)</TD>
</TR>
<?php
} else {
?>	
<TR  CLASS=<?php print $bcolor[$bcol[0]];?>><TH CLASS=heading-body2 COLSPAN=10>Legend</TH></TR>
<TR  CLASS=<?php print $bcolor[$bcol[1]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>TA</TH>
  <TD COLSPAN=9>Total Answered Calls</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[0]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>BR</TH>
  <TD COLSPAN=9>Average Buy Rate</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[1]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>SR</TH>
  <TD COLSPAN=9>Average Sell Rate</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[0]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>AB</TH>
  <TD COLSPAN=9>Average Session Cost</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[1]];?>>
  <TH COLSPAN=1 ALIGN=LEFT>AS</TH>
  <TD COLSPAN=9>Average Session Bill</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[0]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>B</TH>
  <TD COLSPAN=9>Total Cost (TAX Incl.)</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[1]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>S</TH>
  <TD COLSPAN=9>Total Sales (TAX Incl.)</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[0]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>NT</TH>
  <TD COLSPAN=9>Net Tax (TAX Owing)</TD>
</TR>
<TR  CLASS=<?php print $bcolor[$bcol[1]];?>>
  <TH CLASS=heading-body2 COLSPAN=1 ALIGN=LEFT>GP</TH>
  <TD COLSPAN=9>Gross Profit (After Net Tax)</TD>
</TR>
<?php
}
} else if (isset($_POST['type'])) {
  function getper($ptype) {
    $thisweek=date("W");
    $thismonth=date("n");
    if ($_POST['type'] == "5") {
      $thisday=date("z");
      $leap=date("L",mktime(0,0,0,1,1,$_POST['year']));
      for ($i=0;$i<364+$leap;$i++) {
        $i2=$i+1;
        print "<OPTION VALUE=\"date_part('doy',starttime)" . $ptype . $i2 . "\"";
        if ($i == $thisday) {
          print " SELECTED";
        }
        print ">" . date("Y/m/d",strtotime("-$thisday days +$i days")) . "</OPTION>\n";
      }
    } else if ($_POST['type'] == "4") {
      $first=date("w",mktime(0,0,0,1,1,$_POST['year']));
      if ($first > 4) {
        $week1=mktime(0,0,0,1,9-$first,$_POST['year']);
      } else {
        $week1=mktime(0,0,0,1,2-$first,$_POST['year']);
      }
      for ($i=1;$i<=53;$i++) {
        print "<OPTION VALUE=\"date_part('week',starttime) " . $ptype . date("W",$week1) . "\"";
        if ($i == $thisweek) {
          print " SELECTED";
        }
        print ">Week " . date("W",$week1) . " (" . date("Y/m/d",$week1) . ")</OPTION>\n";
        $week1=$week1+86400*7;
        if (date("W",$week1) == 1) {
          break;
        }
      }
    } else if ($_POST['type'] == "3") {
      for($q=1;$q<=12;$q++) {
        print "<OPTION VALUE=\"date_part('month',starttime)" . $ptype . $q ."\"";
        if ($q == $thismonth) {
          print " SELECTED";
        }
        print ">" . date("F",mktime(0,0,0,$q,1,$_POST['year'])) . "</OPTION>\n";
      }
    } else if ($_POST['type'] == "2") {
      for($q=1;$q<=4;$q++) {
        print "<OPTION VALUE=\"date_part('quarter',starttime)" . $ptype . $q ."\"";
        if ($q == (($thismonth - ($thismonth % 3))/3)) {
          print " SELECTED";
        }
        print ">Quarter " . $q ."</OPTION>\n";
      }
    } else if ($_POST['type'] == "1") {
      if ($ptype == " >= ") {
        $range=" 0 ";
        $range1=" 2 ";
        $ptype=" >= ";
        $ptype1=" > ";
      } else {
        $range=" 2 ";
        $range1=" 4 ";
        $ptype=" <= ";
        $ptype1=" <= ";
      }
      print "<OPTION VALUE=\"date_part('quarter',starttime)" . $ptype . $range . "\">1 Half</OPTION>\n";
      print "<OPTION VALUE=\"date_part('quarter',starttime)" . $ptype1 . $range1 . "\"";
      if ($thismonth > 6) {
        print " SELECTED";
      }
      print ">2 Half</OPTION>\n";
    } else if ($_POST['type'] == "0") {
      print "<OPTION VALUE=\"date_part('year',starttime) = " . $_POST['year'] ."\">Full Year</OPTION>\n";
    }
  }
?>
<FORM METHOD=POST NAME=repfrm2 onsubmit="ajaxsubmit(this.name);return false">
<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Select Report Parameters</TH>
<TR CLASS=list-color1>
<TD WIDTH=50%>Select Period To Begin Report For</TH>
<TD>
<SELECT NAME=periods>
<?php
getper(" >= ");
?>
</SELECT></TH></TR>
<TR CLASS=list-color2>
<TD>Select Period To End Report For</TD>
<TD>
<SELECT NAME=periode>
<?php
getper(" <= ");
?>
</SELECT></TH></TR>
<TR CLASS=list-color1><TD>
Show Report By</TD><TD>
<SELECT NAME=orderby>
  <OPTION VALUE=dest>Called Destination</OPTION>
  <OPTION VALUE=username>Username</OPTION>
<?php
  if ($_POST['type'] <= "0" ) {
    print "<OPTION VALUE=half>Half Year</OPTION>\n";
  }
  if ($_POST['type'] <= "1" ) {
    print "<OPTION VALUE=quater>Quater Year</OPTION>\n";
  }
  if ($_POST['type'] <= "2" ) {
  }
  if ($_POST['type'] <= "3" ) {
  }
  if ($_POST['type'] <= "4" ) {
  }
    print "<OPTION VALUE=month>Month</OPTION>\n";
    print "<OPTION VALUE=week>Week</OPTION>\n";
    print "<OPTION VALUE=day>Day</OPTION>\n";
?>
</SELECT></TH><TR>
<TR CLASS=list-color2><TD ALIGN=MIDDLE COLSPAN=2>
<INPUT TYPE=SUBMIT NAME=showrep VALUE="Show Report">
</TH></TR>
</FORM>
<?php
} else {
?>
<FORM METHOD=POST NAME=repfrm1 onsubmit="ajaxsubmit(this.name);return false">
<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>
Report For All Calls
</TR>
<TR CLASS=list-color1>
<TD WIDTH=50%>Select Extent Of Report</TD>
<TD><SELECT NAME=type>
<OPTION VALUE=5>One Day</OPTION>
<OPTION VALUE=4>One Week</OPTION>
<OPTION VALUE=3>One Month</OPTION>
<OPTION VALUE=2>One Quater</OPTION>
<OPTION VALUE=1>One Half</OPTION>
<OPTION VALUE=0>Whole Year</OPTION>
</SELECT></TD></TR>
<TR CLASS=list-color2>
<TD>Year To Report</TD>
<TD>
<?php

$getyear=pg_query("SELECT DISTINCT date_part('year',starttime) as year
           FROM call LEFT OUTER JOIN resellercall ON (calluid=call.uniqueid)
           LEFT OUTER JOIN reseller ON (reseller.id=resellercall.resellerid)
           LEFT OUTER JOIN country ON (call.calledcountry=country.countrycode)
         WHERE (reseller.id=" . $_SESSION['resellerid'] . " OR reseller.owner=" . $_SESSION['resellerid'] . ")
          ORDER BY year");
print "<SELECT NAME=year>";

$thisyear=date("Y");
for($i=0;$i<pg_num_rows($getyear);$i++) {
  $year=pg_fetch_array($getyear);
  print "<OPTION VALUE=" . $year[0];
  if ($year[0] == $thisyear) {
    print " SELECTED";
  }
  print ">" . $year[0] . "</OPTION>\n";
}
?>
</SELECT>
</TD></TR>
<TR CLASS=list-color1><TD>
Display</TD><TD>
<SELECT NAME=repshow>
  <OPTION VALUE="time">Call Information</OPTIONS>
  <OPTION VALUE="money">Financial Information</OPTIONS>
</SELECT></TD></TR>
<TR CLASS=list-color2><TD ALIGN=MIDDLE COLSPAN=2>
<INPUT TYPE=SUBMIT NAME=setrep VALUE="Show Report">
</TD></TR>
</FORM>
<?php
}
?>
</TABLE>
