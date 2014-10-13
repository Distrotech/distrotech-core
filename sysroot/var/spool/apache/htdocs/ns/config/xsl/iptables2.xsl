<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="extip" select="/config/IP/Interfaces/Interface[text() = $extiface]/@ipaddr"/>
<xsl:variable name="loclan" select="concat(/config/IP/Interfaces/Interface[text() = $intiface]/@nwaddr,'/',/config/IP/Interfaces/Interface[text() = $intiface]/@subnet)"/>
<xsl:variable name="dynzone" select="/config/DNS/Config/Option[@option = 'DynZone']"/>
<xsl:variable name="pppint" select="/config/Radius/Config/Option[@option = 'PPPoEIF']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="pppip" select="/config/Radius/Config/Option[@option = 'PPPoE']"/>
<xsl:variable name="sfnew" select="'-m state --state ESTABLISHED,NEW '"/>
<xsl:variable name="sfnre" select="'-m state --state ESTABLISHED,NEW,RELATED '"/>
<xsl:variable name="sfrel" select="'-m state --state ESTABLISHED,RELATED '"/>
<xsl:variable name="sfnrel" select="'-m state --state NEW,RELATED '"/>
<xsl:variable name="sfold" select="'-m state --state ESTABLISHED '"/>
<xsl:variable name="radacport" select="/config/Radius/Config/Option[@option = 'AccPort']"/>
<xsl:variable name="pdns" select="/config/IP/SysConf/Option[@option = 'PrimaryDns']"/>
<xsl:variable name="sdns" select="/config/IP/SysConf/Option[@option = 'SecondaryDns']"/>
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="dynttl" select="/config/DNS/Config/Option[@option = 'DynamicTTL']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
<xsl:variable name="adserv" select="translate(/config/FileServer/Setup/Option[@option = 'ADSServer'],$uppercase,$smallcase)"/>
<xsl:variable name="adsdom" select="translate(/config/FileServer/Setup/Option[@option = 'ADSRealm'],$uppercase,$smallcase)"/>
<xsl:param name="smartkey"/>

<xsl:variable name="extint">
  <xsl:choose>
    <xsl:when test="($extcon = 'ADSL') or ($extiface = 'Dialup')">
       <xsl:text>ppp0</xsl:text>
     </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$extiface"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:variable>

<xsl:variable name="scext">
  <xsl:choose>
    <xsl:when test="($extcon = 'ADSL') or ($extiface = 'Dialup')">
       <xsl:text>Dialup</xsl:text>
     </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$extiface"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:variable>

<xsl:variable name="pppoeint">
  <xsl:choose>
    <xsl:when test="/config/Radius/Config/Option[@option = 'PPPoEIF'] != ''">
      <xsl:value-of select="/config/Radius/Config/Option[@option = 'PPPoEIF']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$intiface"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:variable>
<xsl:variable name="pppoelan" select="concat(/config/IP/Interfaces/Interface[. = $pppoeint]/@nwaddr,'/',/config/IP/Interfaces/Interface[. = $pppoeint]/@subnet)"/>

<xsl:variable name="regress">
  <xsl:choose>
    <xsl:when test="/config/Radius/Config/Option[@option = 'Egress'] != ''">
      <xsl:value-of select="/config/Radius/Config/Option[@option = 'Egress']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>0</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:variable>

<xsl:variable name="ringress">
  <xsl:choose>
    <xsl:when test="/config/Radius/Config/Option[@option = 'Ingress'] != ''">
      <xsl:value-of select="/config/Radius/Config/Option[@option = 'Ingress']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>0</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:variable>

<xsl:variable name="egress">
  <xsl:choose>
    <xsl:when test="/config/IP/SysConf/Option[@option = 'Egress'] != ''">
      <xsl:value-of select="/config/IP/SysConf/Option[@option = 'Egress']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>0</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:variable>

<xsl:variable name="ingress">
  <xsl:choose>
    <xsl:when test="/config/IP/SysConf/Option[@option = 'Ingress'] != ''">
      <xsl:value-of select="/config/IP/SysConf/Option[@option = 'Ingress']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>0</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:variable>

<xsl:template match="/config">
  <xsl:call-template name="common"/>
  <xsl:choose>
    <xsl:when test="($extiface = $intiface) and ($extcon != 'ADSL')">
      <xsl:text>fi;&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="fwconf"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="common">
  <xsl:text>#!/bin/bash

#Configure PPP Addresses

#Exit if not called as ip-up or ip-down

if [ "$1" == "startup" ];then
  DEST_IP="</xsl:text><xsl:value-of select="/config/IP/SysConf/Option[@option = 'Nexthop']"/><xsl:text>";
</xsl:text>
  <xsl:choose>
    <xsl:when test="($extcon = 'ADSL') or ($extiface = 'Dialup')">
      <xsl:choose>
        <xsl:when test="($extcon = 'ADSL') or ($extcon = '3G') or ($extcon = '3GIPW')">
          <xsl:text>  EXT_IP="127.0.0.1/32";&#xa;</xsl:text>
          <xsl:text>  EXT_IP_ADDR="127.0.0.1";&#xa;</xsl:text>
          <xsl:text>  INT_IN="-i lo ";&#xa;</xsl:text>
          <xsl:text>  INT_NAME="lo";&#xa;</xsl:text>
          <xsl:text>  INT_OUT="-o lo ";&#xa;</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="concat('  EXT_IP=&quot;',/config/Dialup/Option[@option = 'Address'],'&quot;;',$nl)"/>
          <xsl:value-of select="concat('  EXT_IP_ADDR=&quot;',/config/Dialup/Option[@option = 'Address'],'&quot;;',$nl)"/>
          <xsl:text>  INT_IN="-i ppp0 ";&#xa;</xsl:text>
          <xsl:text>  INT_NAME="ppp0";&#xa;</xsl:text>
          <xsl:text>  INT_OUT="-o ppp0 ";&#xa;</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('  EXT_IP=&quot;',$extip,'/32&quot;;',$nl)"/>
      <xsl:value-of select="concat('  EXT_IP_ADDR=&quot;',$extip,'&quot;;',$nl)"/>
      <xsl:value-of select="concat('  INT_IN=&quot;-i ',$extiface,' &quot;;',$nl)"/>
      <xsl:value-of select="concat('  INT_NAME=&quot;',$extiface,'&quot;;',$nl)"/>
      <xsl:value-of select="concat('  INT_OUT=&quot;-o ',$extiface,' &quot;;',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
<xsl:text> else
  if [ ! "$1" ] || [ ! "$4" ];then
   exit
  fi
fi; 

if [ "$1" ] &amp;&amp; [ "$1" != "startup" ];then
  INT_IN="-i $1 ";
  INT_OUT="-o $1 ";
  INT_NAME="$1";
fi;

if [ "$4" ];then
  if [ "$4" == "hotplug" ];then
    EXT_IP="$2/32";
    EXT_IP_ADDR="$2";
    DEST_IP="$3";
   else
    EXT_IP="$4/32";
    EXT_IP_ADDR="$4";
    DEST_IP="$5";
  fi;
fi;

NATCMD="SNAT --to-source $EXT_IP_ADDR";
if [ "$0" == "/etc/ppp/ip-up" ];then
  /usr/bin/hostaddr $EXT_IP_ADDR $INT_NAME
  if [ ! "$6" ] &amp;&amp; [ "${1}" == "ppp0" ];then
    /sbin/ip route del 0/0 table 90
    /sbin/ip route add $DEST_IP src $EXT_IP_ADDR dev $INT_NAME scope link table 90
    /sbin/ip route add 0/0 via $DEST_IP src $EXT_IP_ADDR dev $INT_NAME table 90
  fi;
  if [ "$6" == "" ];then
    if [ ! -d /tmp/pppup ];then
      mkdir /tmp/pppup
    fi;
    if [ "${1}" == "ppp0" ];then
      (echo "#!/bin/bash";echo;echo "$0 $@") > /tmp/pppup/ext.ip-up
      chmod 700 /tmp/pppup/ext.ip-up
      /etc/ifconf/ipv6to4 ${EXT_IP_ADDR} &amp;
    fi;
  fi;
  /sbin/ip addr add ::${EXT_IP_ADDR}/96 dev sit0 > /dev/null 2>&amp;1
fi;

if [ "$6" == "3g" ];then
  (cat &lt;&lt;EOF
server </xsl:text><xsl:value-of select="/config/DNS/Config/Option[@option = 'DynServ']"/><xsl:text>
key </xsl:text><xsl:value-of select="concat($dynzone,' ',$smartkey)"/><xsl:text>
zone </xsl:text><xsl:value-of select="$dynzone"/><xsl:text>
update delete $6.</xsl:text><xsl:value-of select="$dynzone"/><xsl:text>. A
update add $6.</xsl:text><xsl:value-of select="$dynzone"/><xsl:text>. 180 A $EXT_IP_ADDR
send
EOF
)>/tmp/dnsup.$6.ppp

  chmod 640 /tmp/dnsup.$6.ppp
  echo $1 > /tmp/ppp.$6.int
  /usr/bin/nsupdate /tmp/dnsup.$6.ppp
  /sbin/ip route add $DEST_IP/32 dev $INT_NAME src $EXT_IP_ADDR table 80;
  /sbin/ip route add 0/0 via $DEST_IP dev $INT_NAME src $EXT_IP_ADDR table 95;
  /sbin/iptables -F 3GIN
  /sbin/iptables -F 3GOUT
  /sbin/iptables -t nat -F 3GNAT

  /sbin/iptables -A 3GIN -j ACCEPT -i $INT_NAME
  /sbin/iptables -I 3GOUT -j ACCEPT -o $INT_NAME
  /sbin/iptables -t nat -A 3GNAT -j SNAT -o $INT_NAME --to-source $EXT_IP_ADDR

  if [ "$USEPEERDNS" == "1" ] &amp;&amp; [ "$DNS1" ];then
    if [ "$DNS2" ];then
       FWD="${DNS1};${DNS2}";
     else
      FWD=${DNS1}
    fi;
    echo "forwarders {${FWD};};" > /etc/bind/forwarders.ppp.3g
  fi;
  if [ -s /etc/bind/forwarders.ppp.3g ];then
    cp /etc/bind/forwarders.ppp.3g /etc/bind/forwarders.conf
    if [ "`/bin/pidof named`" ];then
      /usr/sbin/rndc reload &amp;
      sleep 5
     else
      /usr/sbin/named
    fi;
  fi;
</xsl:text>
</xsl:template>

<xsl:template name="tosoutput">
  <xsl:param name="tosmatch"/>

  <xsl:if test="$tosmatch != 0">
    <xsl:text>  /sbin/iptables -t mangle -A MANGLEO${FILID} -j MARK -o </xsl:text>
    <xsl:value-of select="concat($extint,' -m tos --tos ',$tosmatch,' --set-mark ${MARK} ! -d $EXT_IP_ADDR')"/><xsl:text>
  /sbin/iptables -t mangle -A MANGLEF${FILID} -j MARK -o </xsl:text>
    <xsl:value-of select="concat($extint,' -m tos --tos ',$tosmatch,' --set-mark ${MARK} ! -d $EXT_IP_ADDR')"/><xsl:text>
  /sbin/iptables -t mangle -A MANGLEP${FILID} ! -i ppp+ -j MARK -m tos --tos </xsl:text>
    <xsl:value-of select="concat($tosmatch,' -m mark --mark 0 --set-mark ${MARK} ! -d $EXT_IP_ADDR',$nl)"/>
  </xsl:if>
</xsl:template>

<xsl:template name="tosmatch">
  <xsl:param name="toslst"/>
  <xsl:variable name="cur" select="substring-before($toslst,',')"/>
  <xsl:variable name="next" select="substring-after($toslst,',')"/>

  <xsl:choose>
    <xsl:when test="$next != ''">
      <xsl:call-template name="tosoutput">
        <xsl:with-param name="tosmatch" select="$cur"/>
      </xsl:call-template>
      <xsl:call-template name="tosmatch">
        <xsl:with-param name="toslst" select="$next"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="tosoutput">
        <xsl:with-param name="tosmatch" select="$toslst"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="smbadrules">
  <xsl:param name="adserv"/>
  <xsl:variable name="adsfqdn" select="concat($adserv,'.',$adsdom)"/>

  <xsl:variable name="dcipaddr">
    <xsl:choose>
      <xsl:when test="/config/IP/Hosts/Host[((. = $adserv) and ($domain = $adsdom)) or (. = $adsfqdn)]/@ipaddr != ''">
        <xsl:value-of select="concat(/config/IP/Hosts/Host[((. = $adserv) and ($domain = $adsdom)) or (. = $adsfqdn)]/@ipaddr,'/32')"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat($adsfqdn,'/32')"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:value-of select="concat('#Allow Mail/DNS To DC ',$adserv,' ',$dcipaddr,$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESI -j ACCEPT -p tcp -m state --state NEW,RELATED -i ',$intiface,' -d ',$intip,'/32 --dport 1024:65535 -s ',$dcipaddr,' --sport 25',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESI -j ACCEPT -p tcp ! --syn -m state --state ESTABLISHED -i ',$intiface,' -d ',$intip,'/32 --dport 1024:65535 -s ',$dcipaddr,' --sport 25',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESO -j ACCEPT -p tcp -m state --state NEW,ESTABLISHED -o ',$intiface,' -s ',$intip,'/32 --sport 1024:65535 -d ',$dcipaddr,' --dport 25',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESI -j ACCEPT -p tcp -m state --state NEW,RELATED -i ',$intiface,' -d ',$intip,'/32 --dport 1024:65535 -s ',$dcipaddr,' --sport 53',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESI -j ACCEPT -p tcp ! --syn -m state --state ESTABLISHED -i ',$intiface,' -d ',$intip,'/32 --dport 1024:65535 -s ',$dcipaddr,' --sport 53',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESO -j ACCEPT -p tcp -m state --state NEW,ESTABLISHED -o ',$intiface,' -s ',$intip,'/32 --sport 1024:65535 -d ',$dcipaddr,' --dport 53',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESI -j ACCEPT -p udp -m state --state NEW,RELATED -i ',$intiface,' -d ',$intip,'/32 --dport 1024:65535 -s ',$dcipaddr,' --sport 53',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESI -j ACCEPT -p udp -m state --state ESTABLISHED -i ',$intiface,' -d ',$intip,'/32 --dport 1024:65535 -s ',$dcipaddr,' --sport 53',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESO -j ACCEPT -p udp -m state --state NEW,ESTABLISHED -o ',$intiface,' -s ',$intip,'/32 --sport 1024:65535 -d ',$dcipaddr,' --dport 53',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESI -j ACCEPT -p udp -m state --state NEW,RELATED -i ',$intiface,' -d ',$intip,'/32 --dport 53 -s ',$dcipaddr,' --sport 53',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESI -j ACCEPT -p udp -m state --state ESTABLISHED -i ',$intiface,' -d ',$intip,'/32 --dport 53 -s ',$dcipaddr,' --sport 53',$nl)"/>
  <xsl:value-of select="concat('/sbin/iptables -A SBSRULESO -j ACCEPT -p udp -m state --state NEW,ESTABLISHED -o ',$intiface,' -s ',$intip,'/32 --sport 53 -d ',$dcipaddr,' --dport 53',$nl,$nl)"/>
</xsl:template>

<xsl:template name="setupad">
  <xsl:param name="adsrvlst" select="$adserv"/>
  <xsl:variable name="next" select="substring-after($adsrvlst,' ')"/>
  <xsl:variable name="cur" select="substring-before($adsrvlst,' ')"/>
  <xsl:variable name="active">
    <xsl:choose>
      <xsl:when test="$next = ''">
        <xsl:value-of select="$adsrvlst"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$cur"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:call-template name="smbadrules">
    <xsl:with-param name="adserv" select="$active"/>
  </xsl:call-template>
  <xsl:if test="$next != ''">
    <xsl:call-template name="setupad">
      <xsl:with-param name="adsrvlst" select="$next"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template match="Firewall">
  <xsl:param name="srciface"/>
  <xsl:param name="srcname"/>
  <xsl:param name="srcnwaddr"/>
  <xsl:param name="srcsubnet"/>

  <xsl:variable name="statein">
    <xsl:choose>
      <xsl:when test="@direction = 'Out'">
        <xsl:choose>
          <xsl:when test="@state = 'New'">
            <xsl:text> -m state --state NEW,ESTABLISHED</xsl:text>
          </xsl:when>
          <xsl:otherwise>
            <xsl:if test="@state != 'Any'">
              <xsl:if test="@proto = 'TCP'">
                <xsl:text> ! --syn</xsl:text>
              </xsl:if>
              <xsl:text> -m state --state ESTABLISHED</xsl:text>
            </xsl:if>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>
        <xsl:if test="(@direction = 'In') and (@state != 'Any')">
           <xsl:if test="@proto = 'TCP'">
             <xsl:text> ! --syn</xsl:text>
           </xsl:if>
           <xsl:text> -m state --state ESTABLISHED</xsl:text>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="stateout">
    <xsl:choose>
      <xsl:when test="@direction = 'Out'">
        <xsl:if test="@state != 'Any'">
          <xsl:if test="@proto = 'TCP'">
            <xsl:text> ! --syn</xsl:text>
          </xsl:if>
          <xsl:text> -m state --state ESTABLISHED</xsl:text>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <xsl:when test="(@direction = 'In') and (@state = 'New')">
            <xsl:text> -m state --state NEW,ESTABLISHED</xsl:text>
          </xsl:when>
          <xsl:otherwise>
            <xsl:if test="@state != 'Any'">
              <xsl:text> -m state --state RELATED,ESTABLISHED</xsl:text>
            </xsl:if>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="proto">
    <xsl:if test="@proto != 'ALL'">
      <xsl:value-of select="concat(' -p ',translate(@proto,$uppercase,$smallcase))"/>
    </xsl:if>
  </xsl:variable>

  <xsl:variable name="rpolicy">
    <xsl:if test="@action = 'Accept'">
      <xsl:value-of select="concat('ACCEPT',$proto)"/>
    </xsl:if>
    <xsl:if test="@action = 'Deny And Log'">
      <xsl:value-of select="concat('DENY',$proto)"/>
    </xsl:if>
    <xsl:if test="@action = 'Deny'">
      <xsl:value-of select="concat('REJECT',$proto)"/>
      <xsl:if test="@proto = 'TCP'">
        <xsl:text> --reject-with tcp-reset</xsl:text>
      </xsl:if>
    </xsl:if>
  </xsl:variable>

  <xsl:variable name="INT_IN">
    <xsl:if test="@output = '-'">
      <xsl:text>$INT_IN</xsl:text>
    </xsl:if>
    <xsl:if test="@output = '+'">
      <xsl:text>-i gtun+</xsl:text>
    </xsl:if>
    <xsl:if test="@output = '='">
      <xsl:text>-i vpn0</xsl:text>
    </xsl:if>
    <xsl:if test="(@output != '-') and (@output != '+') and (@output != '=')">
      <xsl:value-of select="concat('-i ',@output)"/> 
    </xsl:if>
  </xsl:variable>

  <xsl:variable name="INT_OUT">
    <xsl:if test="@output = '-'">
      <xsl:text>$INT_OUT</xsl:text>
    </xsl:if>
    <xsl:if test="@output = '+'">
      <xsl:text>-o gtun+</xsl:text>
    </xsl:if>
    <xsl:if test="@output = '='">
      <xsl:text>-o vpn0</xsl:text>
    </xsl:if>
    <xsl:if test="(@output != '-') and (@output != '+') and (@output != '=')">
      <xsl:value-of select="concat('-o ',@output)"/> 
    </xsl:if>
  </xsl:variable>

  <xsl:variable name="dest">
    <xsl:if test="@output = '-'">
      <xsl:text>$EXT_IP</xsl:text>
    </xsl:if>
    <xsl:if test="(@output = '+') or (@output = '=')">
      <xsl:text>0/0</xsl:text>
    </xsl:if>
    <xsl:if test="(@output != '-') and (@output != '+') and (@output != '=')">
      <xsl:variable name="outint" select="@output"/>
      <xsl:value-of select="concat(/config/IP/Interfaces/Interface[. = $outint]/@ipaddr,'/32')"/> 
   </xsl:if>
  </xsl:variable>

  <xsl:variable name="fwnat">
    <xsl:choose>
      <xsl:when test="(@output != '-') and (@output != '+') and (@output != '=')">
        <xsl:variable name="outint" select="@output"/>
        <xsl:value-of select="concat('SNAT --to-source ',/config/IP/Interfaces/Interface[ . = $outint]/@ipaddr)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>$NATCMD</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="rname">
    <xsl:value-of select="concat(translate($srcname,'_',' '),'-(',translate(.,'_',' '),')')"/>
  </xsl:variable>

  <xsl:variable name="localip">
    <xsl:choose>
      <xsl:when test="($srciface = 'Modem') and (@type != 'Proxy') and (($extcon = 'ADSL') or ($extiface = 'Dialup') or
                         (count(/config/IP/ADSL/Links/Link) &gt; 0))">
        <xsl:choose>
          <xsl:when test="(@type = 'Local') or (@type = 'Proxy')">
            <xsl:text>0/0</xsl:text>
          </xsl:when>
          <xsl:otherwise>
            <xsl:text>$EXT_IP_ADDR</xsl:text>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <xsl:when test="(@type = 'Local') or (@type = 'Proxy')">
            <xsl:value-of select="concat(/config/IP/Interfaces/Interface[. = $srciface]/@ipaddr,'/32')"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="/config/IP/Interfaces/Interface[. = $srciface]/@ipaddr"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="priority">
    <xsl:if test="@priority = 'High'">
      <xsl:text>0x101</xsl:text>
    </xsl:if>
    <xsl:if test="@priority = 'Med'">
      <xsl:text>0x102</xsl:text>
    </xsl:if>
    <xsl:if test="@priority = 'Low'">
      <xsl:text>0x103</xsl:text>
    </xsl:if>
  </xsl:variable>

  <xsl:variable name="siface">
    <xsl:choose>
      <xsl:when test="count(/config/IP/GRE/Tunnels/Tunnel[@local = $srciface]) = 1">
        <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
          <xsl:if test="@local = $srciface">
            <xsl:value-of select="concat('gtun',position()-1)"/>
          </xsl:if>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <xsl:when test="($srciface = 'Modem') and (($extcon = 'ADSL') or ($extiface = 'Dialup') or
                         (count(/config/IP/ADSL/Links/Link) &gt; 0))">
            <xsl:choose>
              <xsl:when test="(@type = 'Local') or (@type = 'Proxy')">
                <xsl:text>ppp+</xsl:text>
              </xsl:when>
              <xsl:otherwise>
                <xsl:text>$INT_NAME</xsl:text>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$srciface"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="iface">
    <xsl:choose>
      <xsl:when test="contains($siface,':')">
        <xsl:value-of select="substring-before($siface,':')"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$siface"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="outint">
    <xsl:choose>
      <xsl:when test="($srciface = 'Modem') and (($extcon = 'ADSL') or ($extiface = 'Dialup') or
                      (count(/config/IP/ADSL/Links/Link) &gt; 0))">
        <xsl:text>$INT_NAME</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$srciface"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name='fwsource' select="concat($srcnwaddr,'/',$srcsubnet)"/>

  <xsl:variable name="output">
    <xsl:choose>
      <xsl:when test="contains(@output,':')">
        <xsl:value-of select="substring-before(@output,':')"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="@output"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:if test="(@type = 'Local') and ($iface != 'Modem')">
    <xsl:choose>
      <xsl:when test="(@proto = 'TCP') or (@proto = 'UDP')">
        <xsl:value-of select="concat('  #Local ',@action,' ',$rname,' ',$fwsource,':',@source,' --> ',$localip,':',@dest,$nl)"/>
        <xsl:choose>
          <xsl:when test="@direction = 'Out'">
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALIN -j ',$rpolicy,$statein,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',$localip,' --dport ',@dest,$nl)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALOUT -j ',$rpolicy,$stateout,' -o ',$iface,' -d ',$fwsource,' --dport ',@source,' -s ',$localip,' --sport ',@dest,$nl)"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="@action = 'Accept'">
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -o ',$iface,' -d ',$fwsource,' --dport ',@source,' -s ',$localip,' --sport ',@dest,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',$localip,' --dport ',@dest,$nl)"/>
          <xsl:choose>
            <xsl:when test="@direction = 'Out'">
              <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -o ',$iface,' -d ',$fwsource,' --dport ',@source,' -s ',$localip,' --sport ',@dest,$nl)"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',$localip,' --dport ',@dest,$nl)"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('  #Local ',@action,' ',$rname,' ',$fwsource,' --> ',$localip,$nl)"/>
        <xsl:choose>
          <xsl:when test="@direction = 'Out'">
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALIN -j ',$rpolicy,$statein,' -i ',$iface,' -s ',$fwsource,' -d ',$localip,$nl)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALOUT -j ',$rpolicy,$stateout,' -o ',$iface,' -d ',$fwsource,' -s ',$localip,$nl)"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="@action = 'Accept'">
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -o ',$iface,' -d ',$fwsource,' -s ',$localip,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -i ',$iface,' -s ',$fwsource,' -d ',$localip,$nl)"/>
          <xsl:choose>
            <xsl:when test="@direction = 'Out'">
              <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -o ',$iface,' -d ',$fwsource,' -s ',$localip,$nl)"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -i ',$iface,' -s ',$fwsource,' -d ',$localip,$nl)"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text>&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="(@type = 'NAT') and ($iface != 'Modem')">
    <xsl:choose>
      <xsl:when test="(@proto = 'TCP') or (@proto = 'UDP')">
        <xsl:value-of select="concat('  #NAT ',@action,' ',$rname,' ',$fwsource,':',@source,' --> ',@ip,':',@dest,$nl)"/>
        <xsl:choose>
          <xsl:when test="@direction = 'Out'">
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALFWD -j ',$rpolicy,$statein,' ',$INT_OUT,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',@ip,' --dport ',@dest,$nl)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALFWD -j ',$rpolicy,$stateout,' ',$INT_IN,' -o ',$iface,' -d ',$fwsource,' --dport ',@source,' -s ',@ip,' --sport ',@dest,$nl)"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="@action = 'Accept'">
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',@ip,' --dport ',@dest,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -o ',$iface,' -d ',$fwsource,' --dport ',@source,' -s ',@ip,' --sport ',@dest,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' ',$INT_IN,' -d ',$dest,' --dport ',@source,' -s ',@ip,' --sport ',@dest,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' ',$INT_OUT,' -s ',$dest,' --sport ',@source,' -d ',@ip,' --dport ',@dest,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -A EXTNAT -j ',$fwnat,' -t nat ',$INT_OUT,' ',$proto,$statein,' -s ',$fwsource,' --sport ',@source,' -d ',@ip,' --dport ',@dest,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -A LOCALTOS -j TOS -t mangle',$proto,$statein,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',@ip,' --dport ',@dest,' --set-tos ',@tos,$nl)"/>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('  #NAT ',@action,' ',$rname,' ',$fwsource,' --> ',@ip,$nl)"/>
        <xsl:choose>
          <xsl:when test="@direction = 'Out'">
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALFWD -j ',$rpolicy,$statein,' ',$INT_OUT,' -i ',$iface,' -s ',$fwsource,' -d ',@ip,$nl)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALFWD -j ',$rpolicy,$stateout,' ',$INT_IN,' -o ',$iface,' -d ',$fwsource,' -s ',@ip,$nl)"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="@action = 'Accept'">
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -i ',$iface,' -s ',$fwsource,' -d ',@ip,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -o ',$iface,' -d ',$fwsource,' -s ',@ip,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' ',$INT_IN,' -d ',$dest,' -s ',@ip,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' ',$INT_OUT,' -s ',$dest,' -d ',@ip,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -A EXTNAT -j ',$fwnat,' -t nat ',$INT_OUT,' ',$proto,$statein,' -s ',$fwsource,' -d ',@ip,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -A LOCALTOS -j TOS -t mangle',$proto,$statein,' -i ',$iface,' -s ',$fwsource,' -d ',@ip,' --set-tos ',@tos,$nl)"/>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text>&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="(@type = 'Proxy') and ($iface != 'Modem')">
    <xsl:choose>
      <xsl:when test="(@proto = 'TCP') or (@proto = 'UDP')">
        <xsl:variable name="int_port">
          <xsl:choose>
            <xsl:when test="contains(@dest,'-')">
              <xsl:value-of select="substring-after(@dest,'-')"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="@dest"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:variable name="ext_port">
          <xsl:choose>
            <xsl:when test="contains(@dest,'-')">
              <xsl:value-of select="substring-before(@dest,'-')"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="@dest"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:value-of select="concat('  #Proxy ',@action,' ',$rname,' ',$fwsource,':',@source,' --> ',@ip,':',@dest,$nl)"/>
        <xsl:if test="@action = 'Accept'">
          <xsl:variable name="destip" select="@ip"/>
          <xsl:choose>
            <xsl:when test="@direction = count(/config/IP/Interfaces/Interface[@ipaddr = $destip]) &gt; 0">
              <xsl:value-of select="concat('  /sbin/iptables -A LOCALIN -j ',$rpolicy,$statein,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',@ip,' --dport ',$int_port,$nl)"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="concat('  /sbin/iptables -A LOCALFWD -j ',$rpolicy,$statein,' ',$INT_OUT,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',@ip,' --dport ',$int_port,$nl)"/>
              <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -o ',$iface,' -d ',$fwsource,' --dport ',@source,' -s ',@ip,' --sport ',$int_port,$nl)"/>
              <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',@ip,' --dport ',$int_port,$nl)"/>
            </xsl:otherwise>
          </xsl:choose>
          <xsl:value-of select="concat('  /sbin/iptables -A EXTPROXY -i ',$iface,' -j DNAT -t nat',$proto,$statein,' --to-destination ',@ip,':',$int_port,' -s ',$fwsource,' -d ',$localip,' --sport ',@source,' --dport ',$ext_port,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -A LOCALTOS -j TOS -t mangle',$proto,$statein,' -i ',$output,' -d ',$fwsource,' --dport ',@source,' -s ',@ip,'/32 --sport ',$int_port,' --set-tos ',@tos,$nl)"/>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('  #Proxy ',@action,' ',$rname,' ',$fwsource,' --> ',@ip,$nl)"/>
        <xsl:if test="@action = 'Accept'">
          <xsl:variable name="destip" select="@ip"/>
          <xsl:choose>
            <xsl:when test="@direction = count(/config/IP/Interfaces/Interface[@ipaddr = $destip]) &gt; 0">
              <xsl:value-of select="concat('  /sbin/iptables -A LOCALIN -j ',$rpolicy,$statein,' -i ',$iface,' -s ',$fwsource,' -d ',@ip,$nl)"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="concat('  /sbin/iptables -A LOCALFWD -j ',$rpolicy,$statein,' ',$INT_OUT,' -i ',$iface,' -s ',$fwsource,' -d ',@ip,$nl)"/>
              <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -o ',$iface,' -d ',$fwsource,' -s ',@ip,$nl)"/>
              <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -i ',$iface,' -s ',$fwsource,' -d ',@ip,$nl)"/>
            </xsl:otherwise>
          </xsl:choose>
          <xsl:value-of select="concat('  /sbin/iptables -A EXTPROXY -i ',$iface,' -j DNAT -t nat',$proto,$statein,' --to-destination ',@ip,' -s ',$fwsource,' -d ',$localip,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -A LOCALTOS -j TOS -t mangle',$proto,$statein,' -i ',$output,' -d ',$fwsource,' -s ',@ip,'/32 --set-tos ',@tos,$nl)"/>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text>&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="(@type = 'Forward') and ($iface != 'Modem')">
    <xsl:choose>
      <xsl:when test="(@proto = 'TCP') or (@proto = 'UDP')">
        <xsl:value-of select="concat('  #Forward ',@action,' ',$rname,' ',$fwsource,':',@source,' --> ',@ip,':',@dest,$nl)"/>
        <xsl:choose>
          <xsl:when test="@direction = 'Out'">
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALFWD -j ',$rpolicy,$statein,' -i ',$iface,' ',$INT_OUT,' -s ',$fwsource,' --sport ',@source,' -d ',@ip,' --dport ',@dest,$nl)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALFWD -j ',$rpolicy,$stateout,' -o ',$iface,' ',$INT_IN,' -d ',$fwsource,' --dport ',@source,' -s ',@ip,' --sport ',@dest,$nl)"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="@action = 'Accept'">
          <xsl:value-of select="concat('  /sbin/iptables -t nat -A NOFWDNAT -j ACCEPT ',$proto,$statein,' -o ',$iface,' -d ',$fwsource,' --dport ',@source,' -s ',@ip,' --sport ',@dest,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -A LOCALTOS -j TOS -t mangle',$proto,$statein,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',@ip,' --dport ',@dest,' --set-tos ',@tos,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -o ',$iface,' -d ',$fwsource,' --dport ',@source,' -s ',@ip,' --sport ',@dest,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -i ',$iface,' -s ',$fwsource,' --sport ',@source,' -d ',@ip,' --dport ',@dest,$nl)"/>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('  #Forward ',@action,' ',$rname,' ',$fwsource,' --> ',@ip,$nl)"/>
        <xsl:choose>
          <xsl:when test="@direction = 'Out'">
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALFWD -j ',$rpolicy,$statein,' -i ',$iface,' ',$INT_OUT,' -s ',$fwsource,' -d ',@ip,$nl)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="concat('  /sbin/iptables -A LOCALFWD -j ',$rpolicy,$stateout,' -o ',$iface,' ',$INT_IN,' -d ',$fwsource,' -s ',@ip,$nl)"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="@action = 'Accept'">
          <xsl:value-of select="concat('  /sbin/iptables -t nat -A NOFWDNAT -j ACCEPT ',$proto,$statein,' -o ',$iface,' -d ',$fwsource,' -s ',@ip,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -A LOCALTOS -j TOS -t mangle',$proto,$statein,' -i ',$iface,' -s ',$fwsource,' -d ',@ip,' --set-tos ',@tos,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALOUT -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -o ',$iface,' -d ',$fwsource,' -s ',@ip,$nl)"/>
          <xsl:value-of select="concat('  /sbin/iptables -t mangle -A LOCALIN -j MARK',$proto,' -m mark --mark 0x102 --set-mark ',$priority,' -i ',$iface,' -s ',$fwsource,' -d ',@ip,$nl)"/>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text>&#xa;</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template match="Source">
  <xsl:param name="iface"/>

  <xsl:apply-templates select="Firewall">
    <xsl:with-param name="srciface" select="$iface"/>
    <xsl:with-param name="srcname" select="@name"/>
    <xsl:with-param name="srcnwaddr" select="@ipaddr"/>
    <xsl:with-param name="srcsubnet" select="@subnet"/>
  </xsl:apply-templates>
</xsl:template>

<xsl:template match="Iface">
  <xsl:apply-templates select="Source">
    <xsl:with-param name="iface" select="@iface"/>
  </xsl:apply-templates>
</xsl:template>

<xsl:template name="fwconf">
  <xsl:text>
 elif [ ! "$6" ];then
  #Set Bandwidth Rate

  #Delete Existing Classes
  /sbin/tc qdisc del dev $INT_NAME root > /dev/null 2>&amp;1
  /sbin/tc qdisc del dev imq0 root > /dev/null 2>&amp;1

  if [ "</xsl:text><xsl:value-of select="$ingress"/><xsl:text>" != "0" ];then
    #Apply ingress limit
    if [ ! -e /var/spool/apache/htdocs/mrtg/bw-imq0.rrd ];then
      /usr/bin/rrdtc </xsl:text><xsl:value-of select="$scext"/><xsl:text>
    fi;&#xa;</xsl:text>
    <xsl:value-of select="concat('    rrdtool tune /var/spool/apache/htdocs/mrtg/bw-imq0.rrd -a high:',($ingress div 8)*1024,' -a med:',($ingress div 8)*1024,' -a low:',($ingress div 8)*1024,' -i high:0 -i med:0 -i low:0')"/><xsl:text>
    /sbin/ip link set imq0 up
    /sbin/tc qdisc add dev imq0 root handle 1: htb default 20 r2q 1&#xa;</xsl:text>
    <xsl:value-of select="concat('    /sbin/tc class add dev imq0 parent 1: classid 1:1 htb rate ',$ingress,'Kbit',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc class add dev imq0 parent 1:1 classid 1:10 htb rate ',$ingress*0.5,'Kbit ceil ',$ingress,'Kbit',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc class add dev imq0 parent 1:1 classid 1:20 htb rate ',$ingress*0.3,'Kbit ceil ',$ingress,'Kbit',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc class add dev imq0 parent 1:1 classid 1:30 htb rate ',$ingress*0.2,'Kbit ceil ',$ingress,'Kbit')"/><xsl:text>
    /sbin/tc qdisc add dev imq0 parent 1:10 handle 10: sfq perturb 10
    /sbin/tc qdisc add dev imq0 parent 1:20 handle 20: sfq perturb 10
    /sbin/tc qdisc add dev imq0 parent 1:30 handle 30: sfq perturb 10
    /sbin/tc filter add dev imq0 parent 1: protocol ip handle 0x101 fw flowid 1:10
    /sbin/tc filter add dev imq0 parent 1: protocol ip handle 0x102 fw flowid 1:20
    /sbin/tc filter add dev imq0 parent 1: protocol ip handle 0x103 fw flowid 1:30
  fi;

  if [ "</xsl:text><xsl:value-of select="$egress"/><xsl:text>" != "0" ] &amp;&amp; [ "$INT_NAME" != "lo" ];then
    #Apply egress limit
    if [ ! -e /var/spool/apache/htdocs/mrtg/bw-${INT_NAME}.rrd ];then
      /usr/bin/rrdtc </xsl:text><xsl:value-of select="$scext"/><xsl:text>
    fi;&#xa;</xsl:text>
    <xsl:value-of select="concat('    rrdtool tune /var/spool/apache/htdocs/mrtg/bw-${INT_NAME}.rrd -a high:',($egress div 8)*1024,' -a med:',($egress div 8)*1024,' -a low:',($egress div 8)*1024,' -i high:0 -i med:0 -i low:0')"/><xsl:text>
    /sbin/tc qdisc add dev $INT_NAME root handle 1: htb default 20 r2q 1&#xa;</xsl:text>
    <xsl:value-of select="concat('    /sbin/tc class add dev $INT_NAME parent 1: classid 1:1 htb rate ',$egress,'Kbit',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc class add dev $INT_NAME parent 1:1 classid 1:10 htb rate ',$egress*0.5,'Kbit ceil ',$egress,'Kbit',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc class add dev $INT_NAME parent 1:1 classid 1:20 htb rate ',$egress*0.3,'Kbit ceil ',$egress,'Kbit',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc class add dev $INT_NAME parent 1:1 classid 1:30 htb rate ',$egress*0.2,'Kbit ceil ',$egress,'Kbit')"/><xsl:text>
    /sbin/tc qdisc add dev $INT_NAME parent 1:10 handle 10: sfq perturb 10
    /sbin/tc qdisc add dev $INT_NAME parent 1:20 handle 20: sfq perturb 10
    /sbin/tc qdisc add dev $INT_NAME parent 1:30 handle 30: sfq perturb 10
  fi; 

  #Flush Tables
  /sbin/iptables -F LOCALIN
  /sbin/iptables -F LOCALOUT
  /sbin/iptables -F SBSRULESI
  /sbin/iptables -F SBSRULESO
  /sbin/iptables -F LOCALFWD
  /sbin/iptables -F GWOUT
  /sbin/iptables -A GWOUT -j ACCEPT ${INT_OUT}
  /sbin/iptables -F GWIN
  /sbin/iptables -A GWIN -j ACCEPT ${INT_IN}
  /sbin/iptables -t nat -F EXTNAT
  /sbin/iptables -t nat -F NATOUT
  /sbin/iptables -t nat -A NATOUT -j ${NATCMD} ${INT_OUT}
  /sbin/iptables -t nat -F LOCALPROXY
  /sbin/iptables -t nat -F NOFWDNAT
  /sbin/iptables -t nat -F NOPPPNAT
  /sbin/iptables -t nat -F EXTPROXY
  /sbin/iptables -t mangle -F SYSTOS
  /sbin/iptables -t mangle -F NOSYSTOS
  /sbin/iptables -t mangle -F LOCALTOS
  /sbin/iptables -t mangle -F LOCALIN
  /sbin/iptables -t mangle -F LOCALOUT

  /sbin/iptables -A LOCALIN -j DEFIN $INT_IN -d $EXT_IP
  /sbin/iptables -A LOCALIN -j VOIPIN $INT_IN -d $EXT_IP
  /sbin/iptables -A LOCALIN -j VOIPIN $INT_IN -d </xsl:text><xsl:value-of select="$intip"/><xsl:text> -p udp --sport 1024:65535

  /sbin/iptables -A LOCALOUT -j DEFOUT $INT_OUT -s $EXT_IP
  /sbin/iptables -A LOCALOUT -j VOIPOUT $INT_OUT -s </xsl:text><xsl:value-of select="concat($intip,' -p udp ',$sfnew)"/><xsl:text> --dport 1024:65535

  #Allow Access To STUN Remotely
  /sbin/iptables -A LOCALIN -j ACCEPT $INT_IN-p udp </xsl:text><xsl:value-of select="concat($sfnew,' -s ',$loclan)"/><xsl:text> --sport 1024:65535 -d $EXT_IP --dport 3478:3479
  /sbin/iptables -A LOCALOUT -j ACCEPT $INT_OUT-p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> -s $EXT_IP --sport 3478:3479 -d $EXT_IP --dport 10000:65535
  #Allow Transparent Proxy For External Connections
  /sbin/iptables -t nat -A LOCALPROXY -j REDIRECT $INT_IN -p tcp -s 0.0.0.0/0 -d $EXT_IP --dport 80 --to-port 8080
  /sbin/iptables -t nat -A LOCALPROXY -j EXTPROXY

</xsl:text>

  <xsl:if test="$adserv != ''">
    <xsl:call-template name="setupad"/>
  </xsl:if>

  <xsl:apply-templates select="/config/IP/FW/Iface"/>

  <xsl:text>  if [ "$0" == "/etc/ppp/ip-down" ];then
    cp /etc/mail/sendmail.cf.orig /etc/mail/sendmail.cf
    if [ "$4" != "hotplug" ];then
      EMAIL_PID=`/bin/pidof sendmail`
      if [ "$EMAIL_PID" ];then
        kill -1 `cat /var/run/sendmail.pid |head -1`
       else
        /etc/rc.d/rc.mail sendmail
      fi;
    fi;
  fi;
  

  ##IP-UP

  if [ "$0" == "/etc/ppp/ip-up" ];then
  #Flush ESP SPD
  setkey -F > /dev/null 2>&amp;1
</xsl:text>
  <xsl:if test="/config/DNS/Config/Option[@option = 'Usepeer'] != 'true'">
    <xsl:text>
  if [ "$USEPEERDNS" == "1" ] &amp;&amp; [ "$DNS1" ];then
    if [ "$DNS2" ];then
       FWD="${DNS1};${DNS2}";
     else
      FWD=${DNS1}
    fi;
    echo "forwarders {${FWD};};" > /etc/bind/forwarders.ppp
  fi;

  if [ -s /etc/bind/forwarders.ppp ];then
    cp /etc/bind/forwarders.ppp /etc/bind/forwarders.conf
  fi;
</xsl:text>
  </xsl:if>
<xsl:text>  if [ "`/bin/pidof named`" ];then
    /usr/sbin/rndc reload &amp;
    sleep 5
   fi;
  squid -k reconfigure >/dev/null 2>&amp;1

  FWD_DNS=`echo $EXT_IP_ADDR |awk -F. '{print "dig "$4"."$3"."$2"."$1".in-addr.arpa PTR +short +time=3 +tries=1"}' |sh |head -1`
  if [ "${FWD_DNS}" != ";;" ];then
    REV_DNS=`dig "$FWD_DNS" A +short +time=3 +tries=1|tail -1`
   else
    REV_DNS=${EXT_IP_ADDR}
  fi;

  EMAIL_SERV=`echo $FWD_DNS |awk '{print substr($0,0,length($0)-1)}'`

  if [ "$4" != "hotplug" ];then
    if [ "$REV_DNS" != "$EXT_IP_ADDR" ];then
      ADD_DNS="update add </xsl:text><xsl:value-of select="concat($hname,'.',$dynzone,'. ',$dynttl)"/><xsl:text> A $EXT_IP_ADDR"
      cp /etc/mail/sendmail.cf.orig /etc/mail/sendmail.cf
      EMAIL_PID=`/bin/pidof sendmail`
      if [ "$EMAIL_PID" ];then
          kill -1 `cat /var/run/sendmail.pid |head -1`
         else
          /etc/rc.d/rc.mail sendmail
      fi;
     else
</xsl:text>
  <xsl:choose>
    <xsl:when test="/config/DNS/Config/Option[@option = 'DynamicCNAME'] = 'true'">
      <xsl:value-of select="concat('      ADD_DNS=&quot;update add ',$hname,'.',$dynzone,'. ',$dynttl,' CNAME $FWD_DNS&quot;',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('      ADD_DNS=&quot;update add ',$hname,'.',$dynzone,'. ',$dynttl,' A $EXT_IP_ADDR&quot;',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>      sed -e "s/^Dj.*/Dj$EMAIL_SERV/" /etc/mail/sendmail.cf.orig > /etc/mail/sendmail.cf
      EMAIL_PID=`/bin/pidof sendmail`
      if [ "$EMAIL_PID" ];then
          kill -1 `cat /var/run/sendmail.pid |head -1`
         else
          /etc/rc.d/rc.mail sendmail
      fi;
    fi;
    i6prefix=$(printf "%02x%02x:%02x%02x\n" $(echo ${EXT_IP_ADDR} | sed "s/\./ /g"))

(cat &lt;&lt;EOF
</xsl:text>
<!--
XXX
if (-e "/etc/ipv6.dns") {
  open(SDNS,"/etc/ipv6.dns");
  while(<SDNS>) {
    chop $_;
    @sixdyn=split(/\|/,$_);
    print FWP "update delete @sixdyn[0].$dnsconf{'DynZone'}. AAAA\n";
    print FWP "update add @sixdyn[0].$dnsconf{'DynZone'}. 180 AAAA 2002:\${i6prefix}:@sixdyn[1]\n";
  }
}
-->
  <xsl:value-of select="concat('server ',/config/DNS/Config/Option[@option = 'DynServ'],$nl)"/>
  <xsl:value-of select="concat('key ',$dynzone,' ',$smartkey,$nl)"/>
  <xsl:value-of select="concat('zone ',$dynzone,$nl)"/>
  <xsl:value-of select="concat('update delete ',$dynzone,'. A',$nl)"/>
  <xsl:value-of select="concat('update delete ',$dynzone,'. AAAA',$nl)"/>
  <xsl:value-of select="concat('update add ',$dynzone,'. 180 A $EXT_IP_ADDR',$nl)"/>
  <xsl:value-of select="concat('update add ',$dynzone,'. 180 AAAA 2002:${i6prefix}::1',$nl)"/>
  <xsl:value-of select="concat('update delete ',$hname,'.',$dynzone,'. A',$nl)"/>
  <xsl:value-of select="concat('update delete ',$hname,'.',$dynzone,'. CNAME',$nl)"/>
  <xsl:text>$ADD_DNS
send
EOF
)>/tmp/dnsup.ppp

  chmod 640 /tmp/dnsup.ppp
  echo $1 > /tmp/ppp.main.int
    if [ "`/bin/pidof named`" ];then
      /usr/sbin/rndc reload &amp;
      sleep 5
   fi;
</xsl:text>
  <xsl:if test="$dynzone != ''">
    <xsl:text>    /usr/bin/nsupdate /tmp/dnsup.ppp&#xa;</xsl:text>
  </xsl:if>
<xsl:text>    (/usr/bin/fetchmail;sendmail -q;sendmail -q -Ac) > /dev/null 2>&amp;1 &amp;
    fi;
  fi;

  #No-IP.com
  if [ "$INT_NAME" != "lo" ] &amp;&amp; [ "$INT_NAME" != "dummy0" ];then
    if [ ! -e /etc/.networksentry-lite ] &amp;&amp; [ -e /etc/no-ip2.conf ] &amp;&amp; [ ! "`/bin/pidof noip2`" ];then
      (/usr/sbin/noip2 -I $INT_NAME > /dev/null 2>&amp;1) &amp;
     elif [ ! -e /etc/.networksentry-lite ] &amp;&amp; [ -e /etc/no-ip2.conf ];then
      (/usr/sbin/noip2 -I $INT_NAME -i $EXT_IP_ADDR > /dev/null 2>&amp;1) &amp;
    fi;
  fi;
  #Run Default TOS Script
  if [ -x /etc/rc.d/rc.tos ] &amp;&amp;  [ "$DEST_IP" ];then
    /etc/rc.d/rc.tos $INT_NAME
  fi;

  #Run Mangle Script
  if [ -x /etc/rc.d/rc.mangle ] &amp;&amp;  [ "$DEST_IP" ];then
    /etc/rc.d/rc.mangle $EXT_IP_ADDR $DEST_IP $INT_NAME
  fi;

  if [ "$1" != "startup" ]  &amp;&amp; [ "$4" != "hotplug" ];then
    #Check And Restart GRE Tunnels
    /sbin/ip tun ls |grep -E "gtun[0-9]+:" |awk -F: '{print "/sbin/ip tun del "$1}' |sh > /dev/null 2>&amp;1
    /etc/rc.d/rc.tunnels
    (/usr/sbin/conntrack -F
    /usr/sbin/conntrack -F expect) >/dev/null 2>&amp;1
    /sbin/ip route flush cache
  fi;
</xsl:text>
  <xsl:for-each select="/config/IP/ADSL/Links/Link">
    <xsl:value-of select="concat($nl,' elif [ $6 == &quot;',.,'&quot; ];then',$nl)"/>
    <xsl:text>    ip6prefix="$(printf "%02x%02x:%02x%02x\n" $(echo ${EXT_IP_ADDR} | sed "s/\./ /g"))"&#xa;</xsl:text>
    <xsl:value-of select="concat('    LOCALIP=',$intip,$nl)"/>
    <xsl:value-of select="concat('    MARK=',position(),$nl)"/>
    <xsl:value-of select="concat('    PRIO=',position()+30-1,$nl)"/>
    <xsl:value-of select="concat('    FILID=',position(),$nl)"/>
    <xsl:value-of select="concat('    RTABLE=',position()+100-1,$nl)"/>
    <xsl:value-of select="concat('    OLIMIT=',@bwout,$nl)"/>
    <xsl:value-of select="concat('    OLIMITK=',(@bwout div 8)*1024,$nl)"/>
    <xsl:value-of select="concat('    OLIMIT50=',@bwout*0.5,$nl)"/>
    <xsl:value-of select="concat('    OLIMIT30=',@bwout*0.3,$nl)"/>
    <xsl:value-of select="concat('    OLIMIT20=',@bwout*0.2,$nl)"/>
    <xsl:value-of select="concat('    ILIMIT=',@bwin,$nl)"/>
    <xsl:value-of select="concat('    ILIMITK=',(@bwin div 8)*1024,$nl)"/>
    <xsl:value-of select="concat('    ILIMIT50=',@bwin*0.5,$nl)"/>
    <xsl:value-of select="concat('    ILIMIT30=',@bwin*0.3,$nl)"/>
    <xsl:value-of select="concat('    ILIMIT20=',@bwin*0.2,$nl)"/>
    <xsl:value-of select="concat('    ADSL_LINK=$6',$nl)"/>
    <xsl:value-of select="concat('    METRIC=',position()+256-1,$nl)"/>
    <xsl:text>  if [ ! -d /tmp/pppup ];then
    mkdir /tmp/pppup;
  fi;
  (echo "#!/bin/bash";echo;echo "$0 $@") > /tmp/pppup/$1.ip-up
  chmod 700 /tmp/pppup/$1.ip-up

  #Flush Applicable Chains
  /sbin/iptables -t mangle -F MANGLEP${FILID}
  /sbin/iptables -t mangle -F MANGLEO${FILID}
  /sbin/iptables -t mangle -F MANGLEF${FILID}

  #Set fwmark based on TOS value&#xa;</xsl:text>
    <xsl:call-template name="tosmatch">
      <xsl:with-param name="toslst" select="@tos"/>
    </xsl:call-template>
    <xsl:text>  #Delete Existing Classes
  /sbin/tc qdisc del dev $INT_NAME root > /dev/null 2>&amp;1
  /sbin/tc qdisc del dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> root > /dev/null 2>&amp;1

  #Apply ingress limit
  if [ ! -e /var/spool/apache/htdocs/mrtg/bw-imq</xsl:text><xsl:value-of select="position()"/><xsl:text>.rrd ];then
    /usr/bin/rrdtc </xsl:text><xsl:value-of select="$scext"/><xsl:text>
  fi;
  if [ "${ILIMIT}" ];then
    rrdtool tune /var/spool/apache/htdocs/mrtg/bw-imq</xsl:text><xsl:value-of select="position()"/><xsl:text>.rrd -a high:${ILIMITK} -a med:${ILIMITK} -a low:${ILIMITK} -i high:0 -i med:0 -i low:0
    /sbin/ip link set imq</xsl:text><xsl:value-of select="position()"/><xsl:text> up
    /sbin/tc qdisc add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> root handle 1: htb default 20 r2q 1
    /sbin/tc class add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> parent 1: classid 1:1 htb rate ${ILIMIT}Kbit
    /sbin/tc class add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> parent 1:1 classid 1:10 htb rate ${ILIMIT50}Kbit ceil ${ILIMIT}Kbit
    /sbin/tc class add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> parent 1:1 classid 1:20 htb rate ${ILIMIT30}Kbit ceil ${ILIMIT}Kbit
    /sbin/tc class add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> parent 1:1 classid 1:30 htb rate ${ILIMIT20}Kbit ceil ${ILIMIT}Kbit
    /sbin/tc qdisc add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> parent 1:10 handle 10: sfq perturb 10
    /sbin/tc qdisc add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> parent 1:20 handle 20: sfq perturb 10
    /sbin/tc qdisc add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> parent 1:30 handle 30: sfq perturb 10
    /sbin/tc filter add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> parent 1: prio 0 protocol ip handle 0x101 fw flowid 1:10
    /sbin/tc filter add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> parent 1: prio 1 protocol ip handle 0x102 fw flowid 1:20
    /sbin/tc filter add dev imq</xsl:text><xsl:value-of select="position()"/><xsl:text> parent 1: prio 2 protocol ip handle 0x103 fw flowid 1:30
  fi;

  #Apply egress limit
  if [ ! -e /var/spool/apache/htdocs/mrtg/bw-${INT_NAME}.rrd ];then
    /usr/bin/rrdtc </xsl:text><xsl:value-of select="$scext"/><xsl:text>
  fi;

  if [ "${OLIMIT}" ];then
    rrdtool tune /var/spool/apache/htdocs/mrtg/bw-${INT_NAME}.rrd -a high:${OLIMITK}*1024 -a med:${OLIMITK}*1024 -a low:${OLIMITK}*1024 -i high:0 -i med:0 -i low:0
    /sbin/tc qdisc add dev $INT_NAME root handle 1: htb default 20 r2q 1
    /sbin/tc class add dev $INT_NAME parent 1: classid 1:1 htb rate ${OLIMIT}Kbit
    /sbin/tc class add dev $INT_NAME parent 1:1 classid 1:10 htb rate ${OLIMIT50}Kbit ceil ${OLIMIT}Kbit
    /sbin/tc class add dev $INT_NAME parent 1:1 classid 1:20 htb rate ${OLIMIT30}Kbit ceil ${OLIMIT}Kbit
    /sbin/tc class add dev $INT_NAME parent 1:1 classid 1:30 htb rate ${OLIMIT20}Kbit ceil ${OLIMIT}Kbit
    /sbin/tc qdisc add dev $INT_NAME parent 1:10 handle 10: sfq perturb 10
    /sbin/tc qdisc add dev $INT_NAME parent 1:20 handle 20: sfq perturb 10
    /sbin/tc qdisc add dev $INT_NAME parent 1:30 handle 30: sfq perturb 10
  fi;

  #Delete Any Source Routeing Rules And Recreate Them
  /sbin/ip rule |grep Mark_${MARK} |awk '$3 != "all" {print "/sbin/ip rule del from "$3" iif lo table "$7}' |sh
  /sbin/ip rule add from $EXT_IP_ADDR iif lo table ${RTABLE} prio $PRIO
  /sbin/ip route add $DEST_IP src $EXT_IP_ADDR dev $INT_NAME scope link table ${RTABLE}

  #Setup the alternate route path
  /sbin/ip route del default table ${RTABLE}
  /sbin/ip route add default via $5 dev $INT_NAME src $4 table ${RTABLE}

  #Flush Routing Tables
  /sbin/ip route flush cache

  #NAT All packets outputing the interface with its ip any state
  /sbin/iptables -t nat -I MANGLE -j SNAT -o $INT_NAME --to-source $EXT_IP_ADDR

  #Default Incoming/Outgoing Rules
  /sbin/iptables -I MANGLEIN -j VOIPIN -i $INT_NAME -d ${LOCALIP} -p udp --sport 1024:65535
  /sbin/iptables -I MANGLEIN -j DEFIN -i $INT_NAME -d $EXT_IP_ADDR
  /sbin/iptables -I IP6RDDSL -j ACCEPT -i $INT_NAME -d $EXT_IP_ADDR

  /sbin/iptables -I MANGLEOUT -j ACCEPT -m mark --mark ${MARK}
  /sbin/iptables -I MANGLEOUT -j ACCEPT -o $INT_NAME -m mark --mark ${MARK}
  /sbin/iptables -I MANGLEOUT -j DEFOUT -o $INT_NAME -s $EXT_IP_ADDR
  /sbin/iptables -I MANGLEOUT -j VOIPOUT -o $INT_NAME -s ${LOCALIP} -p udp --dport 1024:65535
  /sbin/iptables -I IP6RDDSL -j ACCEPT -o $INT_NAME -s $EXT_IP_ADDR

  /sbin/iptables -I MANGLEFWD -j ACCEPT -o $INT_NAME 

  #Allow Proxy Requests 
  /sbin/iptables -I MANGLEPROXY -j DEFPROXY -t nat -i $INT_NAME -d $EXT_IP_ADDR

  #Clear TOS Values on marked packets
  /sbin/iptables -t mangle -A MANGLEF${FILID} -j TOS -o </xsl:text><xsl:value-of select="$extint"/><xsl:text> -m tos ! --tos 0 --set-tos 0 -m mark ! --mark 0x0 ! -d $EXT_IP_ADDR
  /sbin/iptables -t mangle -A MANGLEP${FILID} -j TOS ! -i ppp+ -m tos ! --tos 0 --set-tos 0 -m mark ! --mark 0x0 ! -d $EXT_IP_ADDR
</xsl:text>
    <xsl:if test="@virtip != ''">
      <xsl:value-of select="concat($nl,'  /sbin/ip route add ',@virtip,'/32 via ${DEST_IP} src ${EXT_IP_ADDR} dev ${INT_NAME} table Link')"/>
    </xsl:if>
    <xsl:variable name="sitip">
      <xsl:choose>
        <xsl:when test="@remip != ''">
          <xsl:value-of select="@remip"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>192.88.99.1</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:text>
  /sbin/ip route add default via ${DEST_IP} dev ${INT_NAME} metric ${METRIC} table 90

  #IPv6
  /sbin/ip addr add 2002:${ip6prefix}::1/48 dev ${INT_NAME}
  /sbin/ip addr add 2002:${ip6prefix}::1/128 dev sit0

  #Configure Default gateway
  if [ -d /sys/class/net/sit1.</xsl:text><xsl:value-of select="position()"/><xsl:text> ];then
    /sbin/ip tunnel change sit1.</xsl:text><xsl:value-of select="position()"/><xsl:text> local ${EXT_IP_ADDR}
    /sbin/ip link set dev sit1.</xsl:text><xsl:value-of select="position()"/><xsl:text> down
   else
    /sbin/ip tunnel add sit1.</xsl:text><xsl:value-of select="concat(position(),' mode sit remote ',$sitip)"/><xsl:text> local ${EXT_IP_ADDR}
  fi;
  /sbin/ip link set dev sit1.</xsl:text><xsl:value-of select="position()"/><xsl:text> up
  sleep 2
  /sbin/ip -6 route add ::/0 via fe80::${ip6prefix} dev sit1.</xsl:text><xsl:value-of select="position()"/><xsl:text> metric ${METRIC}

  (cat &lt;&lt;EOF
</xsl:text>
  <xsl:value-of select="concat('server ',/config/DNS/Config/Option[@option = 'DynServ'],$nl)"/>
  <xsl:value-of select="concat('key ',$dynzone,' ',$smartkey,$nl)"/>
  <xsl:value-of select="concat('zone ',$dynzone,$nl)"/>
  <xsl:value-of select="concat('update delete $6.',$dynzone,'. A',$nl)"/>
  <xsl:value-of select="concat('update delete $6.',$dynzone,'. AAAA',$nl)"/>
  <xsl:value-of select="concat('update add $6.',$dynzone,'. 180 A $EXT_IP_ADDR',$nl)"/>
  <xsl:value-of select="concat('update add $6.',$dynzone,'. 180 AAAA 2002:${ip6prefix}::1',$nl)"/>
  <xsl:text>send
EOF
)>/tmp/dnsup.$6.ppp

  chmod 640 /tmp/dnsup.$6.ppp
  echo $1 > /tmp/ppp.$6.int

</xsl:text>
  <xsl:if test="$dynzone != ''">
    <xsl:text>  /usr/bin/nsupdate /tmp/dnsup.$6.ppp</xsl:text>
  </xsl:if>
  <xsl:text>
  #Run Local Mangle Script
  if [ -x "/etc/ppp/mangle/$6" ];then
    /etc/ppp/mangle/$6 $EXT_IP_ADDR $DEST_IP $INT_NAME $MARK
  fi;

  /usr/sbin/conntrack -F
  /usr/sbin/conntrack -F expectation
  /sbin/ip route flush cache</xsl:text>
  </xsl:for-each>
  <xsl:text>
 elif [ "$6" == "pppoe" ];then
  #Delete Existing Classes
  /sbin/tc qdisc del dev $INT_NAME root > /dev/null 2>&amp;1
  /sbin/tc qdisc del dev $INT_NAME handle ffff: ingress > /dev/null 2>&amp;1

  if [ "</xsl:text><xsl:value-of select="$ringress"/><xsl:text>" != "0" ];then
    #Apply ingress limit
    /sbin/tc qdisc add dev $INT_NAME handle ffff: ingress&#xa;</xsl:text>
    <xsl:value-of select="concat('    /sbin/tc filter add dev $INT_NAME parent ffff: protocol ip u32 match ip dst ',$pppip,' police rate 100Mbit burst 1500 drop flowid :60',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc filter add dev $INT_NAME parent ffff: protocol ip u32 match ip dst ',$loclan,' police rate 100Mbit burst 1500 drop flowid :70',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc filter add dev $INT_NAME parent ffff: protocol ip u32 match ip dst 0/0 police rate ',$ringress,'Kbit burst 1500 drop flowid :80')"/><xsl:text>
  fi;

  if [ "</xsl:text><xsl:value-of select="$regress"/><xsl:text>" != "0" ];then
    #Apply egress limit
    /sbin/tc qdisc add dev $INT_NAME root handle 1: htb default 20 r2q 1
    /sbin/tc class add dev $INT_NAME parent 1: classid 1:1 htb rate 100Mbit&#xa;</xsl:text>
    <xsl:value-of select="concat('    /sbin/tc class add dev $INT_NAME parent 1:1 classid 1:5 htb rate ',$regress,'Kbit ceil 100Mbit',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc class add dev $INT_NAME parent 1:1 classid 1:10 htb rate ',$regress*0.5,'Kbit ceil ',$regress,'Kbit',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc class add dev $INT_NAME parent 1:1 classid 1:20 htb rate ',$regress*0.3,'Kbit ceil ',$regress,'Kbit',$nl)"/>
    <xsl:value-of select="concat('    /sbin/tc class add dev $INT_NAME parent 1:1 classid 1:30 htb rate ',$regress*0.2,'Kbit ceil ',$regress,'Kbit',$nl)"/><xsl:text>
    /sbin/tc qdisc add dev $INT_NAME parent 1:5 handle 5: sfq perturb 10
    /sbin/tc qdisc add dev $INT_NAME parent 1:10 handle 10: sfq perturb 10
    /sbin/tc qdisc add dev $INT_NAME parent 1:20 handle 20: sfq perturb 10
    /sbin/tc qdisc add dev $INT_NAME parent 1:30 handle 30: sfq perturb 10

  fi;

  /sbin/ip route add  ${5}/32 dev ${1} src ${4} table Link
  /sbin/iptables -t nat -I NOPPPNAT -j ACCEPT -o ${1}
  /sbin/iptables -I PPPFWD -j ACCEPT -i $INT_NAME -o </xsl:text>
    <xsl:value-of select="concat($pppoeint,' -d ',$pppoelan,' -s $5/32')"/><xsl:text>
  /sbin/iptables -I PPPFWD -j ACCEPT -o $INT_NAME -i </xsl:text>
    <xsl:value-of select="concat($pppoeint,' -s ',$pppoelan,' -d $5/32')"/><xsl:text>
  /sbin/iptables -I PPPIN -j SYSIN -i $INT_NAME -d </xsl:text>
    <xsl:value-of select="concat(/config/IP/Interfaces/Interface[. = $pppoeint]/@ipaddr,'/32 -s $5/32')"/><xsl:text>
  /sbin/iptables -I PPPOUT -j SYSOUT -o $INT_NAME -s </xsl:text>
    <xsl:value-of select="concat(/config/IP/Interfaces/Interface[. = $pppoeint]/@ipaddr,'/32 -d $5/32')"/><xsl:text>
  /sbin/iptables -I PPPIN -j MCASTIN -i $INT_NAME -d 224.0.0.0/3 -s $5/32
  /sbin/iptables -I PPPOUT -j MCASTOUT -o $INT_NAME -s 224.0.0.0/3 -d $5/32
#  if [ "$MAC_ADDR" ];then
#    /sbin/iptables -I PPPIN -j RETURN -I -s $5 -m mac --mac-source $MAC_ADDR
#  fi;
 elif [ "$6" == "3g" ];then
  (cat &lt;&lt;EOF
</xsl:text>
  <xsl:value-of select="concat('server ',/config/DNS/Config/Option[@option = 'DynServ'],$nl)"/>
  <xsl:value-of select="concat('key ',$dynzone,' ',$smartkey,$nl)"/>
  <xsl:value-of select="concat('zone ',$dynzone,$nl)"/>
  <xsl:value-of select="concat('update delete $6.',$dynzone,'. A',$nl)"/>
  <xsl:value-of select="concat('update add $6.',$dynzone,'. 180 A $EXT_IP_ADDR',$nl)"/>
<xsl:text>send
EOF
)>/tmp/dnsup.$6.ppp

  chmod 640 /tmp/dnsup.$6.ppp
  echo $1 > /tmp/ppp.$6.int
  /usr/bin/nsupdate /tmp/dnsup.$6.ppp
  /sbin/ip route add 0/0 via $DEST_IP dev $INT_NAME table 95;
  /sbin/iptables -F 3GIN
  /sbin/iptables -F 3GOUT
  /sbin/iptables -t nat -F 3GNAT

  /sbin/iptables -A 3GIN -j ACCEPT -i $INT_NAME
  /sbin/iptables -I 3GOUT -j ACCEPT -o $INT_NAME
  /sbin/iptables -t nat -A 3GNAT -j SNAT -o $INT_NAME --to-source $EXT_IP_ADDR

  if [ "$USEPEERDNS" == "1" ] &amp;&amp; [ "$DNS1" ];then
    if [ "$DNS2" ];then
       FWD="${DNS1};${DNS2}";
     else
      FWD=${DNS1}
    fi;
    echo "forwarders {${FWD};};" > /etc/bind/forwarders.${6}.ppp
    if [ ! -s /etc/bind/forwarders.ppp ];then
      cp /etc/bind/forwarders.ppp.${6} /etc/bind/forwarders.conf
      if [ "`/bin/pidof named`" ];then
        /usr/sbin/rndc reload &amp;
        sleep 5
       else
        /usr/sbin/named
      fi;
    fi;
  fi;
 elif [ "${6:0:4}"  == "l2tp" ];then
  /sbin/ip route add  ${5}/32 dev ${1} src ${4} table Link
  /sbin/iptables -t nat -I NOPPPNAT -j ACCEPT -o ${1}
  /sbin/iptables -I PPPIN -j SYSIN -i ${1} -d </xsl:text><xsl:value-of select="$intip"/><xsl:text> -s ${5}/32
  /sbin/iptables -I PPPOUT -j SYSOUT -o ${1} -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> -d ${5}/32
  /sbin/iptables -I PPPFWD -j ACCEPT -i ${1} -o </xsl:text><xsl:value-of select="$intiface"/><xsl:text>
  /sbin/iptables -I PPPFWD -j ACCEPT -o ${1} -i </xsl:text><xsl:value-of select="$intiface"/><xsl:text>
  /sbin/iptables -I PPPIN -j MCASTIN -i ${1} -d 224.0.0.0/3 -s $5/32
  /sbin/iptables -I PPPOUT -j MCASTOUT -o ${1} -s 224.0.0.0/3 -d $5/32
  /sbin/iptables -I PPPOUT -j MCASTOUT -o ${1} -s ${4} -d 224.0.0.0/3
 elif [ "$6" != "other" ];then
  /usr/sbin/radipup $6 $5
fi;
</xsl:text>
</xsl:template>
</xsl:stylesheet>
