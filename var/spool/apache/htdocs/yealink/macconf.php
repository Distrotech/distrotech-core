<%
include "../cdr/auth.inc";
include "../cdr/autoadd.inc";
include "../ldap/ldapcon.inc";

$mac=strtoupper($mac);

$auth_uss=ldap_bind($ds,$LDAP_ROOT_DN,$LDAP_ROOT_PW);
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
  if (createexten($mac,"YEALINK","","","") > 0) {
    $getphone=pg_query($db,$getphoneq);
  }
}

list($exten,$pass,$name,$domain,$nat,$dtmfmode,$vlantag,$pwchange,$encrypt,$transport)=pg_fetch_array($getphone,0,PGSQL_NUM);

if ($dtmfmode == "info") {
  $dtmfm="2";
} else if ($dtmfmode == "rfc2833") {
  $dtmfm="1";
} else {
  $dtmfm="0";
}

if ($pwchange == "t") {
  if (! isset($agi)) {
    require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
    $agi=new AGI_AsteriskManager();
    $agi->connect("127.0.0.1","admin","admin");
  }
  $agi->command("sip prune realtime peer " . $exten);
  $agi->command("sip prune realtime user " . $exten);
  $pass=randpwgen($pwlen);
  pg_query($db,"UPDATE users SET secret='" . $pass . "' WHERE name='" . $exten . "'");
  $agi->disconnect();
}

if ($encrypt != "no") {
  $encdat=explode(",",$encrypt);
  if ($encdat[0] == "yes") {
    $encrypt="1";
  } else {
    $encrypt="0";
  }
} else {
  $encrypt="0";
}

if ($transport == "udp") {
  $trans="0";
} else if ($transport == "tcp") {
  $trans="1";
} else if ($transport == "tls") {
  $trans="2";
}

if ($domain == "" ) {
  $domain=$LOCAL_DOMAIN;
}

%>
[ AdminPassword ]
path = /config/Setting/autop.cfg
password = <%print $pass . "\n";%>

[ UserPassword ]
path = /config/Setting/autop.cfg
password = <%print $exten . "\n";%>

[ autoprovision ]
path = /config/Setting/autop.cfg  
server_address = http://<%print $SERVER_NAME . "/" . $mac . ".cfg\n";%>

[ account ]
path = /config/voip/sipAccount0.cfg
Enable = 1
Label = <%print $name . "\n";%>
DisplayName = <%print $exten . "\n";%>
AuthName = <%print $exten . "\n";%>
Username = <%print $exten . "\n";%>
Password = <%print $pass . "\n";%>
Transport = <%print $trans . "\n";%>
srtp_encryption = <%print $encrypt . "\n";%>
SIPServerHost = <%print $domain . "\n";%>
SIPServerPort = <%print ($trans == "2") ? "5061\n" : "5060\n"%>
UseOutboundProxy = 0
SubsribeRegister = 1
SubsribeMWI = 1
dialoginfo_callpickup = 1

[ DTMF ]
path = /config/voip/sipAccount0.cfg
DTMFPayload = 101
DTMFToneLen = 300
DTMFInbandTransfer = <%print $dtmfm . "\n";%>
InfoType = 2

[ audio0 ]
path = /config/voip/sipAccount0.cfg
enable = 0
PayloadType = PCMU
priority = 0
rtpmap = 0

[ audio1 ]
path = /config/voip/sipAccount0.cfg
enable = 1
PayloadType = PCMA
priority = 2
rtpmap = 8

[ audio2 ]
path = /config/voip/sipAccount0.cfg
enable = 0
PayloadType = G723_53
priority = 0
rtpmap = 4

[ audio3 ]
path = /config/voip/sipAccount0.cfg
enable = 0
PayloadType = G723_63
priority = 0
rtpmap = 4

[ audio4 ]
path = /config/voip/sipAccount0.cfg
enable = 1
PayloadType = G729
priority = 1
rtpmap = 18

[ audio5 ]
path = /config/voip/sipAccount0.cfg
enable = 1
PayloadType = G722
priority = 3
rtpmap = 9

[ audio6 ]
path = /config/voip/sipAccount0.cfg
enable = 0
PayloadType = iLBC
priority = 0
rtpmap = 102

[ audio7 ]
path = /config/voip/sipAccount0.cfg
enable = 0
PayloadType = G726-16
priority = 0
rtpmap = 112

[ audio8 ]
path = /config/voip/sipAccount0.cfg
enable = 0
PayloadType = G726-24
priority = 0
rtpmap = 102

[ audio9 ]
path = /config/voip/sipAccount0.cfg
enable = 0
PayloadType = G726-32
priority = 0
rtpmap = 2

[ audio10 ]
path = /config/voip/sipAccount0.cfg
enable = 0
PayloadType = G726-40
priority = 0
rtpmap = 104

[ audio11 ]
path = /config/voip/sipAccount0.cfg
enable = 0
PayloadType = iLBC_13_3
priority = 0
rtpmap = 97
para = mode=30

[ audio12 ]
path = /config/voip/sipAccount0.cfg
enable = 0
PayloadType = iLBC_15_2
priority = 0
rtpmap = 97
para = mode=20

[ Message ]
path = /config/Features/Message.cfg
VoiceNumber0 = 100
VoiceNumber1 = 100
VoiceNumber2 = 100
VoiceNumber3 = 100
VoiceNumber4 = 100
VoiceNumber5 = 100

[ Country ]
path = /config/voip/tone.ini
Country = Great Britain

[ Time ]  
path = /config/Setting/Setting.cfg
TimeZone = +2
TimeServer1 = <%print $SERVER_NAME . "\n";%>
TimeServer2 = <%print $SERVER_NAME . "\n";%>
SummerTime = 0

[ LDAP ]
path = /config/Contacts/LDAP.cfg
NameFilter = (&(telephoneNumber=*)(cn=%))
NumberFilter = (&(telephoneNumber=%)(cn=*))
host = <%print $SERVER_NAME . "\n";%>
port = 389
base =
user = cn=Snom,ou=Snom
pswd = <%print $suser["userPassword"][0] . "\n";%>
MaxHits = 50
NameAttr = cn
NumbAttr = telephoneNumber
DisplayName = %cn
version = 3
SearchDelay = 0
CallInLookup = 1
LDAPSort = 1
DialLookup = 1

<%
if ($vlantag > 1) {
%>
[ cutom_option  ]
path = /config/Setting/autop.cfg
cutom_option_code0 = 
cutom_option_type0 = 1

[ VLAN ]
path = /config/Network/Network.cfg
USRPRIORITY = 5
ISVLAN = 1
VID = <%print $vlantag . "\n";%>

<%
} else {
%>

[ cutom_option  ]
path = /config/Setting/autop.cfg
cutom_option_code0 = 250
cutom_option_type0 = 1

[ VLAN ]
path = /config/Network/Network.cfg
USRPRIORITY = 0
ISVLAN = 0
VID = 0

<%
}

for ($lkey=11;$lkey <=16;$lkey++) {
%>
[ memory<%print $lkey;%> ]
path = /config/vpPhone/vpPhone.ini
Line = 1
DKtype = 15

<%
}
%>
[ programablekey3 ]
path = /config/vpPhone/vpPhone.ini
DKtype = 9
Line = 1
Value = *8
