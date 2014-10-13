<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name="nl">&#xa;</xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>

<xsl:template name="getext">
  <xsl:choose>
    <xsl:when test="($extint = 'Dialup') or ($extcon = 'ADSL')">
      <xsl:text>ppp0</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$extint"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="SIT">
  <xsl:text>
#Additonal gateway 2002:</xsl:text><xsl:value-of select="@ipv6to4pre"/><xsl:text>::/</xsl:text><xsl:value-of select="@subnet"/><xsl:text> Via </xsl:text><xsl:value-of select="."/><xsl:text>
if [ -d /sys/class/net/sit</xsl:text><xsl:value-of select="position()+1"/><xsl:text> ];then
  /sbin/ip tunnel change sit</xsl:text><xsl:value-of select="position()+1"/><xsl:text> local </xsl:text><xsl:value-of select="$sitip"/><xsl:text>
  /sbin/ip link set dev sit</xsl:text><xsl:value-of select="position()+1"/><xsl:text> down
 else
  /sbin/ip tunnel add sit</xsl:text><xsl:value-of select="position()+1"/><xsl:text> mode sit remote </xsl:text><xsl:value-of select="."/><xsl:text> local </xsl:text><xsl:value-of select="$sitip"/><xsl:text>
fi;
/sbin/ip link set dev sit</xsl:text><xsl:value-of select="position()+1"/><xsl:text> up
/sbin/ip route add 2002:</xsl:text><xsl:value-of select="@ipv6to4pre"/><xsl:text>::/32 via fe80::${i6prefix} dev sit</xsl:text><xsl:value-of select="position()+1"/>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template name="active">
  <xsl:text>#!/bin/bash

i6prefix=</xsl:text><xsl:value-of select="$ip6prefix"/><xsl:text>

#Configure block on external
/sbin/ip link set sit0 up
/sbin/ip addr add 2002:${i6prefix}::1/64 dev </xsl:text><xsl:call-template name="getext"/><xsl:text> > /dev/null 2>&amp;1
/sbin/ip addr add 2002:${i6prefix}::1/128 dev sit0 > /dev/null 2>&amp;1

#Configure Default gateway
if [ -d /sys/class/net/sit1 ];then
  /sbin/ip tunnel change sit1 local </xsl:text><xsl:value-of select="$sitip"/><xsl:text>
  /sbin/ip link set dev sit1 down
 else
  /sbin/ip tunnel add sit1 mode sit remote 192.88.99.1 local </xsl:text><xsl:value-of select="$sitip"/><xsl:text>
fi;
/sbin/ip link set dev sit1 up
</xsl:text>
  <xsl:apply-templates select="/config/IPv6/IPv6to4/SIT"/>

  <xsl:text>&#xa;</xsl:text>
  <xsl:if test="count(/config/IPv6/IPv6to4/SIT[@subnet &lt;= '16']) = 0">
    <xsl:text>/sbin/ip -6 route add 2002::/16 dev sit0 metric 1 > /dev/null 2>&amp;1&#xa;</xsl:text>
  </xsl:if>

<xsl:text>/sbin/ip -6 route add ::/0 via fe80::</xsl:text>
  <xsl:value-of select="$gwout"/>
  <xsl:text> dev sit1 metric 1 > /dev/null 2>&amp;1

killall -HUP radvd;
sleep 3;
if [ ! "`pidof radvd`" ] &amp;&amp; [ -x /usr/sbin/radvd ] &amp;&amp; [ -s /etc/radvd.conf ];then
  /usr/sbin/radvd;
fi;

if [ "`pidof hostapd`" ];then
  sleep 10;
  killall -HUP hostapd
fi;

/sbin/iptables -F IP6RD
/sbin/iptables -I IP6RD -j ACCEPT -i </xsl:text><xsl:call-template name="getext"/><xsl:text> -d </xsl:text><xsl:value-of select="$sitip"/><xsl:text>
/sbin/iptables -I IP6RD -j ACCEPT -o </xsl:text><xsl:call-template name="getext"/><xsl:text> -s </xsl:text><xsl:value-of select="$sitip"/><xsl:text>

/etc/ifconf/ipv6to4.addr add ${i6prefix}
</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:choose>
    <xsl:when test="$baseprefix != ''">
      <xsl:call-template name="active"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>#!/bin/bash&#xa;&#xa;exit;&#xa;&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
