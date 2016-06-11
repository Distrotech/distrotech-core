<?php
include "../cdr/auth.inc";
include "../cdr/autoadd.inc";

$mac=strtoupper($mac);

if ($mac == "") {
  exit;
}

$getphoneq="SELECT name,secret,fullname,registrar,snomlock,nat,dtmfmode,vlan,cdnd from users 
               LEFT OUTER JOIN features ON (name=exten) WHERE snommac='" . $mac . "' LIMIT 1";
$getphone=pg_query($db,$getphoneq);

if (pg_num_rows($getphone) == 0) {
  if (createexten($mac,"SNOM","","","") > 0) {
    $getphone=pg_query($db,$getphoneq);
  }
}

list($exten,$pass,$name,$domain,$usermode,$nat,$dtmfmode,$vlantag,$dndsetting)=pg_fetch_array($getphone,0);
if ($vlantag > 1) {
  $vlanprio=5;
} else {
  $vlantag=0;
  $vlanprio=0;
}

if ($domain == "" ) {
  $domain=$SERVER_NAME;
}
?>
%SIP_RPORT_ENABLE%:1
%SIP_STUN_ENABLE%:0
%NETWORK_STUN_SERVER%:"stun01.STUNserver.com"
%SIP_STUN_BINDTIME_GUARD%:80
%SIP_STUN_BINDTIME_DETERMINE%:0
%SIP_STUN_KEEP_ALIVE_TIME%:90
%NETWORK_VLAN_ID%:<?php print $vlantag . "\n";?>
%NETWORK_VLAN_USER_PRIORITY%:<?php print $vlanprio . "\n";?>
%MANAGEMENT_TRANSFER_PROTOCOL%:1
%VOIP_LOG_AUTO_UPLOAD%:0
%NETWORK_FWU_SERVER%: "provisioning.snom.com"
%FWU_TFTP_SERVER_PATH%:"/m3/firmware/"
%FWU_POLLING_ENABLE%:1
%NETWORK_SNTP_SERVER%:"<?php print $domain;?>"
%NETWORK_SNTP_SERVER_UPDATE_TIME%:255
%AUTOMATIC_SYNC_CLOCK%:1
%GMT_TIME_ZONE%:15
%DST_ENABLE%:0
%SRV_0_SIP_UA_DATA_SERVER_IS_LOCAL%:1
%SUBSCR_0_UA_DATA_DISP_NAME%:"<?php print $name;?>"
%SUBSCR_0_SIP_UA_DATA_SIP_NAME%:"<?php print $exten;?>"
%SUBSCR_0_UA_DATA_AUTH_PASS%:"<?php print $pass;?>"
%SUBSCR_0_SIP_UA_DATA_SIP_NAME_ALIAS%:"<?php print $exten;?>"
%SUBSCR_0_SIP_UA_DATA_VOICE_MAILBOX_NAME%:""
%SUBSCR_0_SIP_UA_DATA_VOICE_MAILBOX_NUMBER%:""
%SRV_0_SIP_UA_DATA_DOMAIN%:"<?php print $domain;?>"
%SRV_0_SIP_UA_DATA_PROXY_ADDR%:""
%SUBSCR_0_UA_DATA_AUTH_NAME%:"<?php print $exten;?>"
%SRV_0_SIP_UA_DATA_SERVER_PORT%:5060
%SRV_0_SIP_UA_DATA_PROXY_PORT%: 5060
%SRV_0_SIP_UA_DATA_REREG_TIME%:600
%SRV_0_SIP_URI_DOMAIN_CONFIG%:0
%SRV_0_DTMF_SIGNALLING%:2
%SRV_0_SIP_UA_CODEC_PRIORITY%:4,0xFF
%CODEC_SILENCE_SUPPRESSION%:0
%SRV_0_SIP_UA_DATA_SERVER_TYPE%:1
%SUBSCR_0_SIP_UA_DATA_SERVER_ID%:0
%COUNTRY_VARIANT_ID%:0x10
%EMERGENCY_PRIMARY_PORT%:1
%HANDSET_1_NAME%:"<?php print $name;?>"
END_OF_FILE
