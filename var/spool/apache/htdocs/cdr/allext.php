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

  if ($ADMIN_USER != "admin") {
    return;
  }

  $omorder=$morder;
  if ($morder == "") {
     $morder="callcnt";
  }

  if ($mweight == "on") {
    $morder .=" DESC";
  }

  if ($type == "6") {
    $ufield=" ~ '(^5[0-9][0-9]\$)|(^799\$)'";
  } else {
    $ufield="!= ''";
  }

  if (($type == "2") || ($type == "9")) {
    $join="left outer join astdb on (substr(userfield,0,4) = value AND family='Setup' and key='AreaCode') ";
  }

  $avgcdrq="SELECT avg(billsec) AS avtime,
                               avg(duration-billsec) AS holdtime,
                               date_part('year',calldate) as year,
                               date_part('month',calldate) as month
                          from cdr " . $join;
    if ($TMS_USER != 1) {
      $avgcdrq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=accountcode AND bgrp.key='BGRP')";
    }
    $avgcdrq.=" where 
                            userfield $ufield AND $chan AND disposition='ANSWERED' AND
                            date_part('year',calldate) = '$month[1]' AND
                            date_part('month',calldate) = '$month[0]'";
    if ($TMS_USER != 1) {
      $avgcdrq.=" AND " . $clogacl;
    }
    $avgcdrq.=" group by year,month
                          order by year,month";
  
  $avgcdr=pg_query($db,$avgcdrq);
  $monavg=pg_fetch_row($avgcdr);

  if (($type != "6") && ($type != "5") && ($type != "2") && ($type != "9")) {
    if ($xexep == "on") {
      $exceptions=" AND (billsec > " . $monavg[0] . "*" . $exep;
      $exceptions.=" OR duration-billsec > " . $monavg[1] . "*" . $exep; 
      $exceptions.=")";
    }
    $getcdrq="SELECT count(cdr.accountcode) AS callcnt,cdr.accountcode,
                                 sum(billsec) AS tottime,avg(billsec) AS avtime,
                                 avg(duration-billsec) AS holdtime,stddev(billsec) as dv8,
                                 disposition,users.fullname,sum(cost)
                            from cdr left outer join astdb on (substring(cdr.accountcode,0,3) = key AND family='LocalPrefix' AND value='1')
                                     left outer join users on (cdr.accountcode = name)
                                     LEFT OUTER JOIN trunkcost ON (cdr.uniqueid = trunkcost.uniqueid)";
    if ($TMS_USER != 1) {
      $getcdrq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=name AND bgrp.key='BGRP')";
    }
    $getcdrq.=" where 
                              userfield != '' AND $chan AND astdb.value = 1 AND
                              date_part('year',calldate) = '$month[1]' AND
                              date_part('month',calldate) = '$month[0]'$exceptions";

    if ($TMS_USER != 1) {
      $getcdrq.=" AND " . $clogacl;
    }
    $getcdrq.=" group by cdr.accountcode,disposition,fullname
                            order by $morder";
//    print $getcdrq;
  } else if ($type == "6") {
    $getcdrq="SELECT count(userfield) AS callcnt,userfield,
                                 sum(billsec) AS tottime,avg(billsec) AS avtime,
                                 avg(duration-billsec) AS holdtime,stddev(billsec) as dv8,
                                 disposition
                            from cdr 
                                     LEFT OUTER JOIN trunkcost ON (cdr.uniqueid = trunkcost.uniqueid) where 
                              userfield " . $ufield . " AND
                              date_part('year',calldate) = '$month[1]' AND
                              date_part('month',calldate) = '$month[0]'
                            group by userfield,disposition
                            order by $morder";
  } else if ($type == "5") {
    if ($xexep == "on") {
      $exceptions=" AND (billsec > " . $monavg[0] . "*" . $exep;
      $exceptions.=" OR duration-billsec > " . $monavg[1] . "*" . $exep; 
      $exceptions.=")";
    }
    $getcdrq="SELECT count(dst) AS callcnt,dst,
                                 sum(billsec) AS tottime,avg(billsec) AS avtime,
                                 avg(duration-billsec) AS holdtime,stddev(billsec) as dv8,
                                 disposition,fullname,sum(cost)
                            from cdr left outer join users on (dst = name) 
                                     LEFT OUTER JOIN trunkcost ON (cdr.uniqueid = trunkcost.uniqueid) where
                              userfield != '' AND $chan AND
                              date_part('year',calldate) = '$month[1]' AND
                              date_part('month',calldate) = '$month[0]'$exceptions
                            group by disposition,dst,fullname
                            order by $morder";
  } else if (($type == "9") || ($type == "2")) {
    if ($xexep == "on") {
      $exceptions=" AND (billsec > " . $monavg[0] . "*" . $exep;
      $exceptions.=" OR duration-billsec > " . $monavg[1] . "*" . $exep; 
      $exceptions.=")";
    }
    $getcdrq="SELECT count(cdr.accountcode) AS callcnt,cdr.accountcode,
                                 sum(billsec) AS tottime,avg(billsec) AS avtime,
                                 avg(duration-billsec) AS holdtime,stddev(billsec) as dv8,
                                 disposition,fullname,sum(cost)
                            from cdr left outer join astdb on (substr(userfield,0,4) = value AND family='Setup' and key='AreaCode') 
                                     left outer join users on (cdr.accountcode = name)
                                     LEFT OUTER JOIN trunkcost ON (cdr.uniqueid = trunkcost.uniqueid) where                                     
                              userfield != '' AND $chan AND
                              date_part('year',calldate) = '$month[1]' AND
                              date_part('month',calldate) = '$month[0]'$exceptions
                            group by cdr.accountcode,disposition,fullname
                            order by $morder";
  }
//  print $getcdrq . "<BR>\n";
  $getcdr=pg_query($db,$getcdrq);
  if ($_POST['print'] < 2) {

%>
<CENTER>
<FORM METHOD=POST NAME=printrep>
<INPUT TYPE=HIDDEN NAME=tavg VALUE="<%print $monavg[0];%>">
<INPUT TYPE=HIDDEN NAME=thold VALUE="<%print $monavg[1];%>">
<INPUT TYPE=HIDDEN NAME=type VALUE="<%print $type;%>">
<INPUT TYPE=HIDDEN NAME=exep VALUE="<%print $exep;%>">
<INPUT TYPE=HIDDEN NAME=xexep VALUE="<%print $xexep;%>">
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
<INPUT TYPE=HIDDEN NAME=date2 VALUE="<%print $date2;%>">
<INPUT TYPE=HIDDEN NAME=dom VALUE="<%print $dom;%>">
<INPUT TYPE=HIDDEN NAME=dom2 VALUE="<%print $dom2;%>">
<INPUT TYPE=HIDDEN NAME=mweight VALUE="<%print $mweight;%>">
<INPUT TYPE=HIDDEN NAME=morder VALUE="<%print $omorder;%>">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $_SESSION['disppage'];%>">
<INPUT TYPE=HIDDEN NAME=disp>
<INPUT TYPE=HIDDEN NAME=exten>
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<FORM METHOD=POST NAME=getrepform onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<INPUT TYPE=HIDDEN NAME=tavg VALUE="<%print $monavg[0];%>">
<INPUT TYPE=HIDDEN NAME=thold VALUE="<%print $monavg[1];%>">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%
  if ($type != "6") {
    print "cdr/getrep.php";
  } else {
    print "cdr/callq.php";
  }
%>">
<INPUT TYPE=HIDDEN NAME=type VALUE="<%print $type;%>">
<INPUT TYPE=HIDDEN NAME=exep VALUE="<%print $exep;%>">
<INPUT TYPE=HIDDEN NAME=xexep VALUE="<%print $xexep;%>">
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
<INPUT TYPE=HIDDEN NAME=date2 VALUE="<%print $date2;%>">
<INPUT TYPE=HIDDEN NAME=dom VALUE="<%print $dom;%>">
<INPUT TYPE=HIDDEN NAME=dom2 VALUE="<%print $dom2;%>">
<INPUT TYPE=HIDDEN NAME=mweight VALUE="<%print $mweight;%>">
<INPUT TYPE=HIDDEN NAME=morder VALUE="<%print $morder;%>">
<INPUT TYPE=HIDDEN NAME=disp>
<INPUT TYPE=HIDDEN NAME=exten>
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<%

  if ($type == "6") {
    print "<TH ALIGN=LEFT CLASS=heading-body2>Queue</TH>";
  } else if ($type == "5") {
    print "<TH ALIGN=LEFT CLASS=heading-body2>Destination</TH>";
  } else {
    print "<TH ALIGN=LEFT CLASS=heading-body2>Source</TH>";
  }

  if ($type == "6") {
    print "<TH ALIGN=LEFT CLASS=heading-body2>All Calls</TH>";
  } else {
    print "<TH ALIGN=LEFT CLASS=heading-body2>Calls</TH>";
  }
  print "<TH ALIGN=LEFT CLASS=heading-body2>State</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>Time</TH><TH ALIGN=LEFT CLASS=heading-body2>Average</TH><TH ALIGN=LEFT CLASS=heading-body2>Std. Dev.</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>Av. Hold Time</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>Total Cost</TH>";
  print "</TR>\n<TR CLASS=list-color1>";
  } else {
    if ($type == "6") {
      $aedir="Queue";
    } else if ($type == "5") {
      $aedir="Destination";
    } else {
      $aedir="Source";
    }

    if ($type == "6") {
      $acty="All Calls";
    } else {
      $acty="Calls"; 
    }

    $data=array($aedir,$acty,"State","Time","Std. Dev.","Av. Hold Time","Total Cost");
    $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
    print $dataout;
  }

  $ccnt=0;
  while($r = pg_fetch_row($getcdr)) {
    if ($r[1] == "") {
      $r[1]="Unknown";
    }
    if ($r[7] != "") {
      $r[7]=$r[7] . " (" . $r[1] . ")"; 
    } else {
      $r[7]=$r[1]; 
    }
    if ($_POST['print'] < 2) {
      $rem=$ccnt % 2;
      if ($rem == 1) {
        $bcolor=" CLASS=list-color1";
      } else {
        $bcolor=" CLASS=list-color2";
      }
      print "</TD><TD>";
      if ($_POST['print'] != "1") {
        print "<A HREF=\"javascript:opencdrrep('" . $r[6] . "','" . $r[1] . "')\">";
      }
      print $r[7];
      if ($_POST['print'] != "1") {
        print "</A>";
      }
      print "</TD>";
      print "<TD>" . $r[0] . "</TD>";
      print "<TD>" . $r[6] . "</TD>";
      print "<TD>" . gtime($r[2]);
      print "</TD><TD>";
      if (($r[3] > $monavg[0]*$exep) && ($exep > 0) && ($xexep == "")) {
        print "<FONT COLOR=RED>";
      }
      print gtime($r[3]);
      print "</TD><TD>" . gtime($r[5]);
      print "</TD><TD>";
      if (($r[4] > $monavg[1]*$exep) && ($exep > 0) && ($xexep == "")) {
        print "<FONT COLOR=RED>";
      }
      print gtime($r[4]);
      print "</TD><TD>" . sprintf("%0.2f",$r[8]/100000) . "</TD></TR>\n<TR $bcolor>";
    } else {
      $data=array($r[7],$r[0],$r[6],gtime($r[2]),gtime($r[3]),gtime($r[5]),gtime($r[4]),sprintf("%0.2f",$r[8]/100000));
      $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
      print $dataout;
    }
    $ccnt++;
  }
  if ($_POST['print'] < 2) {
    $ccnt++;
    $rem=$ccnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color2";
    }

    if ($_POST['print'] != "1") {
      print "<TR " . $bcolor  . "><TH COLSPAN=9 CLASS=heading-body>";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.printrep)\">";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.printrep)\">";
      print "</TH></TR>";
    }
    print "</TABLE>";
  }
%>
