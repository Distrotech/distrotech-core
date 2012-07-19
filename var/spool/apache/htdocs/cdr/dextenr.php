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

  include_once "func.inc";
  if ($ADMIN_USER != "admin") {
    return;
  }

  if ($morder == "") {
     $morder="callcnt";
  }
  $omorder=$morder;

  if ($mweight == "on") {
    $morder .=" DESC";
  }

  if (($trunk != "") && ($trunk != "G") && ($trunk != "undefined")) {
    $trunko=$tchans[$trunk];
  } else {
    $trunko=$trunkchan;
  }

  if ($pclass != "") {
    $opclass=$pclass;
    if ($pclass != "NULL") {
      if ($exten == "NULL") {
        $pclass=" AND users.name IS NULL AND cost >= 0 AND dst ~ '^[0-9]{4}[0-9]+' AND price='" . $pclass . "' ";
      } else if (($trunk == "G") && ($pclass == "")) {
        $pclass=" AND cost >= 0 AND dst ~ '^[0-9]{4}[0-9]+' AND dstchannel ~ '" . $trunko . "' ";
      } else {
        $pclass=" AND cost >= 0 AND dst ~ '^[0-9]{4}[0-9]+' AND dstchannel ~ '" . $trunko . "' AND price='" . $pclass . "' ";
      }
    } else {
      if ($exten == "NULL") {
        $pclass=" AND users.name IS NULL AND (trunkcost.cost >= 0 AND dst ~ '^[0-9]{4}[0-9]+' OR (trunkcost.cost IS NOT NULL AND dst != 's' AND dst ~ '^[0-9]{4}[0-9]+')) AND
                      dstchannel ~ '" . $trunko . "' ";
      } else {
        $pclass=" AND trunkcost.cost IS NULL AND dst != 's' AND dst ~ '^[0-9]{4}[0-9]+' AND
                      dstchannel ~ '" . $trunko . "' ";
      }
    }
  } else {
    if ($exten == "NULL") {
      $pclass=" AND users.name IS NULL AND price IS NULL AND (cost >= 0 AND dst ~ '^[0-9]{4}[0-9]+' OR (trunkcost.cost IS NULL AND dst != 's')) AND
                      dstchannel ~ '" . $trunko . "' ";
    } else {
      $pclass=" AND (cost >= 0 AND dst ~ '^[0-9]{4}[0-9]+' OR (trunkcost.cost IS NULL AND dst != 's')) AND
                      dstchannel ~ '" . $trunko . "' ";
    }
  }
  
  if ($exten == "") {
    $exfilter=" AND cdr.accountcode IS NOT NULL ";
  } else if (($trunk == "G") && ($exten != "")) {
    if ($exten == "Ungrouped") {
      $exfilter=" AND (astdb.value = '' OR astdb.value IS NULL) ";
    } else {
      $exfilter=" AND astdb.value= '"  . $exten . "' ";
    }
    $exfilter.="AND name is not NULL AND name != '' ";
    $grpjoin=" LEFT OUTER JOIN astdb ON (family=name AND key='BGRP') ";
  } else if ($exten != "NULL") {
    $exfilter=" AND cdr.accountcode= '"  . $exten . "' ";
  }

  $time="(calldate > '" . $month[1] . "-" . $month[0] . "-" . $month[2] . "' AND calldate < '" . $month2[1] . "-" . $month2[0] . "-" . $month2[2] . "')";
  $getcdrq="SELECT count(userfield) AS callcnt,userfield, description,
                               sum(billsec) AS tottime,avg(billsec) AS avtime,
                               avg(duration-billsec) AS holdtime,stddev(billsec) as dv8,sum(cost)
                          from cdr 
                            left outer join trunkcost USING (uniqueid)
			    LEFT OUTER JOIN numdb ON (userfield = number)
                             LEFT OUTER JOIN users ON (cdr.accountcode = name)" . $grpjoin;
  if ($TMS_USER == 1) {
    $getcdrq.=" LEFT OUTER JOIN astdb as bgrp ON (cdr.accountcode=bgrp.family AND bgrp.key='BGRP')";
  }

  $getcdrq.=" where disposition='ANSWERED' AND " . $time . $exfilter . $pclass;
  if (($SUPER_USER != 1) && ($TMS_USER == 1)) {
    $getcdrq.=" AND " . $clogacl;
  }
  $getcdrq.=" group by userfield,description order by $morder";
  
//  print $getcdrq . "<BR>\n";
  $getcdr=pg_query($db,$getcdrq);

  if (pg_num_rows($getcdr) == 0) {
    return;
  }

  if ($_POST['print'] < 2) {%>
<FORM METHOD=POST NAME=printexten>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cdr/dextenr.php">
<INPUT TYPE=HIDDEN NAME=exten VALUE="<%print $exten;%>">
<INPUT TYPE=HIDDEN NAME=pclass VALUE="<%print $opclass;%>">
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
<INPUT TYPE=HIDDEN NAME=date2 VALUE="<%print $date2;%>">
<INPUT TYPE=HIDDEN NAME=dom VALUE="<%print $dom;%>">
<INPUT TYPE=HIDDEN NAME=dom2 VALUE="<%print $dom2;%>">
<INPUT TYPE=HIDDEN NAME=morder VALUE="<%print $omorder;%>">
<INPUT TYPE=HIDDEN NAME=mweight VALUE="<%print $mweight;%>">
<INPUT TYPE=HIDDEN NAME=trunk VALUE="<%print $trunk;%>">
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<FORM METHOD=POST NAME=getrep>
<INPUT TYPE=HIDDEN NAME=tavg VALUE="<%print $monavg[0];%>">
<INPUT TYPE=HIDDEN NAME=thold VALUE="<%print $monavg[1];%>">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cdr/getrep.php">
<INPUT TYPE=HIDDEN NAME=type VALUE="">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE="1">
<INPUT TYPE=HIDDEN NAME=usern VALUE="<%print $usern;%>">
<INPUT TYPE=HIDDEN NAME=exep VALUE="<%print $exep;%>">
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
<INPUT TYPE=HIDDEN NAME=date2 VALUE="<%print $date2;%>">
<INPUT TYPE=HIDDEN NAME=dom VALUE="<%print $dom;%>">
<INPUT TYPE=HIDDEN NAME=dom2 VALUE="<%print $dom2;%>">
<INPUT TYPE=HIDDEN NAME=xexep VALUE="<%print $xexep;%>">
<INPUT TYPE=HIDDEN NAME=mweight VALUE="<%print $mweight;%>">
<INPUT TYPE=HIDDEN NAME=morder VALUE="<%print $morder;%>">
<INPUT TYPE=HIDDEN NAME=disp VALUE="ANSWERED">
<INPUT TYPE=HIDDEN NAME=exten VALUE="<%print ($trunk != "G")?$exten:"";%>">
<INPUT TYPE=HIDDEN NAME=filter>
<INPUT TYPE=HIDDEN NAME=group VALUE="<%print ($trunk == "G")?$exten:"";%>">
</FORM><CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%><TR CLASS=list-color2><%
  }
  if (($trunk == "G") && ($exten != "")) {
    if ($exten == "Ungrouped") {
      $usern=$exten . " Calls";
    } else {
      $usern=$exten . " Group";
    }
  } else if (($exten != "NULL") && ($exten != "")) {
    $getname=pg_query($db,"SELECT name,fullname FROM users WHERE name='" . $exten . "'");
    $getnm=pg_fetch_array($getname,0);
    if ($getnm[1] != "") {
      $usern=$getnm[1] . " (" . $getnm[0] . ")";
    } else {
      $usern=$getnm[0];
    }
  } else if ($exten == "") {
    $usern="All Extensions";
  } else if ($exten == "NULL") {
    $usern="Unknown Extensions";
  }
  $usern=$usern . " [" . $month[1] . "/" . $month[0] . "/" . $month[2] . "-" . $month2[1] . "/" . $month2[0] . "/" . $month2[2] . "]";

  if (($opclass != "") && ($trunk != "G") && ($opclass != "NULL")) {
    $gettype=pg_query($db,"SELECT description FROM localrates WHERE index='" . $opclass . "'");
    $getty=pg_fetch_array($gettype,0);
    $usern=$usern .  " - " . $getty[0];
  } else if (($opclass == "NULL") && ($exten != "NULL")){
    $usern=$usern .  " - Unknown Destinations"; 
  }

  if ($_POST['print'] < 2) {
    print "<TH COLSPAN=8 CLASS=heading-body>Call Report For " . $usern . "</TH></TR>";
    print "<TR CLASS=list-color1>";

  print "<TH ALIGN=LEFT CLASS=heading-body2>Destination</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>Description</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>Calls</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>Time</TH><TH ALIGN=LEFT CLASS=heading-body2>Average</TH><TH ALIGN=LEFT CLASS=heading-body2>Std. Dev.</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>Av. Hold Time</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>Cost</TH>";
  print "</TR>\n<TR CLASS=list-color2>";
  } else {
    print "\"" . $usern . "\"\n";
    $data=array("Destination","Description","Calls","Time","Average","Std. Dev.","Av. Hold Time","Cost");
    $dataout="\"" . str_replace(array("\"","--!@#--"),array("\"\"","\",\""),implode("--!@#--",$data)). "\"\n";
    print $dataout;
  }
  $ccnt=0;
 
  $totcalls=array(0,0,0);
  for($i=0;$i<pg_num_rows($getcdr);$i++) {
    $r=pg_fetch_row($getcdr,$i);
    $rem=$ccnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
    if ($r[1] == "") {
      $r[1]="Unknown";
    }

    if ($_POST['print'] < 2) {
      print "</TD><TD>";
      if ($_POST['print'] != "1") {
        print "<A HREF=\"javascript:opencdrrep2(";
        print "'" . $exten . "','" . $r[1] . "')\">";
      }
      print $r[1];
      if ($_POST['print'] != "1") {
        print "</A>";
      }
      print "</TD>";
      print "<TD>" . $r[2] . "</TD>";
      print "<TD>" . $r[0] . "</TD>";
      print "<TD>" . gtime($r[3]);
      print "</TD><TD>";
      print gtime($r[4]);
      print "</TD><TD>" . gtime($r[6]);
      print "</TD><TD>";
      print gtime($r[5]);
      print "</TD><TD ALIGN=RIGHT>" . sprintf("%0.2f",$r[7]/100000);
      print "</TD></TR>\n<TR $bcolor>";
    } else {
      $data=array($r[1],$r[2],$r[0],gtime($r[3]),gtime($r[4]),gtime($r[5]),gtime($r[6]),sprintf("%0.2f",$r[7]/100000));
      $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
      print $dataout;
    }
    $totcalls[0]=$totcalls[0]+$r[0];
    $totcalls[1]=$totcalls[1]+$r[3];
    $totcalls[2]=$totcalls[2]+($r[5]*$r[0]);
    $totcost=$totcost+$r[7];
    $ccnt++;
  }
  $tavg=$totcalls[1]/$totcalls[0];
  $totcalls[2]=$totcalls[2]/$totcalls[0];
  if ($_POST['print'] < 2) {
    print "<TD CLASS=heading-body2>Total Numbers Called: " . $ccnt . "</TD><TD CLASS=heading-body2>&nbsp;</TD><TD CLASS=heading-body2>" . $totcalls[0];
    print "</TD><TD CLASS=heading-body2>" . gtime($totcalls[1]) . "</TD><TD CLASS=heading-body2>" . gtime($tavg);
    print "</TD><TD CLASS=heading-body2>&nbsp;</TD><TD CLASS=heading-body2>" . gtime($totcalls[2]);
    print "</TD><TD ALIGN=RIGHT CLASS=heading-body2>" . sprintf("%0.2f",$totcost/100000) . "</TR>";
    $rem=$ccnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
  } else {
    $data=array("Total (" . $ccnt . ")","",$totcalls[0],gtime($totcalls[1]),gtime($tavg),"",gtime($totcalls[2]),sprintf("%0.2f",$totcost/100000));
    $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
    print $dataout;
  }
  if ($_POST['print'] < 2) {
    if ($_POST['print'] != "1") {
      print "<TR" . $bcolor . "><TH COLSPAN=8 CLASS=heading-body>";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.printexten)\">";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.printexten)\">";
      print "</TH></TR>";
    }
    print "</TABLE></DIV>";
  }
%>
