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


  include "func.inc";

  if ($search != "") {
    $csearch=" = '" . $search . "'";
  } else {
    $csearch="IS NOT NULL";
  }

  if ($extsearch != "") {
    $esearch=" = '" . $extsearch . "'";
  } else {
    $esearch="IS NOT NULL";
  }


  if ($ADMIN_USER != "admin") {
    return;
  }

  $time="(calldate > '" . $month[1] . "-" . $month[0] . "-" . $month[2] . "' AND calldate < '" . $month2[1] . "-" . $month2[0] . "-" . $month2[2] . "')";


  $getcdrq="SELECT to_char(calldate,'DD HH24:MI:SS'),userfield,billsec, case when (position('-' in dstchannel) > 0) then 
                   substr(dstchannel,0,position('-' in dstchannel)) else lastapp||'('||lastdata||')' end, 
                   duration-billsec AS holdtime,CASE WHEN (billsec>0) THEN cost ELSE 0 END AS cost,accountcode,disposition 
            FROM cdr 
            LEFT OUTER JOIN trunkcost USING (uniqueid)";
  if ($TMS_USER == 1) {
    $getcdrq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=accountcode AND bgrp.key='BGRP')";
  }
  $getcdrq.=" WHERE userfield != '' AND dstchannel ~ '" . $trunkchan . "' AND " . $time . " AND dst " . $csearch . " 
              AND accountcode " . $esearch . "";
  if (($TMS_USER == 1) && ($SUPER_USER != 1)) {
    $getcdrq.=" AND " . $clogacl;
  }
  $getcdrq.=" ORDER by " . $sortby;

//  print $getcdrq . "<BR>";
  $getcdr=pg_query($db,$getcdrq);

  if ($_POST['print'] != "2") {
    print "<FORM NAME=ppage METHOD=POST>";
    print "<INPUT TYPE=HIDDEN NAME=print>";
    print "<INPUT TYPE=HIDDEN NAME=disppage VALUE=\"" . $disppage . "\">\n";
    print "<INPUT TYPE=HIDDEN NAME=date VALUE=\"" . $date . "\">\n";
    print "<INPUT TYPE=HIDDEN NAME=date2 VALUE=\"" . $date2 . "\">\n";
    print "<INPUT TYPE=HIDDEN NAME=dom VALUE=\"" . $dom . "\">\n";
    print "<INPUT TYPE=HIDDEN NAME=dom2 VALUE=\"" . $dom2 . "\">\n";
    print "<INPUT TYPE=HIDDEN NAME=sortby VALUE=\"" . $sortby . "\">\n";
    print "<INPUT TYPE=HIDDEN NAME=search VALUE=\"" . $search . "\">\n";
    print "<INPUT TYPE=HIDDEN NAME=extsearch VALUE=\"" . $extsearch . "\"></FORM>\n";
    print "<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>\n<TR CLASS=list-color2>";

    print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Date") . "</TH>";
    print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Destination") . "</TH>";
    print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Source") . "</TH>";
    print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Result") . "</TH>";
    print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Calltime") . "</TH>";
    print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Hold Time") . "</TH>";
    print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Channel") . "</TH>";
    print "<TH ALIGN=RIGHT CLASS=heading-body2>" . _("Cost") . "</TH>";
    print "</TR>\n<TR CLASS=list-color1>";
  } else {
    print printcsv(array(_("Date"),_("Destination"),_("Source"),_("Result"),_("Calltime"),_("Hold Time"),_("Channel"),_("Cost")));
  }
  $ccnt=0;
  $toc=0;
 
  $unkpre=array();

  for($i=0;$i<pg_num_rows($getcdr);$i++) {
    $r=pg_fetch_row($getcdr,$i);
    if ($_POST['print'] != "2") {
      $rem=$ccnt % 2;
      if ($rem == 1) {
        $bcolor=" CLASS=list-color1";
      } else {
        $bcolor=" CLASS=list-color2";
      }
      print "<TD>" . $r[0] . "&nbsp;&nbsp;</TD>";
      print "<TD>" . $r[1] . "&nbsp;&nbsp;</TD>";
      print "<TD>" . $r[6] . "&nbsp;&nbsp;</TD>";
      print "<TD>" . $r[7] . "&nbsp;&nbsp;</TD>";
      print "<TD>";
      print gtime($r[2]) . "&nbsp;&nbsp;</TD>";
      print "<TD>";
      print gtime($r[4]) . "&nbsp;&nbsp;</TD>";
      print "<TD>" . $r[3] . "</TD>";
      print "<TD ALIGN=RIGHT>" . sprintf("%0.2f",$r[5]/100000) . "</TD>";
//      print "<TD ALIGN=RIGHT>X" . $r[5] . "X</TD>";
      print "</TR>\n<TR $bcolor>";
    } else {
      print printcsv(array($r[0],$r[1],$r[6],$r[7],gtime($r[2]),gtime($r[4]),$r[3],sprintf("%0.2f",$r[5]/100000)));
    }
    $ccnt++;
    $toc=$toc+$r[2];
    $totalcost=$totalcost+$r[5];
    if (($r[7] == "ANSWERED") && (($r[5] == "") || ($r[5] <= 0))) {
      if (preg_match("/(^0[1-79][0-9]{4})[0-9]{4}/i",$r[1],$preinf)) {
        if ($seenpre[$preinf[1]] != 1) {
          $seenpre[$preinf[1]]=1;
          array_push($unkpre,$preinf[1]);
        }
      } else if (preg_match("/^00([1-9][0-9]+)/i",$r[1],$preinf)) {
        $intpreq=pg_query("SELECT * from countryprefix where prefix=substr('" . $preinf[1] . "',1,length(prefix)) ORDER BY length(prefix) DESC LIMIT 1");
        $intpre=pg_fetch_array($intpreq,0);
        if ($seenpre[$intpre[0]] != 1) {
          $seenpre[$intpre[0]]=1;
          array_push($unkpre,"00" . $intpre[0]);
        }
      } else if (preg_match("/^(08[1-9][0-9]{3})[0-9]{4}/i",$r[1],$preinf)) {
        if ($seenpre[$preinf[1]] != 1) {
          $seenpre[$preinf[1]]=1;
          array_push($unkpre,$preinf[1]);
        }
      } else if (preg_match("/^([1-9][0-9]{4}[0-9]+)/i",$r[1],$preinf)) {
        if ($seenpre[$preinf[1]] != 1) {
          $seenpre[$preinf[1]]=1;
          array_push($unkpre,$preinf[1]);
        }
      }
    }
  }


  if ($_POST['print'] != "2") {
    $rem=$ccnt % 2;
    if ($rem == 0) {
      $bcolor=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color2";
    }
    $rcol=$rem;
    print "<TR $bcolor><TD COLSPAN=4>&nbsp;</TD><TD CLASS=heading-body2>" . gtime($toc);
    $rcol++;
    print "</TD><TD>&nbsp;</TD><TD CLASS=heading-body2>";
    print $ccnt . " Calls</TD><TD ALIGN=RIGHT CLASS=heading-body2>" . sprintf("%0.2f",$totalcost/100000) . "</TD></TR>\n";

    if (count($unkpre) > 0) {
      sort($unkpre);
      reset($unkpre);

      print "<TR CLASS=list-color" . (($rcol % 2) + 1) . " STYLE=\"page-break-before: always\"><TH CLASS=heading-body COLSPAN=8>" . _("Unknown Routes") . "</TH></TR>\n";
      $rcol++;
      for($ucnt=0;$ucnt<count($unkpre);$ucnt++) {
        if (($ucnt % 8) == 0) {
          print "</TR>\n<TR CLASS=list-color" . (($rcol % 2) + 1) . ">";
          $rcol++;
        }
        print "<TD>" . $unkpre[$ucnt] . "</TD>";
      }
      if ($ucnt != 8) {
        for($ucnt1=$ucnt % 8;$ucnt1<8;$ucnt1++){
          print "<TD>&nbsp;</TD>";
        }
      }
      print "</TR>\n";
    }
  } else {
    print printcsv(array("","","","",gtime($toc),"",$ccnt . " Calls",sprintf("%0.2f",$totalcost/100000)));
  }
  if ($_POST['print'] < 1) {
    print "<TR CLASS=list-color" . (($rcol % 2)+1) . ">";
    print "<TH COLSPAN=8 CLASS=heading-body><INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\">";
    print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.ppage)\">";
    print "</TH></TR>";
  }
  
  if ($_POST['print'] != "2") {
    print "</TABLE>";
  }
?>
