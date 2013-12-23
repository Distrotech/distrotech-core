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

include_once "auth.inc";

if ($modchan == "1") {%>
<html>
<head>
<link rel="stylesheet" href="/style.php?style=">

<script language="JavaScript" src="/java_popups.php?style=" type="text/javascript"></script>
<script language="JavaScript" src="/hints.js" type="text/javascript"></script>
<script language="JavaScript" src="/hints_cfg.php?disppage=cdr%2Fgsmroute.php" type="text/javascript"></script>

</head>
<%
}

%>

<CENTER>
<FORM METHOD=POST NAME=gsmrform onsubmit="ajaxsubmit(this.name);return false;">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE=<%print $_POST['nomenu'];%>>
<INPUT TYPE=HIDDEN NAME=router VALUE="<%print $router;%>">

<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body COLSPAN=2><%print _("Asterisk GSM Channel Configuration");%></TH>
  </TR>
  <TR CLASS=list-color1>

<%
if ((isset($delchan)) && ($channel != "")) {
  pg_query($db,"DELETE FROM gsmchannels where channel = " . $channel);
} elseif (isset($upchan)) {
  if ($inuse == "on") {
    $inuse="t";
  } else {
    $inuse="f";
  }

  if ($outofservice == "on") {
    $outofservice="now() + interval '30 minutes'";
  } else {
    $outofservice="now()";
  }

  $expires=$eyear . "-" . $emonth . "-" . $eday;
  $ctime=$nminutes*60+$nseconds;
  $gsmup="UPDATE gsmchannels SET calltime=" . $ctime . ",outofservice=" . $outofservice . ",inuse='" . $inuse . "',expires='" . $expires . "',starttime='$nstarttime',endtime='$nendtime',regex='$nregex' WHERE channel = " . $channel;
  pg_query($db,$gsmup);
  if ($_POST['nomenu'] == 1) {
    $modchan=1;
  }
}


if (isset($modchan)) {
  if ($nchannel != "") {
    $ctime=$nminutes*60+$nseconds;
    $insquery="INSERT INTO gsmchannels (channel,calltime,expires,starttime,endtime,regex,router) VALUES (" . $nchannel . "," . $ctime . ",'$eyear-$emonth-$eday','$nstarttime','$nendtime','$nregex','$router')";
    pg_query($db,$insquery);
    $channel=$nchannel;
  }

  $qgetdata=pg_query($db,"SELECT calltime,date(expires),inuse,not outofservice < now(),starttime,endtime,regex,faultcount,CASE WHEN (outofservice < now()) THEN date_trunc('seconds',now()-outofservice) ELSE date_trunc('seconds',outofservice-now()) END,interval '1 second' * 5 ^ faultcount FROM gsmchannels where channel=" . $channel);
  $getdata=pg_fetch_array($qgetdata,0);

  $secs=$getdata[0] % 60;
  $mins=($getdata[0]-$secs) / 60;
  if ($secs < 10) {
    $secs="0" . $secs;
  }
%>
  <TR CLASS=list-color1>
  <TD onmouseover="myHint.show('GR3')" onmouseout="myHint.hide()"><%print _("Calltime (Minutes:Seconds)");%></TD>
  <TD><INPUT TYPE=TEXT SIZE=8 NAME=nminutes VALUE="<%print $mins;%>">:
      <INPUT TYPE=TEXT SIZE=8 NAME=nseconds VALUE="<%print $secs;%>"></TD>

  </TR>

  <TR CLASS=list-color2>
  <TD onmouseover="myHint.show('GR4')" onmouseout="myHint.hide()"><%print _("Start Time [hh:mm:ss]");%></TD>
  <TD><INPUT TYPE=TEXT SIZE=12 NAME=nstarttime VALUE="<%print $getdata[4];%>">
  </TR>
  <TR CLASS=list-color1>
  <TD onmouseover="myHint.show('GR5')" onmouseout="myHint.hide()"><%print _("End Time [hh:mm:ss]");%></TD>
  <TD><INPUT TYPE=TEXT SIZE=12 NAME=nendtime VALUE="<%print $getdata[5];%>">
  </TR>
  <TR CLASS=list-color2>
  <TD onmouseover="myHint.show('GR6')" onmouseout="myHint.hide()"><%print _("Number Match");%></TD>
  <TD><INPUT TYPE=TEXT SIZE=12 NAME=nregex VALUE="<%print $getdata[6];%>">
  </TR>

  <TR CLASS=list-color1>
  <TD onmouseover="myHint.show('GR7')" onmouseout="myHint.hide()"><%print _("Expires");%></TD>
  <TD><SELECT NAME=eyear>
<%
  list($year, $month, $day) = sscanf($getdata[1],"%d-%d-%d");
  for($ey=$year;$ey < $year+3;$ey++) {
    print "<OPTION VALUE=$ey";
    if ($ey == $year) {
      print " SELECTED";
    }
    print ">$ey</OPTION>\n";
  }
%>
  </SELECT>-<SELECT NAME=emonth>
<%
  for($em=1;$em < 12;$em++) {
    print "<OPTION VALUE=$em";
    if ($em == $month) {
      print " SELECTED";
    }
    print ">$em</OPTION>\n";
  }
%>
  </SELECT>-<SELECT NAME=eday>
<%
  for($ed=1;$ed < 31;$ed++) {
    print "<OPTION VALUE=$ed";
    if ($ed == $day) {
      print " SELECTED";
    }
    print ">$ed</OPTION>\n";
  }
%>
  </SELECT>
  </TD>
  <TR CLASS=list-color2>
  <TD onmouseover="myHint.show('GR8')" onmouseout="myHint.hide()"><%print _("Channel Inuse");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=inuse<%if ($getdata[2] == 't') {print " CHECKED";}%>></TD>
  </TR>
  <TR CLASS=list-color1>
  <TD onmouseover="myHint.show('GR9')" onmouseout="myHint.hide()"><%print _("Channel Out Of Service");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=outofservice<%if ($getdata[3] == 't') {print " CHECKED";}%>>
  <%if ($getdata[3] == 't') {print " Unavailable For ";} else {print " Available For ";}%>
  <%print $getdata[8];%>
  </TD>
  </TR>
  <TR CLASS=list-color2>
  <TD onmouseover="myHint.show('GR10')" onmouseout="myHint.hide()"><%print _("Fault Count Penalty")%></TD>
  <TD><%print $getdata[7] . " Penalty ";if ($getdata[7] > 0) {print $getdata[9];} else {print "0";}%></TD>
  </TR>
  <INPUT TYPE=HIDDEN NAME=router VALUE="<%print $router;%>">
  <INPUT TYPE=HIDDEN NAME=channel VALUE="<%print $channel;%>">
  <TR CLASS=list-color1>
    <TD ALIGN=MIDDLE COLSPAN=2>
      <INPUT TYPE=RESET>
      <INPUT TYPE=SUBMIT onclick=this.name='upchan' VALUE="<%print _("Modify");%>">
    </TD><%
} else if ($router != "") {
  $qgetdata=pg_query($db,"SELECT channel,calltime,date(expires),inuse,outofservice from gsmchannels order by channel");
%>
  <TD onmouseover="myHint.show('GR1')" onmouseout="myHint.hide()"><%print _("Select Channel");%></TD>
  <TD><SELECT NAME=channel>
    <OPTION VALUE=""><%print _("Add New Channel");%></OPTION>
  <%
  $dnum=pg_num_rows($qgetdata);
  for($i=0;$i<$dnum;$i++){
    $getdata=pg_fetch_array($qgetdata,$i);
    $secs=$getdata[1] % 60;
    $mins=($getdata[1]-$secs) / 60;
    if ($secs < 10) {
      $secs="0" . $secs;
    }
    print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[0] . " - " . $mins . ":" .$secs . " (Expires " . $getdata[2];
    if ($getdata[3] == "t") {
      print " INUSE";
    }
    if ($getdata[4] == "t") {
      print " DEACTIVE";
    }
    print ")</OPTION>"; 
  }
  %>
  </SELECT> 
  <INPUT TYPE=HIDDEN NAME=company VALUE="<%print $key;%>">
  </TR>

  <TR CLASS=list-color2>
  <TD onmouseover="myHint.show('GR2')" onmouseout="myHint.hide()"><%print _("New Channel");%></TD>
  <TD><INPUT TYPE=TEXT SIZE=4 NAME=nchannel></TD>
  </TR>
  <TR CLASS=list-color1>
  <TD onmouseover="myHint.show('GR3')" onmouseout="myHint.hide()"><%print _("Call Time (Minutes:Seconds)");%></TD>
  <TD><INPUT TYPE=TEXT SIZE=8 NAME=nminutes>:<INPUT TYPE=TEXT SIZE=8 NAME=nseconds></TD>
  </TR>
  <TR CLASS=list-color2>
  <TD onmouseover="myHint.show('GR4')" onmouseout="myHint.hide()"><%print _("Start Time [hh:mm:ss]");%></TD>
  <TD><INPUT TYPE=TEXT SIZE=8 NAME=nstarttime VALUE="00:00:00">
  </TR>
  <TR CLASS=list-color1>
  <TD onmouseover="myHint.show('GR5')" onmouseout="myHint.hide()"><%print _("End Time [hh:mm:ss]");%></TD>
  <TD><INPUT TYPE=TEXT SIZE=8 NAME=nendtime VALUE="00:00:00">
  </TR>
  <TR CLASS=list-color2>
  <TD onmouseover="myHint.show('GR6')" onmouseout="myHint.hide()"><%print _("Number Match");%></TD>
  <TD><INPUT TYPE=TEXT SIZE=8 NAME=nregex>
  </TR>
  <TR CLASS=list-color1>
  <TD onmouseover="myHint.show('GR7')" onmouseout="myHint.hide()"><%print _("Expires");%></TD>
  <TD><SELECT NAME=eyear>
<%
  $today=getdate();
  $year=$today['year'];
  $month=$today['mon'];
  $day=$today['mday'];
  for($ey=$year;$ey < $year+3;$ey++) {
    print "<OPTION VALUE=$ey>$ey</OPTION>\n";
  }
%>
  </SELECT>-<SELECT NAME=emonth>
<%
  for($em=1;$em < 12;$em++) {
    print "<OPTION VALUE=$em";
    if ($em == $month+1) {
      print " SELECTED";
    }
    print ">$em</OPTION>\n";
  }
%>
  </SELECT>-<SELECT NAME=eday>
<%
  for($ed=1;$ed < 31;$ed++) {
    print "<OPTION VALUE=$ed";
    if ($ed == $day) {
      print " SELECTED";
    }
    print ">$ed</OPTION>\n";
  }
%>
  </SELECT>
  <INPUT TYPE=HIDDEN NAME=router VALUE="<%print $router;%>">
  </TD>
  <TR CLASS=list-color2>
    <TD ALIGN=MIDDLE COLSPAN=2>
      <INPUT TYPE=RESET>
      <INPUT TYPE=SUBMIT onclick=this.name='modchan' VALUE="<%print _("Modify");%>">
      <INPUT TYPE=SUBMIT onclick=this.name='delchan' VALUE="<%print _("Delete");%>">
    </TD>
<%
  } else {
  $rtrdata=pg_query($db," SELECT trunk.h323gkid,description from trunk LEFT OUTER JOIN users ON (trunk.h323gkid=name) where trunk.h323prefix='*' or users.h323neighbor='t' order by description");
%>
  <TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('GR0')" onmouseout="myHint.hide()"><%print _("Select Router");%></TD>
  <TD><SELECT NAME=router>
  <%
  $dnum=pg_num_rows($rtrdata);
  for($i=0;$i<$dnum;$i++){
    $getdata=pg_fetch_array($rtrdata,$i);
    print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[1] . "</OPTION>"; 
  }
  %>
  </SELECT> 
  <TR CLASS=list-color2>
  <TD COLSPAN=2 ALIGN=MIDDLE><INPUT TYPE=SUBMIT VALUE="<%print _("Edit Router");%>"></TD>
  </TR>
<%
  }
%>
</TR>
</TABLE>
</FORM>
