<?php
if (! $db) {
  include "auth.inc";
}
$new=0;

if ((isset($mac)) && ($submitd == "Modify/Add")) {
  list($mac,$ipaddr)=explode(":",$mac);
}

if (($submitd == "Delete") && ($mac != "")) {
  pg_query($db,"DELETE FROM astdb WHERE family = '" . $mac . "'");
  $mac="";
  unset($submitd);
} else if (($edelete != "")) {
  pg_query($db,"DELETE FROM astdb WHERE family = '" . $edelete  . "' AND key='SNOMMAC'");
}

if ($mac != "") {
  $mac=strtoupper($mac);
  if ($submitd == "Update") {
    pg_query($db,"UPDATE astdb SET value='" . $pserver . "' WHERE family='" . $mac . "' AND key='PROFILE'");
    pg_query($db,"UPDATE astdb SET value='" . $stunsrv . "' WHERE family='" . $mac . "' AND key='STUNSRV'");
    pg_query($db,"UPDATE astdb SET value='" . $ldescrip . "' WHERE family='" . $mac . "' AND key='LINKSYS'");
    pg_query($db,"UPDATE astdb SET value='" . $rxgain . "' WHERE family='" . $mac . "' AND key='LSYSRXGAIN'");
    pg_query($db,"UPDATE astdb SET value='" . $txgain . "' WHERE family='" . $mac . "' AND key='LSYSTXGAIN'");
    pg_query($db,"UPDATE astdb SET value='" . $vlanid . "' WHERE family='" . $mac . "' AND key='VLAN'");
    if ($exten != "") {
      $newex=pg_query($db,"INSERT INTO astdb (value,key,family) VALUES ('" . $mac . "','SNOMMAC','" . $exten . "')");
      if (! $newex) {
        pg_query($db,"UPDATE astdb SET value='" . $mac . "' WHERE family='" . $exten . "' AND key='SNOMMAC'");
      }
      pg_query($db,"UPDATE users SET dtmfmode='info' WHERE name='" . $exten . "'");
    }
  }
  $lsysconf="SELECT stun.value,pserv.value,descrip.value,rxgain.value,txgain.value,vlanid.value FROM astdb AS stun LEFT OUTER JOIN astdb AS pserv ON (pserv.family=stun.family AND pserv.key='PROFILE') LEFT OUTER JOIN astdb AS vlanid ON (vlanid.family=stun.family AND vlanid.key='VLAN') LEFT OUTER JOIN astdb AS descrip ON (descrip.family=stun.family AND descrip.key='LINKSYS') LEFT OUTER JOIN astdb AS rxgain ON (rxgain.family=stun.family AND rxgain.key='LSYSRXGAIN') LEFT OUTER JOIN astdb AS txgain ON (txgain.family=stun.family AND txgain.key='LSYSTXGAIN') WHERE stun.key='STUNSRV' AND stun.family='" . $mac . "'";
  if ($rxgain == "") {
    pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $mac . "','LSYSRXGAIN','-3')");
    $rxgain="-3";
  }
  if ($txgain == "") {
    pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $mac . "','LSYSTXGAIN','-3')");
    $txgain="-3";
  }
  if ($vlanid == "") {
    pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $mac . "','VLAN','1')");
    $vlanid="1";
  }

  $lsyscnf=pg_query($db,$lsysconf);
  if (pg_num_rows($lsyscnf) > 0) {
    list($stunsrv,$pserver,$ldescrip,$rxgain,$txgain,$vlanid)=pg_fetch_array($lsyscnf,0);
    $new=-1;
  } else {
    $new=1;
  }
}

if (($device != "") && ($mac != "") && ($submitd == "Save") && ($new == "1")) {
  if ($pserver == ""){ 
    $pserver=$SERVER_NAME;
  }
  pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $mac . "','PROFILE','" . $pserver . "')");
  pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $mac . "','STUNSRV','" . $stunsrv . "')");
  pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $mac . "','LINKSYS','" . $ldescrip . "')");
  pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $mac . "','LSYSRXGAIN','" . $rxgain . "')");
  pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $mac . "','LSYSTXGAIN','" . $txgain . "')");
  pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $mac . "','VLAN','" . $vlanid . "')");
?>
<script>
  atapopupwin('<?php print $device;?>','<?php print $pserver;?>');
</script>
<?php 
}

if (($mac == "") && ($submitd != "Save") && (isset($submitd))) { ?>
<CENTER>
<FORM METHOD=POST>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>Linksys ATA Configuration Wizard</TH></TR>
<TR CLASS=list-color1><TD onmouseover="myHint.show('LA1')" onmouseout="myHint.hide()">Device Description</TD><TD><INPUT NAME=ldescrip VALUE=""></TD></TR>
<TR CLASS=list-color2><TD onmouseover="myHint.show('LA2')" onmouseout="myHint.hide()">Device IP (IVR Option 110)</TD><TD><INPUT NAME=device VALUE=""></TD></TR>
<TR CLASS=list-color1><TD onmouseover="myHint.show('LA3')" onmouseout="myHint.hide()">Device MAC Addr (IVR Option 140)</TD><TD><INPUT NAME=mac VALUE=""></TD></TR>
<TR CLASS=list-color2><TD onmouseover="myHint.show('LA4')" onmouseout="myHint.hide()">STUN Server (Optional)</TD><TD><INPUT NAME=stunsrv VALUE=""></TD></TR>
<TR CLASS=list-color1><TD onmouseover="myHint.show('LA5')" onmouseout="myHint.hide()">Settings Server</TD><TD><INPUT NAME=pserver VALUE="<?php print $SERVER_NAME;?>"></TD></TR>

<TR CLASS=list-color2><TD onmouseover="myHint.show('LA6')" onmouseout="myHint.hide()">RX Gain</TD><TD><INPUT NAME=rxgain VALUE="-3"></TD></TR>
<TR CLASS=list-color1><TD onmouseover="myHint.show('LA7')" onmouseout="myHint.hide()">TX Gain</TD><TD><INPUT NAME=txgain VALUE="-3"></TD></TR>
<TR CLASS=list-color2><TD onmouseover="myHint.show('LA8')" onmouseout="myHint.hide()">VLAN Id</TD><TD><INPUT NAME=vlanid VALUE="1"></TD></TR>


<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=MIDDLE><INPUT TYPE=SUBMIT NAME=submitd VALUE="Save"></TD></TR>
</FORM>
</TABLE>
<?php } else if (($mac != "") && ($new == "-1")) {?>
<CENTER>
<FORM METHOD=POST NAME=atapush>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<INPUT TYPE=HIDDEN NAME=mac VALUE="<?php print $mac;?>">
<INPUT TYPE=HIDDEN NAME=ipaddr VALUE="<?php print $ipaddr;?>">
<INPUT TYPE=HIDDEN NAME=edelete>
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>Editing Linksys ATA (<?php print $mac;?>)</TH></TR><TR CLASS=list-color1><TD onmouseover="myHint.show('LA1')" onmouseout="myHint.hide()"
>Device Description</TD><TD><INPUT NAME=ldescrip VALUE="<?php print $ldescrip;?>"></TD></TR>
<TR CLASS=list-color2><TD onmouseover="myHint.show('LA4')" onmouseout="myHint.hide()">STUN Server (Optional)</TD><TD><INPUT NAME=stunsrv VALUE="<?php print $stunsrv;?>"></TD></TR>
<TR CLASS=list-color1><TD onmouseover="myHint.show('LA5')" onmouseout="myHint.hide()">Settings Server</TD><TD><INPUT NAME=pserver VALUE="<?php print $pserver;?>"></TD></TR>

<TR CLASS=list-color2><TD onmouseover="myHint.show('LA6')" onmouseout="myHint.hide()">RX Gain</TD><TD><INPUT NAME=rxgain VALUE="<?php print $rxgain;?>"></TD></TR>
<TR CLASS=list-color1><TD onmouseover="myHint.show('LA7')" onmouseout="myHint.hide()">TX Gain</TD><TD><INPUT NAME=txgain VALUE="<?php print $txgain;?>"></TD></TR>
<TR CLASS=list-color2><TD onmouseover="myHint.show('LA8')" onmouseout="myHint.hide()">VLAN Id</TD><TD><INPUT NAME=vlanid VALUE="<?php print $vlanid;?>"></TD></TR>

<TR CLASS=list-color1><TH COLSPAN=2 CLASS=heading-body2>Configured Extensions</TH></TR>
<?php
  $getexten=pg_query($db,"SELECT fullname,family from astdb left outer join users on (family=name) where key='SNOMMAC' and value='" . $mac . "' ORDER BY family");
  $bcol[0]="<TR CLASS=list-color2>";
  $bcol[1]="<TR CLASS=list-color1>";

  print $bcol[0];
  $rcnt=0;
  for($ecnt=0;$ecnt < pg_num_rows($getexten);$ecnt++) {
    list($user,$exten)=pg_fetch_array($getexten,$ecnt);
    $cexten=htmlentities($user . " - " . $exten);
?>
    <TD><A HREF="javascript:delexten('<?php print $cexten;?>','<?php print $exten;?>')"><B><?php print $cexten;?></B></A></TD>
<?php
    if ($ecnt % 2) {
      $rcnt++;
      print "</TR>" . $bcol[$rcnt % 2] . "\n";
    }
   }
  if ($ecnt % 2) {
    $rcnt++;
    print "<TD>&nbsp;</TD>" . $bcol[$rcnt % 2];
  }
?>
<TD onmouseover="myHint.show('LA7')" onmouseout="myHint.hide()">Select Extension To Link</TD><TD><SELECT NAME=exten>
<OPTION VALUE="">Do Not Add Extension</OPTION>
<?php
  $gdatq="SELECT name,fullname||' ('||name||')' from users left outer join astdb as macaddr on (macaddr.family=name AND macaddr.key='SNOMMAC') 
                 left outer join astdb as lpre on (substr(name,0,3)=lpre.key AND lpre.family='LocalPrefix' and lpre.value='1') 
                where lpre.value='1' AND (macaddr.value='' or macaddr.value is null)";
  $getata=pg_query($db,$gdatq);
  for($atacnt=0;$atacnt < pg_num_rows($getata);$atacnt++) {
    list($uname,$ldescrip)=pg_fetch_array($getata,$atacnt);
    print "<OPTION VALUE=\"" . $uname . "\">" . $ldescrip . "</OPTION>\n";
  }
  $rcnt++;
?>
</TD></TR>
<?php print $bcol[$rcnt % 2];?>
 <TD COLSPAN=2 ALIGN=MIDDLE>
 <INPUT TYPE=SUBMIT NAME=submitd VALUE="Delete">
 <INPUT TYPE=SUBMIT NAME=submitd VALUE="Update">
 <INPUT TYPE=BUTTON NAME=push VALUE=Resync ONCLICK=pushconf(document.atapush.ipaddr.value)>
</TD></TR>
</TABLE>
</FORM>
<?php } else {
 $gdatq="SELECT DISTINCT ON (lsys.value) CASE WHEN (users.ipaddr IS NOT NULL) THEN lsys.family||':'||users.ipaddr ELSE lsys.family||':' END,
                          CASE WHEN (phone.family IS NOT NULL) THEN lsys.value||' - '||lsys.family||' ('||users.fullname||' - '||phone.family||')' ELSE lsys.value||' ('||lsys.family||')' END 
                          from astdb as lsys 
                          left outer join astdb as phone on (phone.value=lsys.family AND phone.key='SNOMMAC') 
                          left outer join users on (users.name=phone.family) 
                          where lsys.key='LINKSYS' order by lsys.value,phone.family";
?>
<CENTER>
<FORM METHOD=POST>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>Linksys ATA Management</TH></TR>
<TR CLASS=list-color1><TD onmouseover="myHint.show('LA0')" onmouseout="myHint.hide()">Select Linksys ATA To Manage</TD><TD><SELECT NAME=mac>
  <OPTION VALUE="">Add New ATA</OPTION>
<?php
  $getata=pg_query($db,$gdatq);
  for($atacnt=0;$atacnt < pg_num_rows($getata);$atacnt++) {
    list($mac,$ldescrip,$phone2)=pg_fetch_array($getata,$atacnt);
    print "<OPTION VALUE=\"" . $mac . "\">" . $ldescrip . "</OPTION>\n";
  }
?>
</TD></TR>
<TR CLASS=list-color2><TD COLSPAN=2 ALIGN=MIDDLE><INPUT TYPE=SUBMIT NAME=submitd VALUE="Modify/Add">
</TD></TR>
</TABLE>
</FORM>
<?php }?>
