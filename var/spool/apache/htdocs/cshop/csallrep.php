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


  include_once "/var/spool/apache/htdocs/cdr/func.inc";

  $cspan=13;

  if ($morder == "") {
     $morder="callcnt";
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
      print "<TD CLASS=heading-body2 ALIGN=RIGHT>" . sprintf("%0.2f",$tr[7]/100000) . "</TD>\n";
      print "<TD CLASS=heading-body2 ALIGN=RIGHT>" . sprintf("%0.2f",$tr[8]/10000) . "</TD>\n";
      print "<TD CLASS=heading-body2 ALIGN=RIGHT>" . sprintf("%0.2f",$tr[10]/10000) . "</TD>\n";
      print "<TD CLASS=heading-body2 ALIGN=RIGHT>" . sprintf("%0.2f",$tr[9]/10000) . "</TD>\n";
      print "<TD CLASS=heading-body2 ALIGN=RIGHT>" . sprintf("%0.2f",$tr[11]/10000) . "</TD>\n";
      print "<TD CLASS=heading-body2 ALIGN=RIGHT>" . sprintf("%0.2f",($tr[7]/100000)-($tr[9]/10000)) . "</TD>\n";
      print "<TD CLASS=heading-body2 ALIGN=RIGHT>" . sprintf("%0.2f",($tr[9]/10000)-($tr[8]/10000)) . "</TD>\n";
      print "<TD CLASS=heading-body2 ALIGN=RIGHT>" . sprintf("%0.2f",($tr[11]/10000)-($tr[10]/10000)) . "</TD></TR>\n";
    } else {
      print printcsv(array($tr[e],gtime($tr[4]),gtime($tr[5]),gtime($tr[6]),sprintf("%0.2f",$tr[7]/100000),sprintf("%0.2f",$tr[8]/10000),
                           sprintf("%0.2f",$tr[10]/10000),sprintf("%0.2f",$tr[9]/10000),sprintf("%0.2f",$tr[11]/10000),sprintf("%0.2f",($tr[7]/100000)-($tr[9]/10000)),
                           sprintf("%0.2f",($tr[9]/10000)-($tr[8]/10000)),sprintf("%0.2f",($tr[11]/10000)-($tr[10]/10000))));
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
/*
      if (($_POST['print'] < "1") && ($re[1] != "Unknown")) {
        if ($re[1] == $trunkd[$trunk]) {
          $re[2]="<A HREF=\"javascript:getextenrep('','','" . $trunk . "')\">" . $re[2] . "</A>";
        } else {
          $re[2]="<A HREF=\"javascript:getextenrep('" . $re[1] . "','" . $re[9] . "','" . $trunk . "')\">" . $re[2] . "</A>";
        }
      } else if (($_POST['print'] < "1") && ($re[9] == "")) {
        $re[2]="<A HREF=javascript:getextenrep('NULL','NULL','" . $trunk . "')>" . $re[2] . "</A>";
      }
    } else if (($_POST['print'] < "1") && ($re[1] == "NULL") && ($re[9] != "") && ($re[2] != "Unknown")) {
        $re[2]="<A HREF=javascript:getextenrep('NULL','" . $re[9] . "','" . $trunk . "')>" . $re[2] . "</A>";
    } else if (($_POST['print'] < "1") && ($re[1] == "NULL") && ($re[9] == "") && ($re[2] == "Unknown")) {
        $re[2]="<A HREF=javascript:getextenrep('NULL','','" . $trunk . "')>" . $re[2] . "</A>";
    } else if (($_POST['print'] < "1") && ($re[1] != "") && ($re[9] != "")) {
        $re[2]="<A HREF=javascript:getextenrep('" . $re[1] . "','" . $re[9] . "','" . $trunk . "')>" . $re[2] . "</A>";
    } else if (($_POST['print'] < "1") && ($re[1] != "") && ($re[9] == "")) {
        $re[2]="<A HREF=javascript:getextenrep('" . $re[1] . "','NULL','" . $trunk . "')>" . $re[2] . "</A>";
    } else if (($_POST['print'] < "1") && ($re[1] == "") && ($re[9] != "")) {
        $re[2]="<A HREF=javascript:getextenrep('','" . $re[9] . "','" . $trunk . "')>" . $re[2] . "</A>";
    } else if (($_POST['print'] < "1") && ($re[1] == "") && ($re[9] == "")) {
        $re[2]="<A HREF=javascript:getextenrep('','NULL','" . $trunk . "')>" . $re[2] . "</A>";
*/
    }

    if ($_POST['print'] < 2) {
      print "<TD" . $rclass . ">" . $re[2] . "</TD>";
      print "<TD" . $rclass . ">" . $re[3] . "</TD>";
      print "<TD" . $rclass . ">" . gtime($re[4]) . "</TD>";
      print "<TD" . $rclass . ">" . gtime($re[5]) . "</TD>";
      print "<TD" . $rclass . ">" . gtime($re[6]) . "</TD>";
      print "<TD" . $rclass . " ALIGN=RIGHT>" . sprintf("%0.2f",$re[7]/100000) . "</TD>";
      print "<TD" . $rclass . " ALIGN=RIGHT>" . sprintf("%0.2f",$re[8]/10000) . "</TD>";
      print "<TD" . $rclass . " ALIGN=RIGHT>" . sprintf("%0.2f",$re[10]/10000) . "</TD>";
      print "<TD" . $rclass . " ALIGN=RIGHT>" . sprintf("%0.2f",$re[9]/10000) . "</TD>";
      print "<TD" . $rclass . " ALIGN=RIGHT>" . sprintf("%0.2f",$re[11]/10000) . "</TD>";
      print "<TD" . $rclass . " ALIGN=RIGHT>" . sprintf("%0.2f",($re[7]/100000)-($re[9]/10000)) . "</TD>";
      print "<TD" . $rclass . " ALIGN=RIGHT>" . sprintf("%0.2f",($re[9]/10000)-($re[8]/10000)) . "</TD>";
      print "<TD" . $rclass . " ALIGN=RIGHT>" . sprintf("%0.2f",($re[11]/10000)-($re[10]/10000)) . "</TD>";
      print "</TR>\n";
    } else {
      print printcsv(array($re[2],$re[3],gtime($re[4]),gtime($re[5]),gtime($re[6]),sprintf("%0.2f",$re[7]/100000),sprintf("%0.2f",$re[8]/10000),
                           sprintf("%0.2f",$re[10]/10000),sprintf("%0.2f",$re[9]/10000),sprintf("%0.2f",$re[11]/10000),sprintf("%0.2f",($re[7]/100000)-($re[9]/10000)),
                           sprintf("%0.2f",($re[9]/10000)-($re[8]/10000)),sprintf("%0.2f",($re[11]/10000)-($re[10]/10000))));
    }
  }

  if ($mweight == "on") {
    $morder .=" DESC";
  }


//  $time="date_part('year',calldate) = '" . $month[1] . "' AND date_part('month',calldate) = '" . $month[0] . "'";
  $time="(starttime >= '" . $month[1] . "-" . $month[0] . "-" . $month[2] . " 00:00:00' AND starttime <= '" . $month2[1] . "-" . $month2[0] . "-" . $month2[2] . " 24:00:00')";


  if ($_POST['print'] < 2) {
    $rhead1="<TH ALIGN=LEFT CLASS=heading-body2>Type Of Call</TH>";

    $rhead2="<TH ALIGN=LEFT CLASS=heading-body2>Number Of Calls</TH>
           <TH ALIGN=LEFT CLASS=heading-body2>Call Time</TH><TH ALIGN=LEFT CLASS=heading-body2>Av. Call Time</TH>
           <TH ALIGN=LEFT CLASS=heading-body2>Std. Dev</TH><TH ALIGN=RIGHT CLASS=heading-body2>Telkom</TH>
           <TH ALIGN=RIGHT CLASS=heading-body2>Bought</TH><TH ALIGN=RIGHT CLASS=heading-body2>Tax (In)</TH>
           <TH ALIGN=RIGHT CLASS=heading-body2>Sold</TH><TH ALIGN=RIGHT CLASS=heading-body2>Tax (Out)</TH>
           <TH ALIGN=RIGHT CLASS=heading-body2>Savings</TH><TH ALIGN=RIGHT CLASS=heading-body2>Profit</TH>
           <TH ALIGN=RIGHT CLASS=heading-body2>Net Tax</TH></TR>\n";
    $rhead=$rhead1 . $rhead2;
  } else {
    $rhead1=printcsv(array("Type Of Call"));
    $rhead2=printcsv(array("Number Of Calls","Call Time","Av. Call Time","Std. Dev","Telkom","Bought","Tax (In)","Sold","Tax (Out)","Savings","GP","Net Tax"));
    $rhead=rtrim($rhead1) . "," . $rhead2;
  }
  
  $ccnt=1;
  if ($_POST['print'] < 2) {
    print "<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>\n<TR CLASS=list-color2>";
  }

  if ($_POST['print'] < 2) {
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . " STYLE=\"page-break-before: always\"><TH COLSPAN=" . $cspan . " CLASS=heading-body>All Resellers</TH></TR>\n";
    $ccnt++;
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH CLASS=heading-body2>Reseller</TH>" . $rhead2;
    $ccnt++;
  } else {
    print "\"All Resellers\"";
    print "\n\"Reseller\"," . $rhead2;
  }

  $totcdruq=" SELECT reseller.description,reseller.id,'',count(call.uniqueid),sum(call.sessiontime), 
avg(call.sessiontime),stddev(call.sessiontime),sum(trunkcost.cost),sum(resellercall.buycost-intax),sum(resellercall.sellcost-outtax),sum(resellercall.intax),sum(resellercall.outtax),sum(resellercall.rsessionbill)/1000 
FROM call LEFT OUTER JOIN trunkcost USING (uniqueid) LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index) LEFT OUTER JOIN users ON (name=call.username) LEFT 
OUTER JOIN reseller ON (users.agentid=reseller.id) LEFT OUTER JOIN resellercall on (call.uniqueid=resellercall.calluid) WHERE 
terminatecause='ANSWER' AND " . $time . " AND resellerid=" . $_SESSION['resellerid'] . " GROUP BY reseller.description,reseller.id ORDER BY sum(trunkcost.cost) DESC";

/*
  $totcdruq.=" GROUP BY users.fullname,cdr.accountcode
                  ORDER BY " . $sortby;
*/

//  print $totcdruq . "<P>";
  $totcdru=pg_query($db,$totcdruq);
  $last="";
  while($r = pg_fetch_row($totcdru)) {
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
    }
    $ccnt++;
    $total[$r[1]]=$r;
    showentry($r);
    for($tcnt=3;$tcnt<count($r);$tcnt++) {
      $rtotal[$tcnt]=$rtotal[$tcnt]+$r[$tcnt];
    }
  }
  $rtotal[5]=$rtotal[5]/$ccnt;
  $rtotal[6]=$rtotal[6]/$ccnt;
  $total['reseller']=$rtotal;
  if ($_POST['print'] < 2) {
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TD>&nbsp;</TD>";
    $ccnt++;
  } else {
    print ",";
  }
  showtot("reseller");

  if ($_POST['print'] < 2) {
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . " STYLE=\"page-break-before: always\"><TH COLSPAN=" . $cspan . " CLASS=heading-body>All Accounts</TH></TR>\n";
    $ccnt++;
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH CLASS=heading-body2>Account</TH>" . $rhead2;
    $ccnt++;
  } else {
    print "\n\"All Accounts\"";
    print "\n\"Account\"," . $rhead2;
  }

  $totcdruq=" SELECT users.fullname,users.name,'',count(call.uniqueid),sum(call.sessiontime), 
avg(call.sessiontime),stddev(call.sessiontime),sum(trunkcost.cost),sum(resellercall.buycost-intax),sum(resellercall.sellcost-outtax),sum(resellercall.intax),sum(resellercall.outtax),sum(resellercall.rsessionbill)/1000 
FROM call LEFT OUTER JOIN trunkcost USING (uniqueid) LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index) LEFT OUTER JOIN users ON (name=call.username) LEFT 
OUTER JOIN reseller ON (users.agentid=reseller.id) LEFT OUTER JOIN resellercall on (call.uniqueid=resellercall.calluid) WHERE 
terminatecause='ANSWER' AND " . $time . " AND reseller.id=" . $_SESSION['resellerid'] . " AND resellercall.resellerid = reseller.id GROUP BY users.fullname,users.name ORDER BY sum(trunkcost.cost) DESC";

/*
  $totcdruq.=" GROUP BY users.fullname,cdr.accountcode
                  ORDER BY " . $sortby;
*/

//  print $totcdruq . "<P>";
  $totcdru=pg_query($db,$totcdruq);
  $last="";
  while($r = pg_fetch_row($totcdru)) {
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
    }
    $ccnt++;
    $total[$r[1]]=$r;
    showentry($r);
  }

  if ($_POST['print'] < 2) {
    print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TD>&nbsp;</TD>";
    $ccnt++;
  } else {
    print ",";
  }
  showtot($_SESSION['resellerid']);


  $totcdruq=" SELECT reseller.description,reseller.id,localrates.description,count(call.uniqueid),sum(call.sessiontime), 
avg(call.sessiontime),stddev(call.sessiontime),sum(trunkcost.cost),sum(resellercall.buycost-intax),sum(resellercall.sellcost-outtax),sum(resellercall.intax),sum(resellercall.outtax),sum(resellercall.rsessionbill)/1000 
FROM call LEFT OUTER JOIN trunkcost USING (uniqueid) LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index) LEFT OUTER JOIN users ON (name=call.username) LEFT 
OUTER JOIN reseller ON (users.agentid=reseller.id) LEFT OUTER JOIN resellercall on (call.uniqueid=resellercall.calluid) WHERE 
terminatecause='ANSWER' AND " . $time . " AND resellerid=" . $_SESSION['resellerid'] . " GROUP BY reseller.description,localrates.description,reseller.id ORDER BY reseller.description,sum(trunkcost.cost) DESC";

/*
  $totcdruq.=" GROUP BY users.fullname,cdr.accountcode
                  ORDER BY " . $sortby;
*/

//  print $totcdruq . "<P>";
  $totcdru=pg_query($db,$totcdruq);
  $last="";
  while($r = pg_fetch_row($totcdru)) {
    if ($last != $r[1]) {
      if ($last != "") {
        if ($_POST['print'] < 2) {
          print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TD>&nbsp;</TD>";
          $ccnt++;
        } else {
          print ",";
        }
        showtot($last);
      }
      if ($_POST['print'] < 2) {
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . " STYLE=\"page-break-before: always\"><TH COLSPAN=" . $cspan . " CLASS=heading-body>" . $r[0] . " (" . $r[1] . ")</TH></TR>\n";
        $ccnt++;
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH CLASS=heading-body2>Destination</TH>" . $rhead2;
        $ccnt++;
      } else {
        print "\n\"" . $r[0] . " (" . $r[1] . ")\"";
        print "\n\"Destination\"," . $rhead2;
      }
      $last=$r[1];
    }
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
    }
    if ($r[2] == "") {
      $r[2]="Unknown";
    }
    $ccnt++;
    showentry($r);
  }

  if ($last != "") {
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TD>&nbsp;</TD>";
      $ccnt++;
    } else {
      print ",";
    }
    showtot($last);
  }


  $totcdruq=" SELECT users.fullname,users.name,localrates.description,count(call.uniqueid),sum(call.sessiontime), 
avg(call.sessiontime),stddev(call.sessiontime),sum(trunkcost.cost),sum(resellercall.buycost-intax),sum(resellercall.sellcost-outtax),sum(resellercall.intax),sum(resellercall.outtax),sum(resellercall.rsessionbill)/1000 
FROM call LEFT OUTER JOIN trunkcost USING (uniqueid) LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index) LEFT OUTER JOIN users ON (name=call.username) LEFT 
OUTER JOIN reseller ON (users.agentid=reseller.id) LEFT OUTER JOIN resellercall on (call.uniqueid=resellercall.calluid) WHERE 
terminatecause='ANSWER' AND " . $time . " AND reseller.id=" . $_SESSION['resellerid'] . " AND resellercall.resellerid = reseller.id GROUP BY users.fullname,users.name,localrates.description,reseller.id ORDER BY users.name,users.fullname,sum(trunkcost.cost) DESC";

/*
  $totcdruq.=" GROUP BY users.fullname,cdr.accountcode
                  ORDER BY " . $sortby;
*/

//  print $totcdruq . "<P>";
  $totcdru=pg_query($db,$totcdruq);
  $last="";
  while($r = pg_fetch_row($totcdru)) {
    if ($last != $r[1]) {
      if ($last != "") {
        if ($_POST['print'] < 2) {
          print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TD>&nbsp;</TD>";
          $ccnt++;
        } else {
          print ",";
        }
        showtot($last);
      }
      if ($_POST['print'] < 2) {
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . " STYLE=\"page-break-before: always\"><TH COLSPAN=" . $cspan . " CLASS=heading-body>" . $r[0] . " (" . $r[1] . ")</TH></TR>\n";
        $ccnt++;
        print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH CLASS=heading-body2>Destination</TH>" . $rhead2;
        $ccnt++;
      } else {
        print "\n\"" . $r[0] . " (" . $r[1] . ")\"";
        print "\n\"Destination\"," . $rhead2;
      }
      $last=$r[1];
    }
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . ">";
    }
    if ($r[2] == "") {
      $r[2]="Unknown";
    }
    $ccnt++;
    showentry($r);
  }

  if ($last != "") {
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TD>&nbsp;</TD>";
      $ccnt++;
    } else {
      print ",";
    }
    showtot($last);
  }

  if ($_POST['print'] < 2) {
    if ($_POST['print'] != "1") {
      print "<TR CLASS=list-color" . (($ccnt % 2)+1) . "><TH COLSPAN=" . $cspan . " CLASS=heading-body>";
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
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $_POST['date'];%>">
<INPUT TYPE=HIDDEN NAME=date2 VALUE="<%print $_POST['date2'];%>">
<INPUT TYPE=HIDDEN NAME=dom VALUE="<%print $_POST['dom'];%>">
<INPUT TYPE=HIDDEN NAME=dom2 VALUE="<%print $_POST['dom2'];%>">
<INPUT TYPE=HIDDEN NAME=sortby VALUE="<%print $_POST['sortby'];%>">
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
