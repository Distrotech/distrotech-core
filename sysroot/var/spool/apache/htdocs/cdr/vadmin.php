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

include_once "auth.inc";
include "setupdef.inc";

//print "<PRE>" . print_r($_POST,TRUE) .  "</PRE>";
/*
print "<PRE>";
print_r($_SESSION);
print "</PRE>";
*/

if ($_SESSION['style'] == "") {
  include "../style.css";
} else {
  include "../" . $_SESSION['style'] . "/style.css";
}

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

$snet["auto"]=_("Auto Negotiation");
$snet["10half"]=_("10 Mbit Half Duplex");
$snet["10full"]=_("10 Mbit Full Duplex");
$snet["100half"]=_("100 Mbit Half Duplex");
$snet["100full"]=_("100 Mbit Full Duplex");

$ipgw=array();
$ipgws=pg_query($db,"SELECT name||' ('||description||')',CASE WHEN (protocol = 'OH323') THEN 'OOH323/'||lpad(trunkprefix,7,'0') ELSE protocol||'/'||providerip||'/' END from provider left outer join trunk using (trunkprefix) WHERE protocol != 'Local' ORDER BY name,description");
for($ipcnt=0;$ipcnt < pg_num_rows($ipgws);$ipcnt++) {
  $ipgwr=pg_fetch_array($ipgws,$ipcnt,PGSQL_NUM);
  array_push($ipgw,array('name'=>$ipgwr[0],'gw'=>$ipgwr[1]));
}

if (isset($pbxupdate)) {
  if ($LDDist == "") {
    $LDDist="0";
  }
  if ($MaxAna > 0) {
    $MaxAna=$MaxAna * 60000;
  } else {
    $MaxAna="1200000";
  }

  if ($DEFNOVMAIL == "on") {
    $DEFNOVMAIL="0";
  } else {
    $DEFNOVMAIL="1";
  }

  $voipcbset=array("VoipFallover","NatAreaCode","AutoAuth","AutoLock","UNKDEF","DEFRECORD","DialAreaCode","AddExCLI","IntFallover",
             "AutoCLI","MaxAll","mISDNimm","mISDNrr","NoEnum","GSMRoute","GSMTrunk","LocalFwd","Default_9","REMDEF","NoOper","ADVPIN",
             "E1mfcr2_get_ani_first","E1mfcr2_allow_collect_calls","mfcr2_double_answer","E1mfcr2_immediate_accept",
             "E1mfcr2_forced_relea","E1mfcr2_charge_call","PPDIS","PRIcrc4","DISADDI","NoBridge","ValidAcc","AddGroup","FollowDDI","IFAXD",
             "DEFAUTHREG");

  for($cbcnt=0;$cbcnt < count($voipcbset);$cbcnt++) {
    $cbval=$voipcbset[$cbcnt];
    if ($$cbval == "on") {
      $$cbval="1";
    } else {
      $$cbval="0";
    }
  }

  $vadmintemp=$vadmindef;
  while(list($defkey,$defval) = each($vadmintemp)) {
    if (isset($$defkey)) {
      pg_query("UPDATE astdb SET value='" . $$defkey . "' WHERE family='Setup' AND key='" . $defkey . "'");
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

  if (($AdminPass1 == $AdminPass2) && ($AdminPass1 != "")){
    pg_query($db,"UPDATE astdb SET value='" . $AdminPass1 . "' WHERE family='Setup' AND key='AdminPass'");
  } else {
?>
    <SCRIPT>
      alert("Password Mismach/Unset.Password Unchanged");
    </SCRIPT>
<?php
  }
  system("/usr/sbin/genconf > /dev/null 2>/dev/null");
}

$qgetdata=pg_query($db,"SELECT key,value FROM astdb WHERE family='Setup'");

$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  $origdata[$getdata[0]]=$getdata[1]; 
}

$vadmintemp=$vadmindef;
while(list($defkey,$defval) = each($vadmintemp)) {
  if (!isset($origdata[$defkey])) {
    pg_query("INSERT INTO astdb (family,key,value) VALUES ('Setup','" . $defkey . "','" . $defval . "')");
    $origdata[$defkey]=$defval; 
  }
}

if ($origdata['MaxAna'] > 0) {
  $origdata['MaxAna']=$origdata['MaxAna']/60000;
}

if ($origdata['FAXBOX'] == "") {
  $asr=ldap_search($ds,"ou=email","sendmailmtakey=astfax");
  if (ldap_count_entries($ds,$asr) <= 0) {
    $addent=array();
    $addent["objectclass"][0]="sendmailMTA";
    $addent["objectclass"][1]="sendmailMTAAlias";
    $addent["objectclass"][2]="sendmailMTAAliasObject";
    $addent["sendmailmtaaliasgrouping"][0]="aliases";
    $addent["sendmailmtacluster"][0]="AllServers";
    $addent["sendmailmtakey"][0]="astfax";
    $addent["sendmailmtaaliasvalue"][0]="pubbox";
    $addent["Description"]="Incoming Faxes:users";
    ldap_add($ds,"sendmailMTAKey=astfax,ou=Email",$addent);
  }
}

?>
<FORM METHOD=POST NAME=pbxform onsubmit="alert('Test');ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=curdiv VALUE=routing>
<INPUT TYPE=HIDDEN NAME=pbxupdate value=1>

<DIV CLASS=content>
<DIV CLASS=list-color2 ID=headcol><DIV CLASS=heading-body><?php print _("Asterisk PBX Configuration");?></DIV></DIV>

<DIV CLASS=list-color1><DIV CLASS=formrow>
<DIV CLASS=formselect ID=routing_but onclick=showdiv('routing',document.pbxform) onmouseover=showdiv('routing',document.pbxform)><?php print _("Routing");?></DIV>
<DIV CLASS=formselect ID=misdn_but onclick=showdiv('misdn',document.pbxform) onmouseover=showdiv('misdn',document.pbxform)><?php print _("mISDN");?></DIV>
<DIV CLASS=formselect ID=e1sig_but onclick=showdiv('e1sig',document.pbxform) onmouseover=showdiv('e1sig',document.pbxform)><?php print _("E1");?></DIV>
<DIV CLASS=formselect ID=default_but onclick=showdiv('default',document.pbxform) onmouseover=showdiv('default',document.pbxform)><?php print _("Defaults");?></DIV>
<DIV CLASS=formselect ID=ivr_but onclick=showdiv('ivr',document.pbxform) onmouseover=showdiv('ivr',document.pbxform)><?php print _("IVR Password");?></DIV>
<DIV CLASS=formselect ID=local_but onclick=showdiv('local',document.pbxform) onmouseover=showdiv('local',document.pbxform)><?php print _("Location");?></DIV>
<DIV CLASS=formselect ID=inbound_but onclick=showdiv('inbound',document.pbxform) onmouseover=showdiv('inbound',document.pbxform)><?php print _("Inbound");?></DIV>
<DIV CLASS=formselect ID=numplan_but onclick=showdiv('numplan',document.pbxform) onmouseover=showdiv('numplan',document.pbxform)><?php print _("Num. Plan");?></DIV>
<DIV CLASS=formselect ID=autoadd_but onclick=showdiv('autoadd',document.pbxform) onmouseover=showdiv('autoadd',document.pbxform)><?php print _("Auto Add");?></DIV>
<DIV CLASS=formselect ID=save_but onclick=ajaxsubmit('pbxform') onmouseover=showdiv('save',document.pbxform)>Save</DIV>
</DIV></DIV>
<DIV id=local CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Local Country Code");?></TD>
  <TD><INPUT TYPE=TEXT NAME=CountryCode VALUE="<?php print $origdata["CountryCode"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Local Area Code");?></TD>
  <TD><INPUT TYPE=TEXT NAME=AreaCode VALUE="<?php print $origdata["AreaCode"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS1')" onmouseout="myHint.hide()"><?php print _("Local Exchange Prefix");?></TD>
  <TD><INPUT TYPE=TEXT NAME=ExCode VALUE="<?php print $origdata["ExCode"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS1')" onmouseout="myHint.hide()"><?php print _("Local Call Distance (Or Blank To Base Calls On Area Code)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=LDDist VALUE="<?php if ($origdata["LDDist"] > 0) {print $origdata["LDDist"];}?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Local Number Length (0 To Disable)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=LocalLength VALUE="<?php print $origdata["LocalLength"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("National Number Length (0 To Disable)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=NatLength VALUE="<?php print $origdata["NatLength"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS1')" onmouseout="myHint.hide()"><?php print _("National Access Code");?></TD>
  <TD><INPUT TYPE=TEXT NAME=LocalAccess VALUE="<?php print $origdata["LocalAccess"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS1')" onmouseout="myHint.hide()"><?php print _("International Access Code");?></TD>
  <TD><INPUT TYPE=TEXT NAME=IntAccess VALUE="<?php print $origdata["IntAccess"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Area Code Includes National Access Code");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NatAreaCode<?php if ($origdata["NatAreaCode"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Local Calls Require National Dialing");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DialAreaCode<?php if ($origdata["DialAreaCode"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Allow Automatic Setting Of CLI");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=AutoCLI<?php if ($origdata["AutoCLI"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Add Exchange Prefix To Outbound CLI On Extension");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=AddExCLI<?php if ($origdata["AddExCLI"] == "1") {print " CHECKED";}?>></TD>
</TR>
</TABLE>
</DIV>
<DIV id=routing CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS2')" onmouseout="myHint.hide()"><?php print _("PSTN Trunk");?></TD>
  <TD>
    <SELECT NAME=Trunk>
      <OPTION VALUE="NONE"><?php print _("Do Not Use 1 Trunk");?></OPTION>
      <OPTION VALUE="mISDN/g:out/"<?php if ($origdata["Trunk"] == "mISDN/g:out/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 1");?></OPTION>
      <OPTION VALUE="mISDN/g:out2/"<?php if ($origdata["Trunk"] == "mISDN/g:out2/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 2");?></OPTION>
      <OPTION VALUE="mISDN/g:out3/"<?php if ($origdata["Trunk"] == "mISDN/g:out3/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 3");?></OPTION>
      <OPTION VALUE="mISDN/g:out4/"<?php if ($origdata["Trunk"] == "mISDN/g:out4/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 4");?></OPTION>
      <OPTION VALUE="DAHDI/r1/"<?php if (($origdata["Trunk"] == "Zap/r1/") || ($origdata["Trunk"] == "DAHDI/r1/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 1");?></OPTION>
      <OPTION VALUE="DAHDI/r2/"<?php if (($origdata["Trunk"] == "Zap/r2/") || ($origdata["Trunk"] == "DAHDI/r2/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 2");?></OPTION>
      <OPTION VALUE="DAHDI/r3/"<?php if (($origdata["Trunk"] == "Zap/r3/") || ($origdata["Trunk"] == "DAHDI/r3/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 3");?></OPTION>
      <OPTION VALUE="DAHDI/r4/"<?php if (($origdata["Trunk"] == "Zap/r4/") || ($origdata["Trunk"] == "DAHDI/r4/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 4");?></OPTION>
      <OPTION VALUE="WOOMERA/g1/"<?php if ($origdata["Trunk"] == "WOOMERA/g1/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 1");?></OPTION>
      <OPTION VALUE="WOOMERA/g2/"<?php if ($origdata["Trunk"] == "WOOMERA/g2/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 2");?></OPTION>
      <OPTION VALUE="WOOMERA/g3/"<?php if ($origdata["Trunk"] == "WOOMERA/g3/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 3");?></OPTION>
      <OPTION VALUE="WOOMERA/g4/"<?php if ($origdata["Trunk"] == "WOOMERA/g4/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 4");?></OPTION>
<?php
      for($ipcnt=0;$ipcnt < count($ipgw);$ipcnt++) {
        print "<OPTION VALUE=\"" . $ipgw[$ipcnt]['gw'] . "\"";
        if ($origdata['Trunk'] == $ipgw[$ipcnt]['gw']) {
          print " SELECTED";
        }
        print ">" . $ipgw[$ipcnt]['name'] . "</OPTION>\n";
      }
?>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS2')" onmouseout="myHint.hide()"><?php print _("PSTN Second Trunk");?></TD>
  <TD>
    <SELECT NAME=Trunk2>
      <OPTION VALUE="NONE"><?php print _("Do Not Use 2 Trunk");?></OPTION>
      <OPTION VALUE="mISDN/g:out/"<?php if ($origdata["Trunk2"] == "mISDN/g:out/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 1");?></OPTION>
      <OPTION VALUE="mISDN/g:out2/"<?php if ($origdata["Trunk2"] == "mISDN/g:out2/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 2");?></OPTION>
      <OPTION VALUE="mISDN/g:out3/"<?php if ($origdata["Trunk2"] == "mISDN/g:out3/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 3");?></OPTION>
      <OPTION VALUE="mISDN/g:out4/"<?php if ($origdata["Trunk2"] == "mISDN/g:out4/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 4");?></OPTION>
      <OPTION VALUE="DAHDI/r1/"<?php if (($origdata["Trunk2"] == "Zap/r1/") || ($origdata["Trunk2"] == "DAHDI/r1/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 1");?></OPTION>
      <OPTION VALUE="DAHDI/r2/"<?php if (($origdata["Trunk2"] == "Zap/r2/") || ($origdata["Trunk2"] == "DAHDI/r2/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 2");?></OPTION>
      <OPTION VALUE="DAHDI/r3/"<?php if (($origdata["Trunk2"] == "Zap/r3/") || ($origdata["Trunk2"] == "DAHDI/r3/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 3");?></OPTION>
      <OPTION VALUE="DAHDI/r4/"<?php if (($origdata["Trunk2"] == "Zap/r4/") || ($origdata["Trunk2"] == "DAHDI/r4/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 4");?></OPTION>
      <OPTION VALUE="WOOMERA/g1/"<?php if ($origdata["Trunk2"] == "WOOMERA/g1/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 1");?></OPTION>
      <OPTION VALUE="WOOMERA/g2/"<?php if ($origdata["Trunk2"] == "WOOMERA/g2/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 2");?></OPTION>
      <OPTION VALUE="WOOMERA/g3/"<?php if ($origdata["Trunk2"] == "WOOMERA/g3/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 3");?></OPTION>
      <OPTION VALUE="WOOMERA/g4/"<?php if ($origdata["Trunk2"] == "WOOMERA/g4/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 4");?></OPTION>
<?php
      for($ipcnt=0;$ipcnt < count($ipgw);$ipcnt++) {
        print "<OPTION VALUE=\"" . $ipgw[$ipcnt]['gw'] . "\"";
        if ($origdata['Trunk2'] == $ipgw[$ipcnt]['gw']) {
          print " SELECTED";
        }
        print ">" . $ipgw[$ipcnt]['name'] . "</OPTION>\n";
      }
?>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS2')" onmouseout="myHint.hide()"><?php print _("PSTN Third Trunk");?></TD>
  <TD>
    <SELECT NAME=Trunk3>
      <OPTION VALUE="NONE"><?php print _("Do Not Use 3 Trunk");?></OPTION>
      <OPTION VALUE="mISDN/g:out/"<?php if ($origdata["Trunk3"] == "mISDN/g:out/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 1");?></OPTION>
      <OPTION VALUE="mISDN/g:out2/"<?php if ($origdata["Trunk3"] == "mISDN/g:out2/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 2");?></OPTION>
      <OPTION VALUE="mISDN/g:out3/"<?php if ($origdata["Trunk3"] == "mISDN/g:out3/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 3");?></OPTION>
      <OPTION VALUE="mISDN/g:out4/"<?php if ($origdata["Trunk3"] == "mISDN/g:out4/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 4");?></OPTION>
      <OPTION VALUE="DAHDI/r1/"<?php if (($origdata["Trunk3"] == "Zap/r1/") || ($origdata["Trunk3"] == "DAHDI/r1/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 1");?></OPTION>
      <OPTION VALUE="DAHDI/r2/"<?php if (($origdata["Trunk3"] == "Zap/r2/") || ($origdata["Trunk3"] == "DAHDI/r2/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 2");?></OPTION>
      <OPTION VALUE="DAHDI/r3/"<?php if (($origdata["Trunk3"] == "Zap/r3/") || ($origdata["Trunk3"] == "DAHDI/r3/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 3");?></OPTION>
      <OPTION VALUE="DAHDI/r4/"<?php if (($origdata["Trunk3"] == "Zap/r4/") || ($origdata["Trunk3"] == "DAHDI/r4/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 4");?></OPTION>
      <OPTION VALUE="WOOMERA/g1/"<?php if ($origdata["Trunk3"] == "WOOMERA/g1/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 1");?></OPTION>
      <OPTION VALUE="WOOMERA/g2/"<?php if ($origdata["Trunk3"] == "WOOMERA/g2/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 2");?></OPTION>
      <OPTION VALUE="WOOMERA/g3/"<?php if ($origdata["Trunk3"] == "WOOMERA/g3/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 3");?></OPTION>
      <OPTION VALUE="WOOMERA/g4/"<?php if ($origdata["Trunk3"] == "WOOMERA/g4/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 4");?></OPTION>
<?php
      for($ipcnt=0;$ipcnt < count($ipgw);$ipcnt++) {
        print "<OPTION VALUE=\"" . $ipgw[$ipcnt]['gw'] . "\"";
        if ($origdata['Trunk3'] == $ipgw[$ipcnt]['gw']) {
          print " SELECTED";
        }
        print ">" . $ipgw[$ipcnt]['name'] . "</OPTION>\n";
      }
?>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS2')" onmouseout="myHint.hide()"><?php print _("PSTN Fourth Trunk");?></TD>
  <TD>
    <SELECT NAME=Trunk4>
      <OPTION VALUE="NONE"><?php print _("Do Not Use 4 Trunk");?></OPTION>
      <OPTION VALUE="mISDN/g:out/"<?php if ($origdata["Trunk4"] == "mISDN/g:out/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 1");?></OPTION>
      <OPTION VALUE="mISDN/g:out2/"<?php if ($origdata["Trunk4"] == "mISDN/g:out2/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 2");?></OPTION>
      <OPTION VALUE="mISDN/g:out3/"<?php if ($origdata["Trunk4"] == "mISDN/g:out3/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 3");?></OPTION>
      <OPTION VALUE="mISDN/g:out4/"<?php if ($origdata["Trunk4"] == "mISDN/g:out4/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 4");?></OPTION>
      <OPTION VALUE="DAHDI/r1/"<?php if (($origdata["Trunk5"] == "Zap/r1/") || ($origdata["Trunk5"] == "DAHDI/r1/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 1");?></OPTION>
      <OPTION VALUE="DAHDI/r2/"<?php if (($origdata["Trunk5"] == "Zap/r2/") || ($origdata["Trunk5"] == "DAHDI/r2/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 2");?></OPTION>
      <OPTION VALUE="DAHDI/r3/"<?php if (($origdata["Trunk5"] == "Zap/r3/") || ($origdata["Trunk5"] == "DAHDI/r3/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 3");?></OPTION>
      <OPTION VALUE="DAHDI/r4/"<?php if (($origdata["Trunk5"] == "Zap/r4/") || ($origdata["Trunk5"] == "DAHDI/r4/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 4");?></OPTION>
      <OPTION VALUE="WOOMERA/g1/"<?php if ($origdata["Trunk4"] == "WOOMERA/g1/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 1");?></OPTION>
      <OPTION VALUE="WOOMERA/g2/"<?php if ($origdata["Trunk4"] == "WOOMERA/g2/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 2");?></OPTION>
      <OPTION VALUE="WOOMERA/g3/"<?php if ($origdata["Trunk4"] == "WOOMERA/g3/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 3");?></OPTION>
      <OPTION VALUE="WOOMERA/g4/"<?php if ($origdata["Trunk4"] == "WOOMERA/g4/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 4");?></OPTION>
<?php
      for($ipcnt=0;$ipcnt < count($ipgw);$ipcnt++) {
        print "<OPTION VALUE=\"" . $ipgw[$ipcnt]['gw'] . "\"";
        if ($origdata['Trunk4'] == $ipgw[$ipcnt]['gw']) {
          print " SELECTED";
        }
        print ">" . $ipgw[$ipcnt]['name'] . "</OPTION>\n";
      }
?>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS2')" onmouseout="myHint.hide()"><?php print _("DDI Forward Trunk");?></TD>
  <TD>
    <SELECT NAME=FTrunk>
      <OPTION VALUE="NONE"><?php print _("Do Not Use Forward Trunk");?></OPTION>
      <OPTION VALUE="mISDN/g:fwd/"<?php if ($origdata["FTrunk"] == "mISDN/g:fwd/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Forward Group");?></OPTION>
      <OPTION VALUE="mISDN/g:out/"<?php if ($origdata["FTrunk"] == "mISDN/g:out/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 1");?></OPTION>
      <OPTION VALUE="mISDN/g:out2/"<?php if ($origdata["FTrunk"] == "mISDN/g:out2/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 2");?></OPTION>
      <OPTION VALUE="mISDN/g:out3/"<?php if ($origdata["FTrunk"] == "mISDN/g:out3/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 3");?></OPTION>
      <OPTION VALUE="mISDN/g:out4/"<?php if ($origdata["FTrunk"] == "mISDN/g:out4/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 4");?></OPTION>
      <OPTION VALUE="DAHDI/r1/"<?php if (($origdata["FTrunk"] == "Zap/r1/") || ($origdata["FTrunk"] == "DAHDI/r1/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 1");?></OPTION>
      <OPTION VALUE="DAHDI/r2/"<?php if (($origdata["FTrunk"] == "Zap/r2/") || ($origdata["FTrunk"] == "DAHDI/r2/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 2");?></OPTION>
      <OPTION VALUE="DAHDI/r3/"<?php if (($origdata["FTrunk"] == "Zap/r3/") || ($origdata["FTrunk"] == "DAHDI/r3/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 3");?></OPTION>
      <OPTION VALUE="DAHDI/r4/"<?php if (($origdata["FTrunk"] == "Zap/r4/") || ($origdata["FTrunk"] == "DAHDI/r4/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 4");?></OPTION>
      <OPTION VALUE="WOOMERA/g1/"<?php if ($origdata["FTrunk"] == "WOOMERA/g1/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 1");?></OPTION>
      <OPTION VALUE="WOOMERA/g2/"<?php if ($origdata["FTrunk"] == "WOOMERA/g2/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 2");?></OPTION>
      <OPTION VALUE="WOOMERA/g3/"<?php if ($origdata["FTrunk"] == "WOOMERA/g3/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 3");?></OPTION>
      <OPTION VALUE="WOOMERA/g4/"<?php if ($origdata["FTrunk"] == "WOOMERA/g4/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 4");?></OPTION>
<?php
      for($ipcnt=0;$ipcnt < count($ipgw);$ipcnt++) {
        print "<OPTION VALUE=\"" . $ipgw[$ipcnt]['gw'] . "\"";
        if ($origdata['FTrunk'] == $ipgw[$ipcnt]['gw']) {
          print " SELECTED";
        }
        print ">" . $ipgw[$ipcnt]['name'] . "</OPTION>\n";
      }
?>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS2')" onmouseout="myHint.hide()"><?php print _("GSM Trunk");?></TD>
  <TD>
    <SELECT NAME=CellGateway>
      <OPTION VALUE=""><?php print _("Do Not Use GSM Trunk");?></OPTION>
      <OPTION VALUE="mISDN/g:fwd/"<?php if ($origdata["CellGateway"] == "mISDN/g:fwd/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Forward Group");?></OPTION>
      <OPTION VALUE="mISDN/g:out/"<?php if ($origdata["CellGateway"] == "mISDN/g:out/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 1");?></OPTION>
      <OPTION VALUE="mISDN/g:out2/"<?php if ($origdata["CellGateway"] == "mISDN/g:out2/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 2");?></OPTION>
      <OPTION VALUE="mISDN/g:out3/"<?php if ($origdata["CellGateway"] == "mISDN/g:out3/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 3");?></OPTION>
      <OPTION VALUE="mISDN/g:out4/"<?php if ($origdata["CellGateway"] == "mISDN/g:out4/") { print " SELECTED";}?>><?php print _("Linux Modular ISDN Group 4");?></OPTION>
      <OPTION VALUE="DAHDI/r1/"<?php if (($origdata["CellGateway"] == "Zap/r1/") || ($origdata["CellGateway"] == "DAHDI/r1/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 1");?></OPTION>
      <OPTION VALUE="DAHDI/r2/"<?php if (($origdata["CellGateway"] == "Zap/r2/") || ($origdata["CellGateway"] == "DAHDI/r2/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 2");?></OPTION>
      <OPTION VALUE="DAHDI/r3/"<?php if (($origdata["CellGateway"] == "Zap/r3/") || ($origdata["CellGateway"] == "DAHDI/r3/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 3");?></OPTION>
      <OPTION VALUE="DAHDI/r4/"<?php if (($origdata["CellGateway"] == "Zap/r4/") || ($origdata["CellGateway"] == "DAHDI/r4/")) { print " SELECTED";}?>><?php print _("Digium Trunk Group 4");?></OPTION>
      <OPTION VALUE="WOOMERA/g1/"<?php if ($origdata["CellGateway"] == "WOOMERA/g1/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 1");?></OPTION>
      <OPTION VALUE="WOOMERA/g2/"<?php if ($origdata["CellGateway"] == "WOOMERA/g2/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 2");?></OPTION>
      <OPTION VALUE="WOOMERA/g3/"<?php if ($origdata["CellGateway"] == "WOOMERA/g3/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 3");?></OPTION>
      <OPTION VALUE="WOOMERA/g4/"<?php if ($origdata["CellGateway"] == "WOOMERA/g4/") { print " SELECTED";}?>><?php print _("Woomera Trunk Group 4");?></OPTION>
<?php
      for($ipcnt=0;$ipcnt < count($ipgw);$ipcnt++) {
        print "<OPTION VALUE=\"" . $ipgw[$ipcnt]['gw'] . "\"";
        if ($origdata['CellGateway'] == $ipgw[$ipcnt]['gw']) {
          print " SELECTED";
        }
        print ">" . $ipgw[$ipcnt]['name'] . "</OPTION>\n";
      }
?>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS3')" onmouseout="myHint.hide()"><?php print _("Level To Start Routing To Master Server");?></TD>
  <TD>
    <SELECT NAME=IPContext>
<?php
      for($i=1;$i<=6;$i++) {
        print "      <OPTION VALUE=" . $i;
        if ($i == $origdata["IPContext"]) {
          print " SELECTED";
        }
        print ">" . $context[$i] . "</OPTION>\n";
      }
?>
    </SELECT>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS3')" onmouseout="myHint.hide()"><?php print _("Number Plan To Send To Master (Local Calls)");?></TD>
  <TD>
    <SELECT NAME=IntLocal>
      <OPTION VALUE=0>As Recived
      <OPTION VALUE=1<?php if ($origdata["IntLocal"] == 1) {print " SELECTED";}?>>International
      <OPTION VALUE=2<?php if ($origdata["IntLocal"] == 2) {print " SELECTED";}?>>International With Access Code
      <OPTION VALUE=3<?php if ($origdata["IntLocal"] == 3) {print " SELECTED";}?>>International With + 
    </SELECT>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS5')" onmouseout="myHint.hide()"><?php print _("Maximum Concurency On VOIP Trunk");?></TD>
  <TD><INPUT TYPE=TEXT NAME=VLIMIT VALUE="<?php print $origdata["VLIMIT"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS4')" onmouseout="myHint.hide()"><?php print _("Prefix Trunk Calls With");?></TD>
  <TD><INPUT TYPE=TEXT NAME=TrunkPre VALUE="<?php if ($origdata["TrunkPre"] != "-") {print $origdata["TrunkPre"];}?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS5')" onmouseout="myHint.hide()"><?php print _("Number Of Digits To Strip On Trunk");?></TD>
  <TD><INPUT TYPE=TEXT NAME=TrunkStrip VALUE="<?php print $origdata["TrunkStrip"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS5')" onmouseout="myHint.hide()"><?php print _("Maximum Call Length On Analogue Trunks (mins)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=MaxAna VALUE="<?php print $origdata["MaxAna"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Apply Call Limt To All Trunks");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=MaxAll<?php if ($origdata["MaxAll"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Allow VOIP Fallover When Trunk Is Unavailable");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=VoipFallover<?php if ($origdata["VoipFallover"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS9')" onmouseout="myHint.hide()"><?php print _("Allow International Trunk Failover When Voip Fails");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=GSMTrunk<?php if ($origdata["IntFallover"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS7')" onmouseout="myHint.hide()"><?php print _("Use ENUM Lookups On Outgoing Calls");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NoEnum<?php if ($origdata["NoEnum"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS8')" onmouseout="myHint.hide()"><?php print _("Use Configured GSM Routers");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=GSMRoute<?php if ($origdata["GSMRoute"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS9')" onmouseout="myHint.hide()"><?php print _("Allow Trunk Failover When Using Configured GSM Routers");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=GSMTrunk<?php if ($origdata["GSMTrunk"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS9')" onmouseout="myHint.hide()"><?php print _("Calls To Internal Extensions Follow Forward Rules");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=LocalFwd<?php if ($origdata["LocalFwd"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS9')" onmouseout="myHint.hide()"><?php print _("Inbound Calls Forwarded To Reception If No Voicemail");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=Default_9<?php if ($origdata["Default_9"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS9')" onmouseout="myHint.hide()"><?php print _("Disable Billing Engine");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=PPDIS<?php if ($origdata["PPDIS"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS9')" onmouseout="myHint.hide()"><?php print _("Allow DISA Passthrough On Trunks");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DISADDI<?php if ($origdata["DISADDI"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS9')" onmouseout="myHint.hide()"><?php print _("Disable Native Bridging On Outbound");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NoBridge<?php if ($origdata["NoBridge"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS9')" onmouseout="myHint.hide()"><?php print _("Disable access to invalid accounts");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=ValidAcc<?php if ($origdata["ValidAcc"] == "1") {print " CHECKED";}?>></TD>
</TR>
</TABLE>
</DIV>
<DIV id=misdn CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("Isdn Ports To Use (Group 1)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=mISDNports VALUE="<?php print $origdata["mISDNports"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("Isdn Ports To Use (Group 2)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=mISDNports2 VALUE="<?php print $origdata["mISDNports2"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("Isdn Ports To Use (Group 3)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=mISDNports3 VALUE="<?php print $origdata["mISDNports3"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("Isdn Ports To Use (Group 4)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=mISDNports4 VALUE="<?php print $origdata["mISDNports4"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("Isdn Ports To Use (In Only)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=mISDNinports VALUE="<?php print $origdata["mISDNinports"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("Isdn Ports To Use (Forwarding)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=mISDNfwdports VALUE="<?php print $origdata["mISDNfwdports"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("RX Gain");?></TD>
  <TD><INPUT TYPE=TEXT NAME=mISDNgainrx VALUE="<?php print $origdata["mISDNgainrx"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("TX Gain");?></TD>
  <TD><INPUT TYPE=TEXT NAME=mISDNgaintx VALUE="<?php print $origdata["mISDNgaintx"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS23')" onmouseout="myHint.hide()"><?php print _("Immeadiate Routing (No MSN/DDI)");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=mISDNimm<?php if ($origdata["mISDNimm"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS24')" onmouseout="myHint.hide()"><?php print _("Use Round Robin Routing");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=mISDNrr<?php if ($origdata["mISDNrr"] == "1") {print " CHECKED";}?>></TD>
</TR>
</TABLE>
</DIV>
<DIV id=e1sig CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body2>PRI Defaults</TH>
</TR>

<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS24')" onmouseout="myHint.hide()" WIDTH=50%><?php print _("Line Build Out");?></TD>
  <TD>
     <SELECT NAME=PRIlbo>
<?php
  $lbo['0']="0 db (CSU) / 0-133 feet (DSX-1)";
  $lbo['1']="133-266 feet (DSX-1)";
  $lbo['2']="266-399 feet (DSX-1)";
  $lbo['3']="399-533 feet (DSX-1)";
  $lbo['4']="533-655 feet (DSX-1)";
  $lbo['5']="-7.5db (CSU)";
  $lbo['6']="-15db (CSU)";
  $lbo['7']="-22.5db (CSU)";
  while(list($r2key,$r2opt) = each($lbo)) {
    print "      <OPTION VALUE=" . $r2key;
    if ($origdata["PRIlbo"] == $r2key) {
      print " SELECTED";
    }
    print ">" . $r2opt . "</OPTION>\n";
  }
?>
     
     </SELECT>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS24')" onmouseout="myHint.hide()" WIDTH=50%><?php print _("PRI Framing");?> (E1 - T1)</TD>
  <TD>
     <SELECT NAME=PRIframing>
<?php
  $framing['cas']="cas - d4/sf/superframe";   
  $framing['ccs']="ccs - esf";
  while(list($r2key,$r2opt) = each($framing)) {
    print "      <OPTION VALUE=" . $r2key;
    if ($origdata["PRIframing"] == $r2key) {
      print " SELECTED";
    }
    print ">" . $r2opt . "</OPTION>\n";
  }
?>
     </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS24')" onmouseout="myHint.hide()" WIDTH=50%><?php print _("PRI Coding");?> (E1 - T1)</TD>
  <TD>
     <SELECT NAME=PRIcoding>
<?php
  $coding['ami']="ami";
  $coding['hdb3']="hdb3 - b8zs";
  while(list($r2key,$r2opt) = each($coding)) {
    print "      <OPTION VALUE=" . $r2key;
    if ($origdata["PRIcoding"] == $r2key) {
      print " SELECTED";
    }
    print ">" . $r2opt . "</OPTION>\n";
  }
?>
     
     </SELECT>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("CRC4 Checking (E1 Only)");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=PRIcrc4<?php if ($origdata["PRIcrc4"] == "1") {print " CHECKED";}?>></TD>
</TR>


<TR CLASS=list-color1>
  <TH COLSPAN=2 CLASS=heading-body2>MFC/R2</TH>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS24')" onmouseout="myHint.hide()" WIDTH=50%><?php print _("Variant");?></TD>
  <TD>
     <SELECT NAME=E1mfcr2_variant>
<?php
  $mfcr2_variant["itu"]="ITU Standard";
  $mfcr2_variant["ar"]="Argentina";
  $mfcr2_variant["br"]="Brazil";
  $mfcr2_variant["cn"]="China";
  $mfcr2_variant["cz"]="Czech Republic";
  $mfcr2_variant["co"]="Columbia";
  $mfcr2_variant["ec"]="Ecuador";
  $mfcr2_variant["mx"]="Mexico";
  $mfcr2_variant["ph"]="Philippines";
  $mfcr2_variant["ve"]="Venezuela";
  while(list($r2key,$r2opt) = each($mfcr2_variant)) {
    print "      <OPTION VALUE=" . $r2key;
    if ($origdata["E1mfcr2_variant"] == $r2key) {
      print " SELECTED";
    }
    print ">" . $r2opt . "</OPTION>\n";
  }
?>
     
     </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS24')" onmouseout="myHint.hide()" WIDTH=50%><?php print _("Caller Category");?></TD>
  <TD>
     <SELECT NAME=E1mfcr2_category>
<?php
  $mfcr2_category["national_subscriber"]="National Subscriber";
  $mfcr2_category["national_priority_subscriber"]="National Priority Subscriber";
  $mfcr2_category["international_subscriber"]="International Subscriber";
  $mfcr2_category["international_priority_subscriber"]="International Priority Subscriber";
  $mfcr2_category["collect_call"]="Collect Call";
  while(list($r2key,$r2opt) = each($mfcr2_category)) {
    print "      <OPTION VALUE=" . $r2key;
    if ($origdata["E1mfcr2_category"] == $r2key) {
      print " SELECTED";
    }
    print ">" . $r2opt . "</OPTION>\n";
  }
?>
     
     </SELECT>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("Max ANI Digits");?></TD>
  <TD><INPUT TYPE=TEXT NAME=E1mfcr2_max_ani VALUE="<?php print $origdata["E1mfcr2_max_ani"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS22')" onmouseout="myHint.hide()"><?php print _("Max DNIS Digits");?></TD>
  <TD><INPUT TYPE=TEXT NAME=E1mfcr2_max_dnis VALUE="<?php print $origdata["E1mfcr2_max_dnis"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS23')" onmouseout="myHint.hide()"><?php print _("ANI Before DNIS");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=E1mfcr2_get_ani_first<?php if ($origdata["E1mfcr2_get_ani_first"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS23')" onmouseout="myHint.hide()"><?php print _("Allow Collect Calls (BR:llamadas por cobrar)");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=E1mfcr2_allow_collect_calls<?php if ($origdata["E1mfcr2_allow_collect_calls"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS23')" onmouseout="myHint.hide()"><?php print _("Block Collect Calls With Double Answer");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=E1mfcr2_double_answer<?php if ($origdata["E1mfcr2_double_answer"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS23')" onmouseout="myHint.hide()"><?php print _("Immeadiate Answer");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=E1mfcr2_immediate_accept<?php if ($origdata["E1mfcr2_immediate_accept"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS23')" onmouseout="myHint.hide()"><?php print _("Forced Release (BR)");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=E1mfcr2_forced_release<?php if ($origdata["E1mfcr2_forced_release"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS23')" onmouseout="myHint.hide()"><?php print _("Accept Call With Charge");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=E1mfcr2_charge_calls<?php if ($origdata["E1mfcr2_charge_calls"] == "1") {print " CHECKED";}?>></TD>
</TR>
</TABLE>
</DIV>
<DIV id=inbound CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS10')" onmouseout="myHint.hide()"><?php print _("Default Attendant");?></TD>
  <TD>
      <SELECT NAME=Attendant>
        <OPTION VALUE=0><?php print _("Auto Attendant");?></OPTION>
<?php
	$exusers=pg_query($db,"SELECT fullname,name FROM users LEFT OUTER JOIN astdb AS epre ON (epre.family='LocalPrefix' AND epre.key=substring(name,1,2)) WHERE length(name) = 4 AND epre.value='1' ORDER BY fullname");
        $unum=pg_num_rows($exusers);
        for($i=0;$i<$unum;$i++){
          $adata=pg_fetch_array($exusers,$i);
          print "      <OPTION VALUE=" . $adata[1];
          if ($origdata["Attendant"] == $adata[1]) {
            print " SELECTED";
          }
          print ">" . $adata[0] . "(" . $adata[1] . ")</OPTION>\n";
        }
?>
      </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS10')" onmouseout="myHint.hide()"><?php print _("Auto Attendant Queue");?></TD>
  <TD>
      <SELECT NAME=AttendantQ>
        <OPTION VALUE=799><?php print _("Default Auto Attendant (Simple Ring All)");?></OPTION>
        <OPTION VALUE=-1<?php if ($origdata["AttendantQ"] == "-1") {print " SELECTED";}?>><?php print _("No Default Attendant");?></OPTION>
<?php
	$exusers=pg_query($db,"SELECT description,name FROM queue_table ORDER BY description");
        $unum=pg_num_rows($exusers);
        for($i=0;$i<$unum;$i++){
          $adata=pg_fetch_array($exusers,$i);
          print "      <OPTION VALUE=" . $adata[1];
          if ($origdata["AttendantQ"] == $adata[1]) {
            print " SELECTED";
          }
          print ">" . $adata[0] . "(" . $adata[1] . ")</OPTION>\n";
        }
?>
      </SELECT>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS11')" onmouseout="myHint.hide()"><?php print _("Default Fax Terminal");?></TD>
  <TD>
      <SELECT NAME=FAXT>
        <OPTION VALUE=><?php print _("Auto Fax Detect & Receive");?></OPTION>
<?php
	$exusers=pg_query($db,"SELECT fullname,name FROM users LEFT OUTER JOIN features ON (name=exten) LEFT OUTER JOIN astdb AS epre ON (epre.family='LocalPrefix' AND epre.key=substring(name,1,2)) WHERE length(name) = 4 AND (allow ~ '(ulaw)|(alaw)' OR zapline > 0) AND epre.value = '1' ORDER BY fullname");
        $unum=pg_num_rows($exusers);
        for($i=0;$i<$unum;$i++){
          $adata=pg_fetch_array($exusers,$i);
          print "      <OPTION VALUE=" . $adata[1];
          if ($origdata["FAXT"] == $adata[1]) {
            print " SELECTED";
          }
          print ">" . $adata[0] . "(" . $adata[1] . ")</OPTION>\n";
        }
?>
      </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS23')" onmouseout="myHint.hide()"><?php print _("Enable Inbound FAX Detect");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=IFAXD<?php if ($origdata["IFAXD"] == "1") {print " CHECKED";}?>></TD>
</TR>
</TABLE>
</DIV>
<DIV id=default CLASS=formpart>
<TABLE CLASS=formtable>

<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS12')" onmouseout="myHint.hide()"><?php print _("Default Extension Permision");?></TD>
  <TD>
    <SELECT NAME=Context>
<?php
      for($i=0;$i<6;$i++) {
        print "      <OPTION VALUE=" . $i;
        if ($i == $origdata["Context"]) {
          print " SELECTED";
        }
        print ">" . $context[$i] . "</OPTION>\n";
      }
?>
    </SELECT>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS12')" onmouseout="myHint.hide()"><?php print _("Default Auth. Extension Permision");?></TD>
  <TD>
    <SELECT NAME=AuthContext>
<?php
      for($i=0;$i<6;$i++) {
        print "      <OPTION VALUE=" . $i;
        if ($i == $origdata["AuthContext"]) {
          print " SELECTED";
        }
        print ">" . $context[$i] . "</OPTION>\n";
      }
?>
    </SELECT>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("After Hours Extension Permision");?></TD>
  <TD>
    <SELECT NAME=DEFALOCK>
<?php
      for($i=0;$i<6;$i++) {
        print "      <OPTION VALUE=" . $i;
        if ($i == $origdata["DEFALOCK"]) {
          print " SELECTED";
        }
        print ">" . $context[$i] . "</OPTION>\n";
      }
?>
    </SELECT>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Valid Line Authentication");?></TD>
  <TD>
    <SELECT NAME=LINEAUTH>
<?php
      for($i=1;$i<=3;$i++) {
        print "      <OPTION VALUE=" . $i;
        if ($i == $origdata["LINEAUTH"]) {
          print " SELECTED";
        }
        print ">" . $authlev[$i] . "</OPTION>\n";
      }
?>
    </SELECT>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Snom Network Port Speed/Duplex");?></TD>
  <TD>
    <SELECT NAME=SnomNet>
<?php
      while(list($netkey,$netval) = each($snet)) {
        print "      <OPTION VALUE=" . $netkey;
        if ($netkey == $origdata["SnomNet"]) {
          print " SELECTED";
        }
        print ">" . $netval . "</OPTION>\n";
      }
?>
    </SELECT>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS13')" onmouseout="myHint.hide()"><?php print _("Default FAX Handler");?></TD>
  <TD><INPUT TYPE=TEXT NAME=FAXBOX VALUE="<?php print $origdata["FAXBOX"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS14')" onmouseout="myHint.hide()"><?php print _("Default Ring Timeout");?></TD>
  <TD><INPUT TYPE=TEXT NAME=Timeout VALUE="<?php print $origdata["Timeout"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS15')" onmouseout="myHint.hide()"><?php print _("Default Extension Prefix (2 Digit Dialing)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=DefaultPrefix VALUE="<?php print $origdata["DefaultPrefix"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS16')" onmouseout="myHint.hide()"><?php print _("Default CLI (Number Displayed To Called Party)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=DefCLI VALUE="<?php if ($origdata["DefCLI"] != 0) {print $origdata["DefCLI"];}?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS17')" onmouseout="myHint.hide()"><?php print _("Default ACD Queue Timeout");?></TD>
  <TD><INPUT TYPE=TEXT NAME=QTimeout VALUE="<?php print $origdata["QTimeout"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS18')" onmouseout="myHint.hide()"><?php print _("Default ACD Queue Agent Timeout");?></TD>
  <TD><INPUT TYPE=TEXT NAME=QATimeout VALUE="<?php print $origdata["QATimeout"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS19')" onmouseout="myHint.hide()"><?php print _("Default ACD Queue Agent Penalty Factor");?></TD>
  <TD><INPUT TYPE=TEXT NAME=QAPenalty VALUE="<?php print $origdata["QAPenalty"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS17')" onmouseout="myHint.hide()"><?php print _("Recording Options");?></TD>
  <TD><INPUT TYPE=TEXT NAME=RecOpt VALUE="<?php print $origdata["RecOpt"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS17')" onmouseout="myHint.hide()"><?php print _("Default SIP IP Subnet");?></TD>
  <TD><INPUT TYPE=TEXT NAME=DEFIPNET VALUE="<?php print $origdata["DEFIPNET"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Record Calls By Default");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DEFRECORD<?php if ($origdata["DEFRECORD"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Enable Voice Mail By Default");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DEFNOVMAIL<?php if ($origdata["DEFNOVMAIL"] == "0") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Hangup Calls To Unknown Numbers/DDI");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=UNKDEF<?php if ($origdata["UNKDEF"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Extensions Are Remote By Default");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=REMDEF<?php if ($origdata["REMDEF"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Disable Routing Of Voice Mail To Reception");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NoOper<?php if ($origdata["NoOper"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Require Extension Number With PIN");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=ADVPIN<?php if ($origdata["ADVPIN"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Add Billing Group To CLI (Inbound)");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=AddGroup<?php if ($origdata["AddGroup"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Follow DDI If Exten (Inbound)");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=FollowDDI<?php if ($origdata["FollowDDI"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Authorise Only When Registered By Default (SIP)");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DEFAUTHREG<?php if ($origdata["DEFAUTHREG"] == "1") {print " CHECKED";}?>></TD>
</TR>
</TABLE>
</DIV>
<DIV id=ivr CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS20')" onmouseout="myHint.hide()"><?php print _("Admin Password");?></TD>
  <TD><INPUT TYPE=PASSWORD NAME=AdminPass1 VALUE="<?php print $origdata["AdminPass"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('PS21')" onmouseout="myHint.hide()"><?php print _("Confirm Admin Password");?></TD>
  <TD><INPUT TYPE=PASSWORD NAME=AdminPass2 VALUE="<?php print $origdata["AdminPass"];?>"></TD>
</TR>
</TABLE>
</DIV>
<DIV id=numplan CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("4 Digit Telco Number Pattern");?></TD>
  <TD><INPUT TYPE=TEXT NAME="InternalPat" VALUE="<?php print $origdata["InternalPat"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Telco Number Pattern (Premium)");?></TD>
  <TD><INPUT TYPE=TEXT NAME="TPremiumPat" VALUE="<?php print $origdata["TPremiumPat"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Genral Premium Numbers Pattern");?></TD>
  <TD><INPUT TYPE=TEXT NAME=PremiumPat VALUE="<?php print $origdata["PremiumPat"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Cellular Numbers Pattern");?></TD>
  <TD><INPUT TYPE=TEXT NAME=GSMPat VALUE="<?php print $origdata["GSMPat"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Explicit Long Distance  Numbers Pattern");?></TD>
  <TD><INPUT TYPE=TEXT NAME=NationalPat VALUE="<?php print $origdata["NationalPat"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Explicit Local Numbers Pattern");?></TD>
  <TD><INPUT TYPE=TEXT NAME=LocalPat VALUE="<?php print $origdata["LocalPat"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Toll Free Numbers Pattern");?></TD>
  <TD><INPUT TYPE=TEXT NAME=FreePat VALUE="<?php print $origdata["FreePat"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Voip Numbers Pattern");?></TD>
  <TD><INPUT TYPE=TEXT NAME=VoipPat VALUE="<?php print $origdata["VoipPat"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("GSM Router Trunk Failover Allow Pattern");?></TD>
  <TD><INPUT TYPE=TEXT NAME=GSMFOPat VALUE="<?php print $origdata["GSMFOPat"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Inbound Local Call Pattern (Trunk Forward)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=TRUNKDDIPat VALUE="<?php print $origdata["TRUNKDDIPat"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("DDI Limit Pattern (Accepted DDI More Than 4 Digits)");?></TD>
  <TD><INPUT TYPE=TEXT NAME=DDIPAT VALUE="<?php print $origdata["DDIPAT"];?>"></TD>
</TR>
</TABLE>
</DIV>
<DIV id=autoadd CLASS=formpart>
<TABLE CLASS=formtable>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Start Exten.");?></TD>
  <TD><INPUT TYPE=TEXT NAME="AutoStart" VALUE="<?php print $origdata["AutoStart"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("End Exten.");?></TD>
  <TD><INPUT TYPE=TEXT NAME="AutoEnd" VALUE="<?php print $origdata["AutoEnd"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("VLAN");?></TD>
  <TD><INPUT TYPE=TEXT NAME="AutoVLAN" VALUE="<?php print $origdata["AutoVLAN"];?>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("STUN Server (Linksys)");?></TD>
  <TD><INPUT TYPE=TEXT NAME="AutoSTUN" VALUE="<?php print $origdata["AutoSTUN"];?>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD WIDTH=50% onmouseover="myHint.show('PS0')" onmouseout="myHint.hide()"><?php print _("Lock Settings (Snom)");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=AutoLock<?php if ($origdata["AutoLock"] == "1") {print " CHECKED";}?>></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('PS6')" onmouseout="myHint.hide()"><?php print _("Require Authorisation");?></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=AutoAuth<?php if ($origdata["AutoAuth"] == "1") {print " CHECKED";}?>></TD>
</TR>
</TABLE>
</DIV>
<DIV id=save CLASS=formpart></DIV>
</DIV>
</FORM>

<SCRIPT>
document.getElementById(document.pbxform.curdiv.value).style.visibility='visible';
document.getElementById(document.pbxform.curdiv.value+'_but').style.backgroundColor='<?php print $menubg2;?>';
document.getElementById(document.pbxform.curdiv.value+'_but').style.color='<?php print $menufg2;?>';
</SCRIPT>
