<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />

<xsl:template name="markset">
  <xsl:param name="mark"/>
  <xsl:param name="proto"/>
  <xsl:choose>
    <xsl:when test="(@protocol = 'TCP') or (@protocol = 'UDP')">
      <xsl:text>/usr/sbin/iptables -t mangle -A SYSINGRESS -j MARK --set-mark </xsl:text>
      <xsl:value-of select="$mark"/> -m mark --mark 0x102 -p <xsl:value-of select="$proto"/> -s <xsl:value-of select="@ipaddr"/> --sport <xsl:value-of select="@dport"/> --dport <xsl:value-of select="@sport"/>
/usr/sbin/iptables -t mangle -A SYSEGRESS -j MARK --set-mark <xsl:value-of select="$mark"/> -m mark --mark 0x102 -p <xsl:value-of select="$proto"/> -d <xsl:value-of select="@ipaddr"/> --dport <xsl:value-of select="@dport"/> --sport <xsl:value-of select="concat(@sport,$nl)"/>
      <xsl:if test="@sport = '80'">
        <xsl:text>/usr/sbin/iptables -t mangle -A SYSINGRESS -j MARK --set-mark </xsl:text>
        <xsl:value-of select="$mark"/> -m mark --mark 0x102 -p <xsl:value-of select="$proto"/> -s <xsl:value-of select="@ipaddr"/> --dport <xsl:value-of select="@dport"/> --sport <xsl:value-of select="'3128'"/>
/usr/sbin/iptables -t mangle -A SYSEGRESS -j MARK --set-mark <xsl:value-of select="$mark"/> -m mark --mark 0x102 -p <xsl:value-of select="$proto"/> -d <xsl:value-of select="@ipaddr"/> --sport <xsl:value-of select="@dport"/> --dport <xsl:value-of select="concat('3128',$nl)"/>
      </xsl:if>
      <xsl:if test="@dport = '80'">
        <xsl:text>/usr/sbin/iptables -t mangle -A SYSINGRESS -j MARK --set-mark </xsl:text>
        <xsl:value-of select="$mark"/> -m mark --mark 0x102 -p <xsl:value-of select="$proto"/> -s <xsl:value-of select="@ipaddr"/> --dport <xsl:value-of select="@sport"/> --sport <xsl:value-of select="'3128'"/>
/usr/sbin/iptables -t mangle -A SYSEGRESS -j MARK --set-mark <xsl:value-of select="$mark"/> -m mark --mark 0x102 -p <xsl:value-of select="$proto"/> -d <xsl:value-of select="@ipaddr"/> --sport <xsl:value-of select="@sport"/> --dport <xsl:value-of select="concat('3128',$nl)"/>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>/usr/sbin/iptables -t mangle -A SYSINGRESS -j MARK --set-mark </xsl:text>
      <xsl:value-of select="$mark"/> -m mark --mark 0x102 -p <xsl:value-of select="$proto"/> -s <xsl:value-of select="@ipaddr"/>
/usr/sbin/iptables -t mangle -A SYSEGRESS -j MARK --set-mark <xsl:value-of select="$mark"/> -m mark --mark 0x102 -p <xsl:value-of select="$proto"/> -d <xsl:value-of select="concat(@ipaddr,$nl)"/>
      <xsl:text>&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="TOS">
  <xsl:variable name="proto" select="translate(@protocol, $uppercase, $smallcase)"/>

  <xsl:value-of select="concat('#Set Tos For ',@name)"/>
  <xsl:choose>
    <xsl:when test=". != 'Normal-Service'">
      <xsl:choose>
        <xsl:when test="(@protocol = 'TCP') or (@protocol = 'UDP')">
/usr/sbin/iptables -t mangle -A SYSTOS -j TOS -p <xsl:value-of select="$proto"/> -d <xsl:value-of select="@ipaddr"/> --dport <xsl:value-of select="@dport"/> --sport <xsl:value-of select="@sport"/> --set-tos <xsl:value-of select="concat(.,$nl)"/>
        </xsl:when>
        <xsl:otherwise>
/usr/sbin/iptables -t mangle -A SYSTOS -j TOS -p <xsl:value-of select="$proto"/> -d <xsl:value-of select="@ipaddr"/> --set-tos <xsl:value-of select="concat(.,$nl)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:choose>
        <xsl:when test="(@protocol = 'TCP') or (@protocol = 'UDP')">
/usr/sbin/iptables -t mangle -A NOSYSTOS -j ACCEPT -p <xsl:value-of select="$proto"/> -d <xsl:value-of select="@ipaddr"/> --dport <xsl:value-of select="@dport"/> --sport <xsl:value-of select="concat(@sport,$nl)"/> 
        </xsl:when>
        <xsl:otherwise>
/usr/sbin/iptables -t mangle -A NOSYSTOS -j ACCEPT -p <xsl:value-of select="$proto"/> -d <xsl:value-of select="concat(@ipaddr,$nl)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:if test="@priority = 'High'">
    <xsl:call-template name="markset">
      <xsl:with-param name="mark" select="'0x101'"/>
      <xsl:with-param name="proto" select="$proto"/>
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="@priority = 'Med'">
    <xsl:call-template name="markset">
      <xsl:with-param name="mark" select="'0x102'"/>
      <xsl:with-param name="proto" select="$proto"/>
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="@priority = 'Low'">
    <xsl:call-template name="markset">
      <xsl:with-param name="mark" select="'0x103'"/>
      <xsl:with-param name="proto" select="$proto"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template match="/config">#!/bin/bash
<xsl:if test="/config/IP/SysConf/Option[@option='Internal'] != /config/IP/SysConf/Option[@option='External']">
#Flushing Rules
/usr/sbin/iptables -t mangle -F SYSTOS
/usr/sbin/iptables -t mangle -F NOSYSTOS
/usr/sbin/iptables -t mangle -F SYSINGRESS
/usr/sbin/iptables -t mangle -F SYSEGRESS

#Set Tos For RTP
/usr/sbin/iptables -t mangle -A SYSTOS -j TOS -p udp -d 0/0 --dport 10000:20000 --sport 1024:65535 --set-tos Minimize-Delay
/usr/sbin/iptables -t mangle -A SYSINGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p udp -s 0/0 --sport 10000:20000 --dport 1024:65535
/usr/sbin/iptables -t mangle -A SYSEGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p udp -d 0/0 --dport 10000:20000 --sport 1024:65535

#Set Tos For TCP H.323 Signaling
/usr/sbin/iptables -t mangle -A SYSTOS -j TOS -p tcp -d 0/0 --sport 10000:11999 --dport 1024:65535 --set-tos Minimize-Delay

#Set Tos For IAX
/usr/sbin/iptables -t mangle -A SYSTOS -j TOS -p udp -d 0/0 --dport 4569 --sport 1024:65535 --set-tos Minimize-Delay
/usr/sbin/iptables -t mangle -A SYSINGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p udp -s 0/0 --sport 4569 --dport 1024:65535
/usr/sbin/iptables -t mangle -A SYSEGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p udp -d 0/0 --dport 4569 --sport 1024:65535

#Set Tos For IAX2
/usr/sbin/iptables -t mangle -A SYSTOS -j TOS -p udp -d 0/0 --dport 5036 --sport 1024:65535 --set-tos Minimize-Delay
/usr/sbin/iptables -t mangle -A SYSINGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p udp -s 0/0 --sport 5036 --dport 1024:65535
/usr/sbin/iptables -t mangle -A SYSEGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p udp -d 0/0 --dport 5036 --sport 1024:65535

#Set Tos For SIP
/usr/sbin/iptables -t mangle -A SYSTOS -j TOS -p udp -d 0/0 --dport 5060 --sport 1024:65535 --set-tos Minimize-Delay
/usr/sbin/iptables -t mangle -A SYSINGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p udp -s 0/0 --sport 5060 --dport 1024:65535
/usr/sbin/iptables -t mangle -A SYSEGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p udp -d 0/0 --dport 5060 --sport 1024:65535

/usr/sbin/iptables -t mangle -A SYSTOS -j TOS -p udp -d 0/0 --dport 5000 --sport 1024:65535 --set-tos Minimize-Delay
/usr/sbin/iptables -t mangle -A SYSINGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p udp -s 0/0 --sport 5000 --dport 1024:65535
/usr/sbin/iptables -t mangle -A SYSEGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p udp -d 0/0 --dport 5000 --sport 1024:65535

/usr/sbin/iptables -t mangle -A SYSTOS -j TOS -p tcp -d 0/0 --dport 5060:5061 --sport 1024:65535 --set-tos Minimize-Delay
/usr/sbin/iptables -t mangle -A SYSINGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p tcp -s 0/0 --sport 5060:5061 --dport 1024:65535
/usr/sbin/iptables -t mangle -A SYSEGRESS -j MARK --set-mark 0x101 -m mark --mark 0x102 -p tcp -d 0/0 --dport 5060:5061 --sport 1024:65535

<xsl:apply-templates select="/config/IP/QOS/TOS"/>
  <xsl:choose>
    <xsl:when test="(/config/IP/Dialup/Option[@option='Connection'] = 'ADSL') or 
                    (/config/IP/SysConf/Option[@option='External'] = 'Dialup')">
      <xsl:text>/usr/sbin/iptables -t mangle -A NOSYSTOS -j ACCEPT -i ppp0&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>/usr/sbin/iptables -t mangle -A NOSYSTOS -j ACCEPT -i </xsl:text>
      <xsl:value-of select="/config/IP/SysConf/Option[@option='External']"/>
      <xsl:text>&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:if>
</xsl:template>
</xsl:stylesheet>
