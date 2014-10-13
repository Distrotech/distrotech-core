<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intint" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>

<xsl:template name="setupiface">
  <xsl:param name="iface"/>
  <xsl:param name="sncnt"/>

  <xsl:choose>
    <xsl:when test="/config/IP/WiFi[. = $iface]/@type = 'Hotspot'">
      <xsl:value-of select="concat('#Skiping Hotspot ',.,' ',$iface,$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('/sbin/ip addr ${1} 2002:${2}:',$sncnt,'::1/64 dev ',$iface,' &gt;/dev/null 2&gt;&amp;1',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Interface">
  <xsl:call-template name="setupiface">
    <xsl:with-param name="iface" select="."/>
    <xsl:with-param name="sncnt" select="position()+1"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="Tunnel">
  <xsl:param name="ifcnt"/>
  <xsl:call-template name="setupiface">
    <xsl:with-param name="iface" select="concat('gtun',position()-1)"/>
    <xsl:with-param name="sncnt" select="position()+1+$ifcnt"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash&#xa;&#xa;</xsl:text>

  <xsl:if test="$baseprefix != ''">
    <xsl:value-of select="concat('/sbin/ip addr ${1} 2002:${2}:',1,'::1/64 dev ',$intint,' &gt;/dev/null 2&gt;&amp;1',$nl)"/>
    <xsl:apply-templates select="/config/IP/Interfaces/Interface[(. != $intint) and ((. != $extint) or ($extcon = 'ADSL')) and 
                   (not(contains(/config/IP/SysConf/Option[@option='Bridge'],.))) and
                   (not(contains(.,':')))]"/>
    <xsl:variable name="ifcnt" select="count(/config/IP/Interfaces/Interface[(. != $intint) and
                   ((. != $extint) or ($extcon = 'ADSL')) and (not(contains(/config/IP/SysConf/Option[@option='Bridge'],.))) and
                   (not(contains(.,':')))])"/>

    <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel">
      <xsl:with-param name="ifcnt" select="$ifcnt"/>
    </xsl:apply-templates>
  </xsl:if>

  <xsl:text>
if [ -x /etc/firewall6.local ];then
  /sbin/ip6tables -F LOCALFWD
  /etc/firewall6.local 2002:${2}
fi;
</xsl:text>
</xsl:template>
</xsl:stylesheet>
