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

$logadmin=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(|(cn=Admin Access)(cn=Call Logging)))");
$clogaccess=ldap_count_entries($ds,$logadmin);

if ($clogaccess <= 0) {
  $cspan="6";
} else {
  $cspan="7";
}

if ($_POST['print'] < 2){
%>



<%
  }
  if ($sortby == "") {
    $sortby="calldate";
  }
  $osortby=$sortby;
  if ($mweight == "on") {
    $sortby .=" DESC";
  }

  include "func.inc";

  if ($exten == "Unknown") {
    $exten="";
  }

  if ($group != "") {
    $exten="";
  }

  if ($xexep == "on") {
    $exceptions=" AND (duration-cdr.billsec > " . $thold . "*" . $exep . " OR cdr.billsec > " . $tavg . "*" . $exep . ")";
  }

  $dchannel="cdr.dstchannel";
  if ($type == "6") {
    $exuser="substr(cdr.dstchannel,5,4) = '$exten' AND cdr.userfield = '$queue'";
    $userd="accountcode";
  } else if ($type == "5") {
    $dchannel="channel";
    $exuser="dst = '$exten'";
    $userd="src";
  } else if ($type == "2") {
    $exuser="accountcode = '$exten'";
    $userd="cdr.userfield";
    $join="left outer join astdb on (substr(cdr.userfield,0,4) = value AND family='Setup' and key='AreaCode') ";
  } else {
    if ($exten == "") {
      $exuser="cdr.accountcode IS NOT NULL";
    } else if ($exten != "NULL") {
      $exuser="cdr.accountcode = '$exten'";
    } else {
      $exuser="users.name IS NULL";
    }
    $userd="cdr.userfield";
  }

  if ($filter != "") {
    $ofilter=$filter;
    $filter=" AND cdr.userfield = '" . $filter . "'";
  }
  if ($group != "") {
    $filter.=" AND bgrp.value='" . $group . "'";
  }

  if (is_array($month2)) {
    $time="(calldate > '" . $month[1] . "-" . $month[0] . "-" . $month[2] . "' AND calldate < '" . $month2[1] . "-" . $month2[0] . "-" . $month2[2] . "')";
  } else {
    $time="date_part('month',calldate) = '$month[0]'";
  }

  if (($trunk != "") && ($trunk != "undefined")) {
    $trunko=" AND cdr.dstchannel ~ '" . $tchans[$trunk] . "'";
  }
	
  $join=$join . "LEFT OUTER JOIN trunkcost ON (cdr.linkedid = trunkcost.uniqueid) LEFT OUTER JOIN users ON (cdr.accountcode = name) LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family = name AND bgrp.key='BGRP') ";
  $getcdrq="SELECT to_char(calldate,'DD HH24:MI:SS'),$userd,cdr.billsec,
                               case when (position('-' in $dchannel) > 0) then substr($dchannel,0,position('-' in $dchannel)) else lastapp||'('||lastdata||')' end,
                               duration-cdr.billsec AS holdtime,cost,cdr.linkedid,count(callleg),cdr.*
                          from cdr LEFT OUTER JOIN calllog ON (cdr.linkedid = calllog.uniqueid) " . $join . "where 
                            cdr.userfield != '' AND $chan AND disposition='$disp' AND
                            $exuser AND date_part('year',calldate) = '$month[1]' AND " . $time . $trunko . $exceptions . $filter . "
                          group by calldate,userfield,billsec,cdr.dstchannel,lastapp,lastdata,duration,cost,cdr.linkedid,clid,src,dst,dcontext,channel,disposition,cdr.amaflags,cdr.accountcode,cdr.uniqueid
                          order by $sortby";
//  print $getcdrq . "<BR>\n";
  $getcdr=pg_query($db,$getcdrq);

  if ($_POST['print'] < 2) {%>
<FORM NAME=sortform METHOD=post>
  <INPUT TYPE=HIDDEN NAME=sortby VALUE="<%print $osortby;%>">
  <INPUT TYPE=HIDDEN NAME=direction VALUE="<%print $mweight;%>">
  <INPUT TYPE=HIDDEN NAME=tavg VALUE="<%print $tavg;%>">
  <INPUT TYPE=HIDDEN NAME=thold VALUE="<%print $thold;%>">
  <INPUT TYPE=HIDDEN NAME=nomenu VALUE="<%print $_POST['nomenu']%>">
  <INPUT TYPE=HIDDEN NAME=type VALUE="<%print $type;%>">
  <INPUT TYPE=HIDDEN NAME=exep VALUE="<%print $exep;%>">
  <INPUT TYPE=HIDDEN NAME=exten VALUE="<%print $exten;%>">
  <INPUT TYPE=HIDDEN NAME=group VALUE="<%print $group;%>">
  <INPUT TYPE=HIDDEN NAME=filter VALUE="<%print $ofilter;%>">
  <INPUT TYPE=HIDDEN NAME=xexep VALUE="<%print $xexep;%>">
  <INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
  <INPUT TYPE=HIDDEN NAME=date2 VALUE="<%print $date2;%>">
  <INPUT TYPE=HIDDEN NAME=dom VALUE="<%print $dom;%>">
  <INPUT TYPE=HIDDEN NAME=dom2 VALUE="<%print $dom2;%>">
  <INPUT TYPE=HIDDEN NAME=trunk VALUE="<%print $trunk;%>">
  <INPUT TYPE=HIDDEN NAME=mweight VALUE="<%print $mweight;%>">
  <INPUT TYPE=HIDDEN NAME=morder VALUE="<%print $morder;%>">
  <INPUT TYPE=HIDDEN NAME=disp VALUE="<%print $disp;%>">
  <INPUT TYPE=HIDDEN NAME=exten VALUE="<%print $exten;%>">
  <INPUT TYPE=HIDDEN NAME=usern VALUE="<%print $usern;%>">
  <INPUT TYPE=HIDDEN NAME=print>
</FORM>

<FORM NAME=printform METHOD=post>
  <INPUT TYPE=HIDDEN NAME=sortby VALUE="<%print $osortby;%>">
  <INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $disppage;%>">
  <INPUT TYPE=HIDDEN NAME=direction VALUE="<%print $mweight;%>">
  <INPUT TYPE=HIDDEN NAME=tavg VALUE="<%print $tavg;%>">
  <INPUT TYPE=HIDDEN NAME=thold VALUE="<%print $thold;%>">
  <INPUT TYPE=HIDDEN NAME=nomenu VALUE="<%print $_POST['nomenu']%>">
  <INPUT TYPE=HIDDEN NAME=type VALUE="<%print $type;%>">
  <INPUT TYPE=HIDDEN NAME=exep VALUE="<%print $exep;%>">
  <INPUT TYPE=HIDDEN NAME=exten VALUE="<%print $exten;%>">
  <INPUT TYPE=HIDDEN NAME=filter VALUE="<%print $ofilter;%>">
  <INPUT TYPE=HIDDEN NAME=xexep VALUE="<%print $xexep;%>">
  <INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
  <INPUT TYPE=HIDDEN NAME=date2 VALUE="<%print $date2;%>">
  <INPUT TYPE=HIDDEN NAME=dom VALUE="<%print $dom;%>">
  <INPUT TYPE=HIDDEN NAME=dom2 VALUE="<%print $dom2;%>">
  <INPUT TYPE=HIDDEN NAME=trunk VALUE="<%print $trunk;%>">
  <INPUT TYPE=HIDDEN NAME=mweight VALUE="<%print $mweight;%>">
  <INPUT TYPE=HIDDEN NAME=morder VALUE="<%print $morder;%>">
  <INPUT TYPE=HIDDEN NAME=disp VALUE="<%print $disp;%>">
  <INPUT TYPE=HIDDEN NAME=exten VALUE="<%print $exten;%>">
  <INPUT TYPE=HIDDEN NAME=usern VALUE="<%print $usern;%>">
  <INPUT TYPE=HIDDEN NAME=print>
</FORM><%
    print "<P><CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>\n";
    print "<TR CLASS=list-color2>";
    print "<TH COLSPAN=" . $cspan . " CLASS=heading-body>Call Report For ";
    if ($usern != "") {
      print $usern;
    } else {
      print $exten;
    }
    if ($ofilter != "") {
      print " To " . $ofilter;
    }
    print "</TH></TR>";
    print "<TR CLASS=list-color1>";

  if ($_POST['print'] != "1") {
    print "<TH ALIGN=LEFT CLASS=heading-body2><A HREF=javascript:getrepsort(\"calldate\")>Date</A></TH>";
    if ($type == "6") {
      print "<TH ALIGN=LEFT CLASS=heading-body2><A HREF=javascript:getrepsort(\"accountcode\")>Source</A></TH>";
    } else if ($type == "5") {
      print "<TH ALIGN=LEFT CLASS=heading-body2><A HREF=javascript:getrepsort(\"accountcode\")>Source</A></TH>";
    } else {
      print "<TH ALIGN=LEFT CLASS=heading-body2><A HREF=javascript:getrepsort(\"cdr.userfield\")>Destination</A></TH>";
    }
    print "<TH ALIGN=LEFT CLASS=heading-body2><A HREF=javascript:getrepsort(\"cdr.billsec\")>Time</A></TH>";
    print "<TH ALIGN=LEFT CLASS=heading-body2><A HREF=javascript:getrepsort(\"holdtime\")>Hold Time</A></TH>";
    print "<TH ALIGN=LEFT CLASS=heading-body2><A HREF=javascript:getrepsort(\"$dchannel\")>Channel</A></TH>";
    if ($clogaccess > 0) {
      print "<TH ALIGN=LEFT CLASS=heading-body2><A HREF=javascript:getrepsort(\"count(callleg)\")>Recordings</A></TH>";
    }
    print "<TH ALIGN=LEFT CLASS=heading-body2><A HREF=javascript:getrepsort(\"cost\")>Cost</A></TH>";
  } else {
    print "<TH ALIGN=LEFT CLASS=heading-body2>Date</A></TH>";
    if ($type == "6") {
      print "<TH ALIGN=LEFT CLASS=heading-body2>Source</A></TH>";
    } else if ($type == "5") {
      print "<TH ALIGN=LEFT CLASS=heading-body2>Source</A></TH>";
    } else {
      print "<TH ALIGN=LEFT CLASS=heading-body2>Destination</A></TH>";
    }
    print "<TH ALIGN=LEFT CLASS=heading-body2>Time</A></TH>";
    print "<TH ALIGN=LEFT CLASS=heading-body2>Hold Time</A></TH>";
    print "<TH ALIGN=LEFT CLASS=heading-body2>Channel</A></TH>";
    if ($clogacess > 0) {
      print "<TH ALIGN=LEFT CLASS=heading-body2>Recordings</A></TH>";
    }
    print "<TH ALIGN=LEFT CLASS=heading-body2>Cost</A></TH>";
  }
  print "</TR>\n<TR CLASS=list-color2>";
} else {
  print "\"User\",";
  if ($usern != "") {
    print $usern;
  } else {
    print $exten;
  }
  if ($ofilter != "") {
    print ",\"";
    print "To " . $ofilter . "\"";
  }
  print "\n";
  if ($type == "6") {
    $grtyp="Source";
  } else if ($type == "5") {
    $grtyp="Source";
  } else {
    $grtyp="Destination";
  }

  $data=array("Date",$grtyp,"Time","Hold Time","Channel","Cost");
  $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
  print $dataout;
}
  $ccnt=0;
  $toc=0;
 
  $cdrdisc[0]="Date";
  $cdrdisc[1]="Caller ID";
  $cdrdisc[2]="Source";
  $cdrdisc[3]="Destination";
  $cdrdisc[4]="Context";
  $cdrdisc[5]="Channel";
  $cdrdisc[6]="Destination Channel";
  $cdrdisc[7]="Last Application";
  $cdrdisc[8]="Last App. Data";
  $cdrdisc[9]="Duration";
  $cdrdisc[10]="Bill Time";
  $cdrdisc[11]="Disposition";
  $cdrdisc[12]="AMA Flags";
  $cdrdisc[13]="Account Code";
  $cdrdisc[14]="Unique ID";
  $cdrdisc[15]="User Data";
  $cdrdisc[16]="Linked ID";

  for($i=0;$i<pg_num_rows($getcdr);$i++) {
    $r=pg_fetch_row($getcdr,$i);
    $rem=$ccnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
    $cdrrec="";
    for($cdrcnt=8;$cdrcnt < count($r);$cdrcnt++) {
      $cdrrec .=$cdrdisc[$cdrcnt-8] . " : " . $r[$cdrcnt] . "\\n";
    }
    $toc=$toc+$r[2];
    if ($_POST['print'] < 2) {
      print "<TD>";
      if ($_POST['print'] != "1") {
        print "<A HREF=\"javascript:alert('" . htmlspecialchars($cdrrec) . "')\">" . $r[0] . "</A>";
      } else {
        print $r[0];
      }
      print "&nbsp;&nbsp;</TD>";
      print "<TD>" . $r[1] . "&nbsp;&nbsp;</TD>";
      print "<TD>";
      if (($r[2] > $tavg*$exep) && ($exep > 0)) {
        print "<FONT COLOR=RED>";
      }
      print gtime($r[2]) . "&nbsp;&nbsp;</TD>";
      print "<TD>";

      if (($r[4] > $thold*$exep) && ($exep > 0)) {
        print "<FONT COLOR=RED>";
      }
      print gtime($r[4]) . "&nbsp;&nbsp;</TD>";

      print "<TD>" . $r[3] . "</TD>";
      if ($clogaccess > 0) {
        if ($r[7]  > 0) {
          if ($_POST['print'] != "1" ) {
            print "<TD><A HREF=javascript:calllog('" . $r[6] . "')>" . $r[7] . " Recordings</A></TD>";
          } else {
            print "<TD>" . $r[7] . " Recordings</TD>";
          }
        } else {
          print "<TD>None</TD>";
        }
      }
      print "<TD ALIGN=RIGHT>" . sprintf("%0.2f",$r[5]/100000) . "</TD>";
      print "</TR>\n<TR $bcolor>";
    } else {
      if (($r[2] > $tavg*$exep) && ($exep > 0)) {
        $r[2]=0-$r[2];
      }
      $data=array($r[0],$r[1],gtime($r[2]),gtime($r[4]),$r[3],sprintf("%0.2f",$r[5]/100000));
      $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
      print $dataout;
    }
    $ccnt++;
    $totalcost=$totalcost+$r[5];
  }
  if ( $_POST['print'] < 2) {
    $rem=$ccnt % 2;
    if ($rem == 0) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
    print "<TR $bcolor><TD";
    if ($clogaccess > 0) {
      print " COLSPAN=2";
    }
    print ">&nbsp;</TD><TD CLASS=heading-body2>" . gtime($toc);
    print "</TD><TD>&nbsp;</TD><TD CLASS=heading-body2>" . $ccnt . " Calls</TD><TD>&nbsp;</TD><TD  CLASS=heading-body2 ALIGN=RIGHT>" . sprintf("%0.2f",$totalcost/100000) . "</TD></TR>\n";
    $rem=$ccnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }

    if ($_POST['print'] != "1") {
      print "<TR" . $bcolor . "><TH COLSPAN=" . $cspan . " CLASS=heading-body>";
      print "<INPUT TYPE=BUTTON NAME=pbuttonc VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.printform)\">";
      print "<INPUT TYPE=BUTTON NAME=pbuttonp VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.printform)\">";
      print "</TH></TR>";
    }
    print "</TABLE>";
  } else {
    $data=array("","",gtime($toc),"",$ccnt,sprintf("%0.2f",$totalcost/100000));
    $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
    print $dataout;
  }
%>
