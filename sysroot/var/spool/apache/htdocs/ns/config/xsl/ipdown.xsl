<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="dynzone" select="/config/DNS/Config/Option[@option = 'DynZone']"/>
<xsl:variable name="pppint" select="/config/Radius/Config/Option[@option = 'PPPoEIF']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="pppip" select="/config/Radius/Config/Option[@option = 'PPPoE']"/>

<xsl:template match="Link">
  <xsl:choose>
    <xsl:when test="position() = 1">
      <xsl:value-of select="concat($nl,'if [ &quot;$6&quot; == &quot;',.,'&quot; ];then',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat(' elif [ &quot;$6&quot; == &quot;',.,'&quot; ];then',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="concat('  MARK=',position(),$nl)"/>
  <xsl:value-of select="concat('  FILID=',position(),$nl)"/>
  <xsl:value-of select="concat('  RTABLE=',100+position()-1,$nl,$nl)"/>

  <xsl:text>  if [ -e /tmp/pppup/$1.ip-up ];then
    rm /tmp/pppup/$1.ip-up
  fi;

  #Flush Applicable Chains
  /sbin/iptables -t mangle -F MANGLEP${FILID}
  /sbin/iptables -t mangle -F MANGLEO${FILID}
  /sbin/iptables -t mangle -F MANGLEF${FILID}

  #Delete Existing Classes
  /sbin/tc qdisc del dev $1 root > /dev/null 2>&amp;1
  /sbin/tc qdisc del dev imq${MARK} root > /dev/null 2>&amp;1

  #Delete the alternate route path
  /sbin/ip route del default table ${RTABLE}
  /sbin/ip rule del from ${4} iif lo table ${RTABLE}
  /sbin/ip route flush cache

  /sbin/ip tunnel del sit1.${FILID}

  /sbin/iptables -D IP6RDDSL -j ACCEPT -i $INT_NAME -d ${4}
  /sbin/iptables -D MANGLEIN -j DEFIN -i $INT_NAME -d ${4}
  /sbin/iptables -D MANGLEIN -j VOIPIN -i $INT_NAME -d ${LOCALIP} -p udp --sport 1024:65535
  /sbin/iptables -D MANGLEOUT -j DEFOUT -o $INT_NAME -s ${4}
  /sbin/iptables -D IP6RDDSL -j ACCEPT -o $INT_NAME -s ${4}
  /sbin/iptables -D MANGLEOUT -j ACCEPT -o $INT_NAME -m mark --mark ${MARK}
  /sbin/iptables -D MANGLEOUT -j ACCEPT -m mark --mark ${MARK}
  /sbin/iptables -D MANGLEOUT -j VOIPOUT -o $INT_NAME -s ${LOCALIP} -p udp --dport 1024:65535
  /sbin/iptables -D MANGLEFWD -j ACCEPT -o $INT_NAME
  /sbin/iptables -t nat -D MANGLEPROXY -j DEFPROXY -t nat -i $INT_NAME -d ${4}
  /sbin/iptables -t nat -D MANGLE -j SNAT -o $INT_NAME --to-source $4

  (cat &lt;&lt;EOF
server </xsl:text><xsl:value-of select="/config/DNS/Config/Option[@option = 'DynServ']"/><xsl:text>
key </xsl:text><xsl:value-of select="concat($dynzone,' ',$smartkey)"/><xsl:text>
zone </xsl:text><xsl:value-of select="$dynzone"/><xsl:text>
update delete $6.</xsl:text><xsl:value-of select="$dynzone"/><xsl:text>. A
update delete $6.</xsl:text><xsl:value-of select="$dynzone"/><xsl:text>. AAAA
send
EOF
)>/tmp/dnsup.$6.ppp

  chmod 640 /tmp/dnsup.$6.ppp
  echo $1 > /tmp/ppp.$6.int
</xsl:text>

  <xsl:if test="$dynzone">
    <xsl:value-of select="concat('  /usr/bin/nsupdate /tmp/dnsup.$6.ppp',$nl)"/>
  </xsl:if>
</xsl:template>

<!--
  @pppoeint=@{$interface->{$wirelessint}};
  $pppoelan=getnw(@pppoeint[2],@pppoeint[1]);
-->

<xsl:template name="pppoedown">
  <xsl:param name="pint"/>

  <xsl:variable name="pppip" select="/config/IP/Interfaces/Interface[. = $pint]/@ipaddr"/>
  <xsl:variable name="pppnw" select="/config/IP/Interfaces/Interface[. = $pint]/@nwaddr"/>
  <xsl:variable name="pppsn" select="/config/IP/Interfaces/Interface[. = $pint]/@subnet"/>

  <xsl:text> elif [ "$6"  == "pppoe" ];then
  /sbin/iptables -t nat -D NOPPPNAT -j ACCEPT -o ${1}
  /sbin/iptables -D PPPFWD -j ACCEPT -i $INT_NAME -o </xsl:text>
    <xsl:value-of select="concat($pint,' -d ',$pppnw,'/',$pppsn,' -s $5/32')"/><xsl:text>
  /sbin/iptables -D PPPFWD -j ACCEPT -o $INT_NAME -i </xsl:text>
    <xsl:value-of select="concat($pint,' -s ',$pppnw,'/',$pppsn,' -d $5/32')"/><xsl:text>
  /sbin/iptables -D PPPIN -j SYSIN -i $INT_NAME -d </xsl:text><xsl:value-of select="$pppip"/><xsl:text>/32 -s $5/32
  /sbin/iptables -D PPPIN -j MCASTIN -i $INT_NAME -d 224.0.0.0/3 -s $5/32
  /sbin/iptables -D PPPOUT -j SYSOUT -o $INT_NAME -s </xsl:text><xsl:value-of select="$pppip"/><xsl:text>/32 -d $5/32
  /sbin/iptables -D PPPOUT -j MCASTOUT -o $INT_NAME -s 224.0.0.0/3 -d $5/32
</xsl:text>
</xsl:template>

<xsl:template name="pppdown">
  <xsl:text>#!/bin/bash&#xa;</xsl:text>
  <xsl:text>
INT_NAME="$1"
LOCALIP="</xsl:text><xsl:value-of select="$intip"/><xsl:text>"

/usr/bin/awk -F\| -v LINK=$1 '$6 == LINK {printf "spddelete %s %s any -P out;\nspddelete %s %s any -P in;\n",$2,$3,$3,$2}' /etc/vpnconf |/usr/sbin/setkey -c
/sbin/ip addr del ::${4}/96 dev sit0 > /dev/null 2>&amp;1
i6prefix=$(printf "%02x%02x:%02x%02x\n" $(echo ${4} | sed "s/\./ /g"))
/sbin/ip addr del 2002:${i6prefix}::1/128 dev sit0 > /dev/null 2>&amp;1
</xsl:text>
  <xsl:apply-templates select="/config/IP/ADSL/Links/Link"/>
  <xsl:if test="count(/config/IP/ADSL/Links/Link)">
    <xsl:text>fi;&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>
if [ ! "${6}" ];then
  (/sbin/ip addr del 2002:${i6prefix}::1/64 dev ${1}
  /sbin/ip addr del 2002:${i6prefix}::1/48 dev sit0
  /sbin/ip addr del 2002:${i6prefix}::1/64 dev sit0) > /dev/null 2>&amp;1
  /etc/ifconf/ipv6to4.addr del ${i6prefix}
  if [ -e /etc/bind/forwarders.ppp ];then
    if [ ! "`diff -u /etc/bind/forwarders.ppp /etc/bind/forwarders.conf`" ];then
      if [ -s /etc/bind/forwarders.static ];then
        cp /etc/bind/forwarders.static /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.dhcp6 ];then
        cp /etc/bind/forwarders.dhcp6 /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.dhcp4 ];then
        cp /etc/bind/forwarders.dhcp4 /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.conf ];then
        echo -n > /etc/bind/forwarders.conf
      fi;
      /usr/sbin/rndc reload
    fi;
    rm /etc/bind/forwarders.ppp
  fi;
</xsl:text>
  <xsl:choose>
    <xsl:when test="$pppint = ''">
      <xsl:call-template name="pppoedown">
        <xsl:with-param name="pint" select="$intiface"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="pppoedown">
        <xsl:with-param name="pint" select="$pppint"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text> elif [ "$6"  == "3g" ];then
  if [ -e /etc/bind/forwarders.ppp.3g ];then
    if [ ! "`diff -u /etc/bind/forwarders.ppp.3g /etc/bind/forwarders.conf`" ];then
      if [ -s /etc/bind/forwarders.ppp ];then
        cp /etc/bind/forwarders.ppp /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.static ];then
        cp /etc/bind/forwarders.static /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.dhcp6 ];then
        cp /etc/bind/forwarders.dhcp6 /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.dhcp4 ];then
        cp /etc/bind/forwarders.dhcp4 /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.conf ];then
        echo -n > /etc/bind/forwarders.conf
      fi;
      /usr/sbin/rndc reload
    fi;
    rm /etc/bind/forwarders.ppp.3g
  fi;
  /sbin/iptables -F 3GIN
  /sbin/iptables -F 3GOUT
  /sbin/iptables -t nat -F 3GNAT
 elif [ "${6:0:4}"  == "l2tp" ];then
  /sbin/iptables -t nat -D NOPPPNAT -j ACCEPT -o ${1}
  /sbin/iptables -D PPPIN -j SYSIN -i ${1} -d </xsl:text><xsl:value-of select="$intip"/><xsl:text> -s ${5}/32
  /sbin/iptables -D PPPOUT -j SYSOUT -o ${1} -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> -d ${5}/32
  /sbin/iptables -D PPPFWD -j ACCEPT -i ${1} -o </xsl:text><xsl:value-of select="$intiface"/><xsl:text>
  /sbin/iptables -D PPPFWD -j ACCEPT -o ${1} -i </xsl:text><xsl:value-of select="$intiface"/><xsl:text>
  /sbin/iptables -D PPPIN -j MCASTIN -i ${1} -d 224.0.0.0/3 -s $5/32
  /sbin/iptables -D PPPOUT -j MCASTOUT -o ${1} -s 224.0.0.0/3 -d $5/32
  /sbin/iptables -D PPPOUT -j MCASTOUT -o ${1} -s ${4} -d 224.0.0.0/3
 elif [ "$6"  != "other" ] &amp;&amp; [ "$6" ];then
  /usr/sbin/radipdown $6 $5
 elif [ "$6" ];then
  if [ -e /etc/bind/forwarders.ppp.${6} ];then
    if [ ! "`diff -u /etc/bind/forwarders.ppp.${6} /etc/bind/forwarders.conf`" ];then
      if [ -s /etc/bind/forwarders.ppp ];then
        cp /etc/bind/forwarders.ppp /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.static ];then
        cp /etc/bind/forwarders.static /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.dhcp6 ];then
        cp /etc/bind/forwarders.dhcp6 /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.dhcp4 ];then
        cp /etc/bind/forwarders.dhcp4 /etc/bind/forwarders.conf
       elif [ -s /etc/bind/forwarders.conf ];then
        echo -n > /etc/bind/forwarders.conf
      fi;
      /usr/sbin/rndc reload
    fi;
    rm /etc/bind/forwarders.ppp.${6}
  fi;
fi;
</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:if test="($intiface != $extiface) or ($extcon = 'ADSL')">  
    <xsl:call-template name="pppdown"/>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
