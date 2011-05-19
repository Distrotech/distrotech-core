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

$qtout=pg_query($db,"SELECT value FROM astdb WHERE family='Setup' AND key='QTimeout'");
if (pg_num_rows($qtout) > 0) {
  list($dqtimeout)=pg_fetch_array($qtout,0);
} else {
  $dqtimeout=0;
}

$qapen=pg_query($db,"SELECT value FROM astdb WHERE family='Setup' AND key='QAPenalty'");
if (pg_num_rows($qapen) > 0) {
  list($dqapenalty)=pg_fetch_array($qapen,0);
} else {
  $dqapenalty=0;
}

if (isset($pbxupdate)) {
  if ($QTIMEOUT == "") {
    $QTIMEOUT=$dqtimeout;
  }
  if ($QAPENALTY == "") {
    $QAPENALTY=$dqapenalty;
  }

  if ($announce_holdtime == "on") {
    $announce_holdtime="yes";
  } else {
    $announce_holdtime="no";
  }

  if ($playmusiconhold == "on") {
    $playmusiconhold="t";
  } else {
    $playmusiconhold="f";
  }

  pg_query($db,"UPDATE astdb SET value='" . $QTIMEOUT . "' WHERE family='Q" . $queue . "' AND key='QTIMEOUT'");
  pg_query($db,"UPDATE astdb SET value='" . $QAPENALTY . "' WHERE family='Q" . $queue . "' AND key='QAPENALTY'");

  pg_query($db,"UPDATE users SET fullname='$description',email='$email' WHERE mailbox='$queue'");
  pg_query($db,"UPDATE queue_table SET strategy='$strategy',timeout='$timeout',
                                       description='$description',wrapuptime='$wrapuptime',
                                       memberdelay='$memberdelay',servicelevel='$servicelevel',
                                       weight='$weight',maxlen='$maxlen',retry='$retry',
                                       announce_frequency='$announce_frequency',announce_holdtime='$announce_holdtime', 
                                       announce_round_seconds='$announce_round_seconds',autopausedelay='$autopausedelay',
                                       playmusiconhold='$playmusiconhold'
                                   WHERE name='$queue'");

                      
  if (($pass1 == $pass2) && ($pass1 != "")){
    pg_query($db,"UPDATE users SET password='$pass1' WHERE mailbox='$queue'");
  } else if ($pass1 != "") {
%>
    <SCRIPT>
      alert("Password Mismach/Unset.Password Unchanged");
    </SCRIPT>
<%
  }
}

$qgetdata=pg_query($db,"SELECT key,value FROM astdb WHERE family='Q" . $queue . "'");
$qgetudata=pg_query($db,"SELECT email,password FROM users WHERE mailbox='" . $queue . "'");
$qgetqdata=pg_query($db,"SELECT strategy,timeout,description,wrapuptime,
                                memberdelay,retry,servicelevel,weight,maxlen,
                                autopausedelay,announce_round_seconds,
                                announce_frequency,announce_holdtime,playmusiconhold
                              FROM queue_table WHERE name='" . $queue . "'");

$udata=pg_fetch_array($qgetudata,0);

$email=$udata[0];
$password=$udata[1];

$qdata=pg_fetch_array($qgetqdata,0);

$strategy=$qdata[0];
$timeout=$qdata[1];
$description=$qdata[2];
$wrapuptime=$qdata[3];
$memberdelay=$qdata[4];
$retry=$qdata[5];
$servicelevel=$qdata[6];
$weight=$qdata[7];
$maxlen=$qdata[8];
$autopausedelay=$qdata[9];
$announce_round_seconds=$qdata[10];
$announce_frequency=$qdata[11];
$announce_holdtime=$qdata[12];
$playmusiconhold=$qdata[13];


$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  $origdata[$getdata[0]]=$getdata[1]; 
}

if ($origdata["QTIMEOUT"] == "") {
  $origdata["QTIMEOUT"]=$dqtimeout;
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('Q" . $queue . "','QTIMEOUT','$dqtimeout')");
}
if ($origdata["QAPENALTY"] == "") {
  $origdata["QAPENALTY"]=$dqapenalty;
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('Q" . $queue . "','QAPENALTY','$dqapenalty')");
}

%>

<INPUT TYPE=HIDDEN NAME=queue VALUE=<%print $queue;%>>
  <TH CLASS=heading-body COLSPAN=2>Configuration For Queue <%print $queue%></TH>
</TR>
<TR CLASS=list-color1>
  <TD>Call Routing Scheme</TD>
  <TD>
    <SELECT NAME=strategy>
      <OPTION VALUE="ringall">Ring All Agents</OPTION>
      <OPTION VALUE="rrmemory"<%if ($strategy == "rrmemory") {print " SELECTED";}%>>Round Robin</OPTION>
      <OPTION VALUE="random"<%if ($strategy == "random") {print " SELECTED";}%>>Random</OPTION>
      <OPTION VALUE="leastrecent"<%if ($strategy == "leastrecent") {print " SELECTED";}%>>Least Recent Call</OPTION>
      <OPTION VALUE="fewestcalls"<%if ($strategy == "fewestcalls") {print " SELECTED";}%>>Fewest Calls</OPTION>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD>Queue Description</TD>
  <TD><INPUT TYPE=TEXT NAME=description VALUE="<%print $description;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD>Email Address To Send Voice Mail</TD>
  <TD><INPUT TYPE=TEXT NAME=email VALUE="<%print $email;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT>Maximum Number Of Waiting Calls</TD>
  <TD><INPUT TYPE=TEXT NAME=maxlen VALUE="<%print $maxlen;%>"></TD></TR>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT>Queue Weight Factor</TD>
  <TD><INPUT TYPE=TEXT NAME=weight VALUE="<%print $weight;%>"></TD></TR>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT>Service Level Time Frame</TD>
  <TD><INPUT TYPE=TEXT NAME=servicelevel VALUE="<%print $servicelevel;%>"></TD></TR>
</TR>
<TR CLASS=list-color1>
  <TD>Queue Time Out</TD>
  <TD>
     <INPUT TYPE=TEXT NAME=QTIMEOUT VALUE="<%if ($origdata["QTIMEOUT"] != "0") {print $origdata["QTIMEOUT"];}%>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD>Queue Default Agent Penalty Factor</TD>
  <TD>
     <INPUT TYPE=TEXT NAME=QAPENALTY VALUE="<%if ($origdata["QAPENALTY"] != "0") {print $origdata["QAPENALTY"];}%>">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD>Queue Agent Time Out</TD>
  <TD>
     <INPUT TYPE=TEXT NAME=timeout VALUE="<%print $timeout;%>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT>Retry Delay</TD>
  <TD><INPUT TYPE=TEXT NAME=retry VALUE="<%print $retry;%>"></TD></TR>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT>Agent Answer Delay</TD>
  <TD><INPUT TYPE=TEXT NAME=memberdelay VALUE="<%print $memberdelay;%>"></TD></TR>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT>Agent Wrap Up Time</TD>
  <TD><INPUT TYPE=TEXT NAME=wrapuptime VALUE="<%print $wrapuptime;%>"></TD></TR>
</TR>
<TR CLASS=list-color1>
  <TD>Announcement Frequency</TD>
  <TD><INPUT TYPE=TEXT NAME=announce_frequency VALUE="<%print $announce_frequency;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD>Announcement Holdtime Round To ...s</TD>
  <TD><INPUT TYPE=TEXT NAME=announce_round_seconds VALUE="<%print $announce_round_seconds;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD>Autologoff Idle Time</TD>
  <TD><INPUT TYPE=TEXT NAME=autopausedelay VALUE="<%print $autopausedelay;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD>Announce Expected Hold Time</TD>
  <TD><INPUT TYPE=CHECKBOX NAME=announce_holdtime<%if ($announce_holdtime == "yes" ) {print " CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color1>
  <TD>Play Music On Hold (Alternative Is Ringing)</TD>
  <TD><INPUT TYPE=CHECKBOX NAME=playmusiconhold<%if ($playmusiconhold == "t" ) {print " CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color2>
  <TD>Voicemail Password</TD>
  <TD><INPUT TYPE=PASSWORD NAME=pass1 VALUE="<%print $password;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD>Confirm Password</TD>
  <TD><INPUT TYPE=PASSWORD NAME=pass2 VALUE="<%print $password;%>"></TD>
</TR>

<TR CLASS=list-color2>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT NAME=pbxupdate VALUE="Configure">
  </TD>
</TR>
</TABLE>
</FORM>
