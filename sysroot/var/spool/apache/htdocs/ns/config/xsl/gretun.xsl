<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="extip" select="/config/IP/Interfaces/Interface[text() = $extiface]/@ipaddr"/>

<!--
XXXX
need ESP testing and get it working for a internal/external interface ppp support
-->

<xsl:template match="Route">
  <xsl:param name="tun"/>
  <xsl:value-of select="concat('    /sbin/ip route add ',.,' via ',@local,' dev ',$tun,' table VPN',$nl)"/>
</xsl:template>

<xsl:template match="Source">
  <xsl:param name="tun"/>
  <xsl:param name="tloc"/>
  <xsl:param name="trem"/>

  <xsl:value-of select="concat('    /sbin/ip route add ',@ipaddr,'/',@subnet,' dev ',$tun,' scope link src ',$intip,' table VPN',$nl)"/>
  <xsl:value-of select="concat('    /sbin/ip route add ',$trem,' dev ',$tun,' scope link src ',$tloc,' table VPN',$nl)"/>

  <xsl:apply-templates select="/config/IP/GRE/Routes/Route[@local = $tloc]">
    <xsl:with-param name="tun" select="$tun"/>
  </xsl:apply-templates>
</xsl:template>

<xsl:template match="Tunnel">
  <xsl:variable name="tun" select="concat('gtun',position()-1)"/>
  <xsl:variable name="tloc" select="@local"/>

  <xsl:value-of select="concat($nl,'#Configure Tunnel ',$tun,$nl,$nl)"/>
  <xsl:value-of select="concat('/sbin/ip link show ',$tun,' > /dev/null 2>&amp;1',$nl)"/>
  <xsl:value-of select="concat('if [ $? != 0 ] || [ -e /tmp/tun_',$tun,' ];then',$nl)"/>
  <xsl:text>  SR=1&#xa;</xsl:text>
  <xsl:text> else&#xa;</xsl:text>
  <xsl:value-of select="concat('  ping -c 3 -l 3 -w 5 -q ',@remote,' > /dev/null 2>&amp;1;',$nl)"/>
  <xsl:text>  SR=$?&#xa;</xsl:text>
  <xsl:text>fi&#xa;&#xa;</xsl:text>

  <xsl:value-of select="concat('if [ &quot;${SR}&quot; == &quot;1&quot; ] &amp;&amp; [ -d /sys/class/net/',@interface,' ];then',$nl)"/>
  <xsl:value-of select="concat('  if [ -e /tmp/tun_',$tun,' ];then',$nl)"/>
  <xsl:value-of select="concat('    rm /tmp/tun_',$tun,$nl)"/>
  <xsl:text>  fi;&#xa;</xsl:text>

  <xsl:text>  LOCAL=&#x60;/sbin/ip addr show dev </xsl:text>
  <xsl:value-of select="@interface"/>
  <xsl:text> |grep inet |head -1 |cut -d/ -f1 |awk &apos;{print $2}&apos;&#x60;&#xa;</xsl:text>
  <xsl:value-of select="concat('  REMOTEIP=`dig +short &quot;',.,'&quot; A IN +tries=1 |tail -1`',$nl)"/>
  <xsl:text>  if [ "$LOCAL" ] &amp;&amp; [ "$REMOTEIP" != ";;" ] &amp;&amp; [ "$REMOTEIP" ];then&#xa;</xsl:text>
  <xsl:text>    #Flush SPD Entry&#xa;</xsl:text>
  <xsl:text>    (cat &lt;&lt;EOF&#xa;</xsl:text>
  <xsl:text>spddelete $LOCAL/32 $REMOTEIP/32 gre -P out;&#xa;</xsl:text>
  <xsl:text>spddelete $REMOTEIP/32 $LOCAL/32 gre -P in;&#xa;</xsl:text>
  <xsl:text>EOF&#xa;</xsl:text>
  <xsl:text>) |setkey -c&#xa;&#xa;</xsl:text>

  <xsl:text>    #Restart Tunnel&#xa;</xsl:text>
  <xsl:value-of select="concat('    /sbin/ip tun del ',$tun,$nl)"/>
  <xsl:value-of select="concat('    /sbin/ip rule del from ',@local,' table VPN prio 20',$nl)"/>
  <xsl:value-of select="concat('    /sbin/ip tunnel add ',$tun,' mode gre remote $REMOTEIP local $LOCAL ttl 255',$nl)"/>
  <xsl:value-of select="concat('    /sbin/ip rule add from ',@local,' table VPN prio 20',$nl)"/>
  <xsl:value-of select="concat('    /sbin/ip addr add ',@local,'/30 peer ',@remote,' dev ',$tun,$nl)"/>
  <xsl:value-of select="concat('    /sbin/ip link set ',$tun,' multicast on',$nl)"/>
  <xsl:value-of select="concat('    /sbin/ip link set ',$tun,' mtu ',@mtu,$nl)"/>
  <xsl:value-of select="concat('    /sbin/ip link set ',$tun,' up',$nl)"/>
  <xsl:text>    REROUTE=1&#xa;</xsl:text>
  <xsl:value-of select="concat('    #Tunnel Routes For ',$tun,$nl)"/>
  <xsl:value-of select="concat('    /sbin/ip route add ',@nwaddr,'/30 src ',@local,' dev ',$tun,' table Link',$nl)"/>

  <xsl:apply-templates select="/config/IP/FW/Iface[@iface = $tloc]/Source">
    <xsl:with-param name="tloc" select="$tloc"/>
    <xsl:with-param name="trem" select="@remote"/>
    <xsl:with-param name="tun" select="$tun"/>
  </xsl:apply-templates>
  <xsl:text>&#xa;</xsl:text>
  <xsl:text>    sleep 10;&#xa;</xsl:text>
  <xsl:value-of select="concat('    /sbin/ip link show ',$tun,' > /dev/null 2>&amp;1',$nl)"/>
  <xsl:text>    if [ $? != 0 ];then&#xa;</xsl:text>
  <xsl:text>      SR=1;&#xa;</xsl:text>
  <xsl:text>     else&#xa;</xsl:text>
  <xsl:value-of select="concat('      ping -c 3 -l 3 -w 5 -q ',@remote,' > /dev/null 2>&amp;1;',$nl)"/>
  <xsl:text>      SR=$?&#xa;</xsl:text>
  <xsl:text>    fi&#xa;&#xa;</xsl:text>
  <xsl:text>    if [ "$SR" == "1" ];then&#xa;</xsl:text>
  <xsl:value-of select="concat('       touch /tmp/tun_',$tun,$nl)"/>
  <xsl:text>     else&#xa;</xsl:text>
  <xsl:text>      #Attempt To Use IPSEC&#xa;</xsl:text>
  <xsl:text>      (cat &lt;&lt;EOF&#xa;</xsl:text>

  <xsl:if test="@ipsec = '0'">
    <xsl:text>spdadd $LOCAL/32 $REMOTEIP/32 gre -P out ipsec esp/transport//use;&#xa;</xsl:text>
    <xsl:text>spdadd $REMOTEIP/32 $LOCAL/32 gre -P in ipsec esp/transport//use;&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="@ipsec = '1'">
    <xsl:text>spdadd $LOCAL/32 $REMOTEIP/32 gre -P out none;&#xa;</xsl:text>
    <xsl:text>spdadd $REMOTEIP/32 $LOCAL/32 gre -P in none;&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="@ipsec = '2'">
    <xsl:text>spdadd $LOCAL/32 $REMOTEIP/32 gre -P out ipsec esp/transport//require;&#xa;</xsl:text>
    <xsl:text>spdadd $REMOTEIP/32 $LOCAL/32 gre -P in ipsec esp/transport//require;&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>EOF&#xa;) |setkey -c&#xa;</xsl:text>
  <xsl:text>    fi;&#xa;</xsl:text>
  <xsl:text>  fi;&#xa;</xsl:text>
  <xsl:text>fi;&#xa;</xsl:text>
</xsl:template>

<xsl:template match="ESPTunnel">
  <xsl:value-of select="concat($nl,'#Configure ESP Tunnel ',@local,' &lt;-&gt; ',$extip,' - ',@dest,' &lt;-&gt; ',.,$nl)"/>

  <xsl:value-of select="concat('/sbin/ip route flush cache ',@gateway,$nl)"/>
  <xsl:value-of select="concat('/usr/bin/ping -q -l 2 -c 2 -w 4 ',@gateway,' > /dev/null 2>&amp;1',$nl)"/>

  <xsl:text>if [ "$?" == "1" ];then&#xa;</xsl:text>
  <xsl:value-of select="concat('  /sbin/ip route del ',.,' table 80',$nl)"/>
  <xsl:value-of select="concat('  /sbin/ip route add ',.,' dev ',$extiface,' src ',substring-before(@local,'/'),' table 80',$nl)"/>

  <xsl:text>  (cat &lt;&lt;EOF&#xa;</xsl:text>
  <xsl:value-of select="concat('deleteall ',@nwaddr,' ',.,' esp;',$nl)"/>
  <xsl:value-of select="concat('deleteall ',.,' ',@nwaddr,' esp;',$nl)"/>
  <xsl:text>EOF&#xa;</xsl:text>
  <xsl:text>) |setkey -c&#xa;</xsl:text>

<!--
deleteall @espdata[0] \${LOCAL} esp;
spddelete $dmznw $dmznw any -P in;
spddelete $dmznw $dmznw any -P out;
spddelete $dmznw @espdata[1] any -P out;
spddelete @espdata[1] $dmznw any -P in;
spdadd $dmznw @espdata[1] any -P out ipsec esp/tunnel/\${LOCAL}-@espdata[0]/use;
spdadd @espdata[1] $dmznw any -P in ipsec esp/tunnel/@espdata[0]-\${LOCAL}/use;
spdadd $dmznw $dmznw any -P in none;
spdadd $dmznw $dmznw any -P out none;
-->
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash&#xa;</xsl:text>
  <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel"/>
  <xsl:apply-templates select="/config/IP/ESP/Tunnels/ESPTunnel"/>

  <xsl:text>if [ "$RESTART_FW" ];then
  DEFINT=`/sbin/ip route show 0.0.0.0/0 table 90 |awk '{print $5}'`
  IPADDR=`/sbin/ip -f inet -o addr show $DEFINT |awk '{print $4}' |cut -d/ -f1`
  DEFGW=`/sbin/ip route show 0.0.0.0/0 table 90|awk '{print $3}'`
  /etc/ppp/ip-up $DEFINT - - $IPADDR $DEFGW
fi;

#if [ "$REROUTE" = 1 ];then
#  killall -9 ospfd
#  sleep 3
#  ospfd -d
#  vtysh -b
#fi;
</xsl:text>
</xsl:template>
</xsl:stylesheet>
