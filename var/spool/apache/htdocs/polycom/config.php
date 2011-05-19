<%
include "getphone.inc";

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

%><<%print "?"%>xml version="1.0" encoding="UTF-8" standalone="yes"?>
<!-- Example Per-phone Configuration File -->
<!-- $Revision: 2.1.2 $  $Date: 2007/07/28 17:05:46 $ -->
<phone1>
  <reg
    reg.1.displayName="<%print $dname;%>"
    reg.1.address="<%print $exten;%>"
    reg.1.auth.userId="<%print $exten;%>"
    reg.1.auth.password="<%print $passwd;%>" 
    reg.1.server.1.address="<%print $proxy;%>"
    reg.1.server.1.port="5060"
    reg.1.server.1.transport="UDPonly"
    reg.1.server.1.expires="300"
    reg.1.server.1.register="1"
    reg.1.server.1.retryTimeOut="30"
    reg.1.server.1.retryMaxCount="0"
    reg.1.ringType="2"
    reg.1.lineKeys="<%print $linecnt%>"
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
    attendant.uri="<%print $exten;%>"
  />
  <sip>
<%
  if ($lnsort == "1") {
%>
    <directory
        dir.local.volatile.2meg="1"
        dir.local.volatile.4meg="1"
        dir.local.volatile.8meg="1"
        dir.search.field="0"
	dir.local.readonly="1"
    />
<%
  } else {
%>
    <directory
        dir.local.volatile.2meg="1"
        dir.local.volatile.4meg="1"
        dir.local.volatile.8meg="1"
	dir.local.readonly="1"
    />
<%
  }
%>
    <dialplan dialplan.impossibleMatchHandling=2>
      <digitmap
        dialplan.digitmap="xxxxT|0T|00[1-9]xxxxxxx.T|[1-9]xxxxxx|[01][1-9]xxxxxxxx|1021x|102x|xxxT|5xxT|9xxT|5xx*|*x.T"
        dialplan.digitmap.timeOut="5|5|5|5|5|5|5|5|5|5|5|5"
      />
      <routing>
        <server
          dialplan.1.routing.server.1.address="<%print $proxy;%>"
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
       <main mb.main.home="http://<%print $SERVER_NAME;%>/polyxml"/>
    </microbrowser>
    <voIpProt>
      <SIP
        voIpProt.SIP.useRFC2543hold="1">
          <outboundProxy
            voIpProt.SIP.outboundProxy.address="<%print $proxy;%>"
            voIpProt.SIP.outboundProxy.port="5060"
            voIpProt.SIP.outboundProxy.transport="UDPonly"
         />
         <specialEvent
           voIpProt.SIP.specialEvent.checkSync.alwaysReboot="1"
         />
      </SIP>
      <server
        voIpProt.server.1.address="<%print $proxy;%>"
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
  </sip>
</phone1>
