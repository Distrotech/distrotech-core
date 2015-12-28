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
if (!isset($_SESSION['auth'])) {
  exit;
}

include "../cdr/auth.inc";
include "../cdr/setupdef.inc";

if (isset($_COOKIE[session_name()])) {
  setcookie(session_name(), $_COOKIE[session_name()], time() + 3600, "/");
}

$snet["auto"]=_("Auto Negotiation");
$snet["10half"]=_("10 Mbit Half Duplex");
$snet["10full"]=_("10 Mbit Full Duplex");
$snet["100half"]=_("100 Mbit Half Duplex");
$snet["100full"]=_("100 Mbit Full Duplex");

$vadmindef["AutoVLAN"]="150";
$vadmindef["AutoStart"]="01";
$vadmindef["AutoEnd"]="90";
$vadmindef["SnomNet"]="100full";

$framing['cas']="cas - d4/sf/superframe";
$framing['ccs']="ccs - esf";
    
$coding['ami']="ami";
$coding['hdb3']="hdb3 - b8zs";
  
$lbo['0']="0 db (CSU) / 0-133 feet (DSX-1)";
$lbo['1']="133-266 feet (DSX-1)";
$lbo['2']="266-399 feet (DSX-1)";
$lbo['3']="399-533 feet (DSX-1)";
$lbo['4']="533-655 feet (DSX-1)";
$lbo['5']="-7.5db (CSU)";
$lbo['6']="-15db (CSU)";
$lbo['7']="-22.5db (CSU)";

$qgetdata=pg_query($db,"SELECT key,value FROM astdb WHERE family='Setup'");
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  $autovars[$getdata[0]]=$getdata[1];
}
while(list($defkey,$defval) = each($vadmindef)) {
  if (!isset($autovars[$defkey])) {
    pg_query("INSERT INTO astdb (family,key,value) VALUES ('Setup','" . $defkey . "','" . $defval . "')");
    $autovars[$defkey]=$defval;
  }
}

function voipmenu($vmendata,$tabnme) {
  global $col,$vdiscrip,$voiplabel,$voipcb,$voiprcb,$autovars,$vlist,$voipcon,$context,$authlev,$snet,$coding,$lbo,$framing;

  print "<DIV id=" . $tabnme . " CLASS=formpart>\n  <TABLE WIDTH=100% cellspacing=0 cellpadding=0>\n";
  while(list($voipkey,$vdiscrip)=each($vmendata)) {
    if ($vdiscrip == "") {
      continue;
    }
    $col++;
    if ($voiplabel[$voipkey] != "") {
      print "    <TR CLASS=list-color" . (($col % 2) + 1)  . ">\n      <TH COLSPAN=2 CLASS=heading-body2>";
      print $voiplabel[$voipkey];
      print "</TH>\n    </TR>\n";
      $col++;
    }
    print "    <TR CLASS=list-color" . (($col % 2) + 1)  . ">\n      <TD WIDTH=50%>" . $vdiscrip . "</TD>\n      <TD>";
    if ($voipcb[$voipkey]) {
      print "<INPUT TYPE=CHECKBOX NAME=\"" . $voipkey . "\"";
      if ($autovars[$voipkey] == "1") {
        print " CHECKED";
      }
      print ">";
    } else if ($voiprcb[$voipkey]) {
      print "<INPUT TYPE=CHECKBOX NAME=\"" . $voipkey . "\"";
      if ($autovars[$voipkey] == "0") {
        print " CHECKED";
      }
      print ">";
    } else if ($voipkey == "AutoVLAN") {
      print "<SELECT NAME=\"AutoVLAN\">\n";
      print "        <OPTION VALUE=\"1\">None</OPTION>\n";
      while(list($vlanid,$vlandiscrip)=each($vlist)) {
        print "        <OPTION VALUE=\"" . $vlanid . "\"";
        if ($autovars[$voipkey] == $vlanid) {
          print " SELECTED";
        }
        print ">" . $vlandiscrip . " (" . $vlanid . ")</OPTION>\n";
      }
      print "      </SELECT>";
    } else if ($voipkey == "LINEAUTH") {
      print "<SELECT NAME=\"" . $voipkey . "\">\n";
      for($acnt=1;$acnt <= count($authlev);$acnt++) {
        print "        <OPTION VALUE=\"" . $acnt . "\"";
        if ($autovars[$voipkey] == $acnt) {
          print " SELECTED";
        }
        print ">" . $authlev[$acnt] . "</OPTION>\n";
      }
      print "      </SELECT>";
    } else if ($voipkey == "PRIcoding") {
      print "<SELECT NAME=\"" . $voipkey . "\">\n";
      while(list($prikey,$prival) = each($coding)) {
        print "        <OPTION VALUE=\"" . $prikey . "\"";
        if ($autovars[$voipkey] == $prikey) {
          print " SELECTED";
        }
        print ">" . $prival . "</OPTION>\n";
      }
      print "      </SELECT>";
    } else if ($voipkey == "PRIframing") {
      print "<SELECT NAME=\"" . $voipkey . "\">\n";
      while(list($prikey,$prival) = each($framing)) {
        print "        <OPTION VALUE=\"" . $prikey . "\"";
        if ($autovars[$voipkey] == $prikey) {
          print " SELECTED";
        }
        print ">" . $prival . "</OPTION>\n";
      }
      print "      </SELECT>";
    } else if ($voipkey == "PRIlbo") {
      print "<SELECT NAME=\"" . $voipkey . "\">\n";
      while(list($prikey,$prival) = each($lbo)) {
        print "        <OPTION VALUE=\"" . $prikey . "\"";
        if ($autovars[$voipkey] == $prikey) {
          print " SELECTED";
        }
        print ">" . $prival . "</OPTION>\n";
      }
      print "      </SELECT>";
    } else if ($voipkey == "SnomNet") {
      print "<SELECT NAME=\"" . $voipkey . "\">\n";
      while(list($netkey,$netval) = each($snet)) {
        print "        <OPTION VALUE=" . $netkey;
        if ($autovars[$voipkey] == $netkey) {
          print " SELECTED";
        }
        print ">" . $netval . "</OPTION>\n";
      }
      print "      </SELECT>";
    } else if ($voipcon[$voipkey]) {
      print "<SELECT NAME=\"" . $voipkey . "\">\n";
      for($concnt=0;$concnt < count($context)-1;$concnt++) {
        print "        <OPTION VALUE=\"" . $concnt . "\"";
        if ($autovars[$voipkey] == $concnt) {
          print " SELECTED";
        }
        print ">" . $context[$concnt] . "</OPTION>\n";
      }
      print "      </SELECT>";
    } else {
      print "<INPUT TYPE=TEXT SIZE=40 NAME=\"" . $voipkey . "\" VALUE=\"" . $autovars[$voipkey] . "\">";
    }
    print "</TD>\n    </TR>\n";
  }
  print "  </TABLE>\n</DIV>\n";
}

$extenvar=array(
	"Context" => "Default Extension Permision",
	"AuthContext" => "Default Auth. Extension Permision",
	"DEFALOCK" => "After Hours Extension Permision",
	"LINEAUTH" => "Valid Line Authentication",
	"SnomNet" => "Snom Network Port Speed/Duplex",
	"AutoVLAN" => "VLAN",
	"DefaultPrefix" => "Default Extension Prefix (2 Digit Dialing)",
	"AutoStart" => "Start Exten.",
	"AutoEnd" => "End Exten.",
	"Timeout" => "Default Ring Timeout",
	"AutoAuth" => "Require Authorisation",
	"AutoLock" => "Lock Settings (Snom)",
	"DEFNOVMAIL" => "Enable Voice Mail By Default",
	"DEFRECORD" => "Record Calls By Default",
	);

$voipvar=array(
	"LocalFwd" => "Calls To Internal Extensions Follow Forward Rules",
	"UNKDEF" => "Hangup Calls To Unknown Numbers/DDI",
	"mISDNports" => "Isdn Ports To Use (In And Out)",
	"mISDNimm" => "ISDN Immeadiate Routeing (No MSN/DDI)",
	"mISDNrr" => "Use Round Robin Routing",
	"AutoCLI" => "Allow Automatic Setting Of CLI (DDI Required)",
	"AreaCode" => "Local Area Code",
	"ExCode" => "Local Exchange Prefix",
        "PRIlbo" => "Line Build Out",
	"PRIframing" => "PRI Framing (E1 - T1)",
	"PRIcoding" => "Coding (E1 - T1)",
	"PRIcrc4" => "CRC4 Checking (E1 Only)"
	);

$attenvar=array(
	"AATimeout" => "Queue Timeout Checked Every 18s",
	"AANext" => "Auto Attendant Mailbox/Forward On No Agent/Timeout",
	"AADelay" => "IVR Delay Between Digits",
	"AANOPROMPT" => "Disable Default Auto Attendant Prompts",
	"AAMOH" => "Music On Hold When Calling Reception",
	"AAREC" => "Record Inbound Calls"
	);

$voipcb=array(
	"AutoAuth" => 1,
	"AutoLock" => 1,
	"LocalFwd" => 1,
	"mISDNimm" => 1,
	"mISDNrr" => 1,
	"DEFRECORD" => 1,
	"AutoCLI" => 1,
	"UNKDEF" => 1,
	"AANOPROMPT" => 1,
	"AAMOH" => 1,
	"AAREC" => 1,
        "PRIcrc4" => 1
	);

$voiplabel=array(
	"LocalFwd" => "Call FLow",
	"mISDNports" => "ISDN Settings",
	"AreaCode" => "Location",
	"PRIlbo" => "PRI Default Span Settings"
	);

$voiprcb=array(
	"DEFNOVMAIL" => 1
	);

$voipcon=array(
	"Context" => 1,
	"AuthContext" => 1,
	"DEFALOCK" => 1,
	);

$context[0]=_("Internal Extensions");
$context[1]=_("Local PSTN Calls");
$context[2]=_("Long Distance PSTN Calls");
$context[3]=_("Cellular Calls");
$context[4]=_("Premium Calls");
$context[5]=_("International Calls");
$context[6]=_("No IP Routing");

$authlev[3]=_("Allow Only VM PW. Not The Same As Line PW. Or Exten.");
$authlev[2]=_("Allow VM PW. Or Line PW. Not The Same As Exten");
$authlev[1]=_("Line PW. That Is Same As Extension And All Bellow");

/*
if (($autovars["Trunk"] != "NONE") && ($autovars["Trunk"] != "mISDN/g:out/")){
  $voipvar["mISDNports"]="";
}
*/

function inprog() {
  if (is_file("/var/spool/apache/htdocs/ns/config/netsentry-sysvars")) {
?>
<CENTER>
<DIV CLASS=content>
<DIV CLASS=list-color2 ID=headcol><DIV CLASS=heading-body>System Reconfigure In Progress</DIV></DIV>
</DIV>
<?php
    return true;
  }
}

  if (inprog()) {
    return;
  }

  $psplit["HN_ADDR"]="Network";
  $discrip["HN_ADDR"]="Hostname";
  $discrip["DOM_ADDR"]="Domain Name";
  $discrip["GW_ADDR"]="Default Gateway";
  $discrip["SMTP_FWD"]="SMTP Gateway";
  $discrip["NTP_SERV"]="NTP Server";

  $psplit["DYN_SERV"]="DynDNS";
  $discrip["DYN_SERV"]="Dynamic DNS Server";
  $discrip["DYN_ZONE"]="Dynamic DNS Zone";
  $discrip["DYN_KEY"]="Dynamic DNS Key";

  $psplit["DNS_SERV1"]="DNS";
  $discrip["DNS_SERV1"]="Primary DNS Server";
  $discrip["DNS_SERV2"]="Secondary DNS Server";
  $discrip["WINS_SERV1"]="Primary WINS Server";
  $discrip["WINS_SERV2"]="Secondary WINS Server";
  $discrip["DNS_MX1"]="Primary MX Server";
  $discrip["DNS_MX2"]="Secondary MX Server";

  $psplit["IP_ADDR"]="Net Int.";
  $discrip["IP_ADDR"]["IP_ADDR"]="IP Address";
  $discrip["IP_ADDR"]["SN_ADDR"]="Subnet Bits";
  $discrip["IP_ADDR"]["IP_SDHCP"]="DHCP Start Address";
  $discrip["IP_ADDR"]["IP_EDHCP"]="DHCP End Address";
//  $discrip["IP_ADDR"]["INT_MAC"]="MAC Address";

  $psplit["VLAN"]="VLAN's";
  $discrip["VLAN"]["IP_ADDRV"]="IP Address";
  $discrip["VLAN"]["SN_ADDRV"]="Subnet Bits";
  $discrip["VLAN"]["IP_SDHCPV"]="DHCP Start Address";
  $discrip["VLAN"]["IP_EDHCPV"]="DHCP End Address";

  $psplit["ALIAS"]="Aliases";
  $discrip["ALIAS"]["IP_ADDRA"]="IP Address";
  $discrip["ALIAS"]["SN_ADDRA"]="Subnet Bits";

  $psplit["X509_C"]="X.509";
  $discrip["X509_C"]="X.509 CA Setup (Country)";
  $discrip["X509_ST"]="X.509 CA Setup (Province/State)";
  $discrip["X509_L"]="X.509 CA Setup (City)";
  $discrip["X509_O"]="X.509 CA Setup (Company)";
  $discrip["X509_OU"]="X.509 CA Setup (Division)";
  $discrip["X509_CN"]="X.509 CA Setup (Name)";
  $discrip["X509_EMAIL"]="X.509 CA Setup (Email Address)";

  $psplit["DOMC"]="Fileserver";
  $discrip["DOMC"]="Domain Contoller (Alternativly Member/Server)";
  $discrip["DOM_WG"]="Workgroup/Domain To Join";
  $discrip["NB_NAME"]="Netbios Aliases";
  $discrip["DOM_DC"]="Domain Controlers (If Joining A Domain)";
  $discrip["DOM_ADS"]="ADS Realm (If Joing ADS Domain)";

  $psplit["MDM_CONN"]="Firewall";
  $discrip["MDM_CONN"]="External Device";
  $discrip["MDM_NUM"]="Nuber/Service ID/APN";
  $discrip["MDM_UN"]="Username";
  $discrip["MDM_PW"]="Password";
  $discrip["MDM_MTU"]="MTU";
  
  $oldconf= fopen("/var/spool/apache/htdocs/ns/config/sysvars", "r");
  if ($oldconf) {
    while(!feof($oldconf)) {
      $inconf=fgets($oldconf, 4096);
      if (ereg("([A-Za-z0-9_]+)=\"(.*)\";",$inconf,$data)) {
        $conf[$data[1]]=$data[2];
      } elseif (ereg("([A-Z0-9_]+)\[([0-9]+)\]=\"(.*)\";",$inconf,$data)) {
        $conf[$data[1]][$data[2]]=$data[3];
      }
    }
    fclose($oldconf);
  }
//print "<PRE>";
//print_r($conf);
//print "</PRE>";

if (isset($saved)) {
  $vtemp=array_merge($extenvar,$voipvar,$attenvar);
/*
  $vtemp["Trunk"]="PSTN Trunk";
  if (!isset($mISDNports)) {
    $Trunk="";
  } else if ($mISDNports > 0) {
    $Trunk="mISDN/g:out/";
  } else {
    $Trunk="NONE";
  }
*/
  while(list($voipkey,$vdiscrip)=each($vtemp)) {
    if ($voipcb[$voipkey]) {
      if ($$voipkey == "on") {
        $$voipkey="1";
      } else {
        $$voipkey="0";
      }
    }
    if ($voiprcb[$voipkey]) {
      if ($$voipkey == "on") {
        $$voipkey="0";
      } else {
        $$voipkey="1";
      }
    }
    if ($$voipkey != "") {
      $ud=pg_query("UPDATE astdb SET value= '" . $$voipkey . "' WHERE family='Setup' AND key = '" . $voipkey . "'");
      if (pg_affected_rows($ud) <= 0) {
        pg_query("INSERT INTO astdb (family,key,value) VALUES ('Setup','" . $voipkey . "','" . $$voipkey . "')");
      }
    }
  }
  $islpre=pg_query($db,"SELECT id,value FROM astdb WHERE family='LocalPrefix' AND key='" . $DefaultPrefix . "'");
  if (pg_num_rows($islpre) == 1) {
    $lpredat=pg_fetch_array($islpre,0);
    if ($lpredat[1] != 1) {
      pg_query($db,"UPDATE astdb SET value='1' WHERE id='" . $lpredat[0] . "'");
    }
  } else {
    pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('LocalPrefix','" . $DefaultPrefix . "','1')");
  }
  if ($DOMC == "on") {
    $_POST['DOMC']="0";
    $_POST['OSLEVEL']="65";
    $_POST['DTYPE']="USER";
    $_POST['DOM_DC']="";
    $_POST['DOM_ADS']="";
  } else {
    $_POST['DOMC']="1";
    if ($_POST['DOM_DC'] != "") {
      $_POST['OSLEVEL']="5";
      if ($_POST['DOM_ADS'] != "") {
        $_POST['DTYPE']="ADS";
      } else {
        $_POST['DTYPE']="DOMAIN";
      }
    } else {
      $_POST['OSLEVEL']="65";
      $_POST['DTYPE']="USER";
    }
  }

  $truefalse=array("LCRFROMU","LCRREG","LCRSRTP","LCRVIDEO");
  for($tfcnt=0;$tfcnt < count($truefalse);$tfcnt++) {
    if ($_POST[$truefalse[$tfcnt]] == on) {
      $_POST[$truefalse[$tfcnt]]="true";
    } else {
      $_POST[$truefalse[$tfcnt]]="false";
    }
  }

  if ($_POST['LCRDTMF'] == "on") {
    $_POST['LCRDTMF']="info";
  } else {
    $_POST['LCRDTMF']="rfc2833";
  }

  if (($MDM_CONN == "3G") || ($MDM_CONN == "3GIPW") || ($MDM_CONN == "Dialup") || ($MDM_CONN == "Leased")) {
    $_POST['FWALL_EXT']="Dialup";
  } else if ($_POST['MDM_CONN'] != "") {
    $_POST['FWALL_EXT']=$_POST['MDM_CONN'];
    if ($_POST['EXTPPPOE'] == "on") {
      $_POST['MDM_CONN']="ADSL";
    } else {
      $_POST['MDM_CONN']="Dialup";
    }
  }

  if (($_POST['HN_ADDR'] != $conf["HN_ADDR"]) || ($_POST['DOM_ADDR'] != $conf["DOM_ADDR"])) {
    $_POST['DEL_DNS']="1";
  }

  $newconf=fopen("/var/spool/apache/htdocs/ns/config/netsentry-sysvars","w");
  $intdata=array("IP_ADDR","SN_ADDR","IP_SDHCP","IP_EDHCP","INT_BWIN","INT_BWOUT","INT_MAC","INT_GW","INT_NAME","INT_IFACE");
  $vlandata=array("VLAN","IP_ADDRV","SN_ADDRV","IP_SDHCPV","IP_EDHCPV","INT_BWINV","INT_BWOUTV","INT_GWV","INT_NAMEV","INT_PARV");
  $aliasdata=array("ALIAS","IP_ADDRA","SN_ADDRA","INT_NAMEA");

  $tmp=$intdata;

  for($sub=1;$sub<=count($conf["IP_ADDR"]);$sub++) {
    $tmp=$intdata;
    while(list($key,$val) = each($tmp)) {
      $nval=$val . ":" . $sub;
      if (isset($$nval)) {
        $sval=$$nval;
      } else {
        $sval=$conf[$val][$sub];
      }
      fwrite($newconf,$val . "[" . $sub . "]=\"" . $sval . "\";\n");
    }
  }

  for($sub=1;$sub<=count($conf["VLAN"]);$sub++) {
    $tmp=$vlandata;
    while(list($key,$val) = each($tmp)) {
      $nval=$val . ":" . $sub;
      if (isset($$nval)) {
        $sval=$$nval;
      } else {
        $sval=$conf[$val][$sub];
      }
      fwrite($newconf,$val . "[" . $sub . "]=\"" . $sval . "\";\n");
    }
  }

  for($sub=1;$sub<=count($conf["ALIAS"]);$sub++) {
    $tmp=$aliasdata;
    while(list($key,$val) = each($tmp)) {
      $nval=$val . ":" . $sub;
      if (isset($$nval)) {
        $sval=$$nval;
      } else {
        $sval=$conf[$val][$sub];
      }
      fwrite($newconf,$val . "[" . $sub . "]=\"" . $sval . "\";\n");
    }
  }

  if ($newconf) {
    while(list($key,$val) = each($conf)) {
      if (! is_array($conf[$key])) {
        if (isset($_POST[$key])) {
          fwrite($newconf, $key . "=\"" . $_POST[$key] . "\";\n");
        } else {
          fwrite($newconf,$key . "=\"" . $conf[$key] . "\";\n");
        }
      }
    }
    fclose($newconf);
  }
  if (inprog()) {
    return;
  }
}
?>
<FORM METHOD=POST NAME=confform onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=curdiv VALUE=split0>
<INPUT TYPE=HIDDEN NAME=saved VALUE="">
<CENTER>
<DIV CLASS=content>
<DIV CLASS=list-color2 ID=headcol><DIV CLASS=heading-body>System Reconfigure</DIV></DIV>

<DIV CLASS=list-color1><DIV CLASS=formrow>
<?php
  $splitc=0;
  while (list($skey,$sval) = each($psplit)) {
    print "<DIV CLASS=formselect ID=split" . $splitc . "_but onclick=showdiv('split" . $splitc;
    print "',document.confform) onmouseover=showdiv('split" . $splitc . "',document.confform)>";
    print $sval . "</DIV>\n";
    $splitc++;
  }
?>
<DIV CLASS=formselect ID=exten_but onclick=showdiv('exten',document.confform) onmouseover=showdiv('exten',document.confform)>Voip Ext.</DIV>
<DIV CLASS=formselect ID=voip_but onclick=showdiv('voip',document.confform) onmouseover=showdiv('voip',document.confform)>PBX</DIV>
<DIV CLASS=formselect ID=atten_but onclick=showdiv('atten',document.confform) onmouseover=showdiv('atten',document.confform)>Auto Atten.</DIV>
<DIV CLASS=formselect ID=lcr_but onclick=showdiv('lcr',document.confform) onmouseover=showdiv('lcr',document.confform)>LCR</DIV>
<DIV CLASS=formselect ID=save_but onclick=savereconfchanges() onmouseover=showdiv('save',document.confform)>Save</DIV>
</DIV></DIV>

<?php
  $splitc=0;
  $col=1;
  while(list($key,$val) = each($discrip)) {
    if ($psplit[$key]) {
      if ($splitc > 0) {
        print "  </TABLE>\n</DIV>\n";
      }
      print "<DIV id=split" . $splitc . " CLASS=formpart>\n  <TABLE WIDTH=100% cellspacing=0 cellpadding=0>\n";
      $col=1;
      $splitc++;
    }
    if (is_array($discrip[$key])) {
      for($sub=1;$sub<=count($conf[$key]);$sub++) {
        print "    <TR CLASS=list-color" . (($col % 2) + 1)  . ">\n      <TH COLSPAN=2 CLASS=heading-body2>";
        if ($key == "IP_ADDR") {
          print "<DIV ID=\"ETH:" . $sub . "\">Interface " . $conf["INT_IFACE"][$sub] . " (" . $conf["INT_NAME"][$sub] . ")</DIV>";
        } else if ($key == "VLAN") {
          print "<DIV ID=\"VLAN:" . $sub . "\">VLAN Interface " . $conf["INT_PARV"][$sub] . "." . $conf["VLAN"][$sub] . " (" . $conf["INT_NAMEV"][$sub] . ")</DIV>";
          $vlist[$conf["VLAN"][$sub]]=$conf["INT_NAMEV"][$sub];
        } else if ($key == "ALIAS") {
          print "<DIV ID=\"ALIAS:" . $sub . "\">Alias Interface " . $conf["ALIAS"][$sub] . " (" . $conf["INT_NAMEA"][$sub] . ")</DIV>";
        }
        print "</TH>\n    </TR>\n";
        $col++;
        $tmp=$discrip[$key];
        while(list($key2,$val2) = each($tmp)) {
          print "    <TR CLASS=list-color" . (($col % 2) + 1)  . ">\n      <TD WIDTH=50%>" . $val2 . "</TD>\n      <TD>";
          print "<INPUT TYPE=TEXT SIZE=40 NAME=\"" . $key2 . ":" . $sub . "\" VALUE=\"" . $conf[$key2][$sub] . "\">";
          print "</TD>\n    </TR>\n";
          $col++;
        }
      }
      if ($sub == 1) {
        print "    <TR CLASS=list-color" . (($col % 2) + 1)  . ">\n      <TH COLSPAN=2 CLASS=heading-body2>No Values</TH>\n    </TR>\n";        
      }
    } else {
      print "    <TR CLASS=list-color" . (($col % 2) + 1)  . ">\n      <TD WIDTH=50%>" . $val . "</TD>\n      <TD>";
      if ($key == "DOMC") {
        print "<INPUT TYPE=CHECKBOX NAME=\"" . $key . "\"";
        if ($conf[$key] == "0") {
          print " CHECKED";
        }
        print ">";
      } else if ($key == "MDM_CONN") {
        $phyint=$conf["INT_IFACE"];
        $vlanint=$conf["VLAN"];
        print "<SELECT NAME=\"MDM_CONN\">\n";
        print "        <OPTION VALUE=\"\">None</OPTION>\n";
        for($icnt=1;$icnt<=count($phyint);$icnt++) {
          $int=$phyint[$icnt];
          print "        <OPTION VALUE=\"" . $int . "\"";
          if ($conf["FWALL_EXT"] == $int) {
            print " SELECTED";
          }
          print ">" . $conf["INT_NAME"][$icnt] . " (" . $int . ")</OPTION>\n";
        }
        for($icnt=1;$icnt<=count($vlanint);$icnt++) {
          $int=$conf["INT_PARV"][$icnt] . "." . $vlanint[$icnt];
          print "        <OPTION VALUE=\"" . $int . "\"";
          if ($conf["FWALL_EXT"] == $int) {
            print " SELECTED";
          }
          print ">" . $conf["INT_NAMEV"][$icnt] . " (" . $int . ")</OPTION>\n";
        }
        print "        <OPTION VALUE=\"3G\"";
        if ($conf["MDM_CONN"] == "3G") {
          print " SELECTED";
        }
        print ">3G</OPTION>\n";
        print "        <OPTION VALUE=\"3GIPW\"";
        if ($conf["MDM_CONN"] == "3GIPW") {
          print " SELECTED";
        }
        print ">Sentech My Wireless</OPTION>\n";
        print "        <OPTION VALUE=\"Dialup\"";
        if (($conf["MDM_CONN"] == "Dialup") && ($conf["FWALL_EXT"] == "Dialup")) {
          print " SELECTED";
        }
        print ">Dialup (Depricated)</OPTION>\n";
        print "        <OPTION VALUE=\"Leased\"";
        if ($conf["MDM_CONN"] == "Leased") {
          print " SELECTED";
        }
        print ">Analogue Leased (Depricated)</OPTION>\n";
        print "      </SELECT></TD>\n    </TR>\n";
        $col++;
        print "    <TR CLASS=list-color" . (($col % 2) + 1)  . ">\n      <TD WIDTH=50%>External Device  Is PPPoE (ADSL)</TD>\n      <TD>";
        print "<INPUT TYPE=CHECKBOX NAME=\"EXTPPPOE\"";
        if ($conf["MDM_CONN"] == "ADSL") {
          print " CHECKED";
        }
        print ">";
      } else {
        print "<INPUT TYPE=TEXT SIZE=40 NAME=\"" . $key . "\" VALUE=\"" . $conf[$key] . "\">";
      }
      print "</TD>\n    </TR>\n";
    }
    $col++;
  }
?>
  </TABLE>
</DIV>
<?php
$col=0;
voipmenu($extenvar,"exten");
$col=0;
voipmenu($voipvar,"voip");
$col=0;
voipmenu($attenvar,"atten");
$col=0;
?>

<DIV id=lcr CLASS=formpart>
<TABLE WIDTH=100% cellspacing=0 cellpadding=0>
    <tr class="list-color2">
      <td width="50%">Account</td>
      <td><input size="52" name="LCRAC" value="<?php print $conf["LCRAC"];?>" type="text"></td>
    </tr>
    <tr class="list-color1">
      <td width="50%">Password</td>
      <td><input size="52" name="LCRPW" value="<?php print $conf["LCRPW"];?>" type="text"></td>
    </tr>
    <tr class="list-color2">
      <td width="50%">Server</td>
      <td><input size="52" name="LCRSRV" value="<?php print $conf["LCRSRV"];?>" type="text"></td>
    </tr>
    <tr class="list-color1">
      <td width="50%">Protocol</td>
      <td><SELECT NAME=LCRPROTO><OPTION VALUE="SIP">SIP</OPTION>
      <OPTION VALUE="IAX"<?php if ($conf['LCRPROTO'] == "IAX") { print " SELECTED";}?>>IAX2</OPTION>
      <OPTION VALUE="H.323"<?php if ($conf['LCRPROTO'] == "H.323") { print " SELECTED";}?>>H.323</OPTION>
      </TD>
    </tr>
    <tr class="list-color2">
      <td width="50%">Register</td>
      <td><INPUT TYPE=CHECKBOX NAME=LCRREG<?php if ($conf['LCRREG'] == "true") { print " CHECKED";}?>>
      </TD>
    </tr>
    <tr class="list-color1">
      <td width="50%">Use DTMF INFO (SIP)</td>
      <td><INPUT TYPE=CHECKBOX NAME=LCRDTMF<?php if ($conf['LCRDTMF'] == "info") { print " CHECKED";}?>>
      </TD>
    </tr>
    <tr class="list-color2">
      <td width="50%">Use SRTP (SIP)</td>
      <td><INPUT TYPE=CHECKBOX NAME=LCRSRTP<?php if ($conf['LCRSRTP'] == "true") { print " CHECKED";}?>>
      </TD>
    </tr>
    <tr class="list-color1">
      <td width="50%">Use From User (SIP [Disables Sending CLI])</td>
      <td><INPUT TYPE=CHECKBOX NAME=LCRFROMU<?php if ($conf['LCRFROMU'] == "true") { print " CHECKED";}?>>
      </TD>
    </tr>
    <tr class="list-color2">
      <td width="50%">Disable Video</td>
      <td><INPUT TYPE=CHECKBOX NAME=LCRVIDEO<?php if ($conf['LCRVIDEO'] == "true") { print " CHECKED";}?>>
      </TD>
    </tr>
</TABLE>
</DIV>

<DIV id=save CLASS=formpart>
<TABLE WIDTH=100% cellspacing=0 cellpadding=0>
    <tr class="list-color2">
      <td width="50%">Serial Key</td>
      <td><input size="52" name="SERIAL" value="<?php print $conf["SERIAL"];?>" type="text"></td>
    </tr>
</TABLE>
</DIV>
</FORM>
<SCRIPT>
document.getElementById(document.confform.curdiv.value).style.visibility='visible';
document.getElementById(document.confform.curdiv.value+'_but').style.backgroundColor='rgb(10,36,106)';
document.getElementById(document.confform.curdiv.value+'_but').style.color='#FFFFFF';
</SCRIPT>

</DIV>
