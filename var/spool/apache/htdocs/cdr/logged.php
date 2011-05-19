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

  if ($exten != "") {
    $esearch="(exten ='" . $exten . "' OR dst = '" . $exten . "') AND ";
  }
  if ($calltime != "") {
    $calltime=" billsec > " . $calltime . "*60 AND";
  }
  $getcdrq="SELECT DISTINCT ON (calldate,cdr.linkedid) to_char(calldate,'YYYY-MM-DD HH24:MI:SS'),clid,billsec,src,
                CASE WHEN (dst != 'h') THEN dst ELSE CASE WHEN (userfield != '') THEN userfield ELSE dst END END,
                count(callleg),cdr.linkedid,
                case when (length(userfield) > 4 AND (length(accountcode) = 4 OR length(src) = 4)) then 'Out' else 'In' end as direc
                FROM cdr 
                  LEFT OUTER JOIN calllog ON (cdr.linkedid=calllog.uniqueid)";
  if ($SUPER_USER != 1) {
    $getcdrq.=" LEFT OUTER JOIN astdb AS bgrp ON ((accountcode=bgrp.family  OR src=bgrp.family OR exten=bgrp.family OR dst=bgrp.family) AND bgrp.key='BGRP')";
  }
  $getcdrq.=" WHERE disposition='ANSWERED' AND " . $esearch . $calltime . " 
		calldate > '" . $time_year . "-" . $time_month . "-" . $time_day ." " . $time_hour . ":" . $time_min . ":" . $time_sec . "' AND 
		calldate < '" . $mtime_year . "-" . $mtime_month . "-" . $mtime_day ." " . $mtime_hour . ":" . $mtime_min . ":" . $mtime_sec . "'";
  if ($SUPER_USER != 1) {
    $getcdrq.=" AND $clogacl";
  }
  $getcdrq.=" GROUP BY calldate,cdr.linkedid,clid,billsec,src,dst,userfield,accountcode
                ORDER BY calldate,cdr.linkedid;";

//  print $getcdrq . "<P>";
/*
                 ((cdr.uniqueid=calllog.uniqueid AND (cdr.channel != calllog.dstchannel OR calllog.dstchannel is null)) OR 
                 (calllog.uniqueid != cdr.uniqueid AND calllog.dstchannel=cdr.dstchannel AND calllog.dstchannel is not null)) 
*/
  $getcdr=pg_query($db,$getcdrq);

  if ($_POST['print'] < 2) {
%>
<CENTER>
<FORM NAME=pform METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $_SESSION['disppage'];%>">
<INPUT TYPE=HIDDEN NAME=time_year VALUE="<%print $time_year;%>">
<INPUT TYPE=HIDDEN NAME=time_month VALUE="<%print $time_month;%>">
<INPUT TYPE=HIDDEN NAME=time_day VALUE="<%print $time_day;%>">
<INPUT TYPE=HIDDEN NAME=time_hour VALUE="<%print $time_hour;%>">
<INPUT TYPE=HIDDEN NAME=time_min VALUE="<%print $time_min;%>">
<INPUT TYPE=HIDDEN NAME=time_sec VALUE="<%print $time_sec;%>">
<INPUT TYPE=HIDDEN NAME=mtime_year VALUE="<%print $mtime_year;%>">
<INPUT TYPE=HIDDEN NAME=mtime_month VALUE="<%print $mtime_month;%>">
<INPUT TYPE=HIDDEN NAME=mtime_day VALUE="<%print $mtime_day;%>">
<INPUT TYPE=HIDDEN NAME=mtime_hour VALUE="<%print $mtime_hour;%>">
<INPUT TYPE=HIDDEN NAME=mtime_min VALUE="<%print $mtime_min;%>">
<INPUT TYPE=HIDDEN NAME=mtime_sec VALUE="<%print $mtime_sec;%>">
<INPUT TYPE=HIDDEN NAME=exten VALUE="<%print $exten;%>">
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
</FORM>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<%
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Date") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Caller ID") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Calltime") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Source") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Destination") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Legs/Play") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Direction") . "</TH>";
  print "</TR>\n<TR CLASS=list-color1>";
} else {
  $data=array(_("Date"),_("Caller ID"),_("Calltime"),_("Source"),_("Destination"),_("Legs Recorded"),_("Call ID"),_("Direction"));
  $dataout="\"" . str_replace(array("\"","--!@#--"),array("\"\"","\",\""),implode("--!@#--",$data)). "\"\n";
  print $dataout;
}
  $ccnt=0;
  $toc=0;

  $rcnt=0;

  for($i=0;$i < pg_num_rows($getcdr);$i++) {
    $r=pg_fetch_row($getcdr, $i);
    if ($r[5] <= 0) {
      continue;
    }
    if (($r[7] == $logdir) || ($logdir == "")) {
      $rem=$rcnt % 2;
      if ($rem == 1) {
        $bcolor=" CLASS=list-color1";
      } else {
        $bcolor=" CLASS=list-color2";
      }

      if ($_POST['print'] < 2) {
        print "<TD>" . $r[0] . "&nbsp;&nbsp;</TD>";
        print "<TD>" . $r[1] . "&nbsp;&nbsp;</TD>";
        print "<TD>";
        print gtime($r[2]) . "&nbsp;&nbsp;</TD>";
        print "<TD>" . $r[3] . "&nbsp;&nbsp;</TD>";
        print "<TD>" . $r[4] . "&nbsp;&nbsp;</TD>";
        print "<TD><A HREF=javascript:calllog('" . $r[6] . "')>" . $r[5] . "</A></TD>";
        print "<TD ALIGN=MIDDLE>" . $r[7] . "</TD>";
        print "</TR>\n<TR $bcolor>";
      } else {
        $data=array($r[0],$r[1],gtime($r[2]),$r[3],$r[4],$r[5],$r[6],$r[7]);
        $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
        print $dataout;
      }
      $ccnt=$ccnt+$r[5];
      $rcnt++;
      $toc=$toc+$r[2];
    }
  }
  if ($_POST['print'] < 2) {
    $rem=$rcnt % 2;
    if ($rem == 0) {
      $bcolor=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color2";
    }
    $rcol=$rem;
    print "<TR $bcolor><TD COLSPAN=2>&nbsp;</TD><TD CLASS=heading-body2>" . gtime($toc);
    print "</TD><TD COLSPAN=2>&nbsp;</TD><TD CLASS=heading-body2>";
    print $ccnt . " Calls</TD><TD COLSPAN=1>&nbsp;</TD></TR>\n";

    $rcol++;
    if ($_POST['print'] != "1") {
      print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=7 CLASS=heading-body>";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.pform)\">";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.pform)\">";
      print "</TH></TR>";
    }
    print "</TABLE>";
  }
%>
