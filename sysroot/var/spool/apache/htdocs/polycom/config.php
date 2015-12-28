<?php
include "getphone.inc";

/* transport
UDPOnly
TCPpreferred
DNSnaptr
TLS
TCPOnly
*/

if ($mac == "") {
  exit;
}

if ($proxy == "") {
  $proxy=$SERVER_NAME;
}

if (($ptype == "IP_601") || ($ptype == "IP_600")){
  $linecnt=6;
} elseif ($ptype == "IP_500") {
  $linecnt=3;
} elseif ($ptype == "IP_4000") {
  $linecnt=1;
} else {
  $linecnt=2;
}

if ($encryption ==  "no") {
  $srtp=0;
  $aes_32=0;
  $encoffer=0;
  $encforce=0;
} else {
  $srtp=1;
  $encoffer=1;
  $encdat=explode(",",$encryption);
  $encforce= ($encdat[0] == "yes") ? 1 : 0;
  $aes_32= ($encdat[1] == "32bit") ? 1 : 0;
}

?><<?php print "?"?>xml version="1.0" encoding="UTF-8" standalone="yes"?>
<!-- Example Per-phone Configuration File -->
<!-- $Revision: 2.1.2 $  $Date: 2007/07/28 17:05:46 $ -->
<phone1>
  <reg
    reg.1.displayName="<?php print $dname;?>"
    reg.1.address="<?php print $exten;?>"
    reg.1.auth.userId="<?php print $exten;?>"
    reg.1.auth.password="<?php print $passwd;?>" 
    reg.1.server.1.address="<?php print $proxy;?>"
    reg.1.server.1.port="5060"
    reg.1.server.1.transport="UDPonly"
    reg.1.server.1.expires="300"
    reg.1.server.1.register="1"
    reg.1.server.1.retryTimeOut="30"
    reg.1.server.1.retryMaxCount="0"
    reg.1.ringType="2"
    reg.1.lineKeys="<?php print $linecnt?>"
  />
  <divert>
    <fwd
      divert.fwd.1.enabled="0"
    />
    <busy
      divert.busy.1.enabled="0"
    />
    <noanswer
      divert.noanswer.1.enabled="0"
    />
    <dnd
      divert.dnd.1.enabled="0"
    />
  </divert>
  <msg>
    <mwi 
      msg.mwi.1.callBackMode="registration"
      msg.mwi.1.callBack="100"
    />
  </msg>
  <nat
    nat.ip="" 
    nat.signalPort=""
    nat.mediaPortStart=""
  />
  <attendant
    attendant.uri="<?php print $exten;?>"
  />
  <sip>
<?php
  if ($lnsort == "1") {
?>
    <directory
        dir.local.volatile.2meg="1"
        dir.local.volatile.4meg="1"
        dir.local.volatile.8meg="1"
        dir.search.field="0"
	dir.local.readonly="1"
    />
<?php
  } else {
?>
    <directory
        dir.local.volatile.2meg="1"
        dir.local.volatile.4meg="1"
        dir.local.volatile.8meg="1"
	dir.local.readonly="1"
    />
<?php
  }
?>
    <dialplan dialplan.impossibleMatchHandling=2>
      <digitmap
        dialplan.digitmap="xxxxT|0T|00[1-9]xxxxxxx.T|[1-9]xxxxxx|[01][1-9]xxxxxxxx|1021x|102x|xxxT|5xxT|9xxT|5xx*|*x.T"
        dialplan.digitmap.timeOut="5|5|5|5|5|5|5|5|5|5|5|5"
      />
      <routing>
        <server
          dialplan.1.routing.server.1.address="<?php print $proxy;?>"
          dialplan.1.routing.server.1.port="5060"
        />
      </routing>
    </dialplan>
    <preferences
      voice.codecPref.G729AB="1"
      voice.codecPref.G711A="2"
      voice.codecPref.G711Mu="3"
      voice.codecPref.IP_300.G729AB="1"
      voice.codecPref.IP_300.G711A="2"
      voice.codecPref.IP_300.G711Mu="3"
      voice.codecPref.IP_650.G729AB="1"
      voice.codecPref.IP_650.G711A="2"
      voice.codecPref.IP_650.G711Mu="3"
      voice.codecPref.IP_650.G722=""
      voice.codecPref.IP_4000.G729AB="1"
      voice.codecPref.IP_4000.G711A="2"
      voice.codecPref.IP_4000.G711Mu="3"
    />
    <user_preferences
      up.headsetMode="1"
      up.useDirectoryNames="1"
    />
    <voice>
      <volume voice.volume.persist.handset="1" voice.volume.persist.headset="1" voice.volume.persist.handsfree="1"/>
    </voice>
    <logging>
      <render
	log.render.realtime="0"
        log.render.stdout="0"
        log.render.file="0"
      />
    </logging>
    <feature 
      feature.1.enabled="1"
    />
    <microbrowser mb.proxy="">
       <main mb.main.home="http://<?php print $SERVER_NAME;?>/polyxml"/>
    </microbrowser>
    <voIpProt>
      <SIP
        voIpProt.SIP.useRFC2543hold="1">
          <outboundProxy
            voIpProt.SIP.outboundProxy.address="<?php print $proxy;?>"
            voIpProt.SIP.outboundProxy.port="5060"
            voIpProt.SIP.outboundProxy.transport="UDPonly"
         />
         <specialEvent
           voIpProt.SIP.specialEvent.checkSync.alwaysReboot="1"
         />
      </SIP>
      <server
        voIpProt.server.1.address="<?php print $proxy;?>"
        voIpProt.server.1.port="5060"
        voIpProt.server.1.transport="UDPonly"
        voIpProt.server.1.expires="300"
        voIpProt.server.1.retryTimeOut="30"
        voIpProt.server.1.retryMaxCount="0"
        voIpProt.server.1.expires.lineSeize="30"
      />
    </voIpProt>
    <TCP_IP>
      <SNTP tcpIpApp.sntp.daylightSavings.enable="0"/>
    </TCP_IP>
    <security sec.tagSerialNo="0">
      <SRTP
        sec.srtp.enable="<?php print $srtp;?>"
        sec.srtp.leg.enable="<?php print $srtp;?>"
        sec.srtp.offer="<?php print $encoffer;?>"
        sec.srtp.require="<?php print $encforce;?>"
        sec.srtp.leg.allowLocalConf="0"
        sec.srtp.offer.HMAC_SHA1_32="<?php print ($aes_32 == "1") ? 1 : 0;?>"
        sec.srtp.offer.HMAC_SHA1_80="<?php print ($aes_32 == "1") ? 0 : 1;?>"
      />
    </security>
  </sip>
</phone1>
