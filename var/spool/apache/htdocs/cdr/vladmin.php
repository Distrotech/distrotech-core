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

function newrpin($exten) {
  global $db;

  $pincnt=1;
  $pintry=1;

  while (($pintry <= 10) && ($pincnt > 0)) {
    $randpin=rand(0,9999);
    $randpin=str_pad($randpin,4,"0",STR_PAD_LEFT);
    $pincntq=pg_query($db,"SELECT count(id) FROM features WHERE roampass='" . $randpin . "'");
    list($pincnt)=pg_fetch_array($pincntq,0);
    $pintry++;
  }
  if ($pincnt == 0) {
    pg_query($db,"UPDATE features SET roampass='" . $randpin . "' WHERE exten='" . $exten . "'");
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

$poscb=array("CDND","DRING","WAIT","RECORD","NOPRES","DFEAT","NOVOIP","CRMPOP","IAXLine","H323Line","Locked",
             "FAXMAIL","SNOMLOCK","POLYDIRLN","DDIPASS","DDIPASS");
$negcb=array("NOVMAIL");
$astdbarr=array("CDND","CFBU","CFIM","CFNA","CFFAX","ALTC","OFFICE","WAIT","RECORD",
                "ALOCK","NOPRES","DFEAT","NOVOIP","CRMPOP","NOVMAIL","FAXMAIL","SNOMLOCK","POLYDIRLN","EFAXD",
                "TOUT","DGROUP","ZAPLine","DDIPASS","ZAPProto","ZAPRXGain","ZAPTXGain","CLI","TRUNK","ACCESS",
                "AUTHACCESS","IAXLine","H323Line","FWDU","Locked","SNOMMAC","VLAN","REGISTRAR","PTYPE","PURSE",
                "DRING","SRING0","SRING1","SRING2","SRING3");

if ((isset($pbxupdate)) && ($pbxupdate == "Save Changes")) {
  if ($_POST['CFIM'] == "") {
    $_POST['CFIM']="0";
  }
  if ($_POST['CFBU'] == "") {
    $_POST['CFBU']="0";
  }
  if ($_POST['CFNA'] == "") {
    $_POST['CFNA']="0";
  }
  if ($_POST['CFFAX'] == "") {
    $_POST['CFFAX']="0";
  }

  if ($_POST['ZAPLine'] == "") {
    $_POST['ZAPLine']="0";
  } else if ($_POST['ZAPLine'] != "0") {
%>
<SCRIPT>
  alert("ZAP Analogue Extensions Are Not Realtime\nChanges Made To The Channel May Only Reflect On The Hour\n");
</SCRIPT>
<%
  }

  if (($newbgroup != "") && ($SUPER_USER == 1)) {
    $_POST['BGRP']=$newbgroup;
  }

  if (($newdgroup != "") && ($SUPER_USER == 1)) {
    $_POST['DGROUP']=$newdgroup;
  }

  for ($cbcnt=0;$cbcnt < count($poscb);$cbcnt++) {
    if ($_POST[$poscb[$cbcnt]] == "on") {
      $_POST[$poscb[$cbcnt]] = "1";
      $$poscb[$cbcnt] = "1";
    } else {
      $_POST[$poscb[$cbcnt]] = "0";
      $$poscb[$cbcnt] = "0";
    }
  }

  for ($cbcnt=0;$cbcnt < count($negcb);$cbcnt++) {
    if ($_POST[$negcb[$cbcnt]] == "on") {
      $_POST[$negcb[$cbcnt]] = "0";
      $$negcb[$cbcnt] = "0";
    } else {
      $_POST[$negcb[$cbcnt]] = "1";
      $$negcb[$cbcnt] = "1";
    }
  }

  if ($_POST['qualify'] == "on") {
    $_POST['qualify']="yes";
  } else {
    $_POST['qualify']="";
  }

  if ($_POST['canreinvite'] == "on") {
    $_POST['canreinvite']="yes";
  } else {
    $_POST['canreinvite']="no";
  }

  if ($_POST['t38pt_udptl'] == "on") {
    $_POST['t38pt_udptl']="yes,redundancy";
  } else {
    $_POST['t38pt_udptl']="no";
  }

  if ($_POST['activated'] == "on") {
    $_POST['activated']="t";
  } else {
    $_POST['activated']="f";
  }

  $_POST['SNOMMAC'] = strtoupper($_POST['SNOMMAC']);

  if (($_POST['h323neighbor'] == "on") && ($_POST['h323permit'] != "") && ($_POST['h323permit'] != "0.0.0.0")) {
    $_POST['h323neighbor']="t";
    pg_query($db,"UPDATE users SET ipaddr='" . $_POST['h323permit'] . "' WHERE name='" . $_POST['exten'] . "'");
    $_POST['h323permit']="allow";
  } else {
    $_POST['h323neighbor']="f";
    if ($_POST['h323permit'] == "0.0.0.0") {
      $_POST['h323permit']="allow";
    } else if ($_POST['h323permit'] == "") {
      $_POST['h323permit']="deny";
    }
  }


  $codecs=$codec[$acodec1] . ";" . $codec[$acodec2] . ";" . $codec[$acodec3] . ";" . $codec[$vcodec1] . ";" . $codec[$vcodec2] . ";" . $codec[$vcodec3];

  for($icnt=0;$icnt < count($astdbarr);$icnt++) {
    pg_query($db, "UPDATE features SET " . $astdbarr[$icnt] . "='" . $_POST[$astdbarr[$icnt]] . "' WHERE exten='" . $_POST['exten'] . "'");
  }
  $ud=pg_query($db, "UPDATE astdb SET value='" . $_POST['BGRP'] . "' WHERE key='BGRP' AND family='" . $_POST['exten'] . "'");
  if (pg_affected_rows($ud) <= 0) {
    pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $_POST['exten'] . "','BGRP','" . $_POST[$astdbarr[$icnt]] . "')");
  }
  if ($_POST['NEWPIN'] == "on") {
    $getpin=newrpin($_POST['exten']);
    print "<SCRIPT>\nalert('The New PIN Code Is\\n\\t" . $getpin . "');\n</SCRIPT>\n";
  }

  if (($SNOMMAC != '') && ($PTYPE == "LINKSYS")) {
    if ($LSYSNAT == "on") {
      $LSYSNAT="NAT";
    } else {
      $LSYSNAT="Bridge";
    }
    pg_query($db,"UPDATE astdb SET value='" . $LSYSPROFILE . "' WHERE key='PROFILE' AND family='" . $SNOMMAC . "'");
    pg_query($db,"UPDATE astdb SET value='" . $LSYSSTUNSRV . "' WHERE key='STUNSRV' AND family='" . $SNOMMAC . "'");
    pg_query($db,"UPDATE astdb SET value='" . $LSYSLINKSYS . "' WHERE key='LINKSYS' AND family='" . $SNOMMAC . "'");
    pg_query($db,"UPDATE astdb SET value='" . $LSYSLSYSRXGAIN . "' WHERE key='LSYSRXGAIN' AND family='" . $SNOMMAC . "'");
    pg_query($db,"UPDATE astdb SET value='" . $LSYSLSYSTXGAIN . "' WHERE key='LSYSTXGAIN' AND family='" . $SNOMMAC . "'");
    pg_query($db,"UPDATE astdb SET value='" . $LSYSVLAN . "' WHERE key='VLAN' AND family='" . $SNOMMAC . "'");
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
    $pwcng.="secret='$pass1',";
  } else if (($pass1 != "") && ($pass1 != $secret)) {
%>
    <SCRIPT>
      alert("Line Password Mismach/Unset.Password Unchanged");
    </SCRIPT>
<%
  }
  if (($VMPass1 == $VMPass2) && ($VMPass1 != "") && ($VMPass1 != $password)) {
    $pwcng.="password='$VMPass1',";
  } else if (($VMpass1 != "") && ($VMPass1 != $password)) {
%>
    <SCRIPT>
      alert("Voicemail Password Mismach/Unset.Password Unchanged");
    </SCRIPT>
<%
  }

  $encdat=explode(",",$_POST['encryption']);
  if ($encdat[1] ==  "32bit") {
    $_POST['encryption_taglen']="32";
  } else {
    $_POST['encryption_taglen']="80";
  }

  $userarr=array("nat","dtmfmode","fullname","email","canreinvite","qualify","activated",
                 "language","callgroup","pickupgroup","insecure","h323prefix","simuse",
                 "tariff","h323gkid","h323permit","h323neighbor","t38pt_udptl","encryption",
                 "encryption_taglen");

  $dbq="";
  for($ucnt=0;$ucnt < count($userarr);$ucnt++) {
    $dbq.=$userarr[$ucnt] . "='" . $_POST[$userarr[$ucnt]] . "',";
  }
  $userup="UPDATE users SET " . $dbq . $pwcng . "allow='" . $codecs . "' WHERE name='" . $_POST['exten'] . "'";
//  print $userup . "\n";
  pg_query($db,$userup);

  if (! isset($agi)) {
    require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
    $agi=new AGI_AsteriskManager();
    $agi->connect("127.0.0.1","admin","admin");
  }
  $agi->command("sip prune realtime peer " . $_POST['exten']);
  $agi->command("sip prune realtime user " . $_POST['exten']);
  if (($_POST['ZAPLine'] == "0") && ($_POST['IAXLine'] == "0") && ($_POST['H323Line'] == "0") && ($_POST['FWDU'] == "0")) {
    $agi->command("sip show peer " . $_POST['exten'] . " load");
  }
  if ($PTYPE == "SNOM") {
    $agi->command("sip notify reconfig-snom " . $_POST['exten']);
  } else if (($PTYPE == "POLYCOM") || (substr($PTYPE,0,2) == "IP")) {
    $agi->command("sip notify reboot-polycom " . $_POST['exten']);
  }
  $agi->disconnect();
}

$qgetdata=pg_query($db,"SELECT * FROM features WHERE exten='" . $_POST['exten'] . "'");

$qconsdata=pg_query($db,"SELECT context,count FROM console WHERE mailbox = '" . $_POST['exten'] . "'");

$qgetudata=pg_query($db,"SELECT nat,dtmfmode,fullname,email,canreinvite,qualify,password,allow,callgroup,pickupgroup,
                                insecure,h323permit,h323gkid,h323prefix,h323neighbor,ipaddr,language,secret,usertype,
                                activated,simuse,tariff,t38pt_udptl,
                                case when (encryption_taglen = '32') then encryption||',32bit' else encryption end
                              FROM users WHERE name='" . $_POST['exten'] . "'");


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

$origdata=pg_fetch_array($qgetdata,0,PGSQL_ASSOC);
unset($origdata['id']);
unset($origdata['exten']);

$bgrpq=pg_query($db,"SELECT value FROM astdb WHERE key='BGRP' AND family='" . $_POST['exten'] . "'");
list($origdata['bgrp'])=pg_fetch_array($bgrpq,0,PGSQL_NUM);

$lsysgetconf=pg_query($db,"SELECT astdb.key,astdb.value FROM  astdb 
 LEFT OUTER JOIN astdb AS exten ON 
   (astdb.family=exten.value AND exten.key='SNOMMAC' AND astdb.family != '' AND exten.family='" . $_POST['exten'] . "') 
  WHERE astdb.family=exten.value AND astdb.family='" . $origdata['snommac'] . "'");

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
    if (($origdata["snommac"] != "") && ($origdata["ptype"] == "LINKSYS")) {
      $curval="LSYS" . $lsysconf[$lval];
      if ($$curval != "") {
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $origdata["snommac"] . "','" . $lsysconf[$lval] . "','" . $$curval . "')");
      } else {
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $origdata["snommac"] . "','" . $lsysconf[$lval] . "','" . $lsysdef[$lsysconf[$lval]] . "')");
      }
      $reload=1; 
    } else {
      $lsysdata[$lsysconf[$lval]]=$lsysdef[$lsysconf[$lval]];
    }
  }
}

if ($reload) {
  $lsysgetconf=pg_query($db,"SELECT astdb.key,astdb.value FROM  astdb LEFT OUTER JOIN astdb AS exten ON (astdb.family=exten.value AND exten.key='SNOMMAC' AND astdb.family != '' AND exten.family='" . $_POST['exten'] . "') WHERE astdb.family=exten.value AND astdb.family='" . $origdata['snommac'] . "'");
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


$dbdef=array("access","authaccess","record","alock","fwdu","novmail");

$def['access']="Context";
$def['authaccess']="AuthContext";
$def['record']="DEFRECORD";
$def['alock']="DEFALOCK";
$def['fwdu']="REMDEF";
$def['novmail']="DEFNOVMAIL";

for($dcnt=0;$dcnt < count($dbdef);$dcnt++) {
  if ($origdata[$dbdef[$dcnt]] == "") {
    $origdata[$dbdef[$dcnt]]=getdefval($def[$dbdef[$dcnt]]);
  }
}

if ($origdata["roampass"] == "") {
  $origdata["roampass"]=newrpin($_POST['exten']);
}

if ($origdata["tout"] == "") {
  $origdata["tout"]=getdefval("Timeout");
  if ($origdata["tout"] == "0") {
    $origdata["tout"]=21;
  }
}

if (isset($rndpin)){%>
  <SCRIPT>
    alert("New Password: <%print $rndpin;%>");
  </SCRIPT>
<%
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
     <INPUT TYPE=TEXT NAME=ALTC VALUE="<%if ($origdata["altc"] != "") {print $origdata["altc"];}%>">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES6') ONMOUSEOUT=myHint.hide()><%print _("Office/Location Shown On Extension List");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=OFFICE VALUE="<%if ($origdata["office"] != "") {print $origdata["office"];}%>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES3') ONMOUSEOUT=myHint.hide()><%print _("Call Forward Immediate");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFIM VALUE="<%if ($origdata["cfim"] != "0") {print $origdata["cfim"];}%>">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES4') ONMOUSEOUT=myHint.hide()><%print _("Call Forward On Busy");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFBU VALUE="<%if ($origdata["cfbu"] != "0") {print $origdata["cfbu"];}%>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES5') ONMOUSEOUT=myHint.hide()><%print _("Call Forward On No Answer");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFNA VALUE="<%if ($origdata["cfna"] != "0") {print $origdata["cfna"];}%>">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES6') ONMOUSEOUT=myHint.hide()><%print _("Call Forward On FAX");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFFAX VALUE="<%if ($origdata["cffax"] != "0") {print $origdata["cffax"];}%>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES7') ONMOUSEOUT=myHint.hide()><%print _("Ring Timeout");%></TD>
  <TD><INPUT TYPE=TEXT NAME=TOUT VALUE="<%print $origdata["tout"];%>"></TD>
</TR>
<%
  if ($SUPER_USER == 1) {
%>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES10') ONMOUSEOUT=myHint.hide() ALIGN=LEFT><%print _("Caller Group (0-63)");%></TD>
  <TD><INPUT TYPE=TEXT NAME=callgroup VALUE="<%print $cgroup;%>"></TD></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES11') ONMOUSEOUT=myHint.hide()><%print _("Pickup Group(s)");%></TD>
  <TD><INPUT TYPE=TEXT NAME=pickupgroup VALUE="<%print $pgroup;%>"></TD></TR>
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
        <OPTION VALUE="on"<%if ($origdata["cdnd"] == "1") {print " SELECTED";}%>><%print _("On");%> 
        <OPTION VALUE="-1"<%if ($origdata["cdnd"] == "-1") {print " SELECTED";}%>><%print _("Disabled");%>
     </SELECT>
</TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD onmouseover=myHint.show('ES8') ONMOUSEOUT=myHint.hide()><%print _("Call Waiting");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=WAIT <%if ($origdata["wait"] == "1") {print "CHECKED";}%>></TD>
</TR>
</TABLE>
</DIV>
<DIV id=auth CLASS=formpart>
<TABLE CLASS=formtable>

<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES12') ONMOUSEOUT=myHint.hide()><%print _("Extension Permission");%></TD>
  <TD>
    <SELECT NAME=ACCESS>
<%
      for($i=0;$i<6;$i++) {
        print "<OPTION VALUE=" . $i;
        if ($i == $origdata["access"]) {
          print " SELECTED";
        }
        print ">" . $context[$i] . "</OPTION>\n";
      }
%>
    </SELECT>
</TR>

<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES13') ONMOUSEOUT=myHint.hide()><%print _("Auth Extension Permission");%></TD>
  <TD>
    <SELECT NAME=AUTHACCESS>
<%
      for($i=0;$i<6;$i++) {
        print "<OPTION VALUE=" . $i;
        if ($i == $origdata["authaccess"]) {
          print " SELECTED";
        }
        print ">" . $context[$i] . "</OPTION>\n";
      }
%>
    </SELECT>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES19') ONMOUSEOUT=myHint.hide()><%print _("After Hours Extension Permission");%></TD>
  <TD>
    <SELECT NAME=ALOCK>
<%
      for($i=0;$i<6;$i++) {
        print "<OPTION VALUE=" . $i;
        if ($i == $origdata["alock"]) {
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
  <TD><INPUT TYPE=INPUT NAME=PURSE VALUE="<%print $origdata["purse"];%>"></TD>
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
    <INPUT TYPE=BUTTON VALUE="Click To See PIN" ONCLICK="javascript:alert('The PIN Code Is\n\t<%print $origdata["roampass"];%>')">
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
        if ($getbgdata[0] == $origdata["bgrp"]) {
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
      $dgrpq="SELECT DISTINCT dgroup FROM features WHERE dgroup != '' AND dgroup IS NOT NULL";
%>
     <OPTION VALUE=""><%print _("Select Existing Group/Add New Group Bellow");%></OPTION>
<%
      $dgroups=pg_query($db,$dgrpq);
      $dgnum=pg_num_rows($dgroups);

      for($i=0;$i<$dgnum;$i++){
        $getdgdata=pg_fetch_array($dgroups,$i);
        print "<OPTION VALUE=\"" . $getdgdata[0] . "\"";
        if ($getdgdata[0] == $origdata["dgroup"]) {
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
  <INPUT TYPE=HIDDEN NAME="DGROUP" VALUE="<%print $origdata["dgroup"];%>">
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
      <OPTION VALUE="mISDN/g:out/"<%if ($origdata["trunk"] == "mISDN/g:out/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 1");%></OPTION>
      <OPTION VALUE="mISDN/g:out2/"<%if ($origdata["trunk"] == "mISDN/g:out2/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 2");%></OPTION>
      <OPTION VALUE="mISDN/g:out3/"<%if ($origdata["trunk"] == "mISDN/g:out3/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 3");%></OPTION>
      <OPTION VALUE="mISDN/g:out4/"<%if ($origdata["trunk"] == "mISDN/g:out4/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 4");%></OPTION>
      <OPTION VALUE="DAHDI/r1/"<%if (($origdata["trunk"] == "Zap/r1/") || ($origdata["trunk"] == "DAHDI/r1/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 1");%></OPTION>
      <OPTION VALUE="DAHDI/r2/"<%if (($origdata["trunk"] == "Zap/r2/") || ($origdata["trunk"] == "DAHDI/r2/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 2");%></OPTION>
      <OPTION VALUE="DAHDI/r3/"<%if (($origdata["trunk"] == "Zap/r3/") || ($origdata["trunk"] == "DAHDI/r3/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 3");%></OPTION>
      <OPTION VALUE="DAHDI/r4/"<%if (($origdata["trunk"] == "Zap/r4/") || ($origdata["trunk"] == "DAHDI/r4/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 4");%></OPTION>
      <OPTION VALUE="WOOMERA/g1/"<%if ($origdata["trunk"] == "WOOMERA/g1/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 1");%></OPTION>
      <OPTION VALUE="WOOMERA/g2/"<%if ($origdata["trunk"] == "WOOMERA/g2/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 2");%></OPTION>
      <OPTION VALUE="WOOMERA/g3/"<%if ($origdata["trunk"] == "WOOMERA/g3/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 3");%></OPTION>
      <OPTION VALUE="WOOMERA/g4/"<%if ($origdata["trunk"] == "WOOMERA/g4/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 4");%></OPTION>
<%
      for($ipcnt=0;$ipcnt < count($ipgw);$ipcnt++) {
        print "<OPTION VALUE=\"" . $ipgw[$ipcnt]['gw'] . "\"";
        if ($origdata['trunk'] == $ipgw[$ipcnt]['gw']) {
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
  <INPUT TYPE=HIDDEN NAME=TRUNK VALUE="<%print $origdata["trunk"];%>">
<%
}
%>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES23') ONMOUSEOUT=myHint.hide()><%print _("Early Fax Detect");%></TD>
  <TD>
    <SELECT NAME=EFAXD> 
      <OPTION VALUE=0<%if ($origdata["efaxd"] == "0") {print " SELECTED";}%>>No Fax Detect</VALUE>
      <OPTION VALUE=1<%if ($origdata["efaxd"] == "1") {print " SELECTED";}%>>Incoming Fax Detect</VALUE>
      <OPTION VALUE=2<%if ($origdata["efaxd"] == "2") {print " SELECTED";}%>>In And Out</VALUE>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES18') ONMOUSEOUT=myHint.hide()><%print _("Outbound CLI");%></TD>
  <TD><INPUT TYPE=TEXT NAME=CLI VALUE="<%if ($origdata["cli"] != "0") {print $origdata["cli"];}%>"></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES19') ONMOUSEOUT=myHint.hide()><%print _("Withhold Presentation");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NOPRES <%if ($origdata["nopres"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES19') ONMOUSEOUT=myHint.hide()><%print _("Call Recording");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=RECORD <%if ($origdata["record"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES19') ONMOUSEOUT=myHint.hide()><%print _("Enable Dial Features (Transfer/One Touch Recording)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DFEAT <%if ($origdata["dfeat"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES20') ONMOUSEOUT=myHint.hide()><%print _("Enable Voice Mail");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NOVMAIL <%if ($origdata["novmail"] == "0") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES21') ONMOUSEOUT=myHint.hide()><%print _("Lock Extension");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=Locked <%if ($origdata["locked"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES22') ONMOUSEOUT=myHint.hide()><%print _("Enable Fax To Mail");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=FAXMAIL <%if ($origdata["faxmail"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color<%print ($cnt % 2) + 1;$cnt++;%>>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES8') ONMOUSEOUT=myHint.hide()><%print _("Distinctive Ring Support (Some Phones)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DRING <%if ($origdata["dring"] == "1") {print "CHECKED";}%>></TD>
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
  <TD><INPUT TYPE=TEXT NAME=SNOMMAC VALUE="<%print $origdata["snommac"];%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES25') ONMOUSEOUT=myHint.hide()><%print _("Registration Domain (SRV/IP)");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=REGISTRAR VALUE="<%print $origdata["registrar"];%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES25') ONMOUSEOUT=myHint.hide()><%print _("Phone Manufacturer/Model.");%></TD>
  <TD>
    <SELECT NAME=PTYPE>
      <OPTION VALUE=SNOM<%if ($origdata["ptype"] == "SNOM") {print " SELECTED";}%>>Snom
      <OPTION VALUE=SNOM_M9<%if ($origdata["ptype"] == "SNOM_M9") {print " SELECTED";}%>>Snom M9
      <OPTION VALUE=LINKSYS<%if ($origdata["ptype"] == "LINKSYS") {print " SELECTED";}%>>Linksys/Audiocodes MP-202
      <OPTION VALUE=POLYCOM<%if ($origdata["ptype"] == "POLYCOM") {print " SELECTED";}%>>Polycom 300/301/320/330/430
      <OPTION VALUE=IP_500<%if ($origdata["ptype"] == "IP_500") {print " SELECTED";}%>>Polycom 500/501/550
      <OPTION VALUE=IP_600<%if ($origdata["ptype"] == "IP_600") {print " SELECTED";}%>>Polycom 600
      <OPTION VALUE=IP_601<%if ($origdata["ptype"] == "IP_601") {print " SELECTED";}%>>Polycom 601/650
      <OPTION VALUE=IP_4000<%if ($origdata["ptype"] == "IP_4000") {print " SELECTED";}%>>Polycom 4000
      <OPTION VALUE=CISCO<%if ($origdata["ptype"] == "YEALINK") {print " SELECTED";}%>>Yealink
      <OPTION VALUE=CISCO<%if ($origdata["ptype"] == "CISCO") {print " SELECTED";}%>>Cisco 79XX
      <OPTION VALUE=OTHER<%if ($origdata["ptype"] == "OTHER") {print " SELECTED";}%>>Other
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TH COLSPAN=2>Snom Settings</TH></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES24') ONMOUSEOUT=myHint.hide()><%print _("VLAN ID");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=VLAN VALUE="<%print $origdata["vlan"];%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES26') ONMOUSEOUT=myHint.hide()><%print _("Lock Phone Settings (Snom's Only)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=SNOMLOCK <%if ($origdata["snomlock"] == "1") {print "CHECKED";}%>></TD>
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
  <TD><INPUT TYPE=CHECKBOX NAME=POLYDIRLN <%if ($origdata["polydirln"] == "1") {print "CHECKED";}%>></TD>
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
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Current IP Address.") . "<BR>" . _("This Must Be Set And Reachable From Your Browser To Initialize The Phone Correctly.");%></TD>
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
  <INPUT TYPE=HIDDEN NAME=SNOMMAC VALUE="<%print $origdata["snommac"];%>">
  <INPUT TYPE=HIDDEN NAME=REGISTRAR VALUE="<%print $origdata["registrar"];%>">
  <INPUT TYPE=HIDDEN NAME=PTYPE VALUE="<%print $origdata["ptype"];%>">
  <INPUT TYPE=HIDDEN NAME=VLAN VALUE="<%print $origdata["vlan"];%>">
  <INPUT TYPE=HIDDEN NAME=SNOMLOCK VALUE="<%if ($origdata["snomlock"] == "1") {print "on";}%>">
  <INPUT TYPE=HIDDEN NAME=POLYDIRLN VALUE="<%if ($origdata["polydirln"] == "1") {print "on";}%>">
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
  <TD onmouseover=myHint.show('ES29') ONMOUSEOUT=myHint.hide()>Console Group</TD>
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
  <TD><INPUT TYPE=CHECKBOX NAME=CRMPOP <%if ($origdata["crmpop"] == "1") {print "CHECKED";}%>></TD>
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
      <OPTION VALUE="1"<%if ($origdata["fwdu"] == "1") { print " SELECTED";}%>>Default</OPTION>
      <OPTION VALUE="mISDN/g:fwd/"<%if ($origdata["fwdu"] == "mISDN/g:fwd/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Forward Group");%></OPTION>
      <OPTION VALUE="mISDN/g:out/"<%if ($origdata["fwdu"] == "mISDN/g:out/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 1");%></OPTION>
      <OPTION VALUE="mISDN/g:out2/"<%if ($origdata["fwdu"] == "mISDN/g:out2/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 2");%></OPTION>
      <OPTION VALUE="mISDN/g:out3/"<%if ($origdata["fwdu"] == "mISDN/g:out3/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 3");%></OPTION>
      <OPTION VALUE="mISDN/g:out4/"<%if ($origdata["fwdu"] == "mISDN/g:out4/") { print " SELECTED";}%>><%print _("Linux Modular ISDN Group 4");%></OPTION>
      <OPTION VALUE="DAHDI/r1/"<%if (($origdata["fwdu"] == "Zap/r1/") || ($origdata["fwdu"] == "DAHDI/r1/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 1");%></OPTION>
      <OPTION VALUE="DAHDI/r2/"<%if (($origdata["fwdu"] == "Zap/r2/") || ($origdata["fwdu"] == "DAHDI/r2/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 2");%></OPTION>
      <OPTION VALUE="DAHDI/r3/"<%if (($origdata["fwdu"] == "Zap/r3/") || ($origdata["fwdu"] == "DAHDI/r3/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 3");%></OPTION>
      <OPTION VALUE="DAHDI/r4/"<%if (($origdata["fwdu"] == "Zap/r4/") || ($origdata["fwdu"] == "DAHDI/r4/")) { print " SELECTED";}%>><%print _("Digium Trunk Group 4");%></OPTION>
      <OPTION VALUE="WOOMERA/g1/"<%if ($origdata["fwdu"] == "WOOMERA/g1/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 1");%></OPTION>
      <OPTION VALUE="WOOMERA/g2/"<%if ($origdata["fwdu"] == "WOOMERA/g2/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 2");%></OPTION>
      <OPTION VALUE="WOOMERA/g3/"<%if ($origdata["fwdu"] == "WOOMERA/g3/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 3");%></OPTION>
      <OPTION VALUE="WOOMERA/g4/"<%if ($origdata["fwdu"] == "WOOMERA/g4/") { print " SELECTED";}%>><%print _("Woomera Trunk Group 4");%></OPTION>
<%
      for($ipcnt=0;$ipcnt < count($ipgw);$ipcnt++) {
        print "<OPTION VALUE=\"" . $ipgw[$ipcnt]['gw'] . "\"";
        if ($origdata['fwdu'] == $ipgw[$ipcnt]['gw']) {
          print " SELECTED";
        }
        print ">" . $ipgw[$ipcnt]['name'] . "</OPTION>\n";
      }
%>
    </SELECT>
  </TD>
</TR> <TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES33') ONMOUSEOUT=myHint.hide()><%print _("Use IAX As VOIP Protocol");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=IAXLine <%if ($origdata["iaxline"] == "1") {print "CHECKED";}%>></TD>
</TR> <TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES49') ONMOUSEOUT=myHint.hide()><%print _("Use H323 As VOIP Protocol (See H.323 Settings Bellow)")%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=H323Line <%$rcnt++;if ($origdata["h323line"] == "1") {print "CHECKED";}%>></TD>
</TR> <TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES32') ONMOUSEOUT=myHint.hide()><%print _("Exclude From LCR (VOIP/GSM)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NOVOIP <%if ($origdata["novoip"] == "1") {print "CHECKED";}%>></TD>
</TR>
</TABLE>
</DIV>
<%
} else {
%>
  <INPUT TYPE=HIDDEN NAME=NOVOIP VALUE="<%if ($origdata["novoip"] == "1") {print "on";}%>">
  <INPUT TYPE=HIDDEN NAME=IAXLine VALUE="<%if ($origdata["iaxline"] == "1") {print "on";}%>">
  <INPUT TYPE=HIDDEN NAME=H323Line VALUE="<%if ($origdata["h323line"] == "1") {print "on";}%>">
  <INPUT TYPE=HIDDEN NAME=FWDU VALUE="<%print $origdata["fwdu"];%>">
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
  <TD onmouseover=myHint.show('ES39') ONMOUSEOUT=myHint.hide()><%print _("SRTP Encryption");%></TD>
  <TD>
    <SELECT NAME=encryption>
      <OPTION VALUE="no">None</OPTION>
      <OPTION VALUE="yes"<%if ($encryption == "yes") {print " SELECTED";}%>><%print _("Enforce (80bit Auth Tag)");%></OPTION>
      <OPTION VALUE="yes,32bit"<%if ($encryption == "yes,32bit") {print " SELECTED";}%>><%print _("Enforce (32bit Auth Tag)");%></OPTION>
      <OPTION VALUE="try"<%if ($encryption == "try") {print " SELECTED";}%>><%print _("Attempt (80bit Auth Tag)");%></OPTION>
      <OPTION VALUE="try,32bit"<%if ($encryption == "try,32bit") {print " SELECTED";}%>><%print _("Attempt (32bit Auth Tag)");%></OPTION>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES37') ONMOUSEOUT=myHint.hide()><%print _("Allow Peer To Peer Connections (Reinvite)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=canreinvite <%if ($canreinvite == "yes") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES39') ONMOUSEOUT=myHint.hide()><%print _("Send Nat Keep Alive Packets");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=qualify <%if ($qualify == "yes") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES39') ONMOUSEOUT=myHint.hide()><%print _("Pass DDI To Extension");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DDIPASS <%if ($origdata["ddipass"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover=myHint.show('ES39') ONMOUSEOUT=myHint.hide()><%print _("Allow T.38 Support");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=t38pt_udptl <%if ($t38pt_udptl != "no") {print "CHECKED";}%>></TD>
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
  <TD><INPUT TYPE=TEXT NAME=ZAPLine VALUE="<%print $origdata["zapline"];%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES46') ONMOUSEOUT=myHint.hide()><%print _("Signalling Used For ZAP Channel");%><BR></TD>
  <TD><SELECT NAME=ZAPProto>
    <OPTION VALUE="fxo_ks"<%if ($origdata["zapproto"] == "fxo_ks") {print " SELECTED";}%>><%print _("Kewl Start");%></OPTION>
    <OPTION VALUE="fxo_ls"<%if ($origdata["zapproto"] == "fxo_ls") {print " SELECTED";}%>><%print _("Loop Start");%></OPTION>
    <OPTION VALUE="fxo_gs"<%if ($origdata["zapproto"] == "fxo_gs") {print " SELECTED";}%>><%print _("Ground Start");%></OPTION>
  </SELECT></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES47') ONMOUSEOUT=myHint.hide()><%print _("RX Gain");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=ZAPRXGain VALUE="<%print $origdata["zaprxgain"];%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES48') ONMOUSEOUT=myHint.hide()><%print _("TX Gain");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=ZAPTXGain VALUE="<%print $origdata["zaptxgain"];%>"></TD>
</TR>
</TABLE>
</DIV>
<DIV id=h323 CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES41') ONMOUSEOUT=myHint.hide()><%print _("Gatekeeper IP") . "<BR>0.0.0.0 " . _("For Any IP Or Blank To Deny Access");%></TD>
  <TD><INPUT TYPE=TEXT NAME=h323permit VALUE="<%print $h323permit;%>"></TD></TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES42') ONMOUSEOUT=myHint.hide()><%print _("Received Prefix");%></TD>
  <TD><INPUT TYPE=TEXT NAME=h323prefix VALUE="<%print $h323prefix;%>"></TD></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES43') ONMOUSEOUT=myHint.hide()><%print _("Gatekeeper ID");%></TD>
  <TD><INPUT TYPE=TEXT NAME=h323gkid VALUE="<%print $h323gkid;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES50') ONMOUSEOUT=myHint.hide()><%print _("Trusted Neighbour");%><BR></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=h323neighbor <%if ($h323neighbor) {print "CHECKED";}%>></TD>
</TR>
</TABLE>
</DIV>
<%
} else {
%>
  <INPUT TYPE=HIDDEN NAME=ZAPProto VALUE="<%print $origdata["zapproto"];%>">
  <INPUT TYPE=HIDDEN NAME=ZAPRXGain VALUE="<%print $origdata["zaprxgain"];%>">
  <INPUT TYPE=HIDDEN NAME=ZAPTXGain VALUE="<%print $origdata["zaptxgain"];%>">
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
    if ($origdata["sring0"] == $ring) {
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
    if ($origdata["sring1"] == $ring) {
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
    if ($origdata["sring2"] == $ring) {
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
    if ($origdata["sring3"] == $ring) {
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
