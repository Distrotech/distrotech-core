<?php
include "../cdr/auth.inc";
include "../cdr/autoadd.inc";
include "../ldap/ldapbind.inc";

$mac=strtoupper($_GET['mac']);

$auth_ussr=ldap_search($ds,"ou=snom","(&(objectClass=person)(cn=snom))");

if (ldap_count_entries($ds,$auth_ussr) <= 0 ) {
  $dn="cn=Snom,ou=Snom";
  $info["objectclass"][0]="person";
  $info["cn"]="snom";
  $info["sn"]="Snom Global Phone Book";
  $info["userpassword"]="snom";

  ldap_add($ds,$dn,$info);

  if (ldap_errno($ds) == "32") {
    $info2["objectclass"][0]="organizationalUnit";
    $info2["ou"]="snom";
    $dn2="ou=snom";
    ldap_add($ds,$dn2,$info2);
    ldap_add($ds,$dn,$info);
  }
  $auth_ussr=ldap_search($ds,"ou=snom","(&(objectClass=person)(cn=snom))");
}

$auth_ures=ldap_first_entry($ds,$auth_ussr);
$suser=ldap_get_attributes($ds,$auth_ures);

$pwlen=8;
$getphoneq="SELECT name,secret,fullname,registrar,nat,dtmfmode,vlan,
                   (name=secret OR length(secret) != " . $pwlen . " OR secret='' OR secret IS NULL OR
                    secret !~ '[0-9]' OR secret !~ '[a-z]' OR secret !~ '[A-Z]'),
		   case when (encryption_taglen = '32') then encryption||',32bit' else encryption end,
                   transport
              FROM users
                LEFT OUTER JOIN features ON (exten=name)
              WHERE snommac='" . $mac . "' LIMIT 1";
$getphone=pg_query($db,$getphoneq);

if (pg_num_rows($getphone) == 0) {
  if (createexten($mac,"DUXBURY","","","") > 0) {
    $getphone=pg_query($db,$getphoneq);
  }
  if (pg_num_rows($getphone) == 0) {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    exit;
  }
}

list($exten,$pass,$name,$domain,$nat,$dtmfmode,$vlantag,$pwchange,$encrypt,$transport)=pg_fetch_array($getphone,0,PGSQL_NUM);

if ($domain == "" ) {
  $domain=$_SERVER['SERVER_NAME'];
}

?>
<<VOIP CONFIG FILE>>Version:2.0002

<GLOBAL CONFIG MODULE>
DHCP Auto DNS      :1
DHCP Auto Time     :1
Host Name          :<?php print "exten-" . $exten. "\n"?>
RTP Initial Port   :10000
RTP Port Quantity  :200
SNTP Timeout       :60
Default UI         :1
MTU Length         :1500
Enable Call History:1
Active Uri IP      :
Boot Completed Url :
Reg On Url         :
Reg Off Url        :
Reg Failed Url     :
Offhook Url        :
Onhook Uri         :
Incmoing Call Url  :
Outgoing Call Url  :
Call Active Url    :
Call Stop Url      :
DND On Url         :
DND Off Url        :
Always FWD On Url  :
Always FWD Off Url :
Busy FWD On Url    :
Busy FWD Off Url   :
No Ans FWD On Url  :
No Ans FWD Off Url :
Transfer Url       :
B Transfer Url     :
A Transfer Url     :
Hold Url           :
Unhold Url         :
Mute Url           :
Unmute Url         :
Missed call Url    :
IP Change Url      :
Idle to Busy Url   :
Busy to Idle Url   :

<LAN CONFIG MODULE>
LAN IP             :192.168.10.1
LAN SubNetMask     :255.255.255.0
Enable Bridge Mode :1
Enable Port Mirror :0

<TELE CONFIG MODULE>
Dial by Pound      :1
BTransfer by Pound :1
Onhook to BXfer    :0
Onhook to AXfer    :0
Memory Key to BXfer:0
Dial Fixed Length  :0
Fixed Length Nums  :11
Dial by Timeout    :1
Dial Timeout value :5
Dialpeer With Line :0
Port Sequence      :0
Accept Any Call    :1
Main Digit Prefix  :
IP Dial Prefix     :.
--Port Config--    :
P1 Enable DND      :0
P1 Mute Ringing    :0
P1 CWaiting Tone   :1
P1 Ban Dial Out    :0
P1 Ban Empty CID   :0
P1 Enable CLIP     :1
P1 CallWaiting     :1
P1 CallTransfer    :1
P1 CallSemiXfer    :1
P1 CallConference  :1
P1 AutoAnswer      :0
P1 No Answer Time  :20
P1 Warm Line Time  :0
P1 Port Ext Num    :
P1 Hotline Num     :
P1 SRV Record Num  :
P1 Use SRV Record  :0
P1 Auto PickupNext :0
P1 Busy No Line    :0
P1 Auto Onhook     :1
P1 Auto Onhook Time:3
P1 DND Code        :1
P1 Busy Code       :2
P1 Reject Code     :3
P1 Enable Intercom :1
P1 Intercom Mute   :0
P1 Intercom Tone   :1
P1 Intercom Barge  :1
P1 Use Auto Redial :0
P1 AutoRedial Delay:10
P1 AutoRedial Times:10
P1 Call Complete   :0
P1 Hide DTMF Type  :0
P1 Talk DTMF Tone  :1
P1 Dial DTMF Tone  :1
P1 Psw Dial Mode   :0
P1 Psw Dial Length :0
P1 Psw Dial Prefix :
P1 Enable MultiLine:1
P1 Allow IP Call   :1
Default Ext line   :0
Default Ans mode   :0
Default Dial mode  :0

<DSP CONFIG MODULE>
Signal Standard    :14
Enable MWI Tone    :1
Onhook Time        :200
G729 Payload Len   :1
G723 Bit Rate      :1
G722 Timestamps    :0
VAD                :0
Dtmf Payload Type  :101
RTP Probe          :0
HD Voice           :1
TX AGC             :0
RX AGC             :0
Sidetone GAIN      :1
--Port Config--    :
P1 Voice Codec1    :G729/8000
P1 Voice Codec2    :G726-32/8000
P1 Voice Codec3    :PCMA/8000
P1 Voice Codec4    :G722/8000
P1 Voice Codec5    :
P1 Voice Codec6    :
--Codec Config--   :
Codec1 Format      :PCMA/8000;payload=8;
Codec2 Format      :PCMU/8000;payload=0;
Codec3 Format      :G722/8000;payload=9;
Codec4 Format      :G723/8000;payload=4;bitrate=6.3;
Codec5 Format      :G729/8000;payload=18;annexb=no;
Codec6 Format      :G726-32/8000;payload=2;
Codec7 Format      :telephone-event/8000;payload=101;

<SIP CONFIG MODULE>
SIP  Port          :5060
STUN Server        :
STUN Port          :3478
STUN Refresh Time  :50
SIP Wait Stun Time :800
Extern NAT Addrs   :
Reg Fail Interval  :60
SIP Pswd Encryption:0
Strict BranchPrefix:0
Video Mute Attr    :0
Enable Group Backup:0
--SIP Line List--  :
SIP1 Phone Number  :<?php print $exten . "\n";?>
SIP1 Display Name  :<?php print $name . "\n";?>
SIP1 Sip Name      :
SIP1 Register Addr :<?php print $domain . "\n";?>
SIP1 Register Port :5060
SIP1 Register User :<?php print $exten . "\n";?>
SIP1 Register Pswd :<?php print $pass . "\n";?>
SIP1 Register TTL  :300
SIP1 Enable Reg    :1
SIP1 Proxy Addr    :<?php print $domain . "\n";?>
SIP1 Proxy Port    :5060
SIP1 Proxy User    :<?php print $exten . "\n";?>
SIP1 Proxy Pswd    :<?php print $pass . "\n";?>
SIP1 BakProxy Addr :
SIP1 BakProxy Port :5060
SIP1 Enable Failbac:0
SIP1 Signal Crypto :0
SIP1 SigCrypto Key :
SIP1 Media Crypto  :0
SIP1 MedCrypto Key :
SIP1 SRTP Auth-Tag :0
SIP1 Local Domain  :
SIP1 FWD Type      :0
SIP1 FWD Number    :
SIP1 FWD Timer     :60
SIP1 Ring Type     :0
SIP1 Hotline Num   :
SIP1 Enable Hotline:0
SIP1 WarmLine Time :0
SIP1 Pickup Num    :
SIP1 Join Num      :
SIP1 NAT UDPUpdate :1
SIP1 UDPUpdate TTL :60
SIP1 Server Type   :0
SIP1 User Agent    :
SIP1 PRACK         :0
SIP1 Keep AUTH     :0
SIP1 Session Timer :0
SIP1 S.Timer Expire:0
SIP1 Enable GRUU   :0
SIP1 DTMF Mode     :2
SIP1 DTMF Info Mode:0
SIP1 NAT Type      :0
SIP1 Enable Rport  :1
SIP1 Subscribe     :1
SIP1 Sub Expire    :300
SIP1 Single Codec  :0
SIP1 CLIR          :0
SIP1 Strict Proxy  :0
SIP1 Direct Contact:0
SIP1 History Info  :0
SIP1 DNS SRV       :0
SIP1 XFER Expire   :0
SIP1 Ban Anonymous :0
SIP1 Dial Off Line :1
SIP1 Quota Name    :0
SIP1 Presence Mode :0
SIP1 RFC Ver       :1
SIP1 Signal Port   :0
SIP1 Transport     :<?php if ($transport == "udp") {print "0\n";} else {print "1\n";}?>
SIP1 Use SRV Mixer :0
SIP1 SRV Mixer Uri :
SIP1 Long Contact  :1
SIP1 Auto TCP      :1
SIP1 Uri Escaped   :0
SIP1 Click to Talk :0
SIP1 MWI Num       :100
SIP1 CallPark Num  :
SIP1 MSRPHelp Num  :
SIP1 User Is Phone :1
SIP1 Auto Answer   :0
SIP1 NoAnswerTime  :60
SIP1 MissedCallLog :1
SIP1 SvcCode Mode  :0
SIP1 DNDOn SvcCode :
SIP1 DNDOff SvcCode:
SIP1 CFUOn SvcCode :
SIP1 CFUOff SvcCode:
SIP1 CFBOn SvcCode :
SIP1 CFBOff SvcCode:
SIP1 CFNOn SvcCode :
SIP1 CFNOff SvcCode:
SIP1 ANCOn SvcCode :
SIP1 ANCOff SvcCode:
SIP1 VoiceCodecMap :G729,G726-32,G711A,G722
SIP1 BLFList Uri   :
SIP1 BLF Server    :
SIP1 Respond 182   :1
SIP1 Enable BLFList:0
SIP1 Caller Id Type:1
SIP1 Syn Clock Time:0
SIP1 Use VPN       :0
SIP1 Enable DND    :0

<IAX2 CONFIG MODULE>
Server Address     :
Server Port        :4569
User Name          :
User Password      :
User Number        :
Voice Number       :0
Voice Text         :mail
EchoTest Number    :1
EchoTest Text      :echo
Local Port         :4569
Enable Register    :0
Refresh Time       :60
Enable G.729       :0

<MMI CONFIG MODULE>
Telnet Port        :23
Web Port           :80
Web Server Type    :0
Https Web Port     :443
Remote Control     :1
Enable MMI Filter  :0
Telnet Prompt      :
--MMI Account--    :
Account1 Name      :admin
Account1 Password  :admin
Account1 Level     :10
Account2 Name      :guest
Account2 Password  :guest
Account2 Level     :5

<QOS CONFIG MODULE>
Enable VLAN        :1
Enable diffServ    :1
LLDP Transmit      :1
LLDP Refresh Time  :60
LLDP Learn Policy  :1
Singalling DSCP    :46
Voice DSCP         :46
VLAN ID            :<?php print $vlantag . "\n";?>
Signalling Priority:0
Voice Priority     :0
VLAN Recv Check    :1
Enable PVID        :1
PVID Value         :254

<DHCP CONFIG MODULE>
DHCP Server Type   :0

<NAT CONFIG MODULE>
Enable Nat         :0
Enable Ftp ALG     :1
Enable H323 ALG    :0
Enable PPTP ALG    :1
Enable IPSec ALG   :1

<PHONE CONFIG MODULE>
Menu Password      :123
KeyLock Password   :123
Fast Keylock Code  :
Enable KeyLock     :0
Emergency Call     :110
LCD Title          :<?php print $name . " (" . $exten . ")\n";?>
LCD Constrast      :5
LCD Luminance      :1
Backlight Off Time :30
Enable Power LED   :0
Time Display Style :0
Enable TimeDisplay :1
Alarm  Clock       :0,,1
Date Display Style :0
Date Separator     :0
Enable Pre-Dial    :1
Default Line       :1
Enable Default Line:0
Enable Auto SelLine:1
Agent Username     :
Agent Password     :
Agent Number       :
Agent Sipline      :0
Agent Status       :0
Fkey1 Type         :2
Fkey1 Value        :SIP1
Fkey1 Title        :
Fkey2 Type         :2
Fkey2 Value        :SIP1
Fkey2 Title        :
Fkey3 Type         :2
Fkey3 Value        :SIP1
Fkey3 Title        :
Fkey4 Type         :2
Fkey4 Value        :SIP1
Fkey4 Title        :
Fkey5 Type         :3

<AUTOUPDATE CONFIG MODULE>
PNP Enable         :0
PNP IP             :224.0.1.75
PNP Port           :5060
PNP Transport      :0
PNP Interval       :1
<<END OF FILE>>
