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
    $esearch=" = '" . $exten . "'";
  } else {
    $esearch="IS NOT NULL";
  }
  if ($calltime != "") {
    $calltime=" billsec > " . $calltime . "*60 AND";
  }

  if ($_POST['fqueue'] != "") {
    $qfilt=" AND queuename='" . $_POST['fqueue'] . "' ";
    $qlev="";
  } else { 
    $qlev="2";
  }

  $acdls="time > '" . $time_year . "-" . $time_month . "-" . $time_day ." " . $time_hour . ":" . $time_min . ":" . $time_sec . "' AND
          time < '" . $mtime_year . "-" . $mtime_month . "-" . $mtime_day ." " . $mtime_hour . ":" . $mtime_min . ":" . $mtime_sec . "'" . $qfilt;

  $discrip['ENTERQUEUE']="Total Amount Of Calls";
  $discrip['CONNECT']="Calls Connected With Agent";
  $discrip['ABANDON']="Calls Abandonded By Caller";
  $discrip['EXITWITHTIMEOUT']="Calls Ignored By Agents";
  $discrip['EXITWITHKEY']="Calls Abandonded By Caller (Exit Key)";
  $discrip['LOST']="Calls Lost/Droped/Queued";
  $discrip['TRANSFER']="Connected Calls Transfered";
  $discrip['COMPLETEAGENT']="Connected Calls Hungup By Agents";
  $discrip['COMPLETECALLER']="Connected Calls Hungup By Caller";
  $discrip['RINGNOANSWER']="Nuber Of Times A Call Rang Without Answer";
  $discrip['AGENTCALLBACKLOGIN']="Agent Login";
  $discrip['ADDMEMBER']="Agent Login";
  $discrip['AGENTCALLBACKLOGOFF']="Agent Logoff";
  $discrip['REMOVEMEMBER']="Agent Logoff";

  $edata['CONNECT']=array(",avg(CAST (data1 AS integer))",1,"Average Hold Time");
  $edata['TRANSFER']=array(",avg(CAST (data3 AS integer)),avg(CAST (data4 AS integer))",2,"Average Hold Time","Average Call Time");
  $edata['COMPLETEAGENT']=array(",avg(CAST (data1 AS integer)),avg(CAST (data2 AS integer))",2,"Average Hold Time","Average Call Time");
  $edata['COMPLETECALLER']=array(",avg(CAST (data1 AS integer)),avg(CAST (data2 AS integer))",2,"Average Hold Time","Average Call Time");
  $edata['RINGNOANSWER']=array(",avg(CAST (data1 AS integer))/1000",1,"Average Ring Time");

  function row_output($okey,$percin) {
     global $rcnt,$qlev,$discrip,$stats,$acdls,$qfilt,$db,$edata;
     if ($stats[$okey] == "") {
       return;
     }
     if ($percin > 0) {
       $perc=sprintf("%0.2f%%",($stats[$okey]/$percin)*100);
     } else {
       $perc="";
     }
     if ($qfilt != "") {
       $qclass=" CLASS=heading-body2 ";
       
     }
     if ($discrip[$okey] != "") {
       $keyname=$discrip[$okey];
     } else {
       $keyname=$okey;
     }

     if ($_POST['print'] < 2) {
       print "\n<TR CLASS=list-color" . (($rcnt % 2) + 1) . "><TD WIDTH=90%" . $qclass . ">";
       if (($_POST['fqueue'] != "") && ($_POST['print'] < 1) && (($okey == "CONNECT") || ($okey == "ABANDON") || ($okey == "AGENTCALLBACKLOGIN") || ($okey == "AGENTCALLBACKLOGOFF") || ($okey == "ADDMEMBER") || ($okey == "REMOVEMEMBER"))) {
         print "<A HREF=\"javascript:openacdpop('" . $okey . "')\">" . $keyname  . "</A>";
       } else {
         print $keyname;
       }
       print "</TD><TD ALIGN=RIGHT" . $qclass . ">" . $stats[$okey] . "&nbsp;&nbsp;</TD>";
       print "<TD ALIGN=RIGHT" . $qclass . ">" . $perc . "&nbsp;&nbsp;</TD></TR>";
     } else {
       $data=array($keyname,"",$stats[$okey],$perc);
       $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
       print $dataout;
     }

     if (($qfilt != "") && ($okey != 'LOST')) {
       $agetinfq="SELECT fullname,agent,count(queue_log.id)" . $edata[$okey][0] . " from queue_log LEFT OUTER JOIN users ON (name=agent) where " . $acdls . "AND agent != 'NONE' AND event='" . $okey . "' group by agent,users.fullname order by count(queue_log.id) desc";
//print $agentinfq . "<P><P>";
       $agetinf=pg_query($db,$agetinfq);
       for($agr=0;$agr < pg_num_rows($agetinf);$agr++) {
         $rcnt++;
         $r=pg_fetch_row($agetinf, $agr);
         $perc2=sprintf("%0.2f%%",($r[2]/$stats[$okey])*100);

         if ($_POST['print'] < 2) {
           print "\n<TR CLASS=list-color" . (($rcnt % 2) + 1) . "><TD WIDTH=90% STYLE=\"padding-left:10px\">";
           if (($r[3] != "") && ($_POST['print'] < 1)) {
             print  "<A HREF=\"javascript:alert('";
             for ($dcnt=0;$dcnt<$edata[$okey][1];$dcnt++) {
               print $edata[$okey][2+$dcnt] . " : " . sprintf("%0.2f",$r[3+$dcnt]) . "\\n";
             }
             print "')\">";
           }
           print $r[0] . " (" . $r[1] . ")";
           if (($r[3] != "") && ($_POST['print'] < 1)) {
            print "</A>";
           }
           print "</TD><TD ALIGN=RIGHT>" . $r[2] . "&nbsp;</TD>";
           print "<TD ALIGN=RIGHT>" . $perc2 . "&nbsp;&nbsp;</TD></TR>";
         } else {
           $data=array("",$r[0] . " (" . $r[1] . ")",$r[2],$perc2);
           $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
           print $dataout;
         }
       }
     }
     $rcnt++;
  }

  $acdlistq="SELECT DISTINCT description,name from queue_log
                      left outer join queue_table on (queuename=name) ";
  if ($SUPER_USER != 1) {
    $acdlistq.="LEFT OUTER JOIN astdb AS bgrp ON ('Q'||name=bgrp.family AND bgrp.key='BGRP') ";
  }
  $acdlistq.="where " . $acdls;
  if ($SUPER_USER != 1) {
    $acdlistq.=" AND " . $clogacl;
  }
//  $acdlistq.=" order by description";
//  print  $acdlistq . "<P><P>";
  $acdlist=pg_query($db,$acdlistq);

  if ($_POST['print'] < 2) {
%>
<CENTER>
<FORM NAME=pform METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $_SESSION['disppage']%>">
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
<INPUT TYPE=HIDDEN NAME=fqueue VALUE="<%print $_POST['fqueue'];%>">
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
<INPUT TYPE=HIDDEN NAME=nomenu>
<INPUT TYPE=HIDDEN NAME=poptype>
</FORM>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=45%>
<TR CLASS=list-color2>
<TH COLSPAN=3 CLASS=heading-body>
ACD Report For Period (<%print $time_year . "-" . str_pad($time_month,2,"0",STR_PAD_LEFT) . "-" . str_pad($time_day,2,"0",STR_PAD_LEFT) .  " " . str_pad($time_hour,2,"0",STR_PAD_LEFT) . ":" . str_pad($time_min,2,"0",STR_PAD_LEFT) . ":" . str_pad($time_sec,2,"0",STR_PAD_LEFT) . " To " . $mtime_year . "-" . str_pad($mtime_month,2,"0",STR_PAD_LEFT) . "-" . str_pad($mtime_day,2,"0",STR_PAD_LEFT) . " " . str_pad($mtime_hour,2,"0",STR_PAD_LEFT) . ":" . str_pad($mtime_min,2,"0",STR_PAD_LEFT) . ":" . str_pad($mtime_sec,2,"0",STR_PAD_LEFT);%>)
</TH>
</TR>
<TR CLASS=list-color1>
<%
/*
  print "<TH ALIGN=LEFT CLASS=heading-body>" . _("Event") . "</TH>";
  print "<TH CLASS=heading-body>" . _("Count") . "</TH>";
  print "<TH CLASS=heading-body>%</TH>";
  print "</TR>\n<TR CLASS=list-color1>";
*/
} else {
/*
  $data=array(_("Event"),_("Count"),"%");
  $dataout="\"" . str_replace(array("\"","--!@#--"),array("\"\"","\",\""),implode("--!@#--",$data)). "\"\n";
  print $dataout;
*/
}
  $ccnt=0;
  $toc=0;

  $rcnt=0;

  $queueold="";
  $stats=array();
  for($ia=0;$ia < pg_num_rows($acdlist);$ia++) {
    $acd=pg_fetch_row($acdlist, $ia);
    $acdstatq="SELECT count(distinct callid),case when (description is not null) then description else queuename end,event,queuename from queue_log
                        left outer join queue_table on (queuename=name) 
                      where 
                        time > '" . $time_year . "-" . $time_month . "-" . $time_day ." " . $time_hour . ":" . $time_min . ":" . $time_sec . "' AND
                        time < '" . $mtime_year . "-" . $mtime_month . "-" . $mtime_day ." " . $mtime_hour . ":" . $mtime_min . ":" . $mtime_sec . "' AND
                        queue_table.name = '" . $acd[1] . "' 
                      group by description,queuename,event 
                      order by queue_log.queuename,event";
//print $acdstatq . "<P><P>\n";
//  if ($SUPER_USER != 1) {
//    $getcdrq.=" LEFT OUTER JOIN astdb AS bgrp ON ((accountcode=bgrp.family  OR src=bgrp.family OR exten=bgrp.family OR dst=bgrp.family) AND bgrp.key='BGRP')";
//  }
//  if ($SUPER_USER != 1) {
//    $getcdrq.=" AND $clogacl";
//  }
//  $getcdrq.=" GROUP BY calldate,calllog.uniqueid,clid,billsec,src,dst,userfield,accountcode,cdr.uniqueid 
//                ORDER BY calldate,cdr.uniqueid;";
//    print $acdstatq . "<P>";
    $getcdr=pg_query($db,$acdstatq);

    for($i=0;$i < pg_num_rows($getcdr);$i++) {
      $r=pg_fetch_row($getcdr, $i);
      $stats[$r[2]]=$r[0];

      if ($r[1] != $queueold) {
        $queueold=$r[1];
        if ($r[1] == "799") {
          $r[1]="Default Ring All Queue (799)";
        } else {
          $r[1].=" (" . $r[3] . ")";
        }
        if ($_POST['print'] < 2) {
          print "<TR CLASS=list-color" . (($rcnt % 2) + 1);
          print "><TH COLSPAN=3 CLASS=heading-body" . $qlev . ">";
	  if (($qlev != "") && ($r[3] != "NONE")) {
		$r[1]="<A HREF=\"javascript:openqueue(" . $r[3] . ")\">" . $r[1] . "</A>"; 
          }
          print $r[1];
          print "</TH>";
          print "</TR>\n";
          $rcnt++;
        } else {
          $data=array($r[1]);
          $dataout="\"" . str_replace(array("\"","--!@#--"),array("\"\"","\",\""),implode("--!@#--",$data)). "\"\n";
          print $dataout;
        }
      }
    }
    if (count($stats) > 0) {
      $stats['LOST']=$stats['ENTERQUEUE']-($stats['CONNECT']+$stats['EXITWITHTIMEOUT']+$stats['ABANDON']+$stats['EXITWITHKEY']);
      print row_output("ENTERQUEUE",$stats['ENTERQUEUE']);
      print row_output("CONNECT",$stats['ENTERQUEUE']);
      print row_output("ABANDON",$stats['ENTERQUEUE']);
      print row_output("EXITWITHTIMEOUT",$stats['ENTERQUEUE']);
      print row_output("EXITWITHKEY",$stats['ENTERQUEUE']);
      print row_output("LOST",$stats['ENTERQUEUE']);
      print row_output("TRANSFER",$stats['CONNECT']);
      print row_output("COMPLETEAGENT",$stats['CONNECT']);
      print row_output("COMPLETECALLER",$stats['CONNECT']);
      print row_output("RINGNOANSWER",0);
      print row_output("AGENTCALLBACKLOGIN",0);
      print row_output("AGENTCALLBACKLOGOFF",0);
      print row_output("ADDMEMBER",0);
      print row_output("REMOVEMEMBER",0);
      while(list($key,$value) = each($stats)) {
        if (! isset($discrip[$key])) {
          print row_output($key,$value);
        }
      }
    }
    $stats=array();
  }
  if ($ia == 0) {
    if ($_POST['print'] < 2) {
      print "<TR CLASS=list-color" . (($rcnt % 2) + 1);
      print "><TH COLSPAN=3 CLASS=heading-body>No Entries Returned For This Period</TH>";
      print "</TR>\n";
      $rcnt++;
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
/*
    print "<TR $bcolor><TD COLSPAN=3>&nbsp;</TD><TD CLASS=heading-body2>" . gtime($toc);
    print "</TD><TD COLSPAN=3>&nbsp;</TD><TD CLASS=heading-body2>";
    print $ccnt . " Calls</TD><TD COLSPAN=1>&nbsp;</TD></TR>\n";
    $rcol++;
*/
    if (($_POST['print'] != "1") && ($ia > 0)) {
      print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=3 CLASS=heading-body>";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.pform)\">";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.pform)\">";
      print "</TH></TR>";
    }
    print "</TABLE>";
  }
%>
