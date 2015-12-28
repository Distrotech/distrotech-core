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

$qdef=pg_query($db,"SELECT key,value FROM astdb WHERE family='Setup' AND (key='QTimeout' OR key='QAPenalty'");

for ($dcnt=0;$dcnt < pg_num_rows($qdef);$dcnt++) {
  $r=pg_fetch_array($qtout,0,PSQL_NUM);
  $defval[$r[0]]=$r[1];
}

if (($defval['QTimeout'] == "") || ($defval['QTimeout'] <= 0)) {
  $defval['QTimeout']=600;
}

if (($defval['QAPenalty'] == "") || ($defval['QAPenalty'] <= 0)) {
  $defval['QAPenalty']=20;
}

if ((isset($pbxupdate)) && (isset($update))) {
  if ($_POST['qtimeout'] == "") {
    $_POST['qtimeout']=$defval['QTimeout'];
  }
  if ($_POST['penalty'] == "") {
    $_POST['penalty']=$defval['QAPenalty'];
  }

  if ($announce_holdtime == "on") {
    $announce_holdtime="yes";
  } else {
    $announce_holdtime="no";
  }

  if ($_POST['dopts'] == "on") {
    $_POST['dopts']="t";
  } else {
    $_POST['dopts']="tr";
  }

  if ($_POST['novmail'] == "on") {
    $_POST['novmail']="1";
  } else {
    $_POST['novmail']="0";
  }

  if ($_POST['record'] == "on") {
    $_POST['record']="1";
  } else {
    $_POST['record']="0";
  }

  if ($_POST['ohonly'] == "on") {
    $_POST['ohonly']="1";
  } else {
    $_POST['ohonly']="0";
  }

  if ($_POST['vmfwd'] == "") {
    $_POST['vmfwd']="NONE";
  }

  if (($newbgroup != "") && ($BGRP == "") && ($SUPER_USER == 1)) {
    $BGRP=$newbgroup;
  }

  pg_query($db,"UPDATE qfeatures SET penalty='" . $_POST['penalty'] . "',dopts='" . $_POST['dopts'] . "',novmail='" . $_POST['novmail'] . "',
                                     ohonly='" . $_POST['ohonly'] . "',rdelay='" . $_POST['rdelay'] . "',record='" . $_POST['record'] . "',
                                     timeout='" . $_POST['qtimeout'] . "',vmfwd='" . $_POST['vmfwd'] . "' 
                                 WHERE queue='" . $queue . "'");
  $ud=pg_query($db,"UPDATE astdb SET value='" . $BGRP . "' WHERE family='Q" . $queue . "' AND key='BGRP'");
  if (pg_affected_rows($ud) <= 0) {
    pg_query("INSERT INTO astdb (family,key,value) VALUES ('Q" . $queue . "','BGRP','')");
  }

  pg_query($db,"UPDATE voicemail SET fullname='$description',email='$email' WHERE mailbox='$queue'");
  pg_query($db,"UPDATE queue_table SET strategy='$strategy',timeout='$timeout',monitor_format='',
                                       description='$description',wrapuptime='$wrapuptime',
                                       memberdelay='$memberdelay',servicelevel='$servicelevel',
                                       weight='$weight',maxlen='$maxlen',retry='$retry',
                                       announce_frequency='$announce_frequency',announce_holdtime='$announce_holdtime', 
                                       announce_round_seconds='$announce_round_seconds',autopausedelay='$autopausedelay'
                                   WHERE name='$queue'");

  if (($pass1 == $pass2) && ($pass1 != "")){
    pg_query($db,"UPDATE voicemail SET password='$pass1' WHERE mailbox='$queue'");
  } else if ($pass1 != "") {
?>
    <SCRIPT>
      alert("Password Mismach/Unset.Password Unchanged");
    </SCRIPT>
<?php
  }
}

$qgetudata=pg_query($db,"SELECT email,password FROM voicemail WHERE mailbox='" . $queue . "'");
$qgetqdata=pg_query($db,"SELECT strategy,timeout,description,wrapuptime,
                                memberdelay,retry,servicelevel,weight,maxlen,
                                autopausedelay,announce_round_seconds,
                                announce_frequency,announce_holdtime
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


$qgetdata=pg_query($db,"SELECT * FROM qfeatures WHERE queue='" . $queue . "'");
if (pg_num_rows($qgetdata) > 0) {
  $origdata=pg_fetch_array($qgetdata,0,PGSQL_ASSOC);
} else {
  pg_query($db,"INSERT INTO qfeatures (queue) VALUES ('" . $queue . "')");
}

$bgrpq=pg_query($db,"SELECT value FROM astdb WHERE key='BGRP' AND family='Q" . $queue . "'");
list($origdata['BGRP'])=pg_fetch_array($bgrpq,0,PGSQL_NUM);

if ($origdata["timeout"] == "") {
  $origdata["timeout"]=$defval['QTimeout'];
}
if ($origdata["penalty"] == "") {
  $origdata["penalty"]=$defval['QAPenalty'];
}

if ($origdata["vmfwd"] == "NONE") {
  $origdata["vmfwd"]="";
}

?>

<INPUT TYPE=HIDDEN NAME=queue VALUE=<?php print $queue;?>>
  <TH CLASS=heading-body COLSPAN=2><?php print _("Configuration For Queue");?> <?php print $queue?></TH>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS2')" onmouseout="myHint.hide()"><?php print _("Call Routing Scheme");?></TD>
  <TD>
    <SELECT NAME=strategy>
      <OPTION VALUE="ringall"><?php print _("Ring All Agents");?></OPTION>
      <OPTION VALUE="rrmemory"<?php if ($strategy == "rrmemory") {print " SELECTED";}?>><?php print _("Round Robin");?></OPTION>
      <OPTION VALUE="random"<?php if ($strategy == "random") {print " SELECTED";}?>><?php print _("Random");?></OPTION>
      <OPTION VALUE="leastrecent"<?php if ($strategy == "leastrecent") {print " SELECTED";}?>><?php print _("Least Recent Call");?></OPTION>
      <OPTION VALUE="fewestcalls"<?php if ($strategy == "fewestcalls") {print " SELECTED";}?>><?php print _("Fewest Calls");?></OPTION>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('QS3')" onmouseout="myHint.hide()"><?php print _("Queue Description");?></TD>
  <TD><INPUT TYPE=TEXT NAME=description VALUE="<?php print $description;?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS4')" onmouseout="myHint.hide()"><?php print _("Email Address To Send Voice Mail");?></TD>
  <TD><INPUT TYPE=TEXT NAME=email VALUE="<?php print $email;?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover="myHint.show('QS5')" onmouseout="myHint.hide()"><?php print _("Maximum Number Of Waiting Calls");?></TD>
  <TD><INPUT TYPE=TEXT NAME=maxlen VALUE="<?php print $maxlen;?>"></TD></TR>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover="myHint.show('QS6')" onmouseout="myHint.hide()"><?php print _("Queue Weight Factor");?></TD>
  <TD><INPUT TYPE=TEXT NAME=weight VALUE="<?php print $weight;?>"></TD></TR>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover="myHint.show('QS7')" onmouseout="myHint.hide()"><?php print _("Service Level Time Frame");?></TD>
  <TD><INPUT TYPE=TEXT NAME=servicelevel VALUE="<?php print $servicelevel;?>"></TD></TR>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS8')" onmouseout="myHint.hide()"><?php print _("Queue Time Out");?></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=qtimeout VALUE="<?php if ($origdata["timeout"] != "0") {print $origdata["timeout"];}?>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('QS9')" onmouseout="myHint.hide()"><?php print _("Queue Default Agent Penalty Factor");?></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=penalty VALUE="<?php if ($origdata["penalty"] != "0") {print $origdata["penalty"];}?>">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS10')" onmouseout="myHint.hide()"><?php print _("Queue Agent Time Out");?></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=timeout VALUE="<?php print $timeout;?>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover="myHint.show('QS11')" onmouseout="myHint.hide()"><?php print _("Retry Delay");?></TD>
  <TD><INPUT TYPE=TEXT NAME=retry VALUE="<?php print $retry;?>"></TD></TR>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover="myHint.show('QS12')" onmouseout="myHint.hide()"><?php print _("Agent Answer Delay");?></TD>
  <TD><INPUT TYPE=TEXT NAME=memberdelay VALUE="<?php print $memberdelay;?>"></TD></TR>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover="myHint.show('QS13')" onmouseout="myHint.hide()"><?php print _("Agent Wrap Up Time");?></TD>
  <TD><INPUT TYPE=TEXT NAME=wrapuptime VALUE="<?php print $wrapuptime;?>"></TD></TR>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS14')" onmouseout="myHint.hide()"><?php print _("Announcement Frequency");?></TD>
  <TD><INPUT TYPE=TEXT NAME=announce_frequency VALUE="<?php print $announce_frequency;?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('QS15')" onmouseout="myHint.hide()"><?php print _("Announcement Holdtime Round To ...s");?></TD>
  <TD><INPUT TYPE=TEXT NAME=announce_round_seconds VALUE="<?php print $announce_round_seconds;?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS16')" onmouseout="myHint.hide()"><?php print _("Autologoff Idle Time");?></TD>
  <TD><INPUT TYPE=TEXT NAME=autopausedelay VALUE="<?php print $autopausedelay;?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('QS17')" onmouseout="myHint.hide()"><?php print _("Ring Delay Before Answering");?></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=rdelay VALUE="<?php print $origdata["rdelay"];?>">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS20')" onmouseout="myHint.hide()"><?php print _("Billing Group");?></TD>
  <TD>
    <SELECT NAME=BGRP>
<?php
      $bgrpq="SELECT DISTINCT value FROM astdb AS bgrp WHERE key='BGRP' AND value != '' ";
      if ($SUPER_USER != 1) {
        $bgrpq.=" AND " . $clogacl;      
      } else {
?>
        <OPTION VALUE=""><?php print _("Select Existing Group/Add New Group Bellow");?></OPTION>
<?php
      }
      $bgrpq.=" ORDER BY value;";
      $bgroups=pg_query($db,$bgrpq);
      $bgnum=pg_num_rows($bgroups);

      for($i=0;$i<$bgnum;$i++){
        $getbgdata=pg_fetch_array($bgroups,$i);
        print "<OPTION VALUE=\"" . $getbgdata[0] . "\"";
        if ($getbgdata[0] == $origdata["BGRP"]) {
          print " SELECTED";
        }
        print ">" . $getbgdata[0] . "</OPTION>\n";
      }
?>
    </SELECT><BR>
<?php
  if ($SUPER_USER == 1) {
?>
    <INPUT TYPE=TEXT NAME=newbgroup>
<?php
  }
?>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('QS20')" onmouseout="myHint.hide()"><?php print _("Voicemail/Call Forward On Timeout/No Agent");?></TD>
  <TD><INPUT TYPE=TEXT NAME=vmfwd VALUE="<?php print $origdata["vmfwd"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS20')" onmouseout="myHint.hide()"><?php print _("Voicemail Password");?> </TD>
  <TD><INPUT TYPE=PASSWORD NAME=pass1 VALUE="<?php print $password;?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('QS21')" onmouseout="myHint.hide()"><?php print _("Confirm Password");?></TD>
  <TD><INPUT TYPE=PASSWORD NAME=pass2 VALUE="<?php print $password;?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS18')" onmouseout="myHint.hide()"><?php print _("Record Queue");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=record<?php if ($origdata["record"] == "1" ) {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('QS18')" onmouseout="myHint.hide()"><?php print _("Announce Expected Hold Time");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=announce_holdtime<?php if ($announce_holdtime == "yes" ) {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS19')" onmouseout="myHint.hide()"><?php print _("Play Music On Hold (Alternative Is Ringing)");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=dopts<?php if ($origdata["dopts"] == "t" ) {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('QS19')" onmouseout="myHint.hide()"><?php print _("Disable Voicemail");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=novmail<?php if ($origdata["novmail"] == "1" ) {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('QS19')" onmouseout="myHint.hide()"><?php print _("Office Hours Only");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=ohonly<?php if ($origdata["ohonly"] == "1" ) {print " CHECKED";}?>></TD>
</TR>
<INPUT TYPE=HIDDEN NAME=update VALUE=seen>
<TR CLASS=list-color2>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=SUBMIT onclick=this.name='delext' VALUE="<?php print _("Delete");?>">
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxupdate' VALUE="<?php print _("Configure");?>">
  </TD>
</TR>
</TABLE>
</FORM>
