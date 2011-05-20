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

$exten=$_POST['exten'];

function newrpin() {
  global $db;

  $pincnt=1;
  $pintry=1;

  while (($pintry <= 10) && ($pincnt > 0)) {
    $randpin=rand(0,9999);
    $randpin=str_pad($randpin,4,"0",STR_PAD_LEFT);
    $pincntq=pg_query($db,"SELECT count(id) FROM astdb WHERE key='RoamPass' AND value='" . $randpin . "'");
    list($pincnt)=pg_fetch_array($pincntq,0);
    $pintry++;
  }
  if ($pincnt == 0) {
    pg_query($db,"UPDATE astdb SET value='" . $randpin . "' WHERE key='RoamPass' AND family='" . $_POST['exten'] . "'");
  }
  return $randpin;
}

function getdefval($value) {
  global $db;
  $defaq=pg_query($db,"SELECT value FROM astdb WHERE family='Setup' AND key='" . $value . "'");
  if (pg_num_rows($defaq) > 0) {
    list($defaccess)=pg_fetch_array($defaq,0);
  } else {
    $defaccess=0;
  }
  return $defaccess;
}

$ipgw=array();
$ipgws=pg_query($db,"SELECT name||' ('||description||')',CASE WHEN (protocol = 'OH323') THEN 'OOH323/'||lpad(trunkprefix,7,'0') ELSE protocol||'/'||providerip||'/' END from provider left outer join trunk using (trunkprefix) WHERE protocol != 'Local' ORDER BY name,description");
for($ipcnt=0;$ipcnt < pg_num_rows($ipgws);$ipcnt++) {
  $ipgwr=pg_fetch_array($ipgws,$ipcnt,PGSQL_NUM);
  array_push($ipgw,array('name'=>$ipgwr[0],'gw'=>$ipgwr[1]));
}

$context[0]=_("Internal Extensions");
$context[1]=_("Local PSTN Calls");
$context[2]=_("Long Distance PSTN Calls");
$context[3]=_("Cellular Calls");
$context[4]=_("Premium Calls");
$context[5]=_("International Calls");

$codec[0]="g723.1";
$codec[1]="g729";
$codec[2]="gsm";
$codec[3]="g726";
$codec[4]="speex";
$codec[5]="ilbc";
$codec[6]="ulaw";
$codec[7]="alaw";
$codec[8]="h263p";
$codec[9]="h263";
$codec[10]="h261";

$codecd[0]=_("G723 Low Bandwidth");
$codecd[1]=_("G729 Low Bandwidth");
$codecd[2]=_("GSM Medium Bandwidth");
$codecd[3]=_("G726 Medium Bandwidth");
$codecd[4]=_("SPEEX Medium Bandwidth");
$codecd[5]=_("ILBC Medium Bandwidth");
$codecd[6]=_("uLAW High Bandwidth");
$codecd[7]=_("aLAW High Bandwidth");
$codecd[8]=_("h263+ Video Codec");
$codecd[9]=_("h263 Video Codec");
$codecd[10]=_("h261 Video Codec");

$langs=array("es","fr");
$langn=array(_("Spanish"),_("French"));

if ((isset($pbxupdate)) && ($pbxupdate == "Save Changes")) {
  if ($CFIM == "") {
    $CFIM="0";
  }
  if ($ZAPLine == "") {
    $ZAPLine="0";
  } else if ($ZAPLine != "0") {
%>
<SCRIPT>
  alert("ZAP Analogue Extensions Are Not Realtime\nChanges Made To The Channel May Only Reflect On The Hour\n");
</SCRIPT>
<%
  }
  if ($CFBU == "") {
    $CFBU="0";
  }
  if ($CFNA == "") {
    $CFNA="0";
  }
  if ($CFFAX == "") {
    $CFFAX="0";
  }

  if (($newbgroup != "") && ($SUPER_USER == 1)) {
    $BGRP=$newbgroup;
  }

  if (($newdgroup != "") && ($SUPER_USER == 1)) {
    $DGROUP=$newdgroup;
  }

  if ($CDND == "on") {
    $CDND="1";
  } else if ($CDND == "") {
    $CDND="0";
  }

  if ($DRING == "on") {
    $DRING="1";
  } else {
    $DRING="0";
  }

  if ($WAIT == "on") {
    $WAIT="1";
  } else {
    $WAIT="0";
  }

  if ($RECORD == "on") {
    $RECORD="1";
  } else {
    $RECORD="0";
  }

  if ($NOPRES == "on") {
    $NOPRES="1";
  } else {
    $NOPRES="0";
  }

  if ($DFEAT == "on") {
    $DFEAT="1";
  } else {
    $DFEAT="0";
  }

  if ($NOVOIP == "on") {
    $NOVOIP="1";
  } else {
    $NOVOIP="0";
  }

  if ($CRMPOP == "on") {
    $CRMPOP="1";
  } else {
    $CRMPOP="0";
  }

  if ($IAXLine == "on") {
    $IAXLine="1";
  } else {
    $IAXLine="0";
  }

  if ($H323Line == "on") {
    $H323Line="1";
  } else {
    $H323Line="0";
  }

  if ($Locked == "on") {
    $Locked="1";
  } else {
    $Locked="0";
  }

  if ($NOVMAIL == "on") {
    $NOVMAIL="0";
  } else {
    $NOVMAIL="1";
  }

  if ($FAXMAIL == "on") {
    $FAXMAIL="1";
  } else {
    $FAXMAIL="0";
  }

  if ($SNOMLOCK == "on") {
    $SNOMLOCK="1";
  } else {
    $SNOMLOCK="0";
  }

  if ($POLYDIRLN == "on") {
    $POLYDIRLN="1";
  } else {
    $POLYDIRLN="0";
  }

  if ($qualify == "on") {
    $qualify="yes";
  } else {
    $qualify="";
  }

  if ($DDIPASS == "on") {
    $DDIPASS="1";
  } else {
    $DDIPASS="0";
  }

  if ($canreinvite == "on") {
    $canreinvite="yes";
  } else {
    $canreinvite="no";
  }

  if ($encryption == "on") {
    $encryption="yes";
  } else {
    $encryption="no";
  }

  if ($t38pt_udptl == "on") {
    $t38pt_udptl="yes,redundancy";
  } else {
    $t38pt_udptl="no";
  }

  if ($activated == "on") {
    $activated="t";
  } else {
    $activated="f";
  }

  if (($h323neighbor == "on") && ($h323permit != "") && ($h323permit != "0.0.0.0")) {
    $h323neighbor="t";
    pg_query($db,"UPDATE users SET ipaddr='" . $h323permit . "' WHERE name='" . $_POST['exten'] . "'");
    $h323permit="allow";
  } else {
    $h323neighbor="f";
    if ($h323permit == "0.0.0.0") {
      $h323permit="allow";
    } else if ($h323permit == "") {
      $h323permit="deny";
    }
  }


  $codecs=$codec[$acodec1] . ";" . $codec[$acodec2] . ";" . $codec[$acodec3] . ";" . $codec[$vcodec1] . ";" . $codec[$vcodec2] . ";" . $codec[$vcodec3];

  pg_query($db,"UPDATE astdb SET value='" . $CDND . "' WHERE family='" . $_POST['exten'] . "' AND key='CDND'");
  pg_query($db,"UPDATE astdb SET value='" . $CFBU . "' WHERE family='" . $_POST['exten'] . "' AND key='CFBU'");
  pg_query($db,"UPDATE astdb SET value='" . $CFIM . "' WHERE family='" . $_POST['exten'] . "' AND key='CFIM'");
  pg_query($db,"UPDATE astdb SET value='" . $CFNA . "' WHERE family='" . $_POST['exten'] . "' AND key='CFNA'");
  pg_query($db,"UPDATE astdb SET value='" . $BGRP . "' WHERE family='" . $_POST['exten'] . "' AND key='BGRP'");
  pg_query($db,"UPDATE astdb SET value='" . $CFFAX . "' WHERE family='" . $_POST['exten'] . "' AND key='CFFAX'");
  pg_query($db,"UPDATE astdb SET value='" . $ALTC . "' WHERE family='" . $_POST['exten'] . "' AND key='ALTC'");
  pg_query($db,"UPDATE astdb SET value='" . $OFFICE . "' WHERE family='" . $_POST['exten'] . "' AND key='OFFICE'");
  pg_query($db,"UPDATE astdb SET value='" . $WAIT . "' WHERE family='" . $_POST['exten'] . "' AND key='WAIT'");
  pg_query($db,"UPDATE astdb SET value='" . $RECORD . "' WHERE family='" . $_POST['exten'] . "' AND key='RECORD'");
  pg_query($db,"UPDATE astdb SET value='" . $ALOCK . "' WHERE family='" . $_POST['exten'] . "' AND key='ALOCK'");
  pg_query($db,"UPDATE astdb SET value='" . $NOPRES . "' WHERE family='" . $_POST['exten'] . "' AND key='NOPRES'");
  pg_query($db,"UPDATE astdb SET value='" . $DFEAT . "' WHERE family='" . $_POST['exten'] . "' AND key='DFEAT'");
  pg_query($db,"UPDATE astdb SET value='" . $NOVOIP . "' WHERE family='" . $_POST['exten'] . "' AND key='NOVOIP'");
  pg_query($db,"UPDATE astdb SET value='" . $CRMPOP . "' WHERE family='" . $_POST['exten'] . "' AND key='CRMPOP'");
  pg_query($db,"UPDATE astdb SET value='" . $NOVMAIL . "' WHERE family='" . $_POST['exten'] . "' AND key='NOVMAIL'");
  pg_query($db,"UPDATE astdb SET value='" . $FAXMAIL . "' WHERE family='" . $_POST['exten'] . "' AND key='FAXMAIL'");
  pg_query($db,"UPDATE astdb SET value='" . $SNOMLOCK . "' WHERE family='" . $_POST['exten'] . "' AND key='SNOMLOCK'");
  pg_query($db,"UPDATE astdb SET value='" . $POLYDIRLN . "' WHERE family='" . $_POST['exten'] . "' AND key='POLYDIRLN'");
  pg_query($db,"UPDATE astdb SET value='" . $EFAXD . "' WHERE family='" . $_POST['exten'] . "' AND key='EFAXD'");
  pg_query($db,"UPDATE astdb SET value='" . $TOUT . "' WHERE family='" . $_POST['exten'] . "' AND key='TOUT'");
  pg_query($db,"UPDATE astdb SET value='" . $DGROUP . "' WHERE family='" . $_POST['exten'] . "' AND key='DGROUP'");
  pg_query($db,"UPDATE astdb SET value='" . $ZAPLine . "' WHERE family='" . $_POST['exten'] . "' AND key='ZAPLine'");
  pg_query($db,"UPDATE astdb SET value='" . $DDIPASS . "' WHERE family='" . $_POST['exten'] . "' AND key='DDIPASS'");
  pg_query($db,"UPDATE astdb SET value='" . $ZAPProto . "' WHERE family='" . $_POST['exten'] . "' AND key='ZAPProto'");
  pg_query($db,"UPDATE astdb SET value='" . $ZAPRXGain . "' WHERE family='" . $_POST['exten'] . "' AND key='ZAPRXGain'");
  pg_query($db,"UPDATE astdb SET value='" . $ZAPTXGain . "' WHERE family='" . $_POST['exten'] . "' AND key='ZAPTXGain'");
  pg_query($db,"UPDATE astdb SET value='" . $CLI . "' WHERE family='" . $_POST['exten'] . "' AND key='CLI'");
  pg_query($db,"UPDATE astdb SET value='" . $TRUNK . "' WHERE family='" . $_POST['exten'] . "' AND key='TRUNK'");
  pg_query($db,"UPDATE astdb SET value='" . $ACCESS . "' WHERE family='" . $_POST['exten'] . "' AND key='ACCESS'");
  pg_query($db,"UPDATE astdb SET value='" . $AUTHACCESS . "' WHERE family='" . $_POST['exten'] . "' AND key='AUTHACCESS'");
  pg_query($db,"UPDATE astdb SET value='" . $IAXLine . "' WHERE family='" . $_POST['exten'] . "' AND key='IAXLine'");
  pg_query($db,"UPDATE astdb SET value='" . $H323Line . "' WHERE family='" . $_POST['exten'] . "' AND key='H323Line'");
  pg_query($db,"UPDATE astdb SET value='" . $FWDU . "' WHERE family='" . $_POST['exten'] . "' AND key='FWDU'");
  pg_query($db,"UPDATE astdb SET value='" . $Locked . "' WHERE family='" . $_POST['exten'] . "' AND key='Locked'");
  pg_query($db,"UPDATE astdb SET value='" . strtoupper($SNOMMAC) . "' WHERE family='" . $_POST['exten'] . "' AND key='SNOMMAC'");
  pg_query($db,"UPDATE astdb SET value='" . $VLAN . "' WHERE family='" . $_POST['exten'] . "' AND key='VLAN'");
  pg_query($db,"UPDATE astdb SET value='" . $REGISTRAR . "' WHERE family='" . $_POST['exten'] . "' AND key='REGISTRAR'");
  pg_query($db,"UPDATE astdb SET value='" . $PTYPE . "' WHERE family='" . $_POST['exten'] . "' AND key='PTYPE'");
  pg_query($db,"UPDATE astdb SET value='" . $PURSE . "' WHERE family='" . $_POST['exten'] . "' AND key='PURSE'");

  pg_query($db,"UPDATE astdb SET value='" . $DRING . "' WHERE family='" . $_POST['exten'] . "' AND key='DRING'");
  pg_query($db,"UPDATE astdb SET value='" . $SRING0 . "' WHERE family='" . $_POST['exten'] . "' AND key='SRING0'");
  pg_query($db,"UPDATE astdb SET value='" . $SRING1 . "' WHERE family='" . $_POST['exten'] . "' AND key='SRING1'");
  pg_query($db,"UPDATE astdb SET value='" . $SRING2 . "' WHERE family='" . $_POST['exten'] . "' AND key='SRING2'");
  pg_query($db,"UPDATE astdb SET value='" . $SRING3 . "' WHERE family='" . $_POST['exten'] . "' AND key='SRING3'");

  if ($NEWPIN == "on") {
    $getpin=newrpin();
    print "<SCRIPT>\nalert('The New PIN Code Is\\n\\t" . $getpin . "');\n</SCRIPT>\n";
  }

  if (($SNOMMAC != '') && ($PTYPE == "LINKSYS")) {
    pg_query($db,"UPDATE astdb SET value='" . $LSYSPROFILE . "' WHERE key='PROFILE' AND family='" . $SNOMMAC . "'");
    pg_query($db,"UPDATE astdb SET value='" . $LSYSSTUNSRV . "' WHERE key='STUNSRV' AND family='" . $SNOMMAC . "'");
    pg_query($db,"UPDATE astdb SET value='" . $LSYSLINKSYS . "' WHERE key='LINKSYS' AND family='" . $SNOMMAC . "'");
    pg_query($db,"UPDATE astdb SET value='" . $LSYSLSYSRXGAIN . "' WHERE key='LSYSRXGAIN' AND family='" . $SNOMMAC . "'");
    pg_query($db,"UPDATE astdb SET value='" . $LSYSLSYSTXGAIN . "' WHERE key='LSYSTXGAIN' AND family='" . $SNOMMAC . "'");
    pg_query($db,"UPDATE astdb SET value='" . $LSYSVLAN . "' WHERE key='VLAN' AND family='" . $SNOMMAC . "'");
    if ($LSYSNAT == "on") {
      $LSYSNAT="NAT";
    } else {
      $LSYSNAT="Bridge";
    }
    pg_query($db,"UPDATE astdb SET value='" . $LSYSNAT . "' WHERE key='NAT' AND family='" . $SNOMMAC . "'");
  } else if (($SNOMMAC != '') && ($PTYPE == "CISCO") && ($REGISTRAR != "")) {
    if (($pass1 == $pass2) && ($pass1 != "") && ($pass1 != $secret)) {
      $cispass=$pass1;
    } else {
      $cispass=$secret;
    }
    include_once "cisco.inc";
    ciscoxml($_POST['exten'],$cispass,$REGISTRAR,$SNOMMAC);
  }
  if ($SUPER_USER == 1) {
    if ($conscont == "") {
      $conscont=$newcgroup;
    }
    if ($conscont != "") {
      pg_query($db,"UPDATE console SET context='" . $conscont . "',count='" . $conscount . "'  WHERE mailbox='" . $_POST['exten'] . "'");
    }
  }
  if (($pass1 == $pass2) && ($pass1 != "") && ($pass1 != $secret)) {
    $pwcng.=",secret='$pass1'";
  } else if (($pass1 != "") && ($pass1 != $secret)) {
%>
    <SCRIPT>
      alert("Line Password Mismach/Unset.Password Unchanged");
    </SCRIPT>
<%
  }
  if (($VMPass1 == $VMPass2) && ($VMPass1 != "") && ($VMPass1 != $password)) {
    $pwcng.=",password='$VMPass1'";
  } else if (($VMpass1 != "") && ($VMPass1 != $password)) {
%>
    <SCRIPT>
      alert("Voicemail Password Mismach/Unset.Password Unchanged");
    </SCRIPT>
<%
  }

  pg_query($db,"UPDATE users SET nat='$nat',dtmfmode='$dtmfmode',fullname='$fullname',email='$email',
                                 canreinvite='$canreinvite',qualify='$qualify',allow='$codecs',activated='$activated',language='" . $language . "',
                                 callgroup='$cgroup',pickupgroup='$pgroup',insecure='$insecure',h323prefix='$h323prefix',simuse='$simuse',tariff='$tariff',
                                 h323gkid='$h323gkid',h323permit='$h323permit',h323neighbor='$h323neighbor'$pwcng,
                                 t38pt_udptl='$t38pt_udptl',encryption='$encryption'
                                WHERE name='" . $_POST['exten'] . "'");


  if (! isset($agi)) {
    require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
    $agi=new AGI_AsteriskManager();
    $agi->connect("127.0.0.1","admin","admin");
  }
  $agi->command("sip prune realtime peer " . $_POST['exten']);
  $agi->command("sip prune realtime user " . $_POST['exten']);
  if (($ZAPLine == "0") && ($IAXLine == "0") && ($H323Line == "0") && ($FWDU == "0")) {
    $agi->command("sip show peer " . $_POST['exten'] . " load");
  }
  if ($PTYPE == "SNOM") {
    $agi->command("sip notify reconfig-snom " . $_POST['exten']);
  } else if (($PTYPE == "POLYCOM") || (substr($PTYPE,0,2) == "IP")) {
    $agi->command("sip notify reboot-polycom " . $_POST['exten']);
  }
  $agi->disconnect();
}

$qgetdata=pg_query($db,"SELECT key,value FROM astdb WHERE family='" . $_POST['exten'] . "'");

$qconsdata=pg_query($db,"SELECT context,count FROM console WHERE mailbox = '" . $_POST['exten'] . "'");

$qgetudata=pg_query($db,"SELECT nat,dtmfmode,fullname,email,canreinvite,qualify,password,allow,callgroup,pickupgroup,insecure,
                                h323permit,h323gkid,h323prefix,h323neighbor,ipaddr,language,secret,usertype,activated,simuse,tariff,t38pt_udptl,encryption FROM users WHERE name='" . $_POST['exten'] . "'");


$udata=pg_fetch_array($qgetudata,0);

if (pg_num_rows($qconsdata) > 0) {
  $consdata=pg_fetch_array($qconsdata,0);
} else {
  pg_query("INSERT INTO console SELECT position+1,'" . $_POST['exten'] . "',context,0 from console where context='default' order by  position desc limit 1");
  $qconsdata=pg_query($db,"SELECT context,count FROM console WHERE mailbox = '" . $_POST['exten'] . "'");
  $consdata=pg_fetch_array($qconsdata,0);
}

$nat=$udata[0];
$dtmfmode=$udata[1];
$fullname=htmlentities($udata[2]);
$email=htmlentities($udata[3]);
$canreinvite=$udata[4];
$qualify=$udata[5];
$password=$udata[6];
$codecs=split(";",$udata[7]);
if ($udata[8] != "") {
  $cgroup=$udata[8];
  $upcgrp=0;
} else {
  $upcgrp=1;
  $cgroup="1";
}
if ($udata[9] != "") {
  $pgroup=$udata[9];
  $uppgrp=0;
} else {
  $uppgrp=1;
  $pgroup="1";
}
if (($uppgrp == "1") || ($upcgrp == "1")) {
  pg_query($db,"UPDATE users SET callgroup='" . $cgroup . "',pickupgroup='" . $pgroup . "' WHERE name='" . $_POST['exten'] . "'");
}

$insecure=$udata[10];
$h323permit=$udata[11];
$h323gkid=$udata[12];
$h323prefix=$udata[13];
$h323neighbor=$udata[14];
$language=$udata[16];
$secret=$udata[17];
$usertype=$udata[18];
$activated=$udata[19];
$simuse=$udata[20];
$tariff=$udata[21];
$t38pt_udptl=$udata[22];
$encryption=$udata[23];

$conscont=$consdata[0];
$conscount=$consdata[1];

if ($h323neighbor == "t") {
  $h323neighbor="1";
  $h323permit=$udata[15];
 } else {
  $h323neighbor="0";
  if ($h323permit == "allow") {
    $h323permit="0.0.0.0";
  } else if ($h323permit == "deny") {
    $h323permit="";
  }
}

$curipaddr=$udata[15];

if ($h323gkid == "") {
  $h323gkid=$_POST['exten'];
}

if ($conscount == 0) {
  $conscount++;
}

$acodec=array();
$vcodec=array();

while($icodec=array_shift($codecs)) {
  if (strstr($icodec,"h26")) {
    array_push($vcodec,$icodec);
  } else {
    array_push($acodec,$icodec);
  }
}

$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  $origdata[$getdata[0]]=$getdata[1]; 
}


$lsysgetconf=pg_query($db,"SELECT astdb.key,astdb.value FROM  astdb LEFT OUTER JOIN astdb AS exten ON (astdb.family=exten.value AND exten.key='SNOMMAC' AND astdb.family != '' AND exten.family='" . $_POST['exten'] . "') WHERE astdb.family=exten.value AND astdb.family='" . $origdata['SNOMMAC'] . "'");
for($lsyscnt=0;$lsyscnt < pg_num_rows($lsysgetconf);$lsyscnt++) {
  $getdata=pg_fetch_array($lsysgetconf,$lsyscnt);
  $lsysdata[$getdata[0]]=$getdata[1]; 
}

$lsysconf=array("PROFILE","STUNSRV","LINKSYS","LSYSRXGAIN","LSYSTXGAIN","VLAN","NAT");
$lsysdef["PROFILE"]=$SERVER_NAME;
$lsysdef["STUNSERV"]="";
$lsysdef["LINKSYS"]="exten-" . $_POST['exten'];
$lsysdef["LSYSRXGAIN"]="-3";
$lsysdef["LSYSTXGAIN"]="-3";
$lsysdef["VLAN"]="1";
$lsysdef["NAT"]="Bridge";

$reload=0;
for($lval=0;$lval < count($lsysconf);$lval++) {
  if (! isset($lsysdata[$lsysconf[$lval]])) {
    if (($origdata["SNOMMAC"] != "") && ($origdata["PTYPE"] == "LINKSYS")) {
      $curval="LSYS" . $lsysconf[$lval];
      if ($$curval != "") {
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $origdata["SNOMMAC"] . "','" . $lsysconf[$lval] . "','" . $$curval . "')");
      } else {
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $origdata["SNOMMAC"] . "','" . $lsysconf[$lval] . "','" . $lsysdef[$lsysconf[$lval]] . "')");
      }
      $reload=1; 
    } else {
      $lsysdata[$lsysconf[$lval]]=$lsysdef[$lsysconf[$lval]];
    }
  }
}

if ($reload) {
  $lsysgetconf=pg_query($db,"SELECT astdb.key,astdb.value FROM  astdb LEFT OUTER JOIN astdb AS exten ON (astdb.family=exten.value AND exten.key='SNOMMAC' AND astdb.family != '' AND exten.family='" . $_POST['exten'] . "') WHERE astdb.family=exten.value AND astdb.family='" . $origdata['SNOMMAC'] . "'");
  for($lsyscnt=0;$lsyscnt < pg_num_rows($lsysgetconf);$lsyscnt++) {
    $getdata=pg_fetch_array($lsysgetconf,$lsyscnt);
    $lsysdata[$getdata[0]]=$getdata[1]; 
  }
}

if (($LSYSIPADDR != "") && ($LSYSPUSH == "on")) {%>
<SCRIPT>
  atapopupwin('<%print $LSYSIPADDR;%>','<%print $LSYSPROFILE;%>');
</SCRIPT>
<%
}

if ($origdata["CDND"] == "") {
  $origdata["CDND"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $_POST['exten'] . "','CDND','0')");
}
if ($origdata["CFBU"] == "") {
  $origdata["CFBU"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $_POST['exten'] . "','CFBU','0')");
}
if ($origdata["CFIM"] == "") {
  $origdata["CFIM"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $_POST['exten'] . "','CFIM','0')");
}
if ($origdata["CFNA"] == "") {
  $origdata["CFNA"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $_POST['exten'] . "','CFNA','0')");
}
if ($origdata["BGRP"] == "") {
  $origdata["BGRP"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $_POST['exten'] . "','BGRP','')");
}
if ($origdata["DGROUP"] == "") {
  $origdata["DGROUP"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','DGROUP','')");
}
if ($origdata["CFFAX"] == "") {
  $origdata["CFFAX"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','CFFAX','0')");
}

if ($origdata["ALTC"] == "") {
  $origdata["ALTC"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','ALTC','')");
}

if ($origdata["OFFICE"] == "") {
  $origdata["OFFICE"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','OFFICE','')");
}

if ($origdata["ACCESS"] == "") {
  $origdata["ACCESS"]=getdefval("Context");
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','ACCESS','" . $origdata["ACCESS"] . "')");
}

if ($origdata["AUTHACCESS"] == "") {
  $origdata["AUTHACCESS"]=getdefval("AuthContext");;
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','AUTHACCESS','" . $origdata["AUTHACCESS"] . "')");
}

if ($origdata["WAIT"] == "") {
  $origdata["WAIT"]="1";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','WAIT','1')");
}

if ($origdata["DRING"] == "") {
  $origdata["DRING"]="1";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','DRING','1')");
}

if ($origdata["NOVOIP"] == "") {
  $origdata["NOVOIP"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','NOVOIP','0')");
}

if ($origdata["CRMPOP"] == "") {
  $origdata["CRMPOP"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','CRMPOP','0')");
}

if ($origdata["RECORD"] == "") {
  $origdata["RECORD"]=getdefval("DEFRECORD");
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','RECORD','" . $origdata["RECORD"] . "')");
}

if ($origdata["ALOCK"] == "") {
  $origdata["ALOCK"]=getdefval("DEFALOCK");
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','ALOCK','" . $origdata["ALOCK"] . "')");
}

if ($origdata["NOPRES"] == "") {
  $origdata["NOPRES"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','NOPRES','0')");
}

if ($origdata["DFEAT"] == "") {
  $origdata["DFEAT"]="1";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','DFEAT','1')");
}

if ($origdata["IAXLine"] == "") {
  $origdata["IAXLine"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','IAXLine','0')");
}
if ($origdata["H323Line"] == "") {
  $origdata["H323Line"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','H323Line','0')");
}

if ($origdata["FWDU"] == "") {
  $origdata["FWDU"]=getdefval("REMDEF");
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','FWDU','" . $origdata["FWDU"] . "')");
}

if ($origdata["Locked"] == "") {
  $origdata["Locked"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','Locked','0')");
}

if ($origdata["RoamPass"] == "") {
  $origdata["RoamPass"]=newrpin();
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','RoamPass','" . $origdata["RoamPass"] . "')");
}

if ($origdata["SRING0"] == "") {
  $origdata["SRING0"]="6";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','SRING0','" . $origdata["SRING0"] . "')");
}

if ($origdata["SRING1"] == "") {
  $origdata["SRING1"]="3";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','SRING1','" . $origdata["SRING1"] . "')");
}
if ($origdata["SRING2"] == "") {
  $origdata["SRING2"]="1";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','SRING2','" . $origdata["SRING2"] . "')");
}
if ($origdata["SRING3"] == "") {
  $origdata["SRING3"]="6";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','SRING3','" . $origdata["SRING3"] . "')");
}

if (!isset($origdata["NOVMAIL"])) {
  $origdata["NOVMAIL"]=getdefval("DEFNOVMAIL");
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','NOVMAIL','" . $origdata["NOVMAIL"] . "')");
}

if ($origdata["FAXMAIL"] == "") {
  $origdata["FAXMAIL"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','FAXMAIL','0')");
}

if ($origdata["SNOMLOCK"] == "") {
  $origdata["SNOMLOCK"]="1";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','SNOMLOCK','1')");
}

if ($origdata["POLYDIRLN"] == "") {
  $origdata["POLYDIRLN"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','POLYDIRLN','0')");
}

if ($origdata["EFAXD"] == "") {
  $origdata["EFAXD"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','EFAXD','0')");
}

if ($origdata["TOUT"] == "") {
  $origdata["TOUT"]=getdefval("Timeout");
  if ($origdata["TOUT"] == "0") {
    $origdata["TOUT"]=21;
  }
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','TOUT','" . $origdata["TOUT"] . "')");
}

if ($origdata["ZAPLine"] == "") {
  $origdata["ZAPLine"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','ZAPLine','0')");
}

if ($origdata["DDIPASS"] == "") {
  $origdata["DDIPASS"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','DDIPASS','0')");
}

if ($origdata["ZAPProto"] == "") {
  $origdata["ZAPProto"]="fxo_ks";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','ZAPProto','fxo_ks')");
}

if ($origdata["ZAPRXGain"] == "") {
  $origdata["ZAPRXGain"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','ZAPRXGain','0')");
}

if ($origdata["ZAPTXGain"] == "") {
  $origdata["ZAPTXGain"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','ZAPTXGain','0')");
}

if ($origdata["CLI"] == "") {
  $origdata["CLI"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','CLI','')");
}

if (!isset($origdata["TRUNK"])) {
  $origdata["TRUNK"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','TRUNK','')");
}

if ($origdata["SNOMMAC"] == "") {
  $origdata["SNOMMAC"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','SNOMMAC','')");
}

if ($origdata["VLAN"] == "") {
  $origdata["VLAN"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','VLAN','')");
}

if (!isset($origdata["PURSE"])) {
  $origdata["PURSE"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','PURSE','')");
}

if (isset($rndpin)){%>
  <SCRIPT>
    alert("New Password: <%print $rndpin;%>");
  </SCRIPT>
<%
}


if ($origdata["REGISTRAR"] == "") {
  $origdata["REGISTRAR"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','REGISTRAR','')");
}

if ($origdata["PTYPE"] == "") {
  $origdata["PTYPE"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','PTYPE','SNOM')");
}
%>
<INPUT TYPE=HIDDEN NAME=exten VALUE=<%print $_POST['exten'];%>>
<INPUT TYPE=HIDDEN NAME=curdiv VALUE=basic>
<INPUT TYPE=HIDDEN NAME=pbxupdate VALUE="Save Changes">
<DIV CLASS=content>
<DIV CLASS=list-color2 ID=headcol><DIV CLASS=heading-body><%print _("Configuration For Extension") . " (" . $_POST['exten'] . ")"%></DIV></DIV>

<DIV CLASS=list-color1><DIV CLASS=formrow>
<DIV CLASS=formselect ID=basic_but onclick=showdiv('basic',document.extenform) onmouseover=showdiv('basic',document.extenform)><%print _("Basic");%></DIV>
<DIV CLASS=formselect ID=advanced_but onclick=showdiv('advanced',document.extenform) onmouseover=showdiv('advanced',document.extenform)><%print _("Advanced");%></DIV>
<%
if ($SUPER_USER == 1) {
%>
  <DIV CLASS=formselect ID=snom_but onclick=showdiv('snom',document.extenform) onmouseover=showdiv('snom',document.extenform)><%print _("Auto. Config");%></DIV>
<%
}
%>
<DIV CLASS=formselect ID=auth_but onclick=showdiv('auth',document.extenform) onmouseover=showdiv('auth',document.extenform)><%print _("Authentication");%></DIV>
<DIV CLASS=formselect ID=console_but onclick=showdiv('console',document.extenform) onmouseover=showdiv('console',document.extenform)><%print _("Voip Console");%></DIV>
<DIV CLASS=formselect ID=sip_but onclick=showdiv('sip',document.extenform) onmouseover=showdiv('sip',document.extenform)><%print _("SIP");%></DIV>
<%
if ($SUPER_USER == 1) {
%>
  <DIV CLASS=formselect ID=proto_but onclick=showdiv('proto',document.extenform) onmouseover=showdiv('proto',document.extenform)><%print _("Protocol");%></DIV>
  <DIV CLASS=formselect ID=tdmset_but onclick=showdiv('tdmset',document.extenform) onmouseover=showdiv('tdmset',document.extenform)><%print _("TDM")%></DIV>
  <DIV CLASS=formselect ID=h323_but onclick=showdiv('h323',document.extenform) onmouseover=showdiv('h323',document.extenform)><%print _("H.323");%></DIV>
<%
}
%>
<DIV CLASS=formselect ID=codec_but onclick=showdiv('codec',document.extenform) onmouseover=showdiv('codec',document.extenform)><%print _("Codecs");%></DIV>
<DIV CLASS=formselect ID=alert_but onclick=ajaxsubmit('extenform') onmouseover=showdiv('alert',document.extenform)><%print _("Alerting");%></DIV>
<%
if ($usertype == 1) {
%>
  <DIV CLASS=formselect ID=cshop_but onclick=ajaxsubmit('extenform') onmouseover=showdiv('cshop',document.extenform)><%print _("Billing");%></DIV>
<%
}
%>
<DIV CLASS=formselect ID=save_but onclick=ajaxsubmit('extenform') onmouseover=showdiv('save',document.extenform)><%print _("Save");%></DIV>
</DIV></DIV>

<DIV id=basic CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES1') ONMOUSEOUT=myHint.hide()><%print _("Fullname");%></TD>
  <TD><INPUT TYPE=TEXT NAME=fullname VALUE="<%print $fullname;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES2') ONMOUSEOUT=myHint.hide()><%print _("Email Address");%></TD>
  <TD><INPUT TYPE=TEXT NAME=email VALUE="<%print $email;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES6') ONMOUSEOUT=myHint.hide()><%print _("Alternate Contact Shown On Extension List");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=ALTC VALUE="<%if ($origdata["ALTC"] != "") {print $origdata["ALTC"];}%>">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES6') ONMOUSEOUT=myHint.hide()><%print _("Office/Location Shown On Extension List");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=OFFICE VALUE="<%if ($origdata["OFFICE"] != "") {print $origdata["OFFICE"];}%>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES3') ONMOUSEOUT=myHint.hide()><%print _("Call Forward Immeadiate");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFIM VALUE="<%if ($origdata["CFIM"] != "0") {print $origdata["CFIM"];}%>">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES4') ONMOUSEOUT=myHint.hide()><%print _("Call Forward On Busy");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFBU VALUE="<%if ($origdata["CFBU"] != "0") {print $origdata["CFBU"];}%>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES5') ONMOUSEOUT=myHint.hide()><%print _("Call Forward On No Answer");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFNA VALUE="<%if ($origdata["CFNA"] != "0") {print $origdata["CFNA"];}%>">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES6') ONMOUSEOUT=myHint.hide()><%print _("Call Forward On FAX");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFFAX VALUE="<%if ($origdata["CFFAX"] != "0") {print $origdata["CFFAX"];}%>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES7') ONMOUSEOUT=myHint.hide()><%print _("Ring Timeout");%></TD>
  <TD><INPUT TYPE=TEXT NAME=TOUT VALUE="<%print $origdata["TOUT"];%>"></TD>
</TR>
<%
  if ($SUPER_USER == 1) {
%>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES10') ONMOUSEOUT=myHint.hide() ALIGN=LEFT><%print _("Caller Group (0-63)");%></TD>
  <TD><INPUT TYPE=TEXT NAME=cgroup VALUE="<%print $cgroup;%>"></TD></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES11') ONMOUSEOUT=myHint.hide()><%print _("Pickup Group(s)");%></TD>
  <TD><INPUT TYPE=TEXT NAME=pgroup VALUE="<%print $pgroup;%>"></TD></TR>
<%
    $cnt=0;
  } else {
    $cnt=0;
%>
<INPUT TYPE=HIDDEN NAME=pgroup VALUE="<%print $pgroup;%>">
<INPUT TYPE=HIDDEN NAME=cgroup VALUE="<%print $cgroup;%>">
<TR CLASS=list-color1>
<%
}
%>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD onmouseover=myHint.show('ES9') ONMOUSEOUT=myHint.hide()><%print _("Do Not Disturb");%></TD>
  <TD><SELECT NAME=CDND>
        <OPTION VALUE="">Off 
        <OPTION VALUE="on"<%if ($origdata["CDND"] == "1") {print " SELECTED";}%>><%print _("On");%> 
        <OPTION VALUE="-1"<%if ($origdata["CDND"] == "-1") {print " SELECTED";}%>><%print _("Disabled");%>
     </SELECT>
</TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD onmouseover=myHint.show('ES8') ONMOUSEOUT=myHint.hide()><%print _("Call Waiting");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=WAIT <%if ($origdata["WAIT"] == "1") {print "CHECKED";}%>></TD>
</TR>
</TABLE>
</DIV>
<DIV id=auth CLASS=formpart>
<TABLE CLASS=formtable>

<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES12') ONMOUSEOUT=myHint.hide()><%print _("Extension Permision");%></TD>
  <TD>
    <SELECT NAME=ACCESS>
<%
      for($i=0;$i<6;$i++) {
        print "<OPTION VALUE=" . $i;
        if ($i == $origdata["ACCESS"]) {
          print " SELECTED";
        }
        print ">" . $context[$i] . "</OPTION>\n";
      }
%>
    </SELECT>
</TR>

<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES13') ONMOUSEOUT=myHint.hide()><%print _("Auth Extension Permision");%></TD>
  <TD>
    <SELECT NAME=AUTHACCESS>
<%
      for($i=0;$i<6;$i++) {
        print "<OPTION VALUE=" . $i;
        if ($i == $origdata["AUTHACCESS"]) {
          print " SELECTED";
        }
        print ">" . $context[$i] . "</OPTION>\n";
      }
%>
    </SELECT>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES19') ONMOUSEOUT=myHint.hide()><%print _("After Hours Extension Permision");%></TD>
  <TD>
    <SELECT NAME=ALOCK>
<%
      for($i=0;$i<6;$i++) {
        print "<OPTION VALUE=" . $i;
        if ($i == $origdata["ALOCK"]) {
          print " SELECTED";
        }
        print ">" . $context[$i] . "</OPTION>\n";
      }
%>
    </SELECT></TD>
</TR>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES14') ONMOUSEOUT=myHint.hide()><%print _("Monthly Call Cost Limit");%></TD>
  <TD><INPUT TYPE=INPUT NAME=PURSE VALUE="<%print $origdata["PURSE"];%>"></TD>
</TR>
<TR CLASS=list-color2>
  <INPUT TYPE=HIDDEN name=secret VALUE="<%print $secret;%>">
  <TD ALIGN=LEFT onmouseover=myHint.show('ES14') ONMOUSEOUT=myHint.hide()><%print _("Line Password");%></TD>
  <TD><INPUT TYPE=PASSWORD NAME=pass1 VALUE="<%print $secret;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES15') ONMOUSEOUT=myHint.hide()><%print _("Confirm Line Password");%></TD>
  <TD><INPUT TYPE=PASSWORD NAME=pass2 VALUE="<%print $secret;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <INPUT TYPE=HIDDEN name=password VALUE="<%print $password;%>">
  <TD ALIGN=LEFT onmouseover=myHint.show('ES16') ONMOUSEOUT=myHint.hide()><%print _("Voicemail Password");%></TD>
  <TD><INPUT TYPE=PASSWORD NAME=VMPass1 VALUE="<%print $password;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES17') ONMOUSEOUT=myHint.hide()><%print _("Confirm Voicemail Password");%></TD>
  <TD><INPUT TYPE=PASSWORD NAME=VMPass2 VALUE="<%print $password;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES16') ONMOUSEOUT=myHint.hide()><%print _("Roaming Password");%></TD>
  <TD>
    <INPUT TYPE=BUTTON VALUE="Click To See PIN" ONCLICK="javascript:alert('The PIN Code Is\n\t<%print $origdata["RoamPass"];%>')">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES16') ONMOUSEOUT=myHint.hide()><%print _("Reset Pin Code");%></TD>
  <TD>
    <INPUT TYPE=CHECKBOX NAME=NEWPIN>
  </TD>
</TR>
</TABLE>
</DIV>
<DIV id=advanced CLASS=formpart>
<TABLE CLASS=formtable>

<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES29') ONMOUSEOUT=myHint.hide()><%print _("Billing Group");%></TD>
  <TD>
    <SELECT NAME=BGRP>
<%
      $bgrpq="SELECT DISTINCT value FROM astdb AS bgrp WHERE key='BGRP' AND value != '' ";
      if ($SUPER_USER != 1) {
        $bgrpq.=" AND " . $clogacl;
      } else {
%>
        <OPTION VALUE=""><%print _("Select Existing Group/Add New Group Bellow");%></OPTION>
<%
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
%>
    </SELECT><BR>
<%
    if ($SUPER_USER == 1) {
%>
      <INPUT TYPE=TEXT NAME=newbgroup>
<%
    }
%>
  </TD>
</TR>


<%
    if ($SUPER_USER == 1) {
      $cnt=0;
%>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD onmouseover=myHint.show('ES7') ONMOUSEOUT=myHint.hide()><%print _("Directory Group");%></TD><TD>
    <SELECT NAME=DGROUP>
<%
      $dgrpq="SELECT DISTINCT value FROM astdb WHERE key='DGROUP' AND value != '' AND value IS NOT NULL";
%>
     <OPTION VALUE=""><%print _("Select Existing Group/Add New Group Bellow");%></OPTION>
<%
      $dgroups=pg_query($db,$dgrpq);
      $dgnum=pg_num_rows($dgroups);

      for($i=0;$i<$dgnum;$i++){
        $getdgdata=pg_fetch_array($dgroups,$i);
        print "<OPTION VALUE=\"" . $getdgdata[0] . "\"";
        if ($getdgdata[0] == $origdata["DGROUP"]) {
          print " SELECTED";
        }
        print ">" . $getdgdata[0] . "</OPTION>\n";
      }
%>
    </SELECT><BR>
      <INPUT TYPE=TEXT NAME=newdgroup>
  </TD>
</TR>
<%
} else {
  $cnt=1;
%>
  <INPUT TYPE=HIDDEN NAME="DGROUP" VALUE="<%print $origdata["DGROUP"];%>">
<%
}
%>

<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES18') ONMOUSEOUT=myHint.hide()><%print _("Voice Prompt Language");%></TD>
  <TD><SELECT NAME=language>
    <OPTION VALUE=en>English</OPTION>
<%
    for($lang=0;$lang < count($langs);$lang++) {
       print "<OPTION VALUE=" . $langs[$lang];
       if ($language == $langs[$lang]) {
         print " SELECTED";
       }
       print ">"  . $langn[$lang] . "</OPTION>\n";
    }
%>
  </TD>
</TR>
<%
if ($SUPER_USER == 1) {
  $cnt=0;
%>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD WIDTH=50% onmouseover="myHint.show('PS2')" onmouseout="myHint.hide()"><%print _("PSTN Trunk (Only Use This Trunk When Set)");%></TD>
  <TD>
    <SELECT NAME=TRUNK>
      <OPTION VALUE=""><%print _("Not Set - Follow System Routing");%></OPTION>
      <OPTION VALUE="mISDN/g:out/"<%if ($origdata["TRUNK"] == "mISDN/g:out/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 1");%></OPTION>
      <OPTION VALUE="mISDN/g:out2/"<%if ($origdata["TRUNK"] == "mISDN/g:out2/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 2");%></OPTION>
      <OPTION VALUE="mISDN/g:out3/"<%if ($origdata["TRUNK"] == "mISDN/g:out3/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 3");%></OPTION>
      <OPTION VALUE="mISDN/g:out4/"<%if ($origdata["TRUNK"] == "mISDN/g:out4/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 4");%></OPTION>
      <OPTION VALUE="DAHDI/r1/"<%if (($origdata["TRUNK"] == "Zap/r1/") || ($origdata["TRUNK"] == "DAHDI/r1/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 1");%></OPTION>
      <OPTION VALUE="DAHDI/r2/"<%if (($origdata["TRUNK"] == "Zap/r2/") || ($origdata["TRUNK"] == "DAHDI/r2/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 2");%></OPTION>
      <OPTION VALUE="DAHDI/r3/"<%if (($origdata["TRUNK"] == "Zap/r3/") || ($origdata["TRUNK"] == "DAHDI/r3/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 3");%></OPTION>
      <OPTION VALUE="DAHDI/r4/"<%if (($origdata["TRUNK"] == "Zap/r4/") || ($origdata["TRUNK"] == "DAHDI/r4/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 4");%></OPTION>
      <OPTION VALUE="WOOMERA/g1/"<%if ($origdata["TRUNK"] == "WOOMERA/g1/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 1");%></OPTION>
      <OPTION VALUE="WOOMERA/g2/"<%if ($origdata["TRUNK"] == "WOOMERA/g2/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 2");%></OPTION>
      <OPTION VALUE="WOOMERA/g3/"<%if ($origdata["TRUNK"] == "WOOMERA/g3/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 3");%></OPTION>
      <OPTION VALUE="WOOMERA/g4/"<%if ($origdata["TRUNK"] == "WOOMERA/g4/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 4");%></OPTION>
<%
      for($ipcnt=0;$ipcnt < count($ipgw);$ipcnt++) {
        print "<OPTION VALUE=\"" . $ipgw[$ipcnt]['gw'] . "\"";
        if ($origdata['TRUNK'] == $ipgw[$ipcnt]['gw']) {
          print " SELECTED";
        }
        print ">" . $ipgw[$ipcnt]['name'] . "</OPTION>\n";
      }
%>
    </SELECT>
  </TD>
</TR>
<%
} else {
%>
  <INPUT TYPE=HIDDEN NAME=TRUNK VALUE="<%print $origdata["TRUNK"];%>">
<%
}
%>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES23') ONMOUSEOUT=myHint.hide()><%print _("Early Fax Detect");%></TD>
  <TD>
    <SELECT NAME=EFAXD> 
      <OPTION VALUE=0<%if ($origdata["EFAXD"] == "0") {print " SELECTED";}%>>No Fax Detect</VALUE>
      <OPTION VALUE=1<%if ($origdata["EFAXD"] == "1") {print " SELECTED";}%>>Incoming Fax Detect</VALUE>
      <OPTION VALUE=2<%if ($origdata["EFAXD"] == "2") {print " SELECTED";}%>>In And Out</VALUE>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES18') ONMOUSEOUT=myHint.hide()><%print _("Outbound CLI");%></TD>
  <TD><INPUT TYPE=TEXT NAME=CLI VALUE="<%if ($origdata["CLI"] != "0") {print $origdata["CLI"];}%>"></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES19') ONMOUSEOUT=myHint.hide()><%print _("Withhold Presentation");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NOPRES <%if ($origdata["NOPRES"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES19') ONMOUSEOUT=myHint.hide()><%print _("Call Recording");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=RECORD <%if ($origdata["RECORD"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES19') ONMOUSEOUT=myHint.hide()><%print _("Enable Dial Features (Transfer/One Touch Recording)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DFEAT <%if ($origdata["DFEAT"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES20') ONMOUSEOUT=myHint.hide()><%print _("Enable Voice Mail");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NOVMAIL <%if ($origdata["NOVMAIL"] == "0") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES21') ONMOUSEOUT=myHint.hide()><%print _("Lock Extension");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=Locked <%if ($origdata["Locked"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES22') ONMOUSEOUT=myHint.hide()><%print _("Enable Fax To Mail");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=FAXMAIL <%if ($origdata["FAXMAIL"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES8') ONMOUSEOUT=myHint.hide()><%print _("Distintive Ring Support (Some Phones)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DRING <%if ($origdata["DRING"] == "1") {print "CHECKED";}%>></TD>
</TR>
</TABLE>
</DIV>
<%
if ($SUPER_USER == 1) {
%>
<DIV id=snom CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES24') ONMOUSEOUT=myHint.hide()><%print _("Phones MAC Address");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=SNOMMAC VALUE="<%print $origdata["SNOMMAC"];%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES25') ONMOUSEOUT=myHint.hide()><%print _("Registration Domain (SRV/IP)");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=REGISTRAR VALUE="<%print $origdata["REGISTRAR"];%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES25') ONMOUSEOUT=myHint.hide()><%print _("Phone Manufacturer/Model.");%></TD>
  <TD>
    <SELECT NAME=PTYPE>
      <OPTION VALUE=SNOM<%if ($origdata["PTYPE"] == "SNOM") {print " SELECTED";}%>>Snom
      <OPTION VALUE=SNOM_M9<%if ($origdata["PTYPE"] == "SNOM_M9") {print " SELECTED";}%>>Snom M9
      <OPTION VALUE=LINKSYS<%if ($origdata["PTYPE"] == "LINKSYS") {print " SELECTED";}%>>Linksys/Audiocodes MP-202
      <OPTION VALUE=POLYCOM<%if ($origdata["PTYPE"] == "POLYCOM") {print " SELECTED";}%>>Polycom 300/301/320/330/430
      <OPTION VALUE=IP_500<%if ($origdata["PTYPE"] == "IP_500") {print " SELECTED";}%>>Polycom 500/501/550
      <OPTION VALUE=IP_600<%if ($origdata["PTYPE"] == "IP_600") {print " SELECTED";}%>>Polycom 600
      <OPTION VALUE=IP_601<%if ($origdata["PTYPE"] == "IP_601") {print " SELECTED";}%>>Polycom 601/650
      <OPTION VALUE=IP_4000<%if ($origdata["PTYPE"] == "IP_4000") {print " SELECTED";}%>>Polycom 4000
      <OPTION VALUE=CISCO<%if ($origdata["PTYPE"] == "CISCO") {print " SELECTED";}%>>Cisco 79XX
      <OPTION VALUE=OTHER<%if ($origdata["PTYPE"] == "OTHER") {print " SELECTED";}%>>Other
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TH COLSPAN=2>Snom Settings</TH></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES24') ONMOUSEOUT=myHint.hide()><%print _("VLAN ID");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=VLAN VALUE="<%print $origdata["VLAN"];%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES26') ONMOUSEOUT=myHint.hide()><%print _("Lock Phone Settings (Snom's Only)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=SNOMLOCK <%if ($origdata["SNOMLOCK"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR  CLASS=list-color2>
  <TD onmouseover=myHint.show('ES27') ONMOUSEOUT=myHint.hide()><%print _("Configure Keypad Function Keys ");%></TD>
  <TD><INPUT TYPE=BUTTON VALUE="1-12" TARGET=_blank ONCLICK=snomkeywin("<%print urlencode($_POST['exten']);%>","kp")>
      <INPUT TYPE=BUTTON VALUE="13-54" TARGET=_blank ONCLICK=snomkeywin("<%print urlencode($_POST['exten']);%>","xp")>
      <INPUT TYPE=BUTTON VALUE="55-96" TARGET=_blank ONCLICK=snomkeywin("<%print urlencode($_POST['exten']);%>","xp2")>
      <INPUT TYPE=BUTTON VALUE="97-138" TARGET=_blank ONCLICK=snomkeywin("<%print urlencode($_POST['exten']);%>","xp3")></TD>
</TR>
<TR CLASS=list-color1>
  <TH COLSPAN=2>Polycom Settings</TH></TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES26') ONMOUSEOUT=myHint.hide()><%print _("Search Directory By Last Name");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=POLYDIRLN <%if ($origdata["POLYDIRLN"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color1>
  <TH COLSPAN=2>Linksys/Audiocodes MP-202 Settings (Shared By All Ports)</TH></TR>
<TR  CLASS=list-color2>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Host Name");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSLINKSYS VALUE="<%print $lsysdata["LINKSYS"];%>"></TD>
</TR>
<TR  CLASS=list-color1>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Settings Server");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSPROFILE VALUE="<%print $lsysdata["PROFILE"];%>"></TD>
</TR>
<TR  CLASS=list-color2>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Stun Server");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSSTUNSRV VALUE="<%print $lsysdata["STUNSRV"];%>"></TD>
</TR>
<TR  CLASS=list-color1>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("VLAN ID (Handsets)") . "<BR>" . _("Set It On The Menu And Power Cycle The Device Before Sending The Config");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSVLAN VALUE="<%print $lsysdata["VLAN"];%>"></TD>
</TR>
<TR  CLASS=list-color2>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("RX/TX Gain (ATA's)");%></TD>
  <TD>
    <INPUT TYPE=TEXT NAME=LSYSLSYSRXGAIN SIZE=3 VALUE="<%print $lsysdata["LSYSRXGAIN"];%>">/
    <INPUT TYPE=TEXT NAME=LSYSLSYSTXGAIN SIZE=3 VALUE="<%print $lsysdata["LSYSTXGAIN"];%>">
  </TD>
</TR>
<TR  CLASS=list-color1>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Current IP Address.") . "<BR>" . _("This Must Be Set And Reachable From Your Browser To Initilise The Phone Correctly.");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSIPADDR VALUE="<%print $curipaddr;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES8') ONMOUSEOUT=myHint.hide()><%print _("Enable NAT/DHCP On Lan Port");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=LSYSNAT<%if ($lsysdata["NAT"] == "NAT") {print " checked";}%>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES8') ONMOUSEOUT=myHint.hide()><%print _("Send Settings To The Phone When Extension Is Saved");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=LSYSPUSH></TD>
</TR>
</TABLE>
</DIV>
<%
} else {
%>
  <INPUT TYPE=HIDDEN NAME=SNOMMAC VALUE="<%print $origdata["SNOMMAC"];%>">
  <INPUT TYPE=HIDDEN NAME=REGISTRAR VALUE="<%print $origdata["REGISTRAR"];%>">
  <INPUT TYPE=HIDDEN NAME=PTYPE VALUE="<%print $origdata["PTYPE"];%>">
  <INPUT TYPE=HIDDEN NAME=VLAN VALUE="<%print $origdata["VLAN"];%>">
  <INPUT TYPE=HIDDEN NAME=SNOMLOCK VALUE="<%if ($origdata["SNOMLOCK"] == "1") {print "on";}%>">
  <INPUT TYPE=HIDDEN NAME=POLYDIRLN VALUE="<%if ($origdata["POLYDIRLN"] == "1") {print "on";}%>">
  <INPUT TYPE=HIDDEN NAME=LSYSLINKSYS VALUE="<%print $lsysdata["LINKSYS"];%>">
  <INPUT TYPE=HIDDEN NAME=LSYSPROFILE VALUE="<%print $lsysdata["PROFILE"];%>">
  <INPUT TYPE=HIDDEN NAME=LSYSSTUNSRV VALUE="<%print $lsysdata["STUNSRV"];%>">
  <INPUT TYPE=HIDDEN NAME=LSYSVLAN VALUE="<%print $lsysdata["VLAN"];%>">
  <INPUT TYPE=HIDDEN NAME=LSYSLSYSRXGAIN VALUE="<%print $lsysdata["LSYSRXGAIN"];%>">
  <INPUT TYPE=HIDDEN NAME=LSYSLSYSTXGAIN VALUE="<%print $lsysdata["LSYSTXGAIN"];%>">
  <INPUT TYPE=HIDDEN NAME=LSYSNAT VALUE="<%if ($lsysdata["NAT"] == "NAT") {print "on";}%>">
<%
}
%>
<DIV id=console CLASS=formpart>
<TABLE CLASS=formtable>
<%
if ($SUPER_USER == 1) {
%>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES29') ONMOUSEOUT=myHint.hide()>Conslole Group</TD>
  <TD>
    <SELECT NAME=conscont>
      <OPTION VALUE="">Add New Group Bellow</OPTION>
      <OPTION VALUE="default"<%if ($conscont == "") {print " SELECTED";}%>><%print _("Genral/Default");%></OPTION>
<%
      $congroups=pg_query("SELECT DISTINCT context FROM console WHERE context != 'default'");
      $cgnum=pg_num_rows($congroups);

      for($i=0;$i<$cgnum;$i++){
        $getcgdata=pg_fetch_array($congroups,$i);
        print "<OPTION VALUE=" . $getcgdata[0];
        if ($getcgdata[0] == $conscont) {
          print " SELECTED";
        }
        print ">" . $getcgdata[0] . "</OPTION>\n";
      }
%>
    </SELECT><BR>
    <INPUT TYPE=TEXT NAME=newcgroup>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES30') ONMOUSEOUT=myHint.hide()><%print _("Number Of Lines Viewable On Console");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=conscount VALUE="<%print $conscount;%>">
  </TD>
</TR>
<TR CLASS=list-color2>
<%
} else {
%>
  <INPUT TYPE=HIDDEN NAME=conscont VALUE="<%print $conscont;%>">
  <INPUT TYPE=HIDDEN NAME=conscount VALUE="<%print $conscount;%>">
<TR CLASS=list-color1>
<%
}
%>
  <TD onmouseover=myHint.show('ES31') ONMOUSEOUT=myHint.hide()><%print _("Open Up CRM Page On Incoming Call");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=CRMPOP <%if ($origdata["CRMPOP"] == "1") {print "CHECKED";}%>></TD>
</TR>

</TABLE>
</DIV>
<%
if ($SUPER_USER == 1) {
%>
<DIV id=proto CLASS=formpart>
<TABLE CLASS=formtable>

<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES49') ONMOUSEOUT=myHint.hide()><%print _("Route This Extension On Forward Trunk/Remotely")%></TD>
  <TD>
    <SELECT NAME=FWDU>
      <OPTION VALUE="0">None</OPTION>
      <OPTION VALUE="1"<%if ($origdata["FWDU"] == "1") { print " SELECTED";}%>>Default</OPTION>
      <OPTION VALUE="mISDN/g:fwd/"<%if ($origdata["FWDU"] == "mISDN/g:fwd/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Forward Group");%></OPTION>
      <OPTION VALUE="mISDN/g:out/"<%if ($origdata["FWDU"] == "mISDN/g:out/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 1");%></OPTION>
      <OPTION VALUE="mISDN/g:out2/"<%if ($origdata["FWDU"] == "mISDN/g:out2/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 2");%></OPTION>
      <OPTION VALUE="mISDN/g:out3/"<%if ($origdata["FWDU"] == "mISDN/g:out3/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 3");%></OPTION>
      <OPTION VALUE="mISDN/g:out4/"<%if ($origdata["FWDU"] == "mISDN/g:out4/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 4");%></OPTION>
      <OPTION VALUE="DAHDI/r1/"<%if (($origdata["FWDU"] == "Zap/r1/") || ($origdata["FWDU"] == "DAHDI/r1/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 1");%></OPTION>
      <OPTION VALUE="DAHDI/r2/"<%if (($origdata["FWDU"] == "Zap/r2/") || ($origdata["FWDU"] == "DAHDI/r2/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 2");%></OPTION>
      <OPTION VALUE="DAHDI/r3/"<%if (($origdata["FWDU"] == "Zap/r3/") || ($origdata["FWDU"] == "DAHDI/r3/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 3");%></OPTION>
      <OPTION VALUE="DAHDI/r4/"<%if (($origdata["FWDU"] == "Zap/r4/") || ($origdata["FWDU"] == "DAHDI/r4/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 4");%></OPTION>
      <OPTION VALUE="WOOMERA/g1/"<%if ($origdata["FWDU"] == "WOOMERA/g1/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 1");%></OPTION>
      <OPTION VALUE="WOOMERA/g2/"<%if ($origdata["FWDU"] == "WOOMERA/g2/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 2");%></OPTION>
      <OPTION VALUE="WOOMERA/g3/"<%if ($origdata["FWDU"] == "WOOMERA/g3/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 3");%></OPTION>
      <OPTION VALUE="WOOMERA/g4/"<%if ($origdata["FWDU"] == "WOOMERA/g4/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 4");%></OPTION>
<%
      for($ipcnt=0;$ipcnt < count($ipgw);$ipcnt++) {
        print "<OPTION VALUE=\"" . $ipgw[$ipcnt]['gw'] . "\"";
        if ($origdata['FWDU'] == $ipgw[$ipcnt]['gw']) {
          print " SELECTED";
        }
        print ">" . $ipgw[$ipcnt]['name'] . "</OPTION>\n";
      }
%>
    </SELECT>
  </TD>
</TR> <TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES33') ONMOUSEOUT=myHint.hide()><%print _("Use IAX As VOIP Protocol");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=IAXLine <%if ($origdata["IAXLine"] == "1") {print "CHECKED";}%>></TD>
</TR> <TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES49') ONMOUSEOUT=myHint.hide()><%print _("Use H323 As VOIP Protocol (See H.323 Settings Bellow)")%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=H323Line <%$rcnt++;if ($origdata["H323Line"] == "1") {print "CHECKED";}%>></TD>
</TR> <TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES32') ONMOUSEOUT=myHint.hide()><%print _("Exclude From LCR (VOIP/GSM)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NOVOIP <%if ($origdata["NOVOIP"] == "1") {print "CHECKED";}%>></TD>
</TR>
</TABLE>
</DIV>
<%
} else {
%>
  <INPUT TYPE=HIDDEN NAME=NOVOIP VALUE="<%if ($origdata["NOVOIP"] == "1") {print "on";}%>">
  <INPUT TYPE=HIDDEN NAME=IAXLine VALUE="<%if ($origdata["IAXLine"] == "1") {print "on";}%>">
  <INPUT TYPE=HIDDEN NAME=H323Line VALUE="<%if ($origdata["H323Line"] == "1") {print "on";}%>">
  <INPUT TYPE=HIDDEN NAME=FWDU VALUE="<%print $origdata["FWDU"];%>">
<%
}
%>
<DIV id=sip CLASS=formpart>
<TABLE CLASS=formtable>

<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES34') ONMOUSEOUT=myHint.hide()><%print _("NAT Handling");%></TD>
  <TD>
    <SELECT NAME=nat>
      <OPTION VALUE=no <%if ($nat == "no") {print " SELECTED";}%>><%print _("Use NAT If Required");%></OPTION>
      <OPTION VALUE=yes <%if ($nat == "yes") {print " SELECTED";}%>><%print _("Always Use Nat");%></OPTION>
      <OPTION VALUE=never <%if (($nat == "never") || ($nat == "")) {print " SELECTED";}%>><%print _("Never Use NAT");%></OPTION>
      <OPTION VALUE=route <%if ($nat == "route") {print " SELECTED";}%>><%print _("Assume NAT Dont Send Port");%></OPTION>
    </SELECT>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES35') ONMOUSEOUT=myHint.hide()><%print _("DTMF Handling");%></TD>
  <TD>
    <SELECT NAME=dtmfmode>
      <OPTION VALUE=rfc2833 <%if ($dtmfmode == "rfc2833") {print " SELECTED";}%>><%print _("Use Standard DTMF");%></OPTION>
      <OPTION VALUE=info <%if ($dtmfmode == "info") {print " SELECTED";}%>><%print _("Send DTMF In SIP INFO");%></OPTION>
      <OPTION VALUE=inband <%if ($dtmfmode == "inband") {print " SELECTED";}%>><%print _("Send DTMF Inband");%></OPTION>
    </SELECT>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES36') ONMOUSEOUT=myHint.hide()><%print _("Relaxed Authentication");%></TD>
  <TD>
    <SELECT NAME=insecure>
      <OPTION VALUE="">Never</OPTION>
      <OPTION VALUE="port"<%if ($insecure == "port") {print " SELECTED";}%>><%print _("Based On Port");%></OPTION>
      <OPTION VALUE="invite"<%if ($insecure == "invite") {print " SELECTED";}%>><%print _("On Invite");%></OPTION>
      <OPTION VALUE="port,invite"<%if ($insecure == "port,invite") {print " SELECTED";}%>><%print _("On Port And Invite");%></OPTION>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES37') ONMOUSEOUT=myHint.hide()><%print _("Allow Peer To Peer Connections (Reinvite)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=canreinvite <%if ($canreinvite == "yes") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES39') ONMOUSEOUT=myHint.hide()><%print _("Send Nat Keep Alive Packets");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=qualify <%if ($qualify == "yes") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES39') ONMOUSEOUT=myHint.hide()><%print _("Pass DDI To Extension");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DDIPASS <%if ($origdata["DDIPASS"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES39') ONMOUSEOUT=myHint.hide()><%print _("Allow T.38 Support");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=t38pt_udptl <%if ($t38pt_udptl != "no") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES39') ONMOUSEOUT=myHint.hide()><%print _("SRTP Encryption");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=encryption <%if ($encryption != "no") {print "CHECKED";}%>></TD>
</TR>
</TABLE>
</DIV>
<%
  if ($SUPER_USER == 1) {
%>
<DIV id=tdmset CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES40') ONMOUSEOUT=myHint.hide()><%print _("TDM Port Non VOIP (ZAP Channel)");%></TD>
  <TD><INPUT TYPE=TEXT NAME=ZAPLine VALUE="<%print $origdata["ZAPLine"];%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES46') ONMOUSEOUT=myHint.hide()><%print _("Signaling Used For ZAP Channel");%><BR></TD>
  <TD><SELECT NAME=ZAPProto>
    <OPTION VALUE="fxo_ks"<%if ($origdata["ZAPProto"] == "fxo_ks") {print " SELECTED";}%>><%print _("Kewl Start");%></OPTION>
    <OPTION VALUE="fxo_ls"<%if ($origdata["ZAPProto"] == "fxo_ls") {print " SELECTED";}%>><%print _("Loop Start");%></OPTION>
    <OPTION VALUE="fxo_gs"<%if ($origdata["ZAPProto"] == "fxo_gs") {print " SELECTED";}%>><%print _("Ground Start");%></OPTION>
  </SELECT></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES47') ONMOUSEOUT=myHint.hide()><%print _("RX Gain");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=ZAPRXGain VALUE="<%print $origdata["ZAPRXGain"];%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES48') ONMOUSEOUT=myHint.hide()><%print _("TX Gain");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=ZAPTXGain VALUE="<%print $origdata["ZAPTXGain"];%>"></TD>
</TR>
</TABLE>
</DIV>
<DIV id=h323 CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES41') ONMOUSEOUT=myHint.hide()><%print _("Gatekeeper IP") . "<BR>0.0.0.0 " . _("For Any IP Or Blank To Deny Access");%></TD>
  <TD><INPUT TYPE=TEXT NAME=h323permit VALUE="<%print $h323permit;%>"></TD></TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES42') ONMOUSEOUT=myHint.hide()><%print _("Recived Prefix");%></TD>
  <TD><INPUT TYPE=TEXT NAME=h323prefix VALUE="<%print $h323prefix;%>"></TD></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES43') ONMOUSEOUT=myHint.hide()><%print _("Gatekeeper ID");%></TD>
  <TD><INPUT TYPE=TEXT NAME=h323gkid VALUE="<%print $h323gkid;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES50') ONMOUSEOUT=myHint.hide()><%print _("Trusted Neighbor");%><BR></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=h323neighbor <%if ($h323neighbor) {print "CHECKED";}%>></TD>
</TR>
</TABLE>
</DIV>
<%
} else {
%>
  <INPUT TYPE=HIDDEN NAME=ZAPProto VALUE="<%print $origdata["ZAPProto"];%>">
  <INPUT TYPE=HIDDEN NAME=ZAPRXGain VALUE="<%print $origdata["ZAPRXGain"];%>">
  <INPUT TYPE=HIDDEN NAME=ZAPTXGain VALUE="<%print $origdata["ZAPTXGain"];%>">
  <INPUT TYPE=HIDDEN NAME=h323permit VALUE="<%print $h323permit;%>">
  <INPUT TYPE=HIDDEN NAME=h323prefix VALUE="<%print $h323prefix;%>">
  <INPUT TYPE=HIDDEN NAME=h323gkid VALUE="<%print $h323gkid;%>">
  <INPUT TYPE=HIDDEN NAME=h323neighbor VALUE="<%if ($h323neighbor) {print "on";}%>">
<%
}
%>
<DIV id=codec CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES44') ONMOUSEOUT=myHint.hide()><%print _("First Audio Codec Choice");%></TD>
  <TD>
    <SELECT NAME=acodec1>
      <%if (is_file("/usr/lib/asterisk/modules-1.8/codec_g723.so")) {
          print "<OPTION VALUE=0";
          if ($acodec[0] == $codec[0]) {
            print " SELECTED";
          }
          print ">" . $codecd[0] . "</OPTION>\n";
        }
        if (is_file("/usr/lib/asterisk/modules-1.8/codec_g729.so")) {
          print "<OPTION VALUE=1";
          if ($acodec[0] == $codec[1]) {
            print " SELECTED";
          }
          print ">" . $codecd[1] . "</OPTION>\n";
        }
        for ($i=2;$i<=7;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($acodec[0] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>

<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES44') ONMOUSEOUT=myHint.hide()><%print _("Second Audio Codec Choice");%></TD>
  <TD>
    <SELECT NAME=acodec2>
      <%if (is_file("/usr/lib/asterisk/modules-1.4/codec_g723.so")) {
          print "<OPTION VALUE=0";
          if ($acodec[1] == $codec[0]) {
            print " SELECTED";
          }
          print ">" . $codecd[0] . "</OPTION>\n";
        }
        if (is_file("/usr/lib/asterisk/modules-1.4/codec_g729.so")) {
          print "<OPTION VALUE=1";
          if ($acodec[1] == $codec[1]) {
            print " SELECTED";
          }
          print ">" . $codecd[1] . "</OPTION>\n";
        }
        for ($i=2;$i<=7;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($acodec[1] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>

<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES44') ONMOUSEOUT=myHint.hide()><%print _("Third Audio Codec Choice");%></TD>
  <TD>
    <SELECT NAME=acodec3>
      <%if (is_file("/usr/lib/asterisk/modules-1.4/codec_g723.so")) {
          print "<OPTION VALUE=0";
          if ($acodec[2] == $codec[0]) {
            print " SELECTED";
          }
          print ">" . $codecd[0] . "</OPTION>\n";
        }
        if (is_file("/usr/lib/asterisk/modules-1.4/codec_g729.so")) {
          print "<OPTION VALUE=1";
          if ($acodec[2] == $codec[1]) {
            print " SELECTED";
          }
          print ">" . $codecd[1] . "</OPTION>\n";
        }
        for ($i=2;$i<=7;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($acodec[2] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>

<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('ES45')" onmouseout="myHint.hide()"><%print _("First Video Codec Choice");%></TD>
  <TD>
    <SELECT NAME=vcodec1><%
        for ($i=8;$i<=10;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($vcodec[0] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>

<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('ES45')" onmouseout="myHint.hide()"><%print _("Second Video Codec Choice");%></TD>
  <TD>
    <SELECT NAME=vcodec2><%
        for ($i=8;$i<=10;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($vcodec[1] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>

<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('ES45')" onmouseout="myHint.hide()"><%print _("Third Video Codec Choice");%></TD>
  <TD>
    <SELECT NAME=vcodec3><%
        for ($i=8;$i<=10;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($vcodec[2] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>
</TABLE>
</DIV>

<DIV id=alert CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2><TH COLSPAN=2><%print _("For Snom Phones");%></TH></TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES41') ONMOUSEOUT=myHint.hide()><%print _("Default Ringer");%></TD>
  <TD><SELECT NAME=SRING0>
<%
  for ($ring=1;$ring <= 10;$ring++) {
    print "<OPTION VALUE=" . $ring;
    if ($origdata["SRING0"] == $ring) {
      print " SELECTED";
    }
    print ">" . $ring . "</OPTION>\n";
  }
%>
  </SELECT></TD></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES41') ONMOUSEOUT=myHint.hide()><%print _("Internal Ringer");%></TD>
  <TD><SELECT NAME=SRING1>
<%
  for ($ring=1;$ring <= 10;$ring++) {
    print "<OPTION VALUE=" . $ring;
    if ($origdata["SRING1"] == $ring) {
      print " SELECTED";
    }
    print ">" . $ring . "</OPTION>\n";
  }
%>
  </SELECT></TD></TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES41') ONMOUSEOUT=myHint.hide()><%print _("Group Ringer");%></TD>
  <TD><SELECT NAME=SRING2>
<%
  for ($ring=1;$ring <= 10;$ring++) {
    print "<OPTION VALUE=" . $ring;
    if ($origdata["SRING2"] == $ring) {
      print " SELECTED";
    }
    print ">" . $ring . "</OPTION>\n";
  }
%>
  </SELECT></TD></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES41') ONMOUSEOUT=myHint.hide()><%print _("External Ringer");%></TD>
  <TD><SELECT NAME=SRING3>
<%
  for ($ring=1;$ring <= 10;$ring++) {
    print "<OPTION VALUE=" . $ring;
    if ($origdata["SRING3"] == $ring) {
      print " SELECTED";
    }
    print ">" . $ring . "</OPTION>\n";
  }
%>
  </SELECT></TD></TR>
</TABLE>
</DIV>
<DIV id=cshop CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2><TD WIDTH=50%>Simuse</TD><TD>
  <INPUT TYPE=TEXT NAME=simuse VALUE="<%print $simuse;%>"></TD></TR>
<TR CLASS=list-color1><TD>Activated</TD><TD>
  <INPUT TYPE=CHECKBOX NAME=activated<%if ($activated == "t") {print " CHECKED";};%>></TD></TR>
<TR CLASS=list-color2><TD>Rate Plan</TD><TD>

<SELECT NAME=tariff><%
  $tplan=pg_query($db,"SELECT tariffname,tariffcode FROM tariff WHERE tariffcode LIKE '" .
                       $_SESSION['resellerid'] . "-%' ORDER BY tariffname");
  $num=pg_num_rows($tplan);
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_array($tplan,$i,PGSQL_NUM);
    print "<OPTION VALUE=\"" . $r[1] . "\"";
    if ($tariff == "$r[1]") {
      print " SELECTED";
    }
    print ">" . $r[0] . "</OPTION>\n";
  }%>
<% 
%>
</SELECT></TD></TR>
</TABLE>
</DIV>

<DIV id=save CLASS=formpart></DIV>

</DIV>
</FORM>

<SCRIPT>
document.getElementById(document.extenform.curdiv.value).style.visibility='visible';
document.getElementById(document.extenform.curdiv.value+'_but').style.backgroundColor='<%print $menubg2;%>';
document.getElementById(document.extenform.curdiv.value+'_but').style.color='<%print $menufg2;%>';
</SCRIPT>
