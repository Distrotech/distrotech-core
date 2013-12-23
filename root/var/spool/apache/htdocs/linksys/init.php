<%
include "/var/spool/apache/htdocs/cdr/auth.inc";
include "/var/spool/apache/htdocs/cdr/autoadd.inc";

//$_SERVER['HTTP_USER_AGENT']="Linksys/SPA8000-6.1.3";
//$mac="001ee52e6474";
//$mac="000e08e84c63";

$pwlen=8;

if ($mac == "") {
  exit;
}

$mac=ereg_replace(":","",$mac);


$mac=strtoupper($mac);

$getports="SELECT users.name,registrar,users.fullname,users.secret,users.nat,
                  substr(users.allow,1,position(';' in users.allow)-1),
                  (name=secret OR length(secret) != " . $pwlen . " OR secret='' OR secret IS NULL OR 
                   secret !~ '[0-9]' OR secret !~ '[a-z]' OR secret !~ '[A-Z]')
                 FROM features
                   LEFT OUTER JOIN users ON (name=exten) 
                 WHERE snommac ='" . $mac . "' ORDER BY users.name";
//print $getports;
/*
 * if i dont have one or more ports create one except for SPA 8000 perhaps ??
 */

$codec["g729"]="G729a";
$codec["g723.1"]="G723";
$codec["gsm"]="G729a";
$codec["g726"]="G726-32";
$codec["speex"]="G729a";
$codec["ilbc"]="G726-32";
$codec["ulaw"]="G711u";
$codec["alaw"]="G711a";

$devline["901"]=1;
$autoadd["901"]=1;

$devline["921"]=1;
$autoadd["921"]=1;

$devline["922"]=1;
$autoadd["922"]=1;

$devline["941"]=1;
$slaline["941"]=2;
$autoadd["941"]=1;

$devline["942"]=1;
$slaline["942"]=4;
$autoadd["942"]=1;

$devline["962"]=1;
$slaline["962"]=6;
$autoadd["962"]=1;

$devline["3102"]=2;
//$fxoline["3102"]=1;
$autoadd["3102"]=1;

$devline["2102"]=2;
$autoadd["2102"]=1;

$devline["PAP2T"]=2;
$autoadd["PAP2T"]=1;

$devline["SPA8000"]=8;
$devtrunk["SPA8000"]=4;
$autoadd["SPA8000"]=1;

$uadata=explode(" ",$_SERVER['HTTP_USER_AGENT']);
$devinf=substr($uadata[0],strpos($uadata[0],"/")+1);
$uaver=explode("-",$devinf);


if (count($uaver) > 2) {
  $spaver=$uaver[1];
} else {
  $spaver=$uaver[0];
}

//$spaver="3102";

if (isset($devline[$spaver])) {
  $maxline=$devline[$spaver];
} else {
  $maxline=2;
}

if (isset($devtrunk[$spaver])) {
  $trunkline=$devtrunk[$spaver];
} else {
  $trunkline=0;
}

$uports=pg_query($db,$getports);
if ((pg_num_rows($uports) <= 0) && ($mac != "") && ($autoadd[$spaver] == "1")) {
  $loadnew=FALSE;
  for($cexten=0;$cexten < $maxline;$cexten++) {
    $newexten=createexten($mac,"LINKSYS","","",""); 
    if ($newexten != "") {
      if (($spaver == "3102") && ($cexten > 0)) {
	pg_query($db,"UPDATE users set callerid=name,fromuser=name,context='ddi',sendrpid='yes' where name='" . $newexten . "'");
      }
      $loadnew=TRUE;
    }
  }
  if ($loadnew) {
    $uports=pg_query($db,$getports);
  }
}

$lsysconf="SELECT stunsrv,profile,hostname,rxgain,txgain,vlan,nat FROM atatable WHERE mac='" . $mac . "'";
$lsyscnf=pg_query($db,$lsysconf);
$confnum=pg_num_rows($lsyscnf);
if ($confnum > 0) {
  list($stunsrv,$pserver,$hostname,$rxgain,$txgain,$vlanid,$nat)=pg_fetch_array($lsyscnf,0);
} else {
  $nat="NAT";
}

if ($rxgain == "") {
  $rxgain="-3";
}

if ($txgain == "") {
  $txgain="-3";
}

if ($pserver == "") {
  $pserver=$SERVER_NAME;
}

if ($nat == "NAT") {
  $cmode="PPPOE,DHCP";
  $dhcps="yes";
} else {
  $cmode="DHCP";
  $dhcps="no";
}


print "<flat-profile>\n";
%>
<FXS_Port_Input_Gain><%print $rxgain . "\n";%>
	</FXS_Port_Input_Gain>
<FXS_Port_Output_Gain><%print $txgain . "\n";%>
	</FXS_Port_Output_Gain>
<Networking_Service><%print $nat . "\n";%>
	</Networking_Service>
<Profile_Rule>http://<%print $pserver;%>/init-$MA.cfg
	</Profile_Rule>
<Resync_Periodic>3600
	</Resync_Periodic>
<Enable_Web_Server>yes
	</Enable_Web_Server>
<Web_Server_Port>80
	</Web_Server_Port>
<Enable_Web_Admin_Access>yes
	</Enable_Web_Admin_Access>
<RTP-Start-Loopback_Codec>G711a
	</RTP-Start-Loopback_Codec>
<Protect_IVR_FactoryReset>yes
	</Protect_IVR_FactoryReset>
<Dial_Tone>400*33@-19;10(*/0/1)
	</Dial_Tone>
<Second_Dial_Tone>400*33@-19;10(*/0/1)
	</Second_Dial_Tone>
<Outside_Dial_Tone>400*33@-19;10(*/0/1)
	</Outside_Dial_Tone>
<Busy_Tone>400@-19;10(.5/.5/1)
	</Busy_Tone>
<Reorder_Tone>400@-1910(.25/.25/1)
	</Reorder_Tone>
<Ring_Back_Tone>400*33@-19;*(.4/.2/1,.4/2/1)
	</Ring_Back_Tone>
<SIT1_Tone>950@-19,1400@-19,1800@-19;2(.33/0/1,.33/0/2,.33/1/3)
	</SIT1_Tone>
<SIT2_Tone>950@-19,1400@-19,1800@-19;2(.33/0/1,.33/0/2,.33/1/3)
	</SIT2_Tone>
<SIT3_Tone>950@-19,1400@-19,1800@-19;2(.33/0/1,.33/0/2,.33/1/3)
	</SIT3_Tone>
<SIT4_Tone>950@-19,1400@-19,1800@-19;2(.33/0/1,.33/0/2,.33/1/3)
	</SIT4_Tone>
<MWI_Dial_Tone>400*33@-19;2(.1/.1/1);10(*/0/1)
	</MWI_Dial_Tone>
<Cfwd_Dial_Tone>400*33@-19;2(.2/.2/1);10(*/0/1)
	</Cfwd_Dial_Tone>
<Ring_Waveform>Trapezoid
	</Ring_Waveform>
<Ring1_Cadence>60(.4/.2,.4/2)
	</Ring1_Cadence>
<Voice_Mail_Number>100
	</Voice_Mail_Number>
<Hook_Flash_Timer_Min>.06
	</Hook_Flash_Timer_Min>
<Hook_Flash_Timer_Max>.2
	</Hook_Flash_Timer_Max>
<Time_Zone>GMT+02:00
	</Time_Zone>
<Daylight_Saving_Time_Enable>no
	</Daylight_Saving_Time_Enable>
<Daylight_Saving_Time_Rule>
	</Daylight_Saving_Time_Rule>
<FXS_Port_Impedance>220+820||115nF
	</FXS_Port_Impedance>
<Caller_ID_Method>ETSI FSK With PR(UK)
	</Caller_ID_Method>
<Caller_ID_FSK_Standard>v.23
	</Caller_ID_FSK_Standard>
<Enable_DHCP_Server><%print $dhcps . "\n";%>
	</Enable_DHCP_Server>
<Connection_Type><%print $cmode . "\n";%>
	</Connection_Type>
<Short_Name_1_>L 1
	</Short_Name_1_>
<Short_Name_2_>L 2
	</Short_Name_2_>
<Short_Name_3_>L 3
	</Short_Name_3_>
<Short_Name_4_>L 4
	</Short_Name_4_>
<%
for ($lcnt=1;$lcnt <= $maxline;$lcnt++) {
//extremely high
%>
<Use_Auth_ID_<%print $lcnt;%>_>No
	</Use_Auth_ID_<%print $lcnt;%>_>
<Restrict_Source_IP_<%print $lcnt;%>_>Yes
	</Restrict_Source_IP_<%print $lcnt;%>_>
<Auth_Resync-Reboot_<%print $lcnt;%>_>No
	</Auth_Resync-Reboot_<%print $lcnt;%>_>No
<Network_Jitter_Level_<%print $lcnt;%>_>low
	</Network_Jitter_Level_<%print $lcnt;%>_>
<Jitter_Buffer_Adjustment_<%print $lcnt;%>_>disable
	</Jitter_Buffer_Adjustment_<%print $lcnt;%>_>
<DNS_SRV_Auto_Prefix_<%print $lcnt;%>_>no
	</DNS_SRV_Auto_Prefix_<%print $lcnt;%>_>
<Proxy_Redundancy_Method_<%print $lcnt;%>_>Normal
	</Proxy_Redundancy_Method_<%print $lcnt;%>_>
<Blind_Attn-Xfer_Enable_<%print $lcnt;%>_>yes
	</Blind_Attn-Xfer_Enable_<%print $lcnt;%>_>
<Message_Waiting_<%print $lcnt;%>_>yes
	</Message_Waiting_<%print $lcnt;%>_>
<Sticky_183_<%print $lcnt;%>_>no
	</Sticky_183_<%print $lcnt;%>_>
<Make_Call_Without_Reg_<%print $lcnt;%>_>yes
	</Make_Call_Without_Reg_<%print $lcnt;%>_>
<Ans_Call_Without_Reg_<%print $lcnt;%>_>yes
	</Ans_Call_Without_Reg_<%print $lcnt;%>_>
<Register_Expires_<%print $lcnt;%>_>600
	</Register_Expires_<%print $lcnt;%>_>
<DTMF_Tx_Method_<%print $lcnt;%>_>INFO
	</DTMF_Tx_Method_<%print $lcnt;%>_>
<G723_Enable_<%print $lcnt;%>_>no
	</G723_Enable_<%print $lcnt;%>_>
<G726-16_Enable_<%print $lcnt;%>_>no
	</G726-16_Enable_<%print $lcnt;%>_>
<G726-24_Enable_<%print $lcnt;%>_>no
	</G726-24_Enable_<%print $lcnt;%>_>
<G726-40_Enable_<%print $lcnt;%>_>no
	</G726-40_Enable_<%print $lcnt;%>_>
<FAX_Disable_ECAN_<%print $lcnt;%>_>yes
	</FAX_Disable_ECAN_<%print $lcnt;%>_>
<FAX_Passthru_Codec_<%print $lcnt;%>_>G711a
	</FAX_Passthru_Codec_<%print $lcnt;%>_>
<FAX_Passthru_Method_<%print $lcnt;%>_>ReINVITE
	</FAX_Passthru_Method_<%print $lcnt;%>_>
<FAX_T38_Redundancy_<%print $lcnt;%>_>3
	</FAX_T38_Redundancy_<%print $lcnt;%>_>
<FAX_Process_NSE_<%print $lcnt;%>_>no
	</FAX_Process_NSE_<%print $lcnt;%>_>
<FAX_CNG_Detect_Enable_<%print $lcnt;%>_>yes
	</FAX_CNG_Detect_Enable_<%print $lcnt;%>_>
<FAX_CED_Detect_Enable_<%print $lcnt;%>_>yes
	</FAX_CED_Detect_Enable_<%print $lcnt;%>_>
<%
}
$numreg=pg_num_rows($uports);
$defproxy=$SERVER_NAME;

if ($trunkline > 0) {
  $pstart=$maxline;
  $pmax=$trunkline;
} else {
  $pstart=0;
  $pmax=$maxline;
}

if ($pmax > $numreg) {
  $pmax=$numreg;
}

for($port=$pstart;$port < $pstart+$pmax;$port++) {
  list($exten,$proxy,$dname,$passwd,$pnat,$pcodec,$pwchange)=pg_fetch_array($uports,$port-$pstart);
  if ($pwchange == "t") {
    if (! isset($agi)) {
      require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
      $agi=new AGI_AsteriskManager();
      $agi->connect("127.0.0.1","admin","admin");
    }
    $agi->command("sip prune realtime peer " . $exten);
    $agi->command("sip prune realtime user " . $exten);
    $passwd=randpwgen($pwlen);
    pg_query($db,"UPDATE users SET secret='" . $passwd . "' WHERE name='" . $exten . "'");
    $agi->disconnect();
  }
  if ($proxy == "") {
    $proxy=$defproxy;
  }
  if ($port == $pstart) {
    $defproxy=$proxy;
    if ($hostname == "") {
      $hostname="exten-" . $exten;
    }
    $apasswd="0000";
//   $apasswd=$passwd;
%>
<HostName><%print $hostname . "\n";%>
	</HostName>
<User_Password><%print $apasswd . "\n";%>
	</User_Password>
<Admin_Passwd><%print $apasswd . "\n";%>
	</Admin_Passwd>
<%}%>
<Display_Name_<%print $port+1%>_><%print $dname . "\n";%>
	</Display_Name_<%print $port+1%>_>
<Proxy_<%print $port+1%>_><%print $proxy . "\n";%>
	</Proxy_<%print $port+1%>_>
<User_ID_<%print $port+1%>_><%print $exten . "\n";%>
	</User_ID_<%print $port+1%>_>
<Password_<%print $port+1%>_><%print $passwd . "\n";%>
	</Password_<%print $port+1%>_>
<%
  $pdone[$port+1]=1;
  if ($trunkline == 0) {%>
<Trunk_Group_<%print $lcnt%>_>none
	</Trunk_Group_<%print $lcnt%>_>
<Preferred_Codec_<%print $port+1%>_><%print $codec[$pcodec] . "\n";%>
	</Preferred_Codec_<%print $port+1%>_>
<%
  }

  if (($spaver == "3102") && ($port > 0)) {%>
<VoIP_User_1_Auth_ID_2_><%print $exten . "\n";%>
	</VoIP_User_1_Auth_ID_2_>
<VoIP_User_1_Password_2_><%print $passwd . "\n";%>
	</VoIP_User_1_Password_2_>
<VoIP_User_1_DP_2_>2
	</VoIP_User_1_DP_2_>
<Dial_Plan_1_<%print $port+1%>_>(S0&lt;:<%print $exten%>&gt;)
	</Dial_Plan_1_<%print $port+1%>_>
<PSTN_PIN_Digit_Timeout_2_>0
	</PSTN_PIN_Digit_Timeout_2_>
<VoIP_PIN_Digit_Timeout_2_>0
	</VoIP_PIN_Digit_Timeout_2_>
<VoIP_Caller_Auth_Method_<%print $port+1%>_>HTTP Digest
	</VoIP_Caller_Auth_Method_<%print $port+1%>_>
<One_Stage_Dialing_<%print $port+1%>_>Yes
	</One_Stage_Dialing_<%print $port+1%>_>
<VoIP_Caller_Default_DP_<%print $port+1%>_>2
	</VoIP_Caller_Default_DP_<%print $port+1%>_>
<Line_1_VoIP_Caller_DP_<%print $port+1%>_>2
	</Line_1_VoIP_Caller_DP_<%print $port+1%>_>
<Line_1_Fallback_DP_<%print $port+1%>_>2
	</Line_1_Fallback_DP_<%print $port+1%>_>
<VoIP-To-PSTN_Gateway_Enable_<%print $port+1%>_>Yes
	</VoIP-To-PSTN_Gateway_Enable_<%print $port+1%>_>
<PSTN-To-VoIP_Gateway_Enable_<%print $port+1%>_>yes
	</PSTN-To-VoIP_Gateway_Enable_<%print $port+1%>_>
<PSTN_Caller_Auth_Method_<%print $port+1%>_>None
	</PSTN_Caller_Auth_Method_<%print $port+1%>_>
<PSTN_Ring_Thru_Line_1_<%print $port+1%>_>No
	</PSTN_Ring_Thru_Line_1_<%print $port+1%>_>
<PSTN_Caller_Default_DP_<%print $port+1%>_>1
	</PSTN_Caller_Default_DP_<%print $port+1%>_>
<PSTN_Answer_Delay_<%print $port+1%>_>0
	</PSTN_Answer_Delay_<%print $port+1%>_>
<VoIP_Answer_Delay_<%print $port+1%>_>0
	</VoIP_Answer_Delay_<%print $port+1%>_>
<PSTN_Ring_Thru_Delay_<%print $port+1%>_>1
	</PSTN_Ring_Thru_Delay_<%print $port+1%>_>
<Ringer_Impedance_<%print $port+1%>_>Synthesized (Poland,S.Africa,Slovenia)
	</Ringer_Impedance_<%print $port+1%>_>
<Off_Hook_While_Calling_VoIP_<%print $port+1%>_>Yes
	</Off_Hook_While_Calling_VoIP_<%print $port+1%>_>
<FXO_Port_Impedance_<%print $port+1%>_>220+820||120nF
	</FXO_Port_Impedance_<%print $port+1%>_><%
  }
}

for($lcnt=1;$lcnt <= $maxline + $trunkline;$lcnt++) {%>
<%
  if ($pdone[$lcnt] != "1") {%>
<Preferred_Codec_<%print $lcnt%>_>G729a
	</Preferred_Codec_<%print $lcnt%>_>
<%if ($lcnt <= $maxline) {%>
<Display_Name_<%print $lcnt%>_><%print "Line " . $lcnt . "\n";%>
	</Display_Name_<%print $lcnt%>_>
<%} else {%>
<Display_Name_<%print $lcnt%>_><%print "Trunk " . ($lcnt - $maxline) . "\n";%>
	</Display_Name_<%print $lcnt%>_>
<%
}
/*
<Trunk_Group_<%print $lcnt%>_>1
	</Trunk_Group_<%print $lcnt%>_>
*/
}

if ($lcnt >= $maxline) {%>
<DNS_SRV_Auto_Prefix_<%print $lcnt;%>_>no
	</DNS_SRV_Auto_Prefix_<%print $lcnt;%>_>
<Proxy_Redundancy_Method_<%print $lcnt;%>_>Normal
	</Proxy_Redundancy_Method_<%print $lcnt;%>_>
<Register_Expires_<%print $lcnt;%>_>600
	</Register_Expires_<%print $lcnt;%>_>
<Make_Call_Without_Reg_<%print $lcnt;%>_>yes
	</Make_Call_Without_Reg_<%print $lcnt;%>_>
<Ans_Call_Without_Reg_<%print $lcnt;%>_>yes
	</Ans_Call_Without_Reg_<%print $lcnt;%>_>
<%}
}

if (($confnum <= 0) && ($mac != "") && ($autoadd[$spaver] == "1")) {
  if (!is_array($autovars)) {
    getdefvars();
  }
  $vlanid=$autovars['AutoVLAN'];
  $stunsrv=$autovars['AutoSTUN'];

  pg_query($db,"INSERT INTO atatable (mac,profile,rxgain,txgain,nat,hostname,stunsrv,vlan)
                  VALUES ('" . $mac . "','" . $pserver . "','" . $rxgain . "','" . $txgain . "',
                          '" . $nat . "','" . $hostname . "','" . $stunsrv . "','" . $vlanid . "')");
}
if ($stunsrv != "") {%>
<STUN_Enable>yes
	</STUN_Enable>
<STUN_Server><%print $stunsrv . "\n";%>
	</STUN_Server>
<%} else {%>
<STUN_Enable>no
	</STUN_Enable>
<%}
if ($vlanid > 1) {%>
<Enable_VLAN>yes
	</Enable_VLAN>
<VLAN_ID><%print $vlanid . "\n";%>
	</VLAN_ID>
<%} else {%>
<Enable_VLAN>no
	</Enable_VLAN>
<VLAN_ID>1
	</VLAN_ID>
<%}
/*
<Dial_Plan_8_2_>(S0<:@gw1>)
	</Dial_Plan_8_2_>
<PSTN_Caller_Default_DP_2_>8
	</PSTN_Caller_Default_DP_2_>
<PSTN_Answer_Delay_2_>0
	</PSTN_Answer_Delay_2_>
<FX0_Port_Impedance_2_>220+820||120nF
	</FX0_Port_Impedance_2_>
<FX0_Port_Impedance>220+820||120nF
	</FX0_Port_Impedance>
*/
%>
</flat-profile>
