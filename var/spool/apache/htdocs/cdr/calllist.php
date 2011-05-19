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

  if ((isset($_POST['mmap'])) && (!isset($_POST['callid']))) {
    $callid=$_POST['mmap'];
  } else if (isset($_POST['callid'])) {
    $callid=$_POST['callid'];
  } else if (isset($callid)) {
    unset($callid);
  }


  if ($exten != "") {
    $esearch=" = '" . $exten . "'";
  } else {
    $esearch="IS NOT NULL";
  }
  $getcdrq="SELECT DISTINCT callleg,exten,fullname,
         date_part('year',calldate),date_part('month',calldate),date_part('day',calldate),'/'||exten||'/'||calllog.uniqueid||'-'||callleg||'.WAV'
       AS filename FROM cdr
     LEFT OUTER JOIN calllog ON (cdr.uniqueid=calllog.uniqueid OR cdr.channel = calllog.dstchannel)
     LEFT OUTER JOIN users ON (name=exten)";
  if ($SUPER_USER != 1) {
    $getcdrq.=" LEFT OUTER JOIN astdb AS bgrp ON (exten=bgrp.family AND bgrp.key='BGRP')";
  }
  $getcdrq.=" WHERE exten IS NOT NULL AND calllog.uniqueid='" . $callid . "'";
  if ($SUPER_USER != 1) {
    $getcdrq.=" AND " .  $clogacl;
  }
  $getcdrq.=" ORDER BY callleg";
//  print $getcdrq . "<P>";
  $getcdr=pg_query($db,$getcdrq);

%>
<FORM NAME=pform METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
<INPUT TYPE=HIDDEN NAME=date VALUE="<%print $date;%>">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $showpage;%>">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE="<%if ($_POST['nomnenu'] < 2) {print $_POST['nomenu'];}%>">
</FORM>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2><%

  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Call Leg") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Extension") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Full Name") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Length") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Play File") . "</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Download") . "</TH>";
  print "</TR>\n";
  $ccnt=0;
  $toc=0;
 

  $rcnt=0;
  $bcolor[0]=" CLASS=list-color1";
  $bcolor[1]=" CLASS=list-color2";
  for($i=0;$i < pg_num_rows($getcdr);$i++) {
    $r=pg_fetch_row($getcdr,$i);
    $rem=$rcnt % 2;
    print "<TR $bcolor[$rem]>";

    print "  <TD>" . $r[0] . "&nbsp;&nbsp;</TD>\n";
    print "  <TD>" . $r[1] . "&nbsp;&nbsp;</TD>\n";
    print "  <TD>" . $r[2] . "&nbsp;&nbsp;</TD>\n";
    print "  <TD";
    if ($r[4] < 10) {
      $r[4]="0" . $r[4];
    }
    if ($r[5] < 10) {
      $r[5]="0" . $r[5];
    }
    $r[3]=$r[3] . "-" . $r[4] . "-" . $r[5] . $r[6];
    $fname="/var/spool/asterisk/monitor/" . $r[3];
    if (is_file($fname)) {
      $fsize=gtime(ceil((((filesize($fname)-44)/1024)*24)/41));
      print ">" . $fsize . "</TD>\n";
      print "  <TD><embed src=\"/auth/getlog.php?logfile=" . urlencode($r[3]) . "\" autostart=false loop=false height=62 width=144 controls=console></TD>\n";
      print "  <TD><A HREF=/cdr/logdload/" . $r[3] . ">";
      print "<IMAGE SRC=/images/dload.png width=32 height=32 border=0></A></TD>\n";
    } else {
      print ">&nbsp;</TD>\n  <TD>No Recording</TD>\n  <TD>&nbsp</TD>\n";
    }
    print "</TD>\n</TR>\n";
    $ccnt=$ccnt+$r[5];
    $rcnt++;
    $toc=$toc+$r[2];
  }

  $rem=$rcnt % 2;
  $getprevq="SELECT cdr.uniqueid from cdr left outer join calllog on (cdr.channel=calllog.dstchannel) where 
             calllog.uniqueid='" . $callid . "' order by calllog.uniqueid LIMIT 1";
//  print $getprevq . ";<P>";
  $getprev=pg_query($db,$getprevq);
  print "<TR " . $bcolor[$rem] . ">\n<TH COLSPAN=3 CLASS=heading-body>";
  if (pg_num_rows($getprev) > 0) {
    $puid=pg_fetch_row($getprev,0);
    print "<A HREF=\"javascript:calllog('" . $puid[0] . "')\">Prev</A>";
  } else {
    print "&nbsp;";
  }
  print "</TH>\n<TH COLSPAN=3 CLASS=heading-body>";
  $rcnt++;

  $rem=$rcnt % 2;
  $getnextq="SELECT calllog.uniqueid from cdr 
               left outer join cdr as cdr2 on (cdr.channel=cdr2.dstchannel) 
               left outer join calllog on (cdr.channel=calllog.dstchannel) 
             where (cdr2.calldate <= cdr.calldate + cdr.duration * interval '1 second' OR calllog.uniqueid is not null) AND
                cdr.uniqueid = '" . $callid . "' order by calllog.uniqueid LIMIT 1"; 
//  print $getnextq . ";<P>";
  $getnext=pg_query($db,$getnextq);
  if (pg_num_rows($getnext) > 0) {
    $nuid=pg_fetch_row($getnext,0);
    if ($nuid[0] != "") {
      print "<A HREF=\"javascript:calllog('" . $nuid[0] . "')\">Next</A>";
    } else {
      print "&nbsp;";
    }
  } else {
    print "&nbsp;";
  }
  print "</TH></TR>\n";

  print "</TABLE>";
%>
