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

  include "func.inc";
  if ($morder == "") {
     $morder="callcnt";
  }
  if ($ADMIN_USER != "admin") {
    return;
  }

  if ($sortby == "name") {
    $sortby="fullname";
    $usortby="fullname";
    $gsortby="bgroup";
  } else if ($sortby == "exten") {
    $sortby="accountcode";
    $usortby="cdr.accountcode";
    $gsortby="bgroup";
  } else if ($sortby == "cost") {
    $sortby="sum(trunkcost.cost)";
    $usortby="fullname";
    $gsortby="sum(trunkcost.cost)";
  }

  if ($sortdown == "on") {
    $sortby.=" DESC";
    $gsortby.=" DESC";
    if ($sortby == "cost") {
      $usortby.=" DESC";
    }
  }


  function showtot($accode) {
    global $total;
    $tr=$total[$accode];
    if ($_POST['print'] < 2) {
      print "<TD CLASS=heading-body2>" . $tr[3] . "</TD>";
      print "<TD CLASS=heading-body2>" . gtime($tr[4]) . "</TD>";
      print "<TD CLASS=heading-body2>" . gtime($tr[5]) . "</TD>";
      print "<TD CLASS=heading-body2>" . gtime($tr[6]) . "</TD>";
      print "<TD CLASS=heading-body2>" . gtime($tr[7]) . "</TD>";
      print "<TD CLASS=heading-body2 ALIGN=RIGHT>" . sprintf("%0.2f",$tr[8]/100000) . "</TD></TR>\n";
    } else {
      print printcsv(array($tr[e],gtime($tr[4]),gtime($tr[5]),gtime($tr[6]),gtime($tr[7]),sprintf("%0.2f",$tr[8]/100000)));
    }
  }


  function showentry($re,$rclass,$trunk){
    global $trunkd;

    if (($re[2] == "") && ($re[0] == "") && ($re[1] =="") && ($_POST['print'] < "1")) {
      $re[2]="&nbsp;";
    } else if ($re[2] == "") {
      if ($re[0] != "") {
        $re[2]=$re[0] . " (" . $re[1] . ")";
      } else {
        $re[2]=$re[1];
      }
      if (($_POST['print'] < "1") && ($re[1] != "Unknown")) {
        if ($re[1] == $trunkd[$trunk]) {
          $re[2]="<A HREF=\"javascript:getextenrep('','','" . $trunk . "')\">" . $re[2] . "</A>";
        } else {
          $re[2]="<A HREF=\"javascript:getextenrep('" . $re[1] . "','" . $re[9] . "','" . $trunk . "')\">" . $re[2] . "</A>";
        }
      } else if (($_POST['print'] < "1") && ($re[9] == "")) {
        $re[2]="<A HREF=\"javascript:getextenrep('NULL','NULL','" . $trunk . "')\">" . $re[2] . "</A>";
      }
    } else if (($_POST['print'] < "1") && ($re[1] == "NULL") && ($re[9] != "") && ($re[2] != "Unknown")) {
        $re[2]="<A HREF=\"javascript:getextenrep('NULL','" . $re[9] . "','" . $trunk . "')\">" . $re[2] . "</A>";
    } else if (($_POST['print'] < "1") && ($re[1] == "NULL") && ($re[9] == "") && ($re[2] == "Unknown")) {
        $re[2]="<A HREF=\"javascript:getextenrep('NULL','','" . $trunk . "')\">" . $re[2] . "</A>";
    } else if (($_POST['print'] < "1") && ($re[1] != "") && ($re[9] != "")) {
        $re[2]="<A HREF=\"javascript:getextenrep('" . $re[1] . "','" . $re[9] . "','" . $trunk . "')\">" . $re[2] . "</A>";
    } else if (($_POST['print'] < "1") && ($re[1] != "") && ($re[9] == "")) {
        $re[2]="<A HREF=\"javascript:getextenrep('" . $re[1] . "','NULL','" . $trunk . "')\">" . $re[2] . "</A>";
    } else if (($_POST['print'] < "1") && ($re[1] == "") && ($re[9] != "")) {
        $re[2]="<A HREF=\"javascript:getextenrep('','" . $re[9] . "','" . $trunk . "')\">" . $re[2] . "</A>";
    } else if (($_POST['print'] < "1") && ($re[1] == "") && ($re[9] == "")) {
        $re[2]="<A HREF=\"javascript:getextenrep('','NULL','" . $trunk . "')\">" . $re[2] . "</A>";
    }

    if ($_POST['print'] < 2) {
      print "<TD" . $rclass . ">" . $re[2] . "</TD>";
      print "<TD" . $rclass . ">" . $re[3] . "</TD>";
      print "<TD" . $rclass . ">" . gtime($re[4]) . "</TD>";
      print "<TD" . $rclass . ">" . gtime($re[5]) . "</TD>";
      print "<TD" . $rclass . ">" . gtime($re[6]) . "</TD>";
      print "<TD" . $rclass . ">" . gtime($re[7]) . "</TD>";
      print "<TD" . $rclass . " ALIGN=RIGHT>" . sprintf("%0.2f",$re[8]/100000) . "</TD></TR>\n";
    } else {
      print printcsv(array($re[2],$re[3],gtime($re[4]),gtime($re[5]),gtime($re[6]),gtime($re[7]),sprintf("%0.2f",$re[8]/100000)));
    }
  }

  if ($mweight == "on") {
    $morder .=" DESC";
  }

//  $time="date_part('year',calldate) = '" . $month[1] . "' AND date_part('month',calldate) = '" . $month[0] . "'";
  $time="(calldate > '" . $month[1] . "-" . $month[0] . "-" . $month[2] . "' AND calldate < '" . $month2[1] . "-" . $month2[0] . "-" . $month2[2] . "')";


  if ($_POST['print'] < 2) {
    $rhead1="<TH ALIGN=LEFT CLASS=heading-body2>Type Of Call</TH>";

    $rhead2="<TH ALIGN=LEFT CLASS=heading-body2>Number Of Calls</TH>
           <TH ALIGN=LEFT CLASS=heading-body2>Avg Hold Time</TH><TH ALIGN=LEFT CLASS=heading-body2>Call Time</TH><TH ALIGN=LEFT CLASS=heading-body2>Av. Call Time</TH>
           <TH ALIGN=LEFT CLASS=heading-body2>Std. Dev</TH><TH ALIGN=LEFT CLASS=heading-body2>Total Cost</TH></TR>\n";
    $rhead=$rhead1 . $rhead2;
  } else {
    $rhead1=printcsv(array("Type Of Call"));
    $rhead2=printcsv(array("Number Of Calls","Avg Hold Time","Call Time","Av. Call Time","Std. Dev","Total Cost"));
    $rhead=rtrim($rhead1) . "," . $rhead2;
  }
  
  $ccnt=1;
  if ($_POST['print'] < 2) {
    print "<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>\n<TR CLASS=list-color2>";
  }
  if ($TMS_USER == 1) {
    if ($_POST['print'] < 2) {
      print "<TH COLSPAN=7 CLASS=heading-body>";
      if ($_POST['print'] != "1") {
        print "<a href=\"javascript:getextenrep('','','')>\"";
      }
      print "All Calls " . $month[1] . "/" . $month[0];
      if ($_POST['print'] != "1") {
        print "</A>";
      }
      print "</TH></TR>";
      $ccnt++;
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH ALIGN=LEFT CLASS=heading-body2>Provider</TH>" . $rhead2;
      $ccnt++;
    } else {
      print "\"All Calls " . $month[1] . "/" . $month[0] . "\"\n\"Provider\"," . $rhead2;
    }


    for($prov=0;$prov < count($tchans);$prov++) {
      $totcdrtq=" SELECT '','','',count(cdr.uniqueid),avg(duration-billsec),sum(cdr.billsec),
                        avg(cdr.billsec),stddev(cdr.billsec),sum(trunkcost.cost) 
                      FROM cdr 
                        LEFT OUTER JOIN trunkcost USING (uniqueid)";
      if ($TMS_USER == 1) {
        $totcdrtq.=" LEFT OUTER JOIN astdb as bgrp ON (accountcode=bgrp.family AND key='BGRP')";
      }

      $totcdrtq.=" WHERE disposition='ANSWERED' AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's' AND dst ~ '^[0-9]{4}[0-9]+')) AND
                        dstchannel ~ '" . $tchans[$prov] . "' AND " . $time;
      if (($SUPER_USER != 1) && ($TMS_USER == 1)) {
        $totcdrtq.=" AND " . $clogacl;
      }
      $totcdrtq.=" ORDER BY sum(cost) DESC";
//      print $totcdrtq . "<P>\n";
      $totcdrt=pg_query($db,$totcdrtq);
      $rtot[$prov]=pg_fetch_array($totcdrt,0);
      if ($_POST['print'] < 2) {
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
        $ccnt++;
      }
      $rtot[$prov][1]=$trunkd[$prov];
      showentry($rtot[$prov]," CLASS=heading-body2",$prov);
      $rtot[$prov][1]="";
    }

    $totcdrtq=" SELECT '','','',count(cdr.uniqueid),avg(duration-billsec),sum(cdr.billsec),
                        avg(cdr.billsec),stddev(cdr.billsec),sum(trunkcost.cost) 
                      FROM cdr 
                        LEFT OUTER JOIN trunkcost USING (uniqueid)";
    if ($TMS_USER == 1) {
      $totcdrtq.=" LEFT OUTER JOIN astdb as bgrp ON (accountcode=bgrp.family AND key='BGRP')";
    }
    $totcdrtq.=" WHERE disposition='ANSWERED' AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's' AND dst ~ '^[0-9]{4}[0-9]+')) AND
                        dstchannel ~ '" . $trunkchan . "' AND " . $time;
    if (($SUPER_USER != 1) && ($TMS_USER == 1)) {
      $totcdrtq.=" AND " . $clogacl;
    }
    $totcdrtq.=" ORDER BY sum(cost) DESC";
//    print $totcdrtq . "<P>\n";
    $totcdrt=pg_query($db,$totcdrtq);
    $actot=pg_fetch_array($totcdrt,0);
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
      $ccnt++;
    }
    showentry($actot," CLASS=heading-body2","");

    for($prov=0;$prov < count($tchans);$prov++) {
      $totcdrq=" SELECT '','',CASE WHEN (localrates.description IS NULL) THEN 'Unknown' ELSE localrates.description END,count(cdr.uniqueid),avg(duration-billsec),sum(cdr.billsec),
                        avg(cdr.billsec),stddev(cdr.billsec),sum(trunkcost.cost),trunkcost.price
                      FROM cdr 
                        LEFT OUTER JOIN trunkcost USING (uniqueid)
                        LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index)
                        LEFT OUTER JOIN users ON (cdr.accountcode=name)";
      if ($TMS_USER == 1) {
        $totcdrq.=" LEFT OUTER JOIN astdb as bgrp ON (cdr.accountcode=bgrp.family AND key='BGRP')";
      }

      $totcdrq.="WHERE disposition='ANSWERED' AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's' AND  dst ~ '^[0-9]{4}[0-9]+')) AND 
                        dstchannel ~ '" . $tchans[$prov] . "' AND " . $time;
      if (($SUPER_USER != 1) && ($TMS_USER == 1)) {
        $totcdrq.=" AND " . $clogacl;
      }
      $totcdrq.=" GROUP BY localrates.description,trunkcost.price
                      ORDER BY sum(trunkcost.cost) DESC";

//      print $totcdrq . ";<P>";
      $totcdr=pg_query($db,$totcdrq);
      if ($_POST['print'] < 2) {
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
        $ccnt++;
        print "<TH COLSPAN=7 CLASS=heading-body>";
        if ($_POST['print'] != "1") {
          print "<a href=\"javascript:getextenrep('','','" . $prov . "')\">";
        }
        print "All " . $trunkd[$prov] . " Calls " . $month[1] . "/" . $month[0];
        if ($_POST['print'] != "1") {
          print "</A>";
        }
        print "</TH></TR>\n";
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">" . $rhead;
      } else {
        print "\n\"All " . $trunkd[$prov] . " Calls\"\n" . $rhead;
      }
      $ccnt++;
      while($r = pg_fetch_row($totcdr)) {
        if ($_POST['print'] < 2) {
          print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
        }
        $ccnt++;
        showentry($r,"",$prov);    
      }

      if ($_POST['print'] < 2) {
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
        $ccnt++;
      }
      showentry($rtot[$prov]," CLASS=heading-body2",$prov);    
    }
  }
  if ($_POST['print'] < 2) {
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . " STYLE=\"page-break-before: always\"><TH COLSPAN=7 CLASS=heading-body>All Extensions</TH></TR>\n";
    $ccnt++;
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH CLASS=heading-body2>Extension</TH>" . $rhead2;
    $ccnt++;
  } else {
    print "\n\"All Extensions\"";
    print "\n\"Extension\"," . $rhead2;
  }

  $totcdruq=" SELECT users.fullname,cdr.accountcode,'',count(cdr.uniqueid),avg(duration-billsec),sum(cdr.billsec),
                    avg(cdr.billsec),stddev(cdr.billsec),sum(trunkcost.cost) 
                  FROM cdr 
                    LEFT OUTER JOIN trunkcost USING (uniqueid)
                    LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index)
                    LEFT OUTER JOIN users ON (cdr.accountcode=name)";
  if ($TMS_USER == 1) {
    $totcdruq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=name AND bgrp.key='BGRP')";
  } 
  $totcdruq.=" WHERE disposition='ANSWERED' AND users.name IS NOT NULL AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's'  AND  dst ~ '^[0-9]{4}[0-9]+')) AND
                    dstchannel ~ '" . $trunkchan . "' AND " . $time;
  if (($SUPER_USER != 1) && ($TMS_USER == 1)) {
    $totcdruq.=" AND " . $clogacl;
  }
  $totcdruq.=" GROUP BY users.fullname,cdr.accountcode
                  ORDER BY " . $sortby;

//  print $totcdruq . "<P>";
  $totcdru=pg_query($db,$totcdruq);
  while($r = pg_fetch_row($totcdru)) {
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
    }
    $ccnt++;
    $total[$r[1]]=$r;
    showentry($r,"","");
  }

  if ($TMS_USER != 1) {
    $totcdroq=" SELECT '','','',count(cdr.uniqueid),avg(duration-billsec),sum(cdr.billsec),
                      avg(cdr.billsec),stddev(cdr.billsec),sum(trunkcost.cost) 
                    FROM cdr 
                      LEFT OUTER JOIN trunkcost USING (uniqueid)
                      LEFT OUTER JOIN users ON (cdr.accountcode=name)
                    WHERE disposition='ANSWERED' AND users.name IS NULL AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's' AND  dst ~ '^[0-9]{4}[0-9]+')) AND
                      dstchannel ~ '" . $trunkchan . "' AND " . $time;
//    print $totcdroq . "<P>";
    $totcdro=pg_query($db,$totcdroq);
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
      $ccnt++;
    }
    $otot=pg_fetch_array($totcdro,0);
    $otot[1]="Unknown";
    showentry($otot,"","");    
  }

  $groups=array();
  if ($_POST['print'] < 2) {
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH COLSPAN=7 CLASS=heading-body>All Groups</TH></TR>\n";
    $ccnt++;
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH CLASS=heading-body2>Extension</TH>" . $rhead2;
    $ccnt++;
  } else {
    print "\n\"All Groups\"";
    print "\n\"Group\"," . $rhead2;
  }

  $totcdruq=" SELECT '',CASE WHEN (bgrp.value IS NULL OR bgrp.value = '') THEN 'Ungrouped' ELSE bgrp.value END AS bgroup,'',count(cdr.uniqueid),avg(duration-billsec),sum(cdr.billsec),
                    avg(cdr.billsec),stddev(cdr.billsec),sum(trunkcost.cost) 
                  FROM cdr 
                    LEFT OUTER JOIN trunkcost USING (uniqueid)
                    LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index)
                    LEFT OUTER JOIN users ON (cdr.accountcode=name) 
                    LEFT OUTER JOIN astdb AS bgrp ON (cdr.accountcode=family AND bgrp.key='BGRP') ";
  $totcdruq.=" WHERE disposition='ANSWERED' AND users.name IS NOT NULL AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's' AND dst ~ '^[0-9]{4}[0-9]+')) AND
                    dstchannel ~ '" . $trunkchan . "' AND " . $time;
  if (($SUPER_USER != 1) && ($TMS_USER == 1)) {
    $totcdruq.=" AND " . $clogacl;
  }
  $totcdruq.=" GROUP BY  bgroup
                  ORDER BY " . $gsortby;

//  print $totcdruq . "\n";
  $totcdru=pg_query($db,$totcdruq);
  while($r = pg_fetch_row($totcdru)) {
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
    }
    if ($r[1] != "Ungrouped") {
      array_push($groups,$r[1]);
    }
    if (($TMS_USER == 1) || ($r[1] != "Ungrouped")) {
      $ccnt++;
      showentry($r,"","G");
      $grp=$r[1];
      $r[1]="";
      $grdat[$grp]=$r;
    }
  }

  for($gcnt=0;$gcnt < count($groups);$gcnt++) {
    $totcdrq=" SELECT '','" . $groups[$gcnt] . "',CASE WHEN (localrates.description IS NULL) THEN 'Unknown' ELSE localrates.description END,count(cdr.uniqueid),avg(duration-billsec),sum(cdr.billsec),
                      avg(cdr.billsec),stddev(cdr.billsec),sum(trunkcost.cost),trunkcost.price
                    FROM cdr 
                      LEFT OUTER JOIN trunkcost USING (uniqueid)
                      LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index)
                      LEFT OUTER JOIN USERS ON (cdr.accountcode=name)
                      LEFT OUTER JOIN astdb AS bgrp ON (family=name AND key='BGRP')
                    WHERE disposition='ANSWERED' AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's' AND  dst ~ '^[0-9]{4}[0-9]+')) AND 
                      " . $time . " AND value = '" . $groups[$gcnt] . "' AND dstchannel ~ '" . $trunkchan . "'";
    $totcdrq.=" GROUP BY localrates.description,trunkcost.price
                    ORDER BY sum(trunkcost.cost) DESC";
//    print $totcdrq . ";<P>";
    $totcdr=pg_query($db,$totcdrq);
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . " STYLE=\"page-break-before: always\">";
      $ccnt++;
      print "<TH COLSPAN=7 CLASS=heading-body>";
      if ($_POST['print'] != "1") {
        print "<a href=\"javascript:getextenrep('" . $groups[$gcnt] . "','','G')\">";
      }
      print "All Calls " . $month[1] . "/" . $month[0] . " (" . $groups[$gcnt] . " Group)";
      if ($_POST['print'] != "1") {
        print "</A>";
      }
      print "</TH></TR>\n";
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">" . $rhead;
    } else {
      print "\n\"All Calls " . $month[1] . "/" . $month[0] . " (" . $groups[$gcnt] . " Group)\"\n" . $rhead;
    }
    $ccnt++;
    while($r = pg_fetch_row($totcdr)) {
      if ($_POST['print'] < 2) {
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
      }
      $ccnt++;
      showentry($r,"","G");
    }

    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
      $ccnt++;
    }
    showentry($grdat[$groups[$gcnt]],"","");

    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH COLSPAN=7 CLASS=heading-body>All Extensions (" . $groups[$gcnt] . " Group)</TH></TR>\n";
      $ccnt++;
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH CLASS=heading-body2>Extension</TH>" . $rhead2;
      $ccnt++;
    } else {
      print "\n\"All Extensions (" . $groups[$gcnt] . " Group)\"";
      print "\n\"Extension\"," . $rhead2;
    }

    $totcdruq=" SELECT users.fullname,cdr.accountcode,'',count(cdr.uniqueid),avg(duration-billsec),sum(cdr.billsec),
                      avg(cdr.billsec),stddev(cdr.billsec),sum(trunkcost.cost) 
                    FROM cdr 
                      LEFT OUTER JOIN trunkcost USING (uniqueid)
                      LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index)
                      LEFT OUTER JOIN users ON (cdr.accountcode=name)
                      LEFT OUTER JOIN astdb AS bgrp ON (family=name AND key='BGRP')
                    WHERE disposition='ANSWERED' AND users.name IS NOT NULL AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's'  AND  dst ~ '^[0-9]{4}[0-9]+')) AND
                      dstchannel ~ '" . $trunkchan . "' AND " . $time . " AND value = '" . $groups[$gcnt] . "' 
                    GROUP BY users.fullname,cdr.accountcode
                    ORDER BY " . $sortby;
    $totcdru=pg_query($db,$totcdruq);
    while($r = pg_fetch_row($totcdru)) {
      if ($_POST['print'] < 2) {
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
      }
      $ccnt++;
      showentry($r,"","");    
    }
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
    }
    $ccnt++;
    showentry($grdat[$groups[$gcnt]],"","");
  }

  $getcdrq=" SELECT users.fullname,cdr.accountcode,CASE WHEN (localrates.description IS NULL) THEN 'Unknown' ELSE localrates.description END,
                    count(cdr.uniqueid),avg(duration-billsec),sum(cdr.billsec),
                    avg(cdr.billsec),stddev(cdr.billsec),sum(trunkcost.cost),trunkcost.price
                  FROM cdr 
                    LEFT OUTER JOIN trunkcost USING (uniqueid)
                    LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index)
                    LEFT OUTER JOIN USERS ON (cdr.accountcode=name)";
    if ($TMS_USER == 1) {
      $getcdrq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=name AND bgrp.key='BGRP')";
    }
    $getcdrq.=" WHERE disposition='ANSWERED' AND users.name IS NOT NULL AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's'  AND  dst ~ '^[0-9]{4}[0-9]+')) AND
                    dstchannel ~ '" . $trunkchan . "' AND " . $time;
  if (($SUPER_USER != 1) && ($TMS_USER == 1)) {
    $getcdrq.=" AND " . $clogacl;
  }
  $getcdrq.=" GROUP BY users.fullname,cdr.accountcode,localrates.description,trunkcost.price
                  ORDER BY " . $usortby;
//    print $getcdrq . ";<P>";
    $getcdr=pg_query($db,$getcdrq);

  $last="";
  while($r = pg_fetch_row($getcdr)) {
    if ($last != $r[1]) {
      if ($last != "") {
        if ($_POST['print'] < 2) {
          print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TD>&nbsp;</TD>";
        } else {
          print ",";
        }
        $ccnt++;
        showtot($last);
      }
      if ($_POST['print'] < 2) {
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . " STYLE=\"page-break-before: always\"><TH COLSPAN=7 CLASS=heading-body>";
        if (($r[1] != "") && ($_POST['print'] != "1")) {
          print "<A HREF=\"javascript:getextenrep('" . $r[1] . "','')\">";
        }
        print "Call Summary For ";
        if ($r[0] != "") {
          print $r[0] . " (" . $r[1] . ") " . $month[1] . "/" . $month[0] . "</A>";
        } else if ($r[1] != "") {
          print $r[1];
          print $month[1] . "/" . $month[0];
          print "</A>";
        } else {
          print "Unknown";
        }
        print "</TH></TR>\n";
        $ccnt++;
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">" . $rhead;
        $ccnt++;
      } else {
        print "\n\"";
        if ($r[0] != "") {
          print $r[0] . " (" . $r[1] . ")";
        } else if ($r[1] != "") {
          print $r[1];
        } else {
          print "Unknown";
        }
        print "\"\n" . $rhead;
      }
      $last=$r[1];
    }
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
    }
    $ccnt++;
    showentry($r,"","");    
  }
  if ($_POST['print'] < 2) {
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TD>&nbsp;</TD>";
    $ccnt++;
  } else {
    print ",";
  }
  showtot($last);

  $getcdrukq=" SELECT '','',CASE WHEN (localrates.description IS NULL) THEN 'Unknown' ELSE localrates.description END,count(cdr.uniqueid),avg(duration-billsec),sum(cdr.billsec),
                    avg(cdr.billsec),stddev(cdr.billsec),sum(trunkcost.cost),trunkcost.price
                  FROM cdr 
                    LEFT OUTER JOIN trunkcost USING (uniqueid)
                    LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index)
                    LEFT OUTER JOIN users ON (cdr.accountcode=name)
                  WHERE disposition='ANSWERED' AND users.name IS NULL AND (trunkcost.cost >= 0 OR (trunkcost.cost IS NULL AND dst != 's'  AND  dst ~ '^[0-9]{4}[0-9]+')) AND 
                    dstchannel ~ '" . $trunkchan . "' AND" . $time . "
                  GROUP BY localrates.description,trunkcost.price
                  ORDER BY " . $sortby;
//  print $getcdrukq . "\n";
  if ($SUPER_USER == 1) {
    $getcdruk=pg_query($db,$getcdrukq);
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "  STYLE=\"page-break-before: always\"><TH COLSPAN=7 CLASS=heading-body>";
      if ($_POST['print'] != "1") {
        print "<a href=\"javascript:getextenrep('NULL','NULL','')\">";
      }
      print "Call Summary For Unknown Caller " . $month[1] . "/" . $month[0];
      if ($_POST['print'] != "1") {
        print "</A>";
      }
      print "</TH></TR>\n";
      $ccnt++;
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">" . $rhead;
      $ccnt++;
    } else {
      print "\n\"Unknown\"\n" . $rhead;
    }
    while($r = pg_fetch_row($getcdruk)) {
      if ($_POST['print'] < 2) {
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
      }
      $ccnt++;
      $r[1]="NULL";
      showentry($r,"","");    
    }
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
    }
    $ccnt++;
    $otot[1]="";
    showentry($otot," CLASS=heading-body2","");
  }
  if ($_POST['print'] < 2) {
    if ($_POST['print'] != "1") {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH COLSPAN=7 CLASS=heading-body>";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.allrep)\">";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.allrep)\"></TH></TR>";
    }
    print "</TABLE>";
  }
if ($_POST['print'] < 2) {
%>
<FORM METHOD=POST NAME=allrep>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $_SESSION['disppage'];%>">
<INPUT TYPE=HIDDEN NAME=print VALUE="0">
<INPUT TYPE=HIDDEN NAME=type VALUE="<%print $type;%>">
<INPUT TYPE=HIDDEN NAME=exep VALUE="<%print $exep;%>">
<INPUT TYPE=HIDDEN NAME=xexep VALUE="<%print $xexep;%>">
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
<INPUT TYPE=HIDDEN NAME=date2 VALUE="<%print $date2;%>">
<INPUT TYPE=HIDDEN NAME=dom VALUE="<%print $dom;%>">
<INPUT TYPE=HIDDEN NAME=dom2 VALUE="<%print $dom2;%>">
<INPUT TYPE=HIDDEN NAME=sortby VALUE="<%print $sortby;%>">
<INPUT TYPE=HIDDEN NAME=gsortby VALUE="<%print $gsortby;%>">
<INPUT TYPE=HIDDEN NAME=usortby VALUE="<%print $usortby;%>">
<INPUT TYPE=HIDDEN NAME=disp>
<INPUT TYPE=HIDDEN NAME=exten>
</FORM>
<FORM METHOD=POST NAME=extenrep>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cdr/dextenr.php">
<INPUT TYPE=HIDDEN NAME=type VALUE="">
<INPUT TYPE=HIDDEN NAME=trunk VALUE="">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE="1">
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date%>">
<INPUT TYPE=HIDDEN NAME=date2 VALUE="<%print $date2;%>">
<INPUT TYPE=HIDDEN NAME=dom VALUE="<%print $dom;%>">
<INPUT TYPE=HIDDEN NAME=dom2 VALUE="<%print $dom2;%>">
<INPUT TYPE=HIDDEN NAME=mweight VALUE="on">
<INPUT TYPE=HIDDEN NAME=morder VALUE="sum(cost)">
<INPUT TYPE=HIDDEN NAME=disp VALUE="ANSWERED">
<INPUT TYPE=HIDDEN NAME=exten>
<INPUT TYPE=HIDDEN NAME=pclass>
</FORM><%
}
%>
