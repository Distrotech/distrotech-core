<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intint" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="pppint" select="/config/Radius/Config/Option[@option = 'PPPoEIF']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intint]/@ipaddr"/>
<xsl:variable name="pppip" select="/config/Radius/Config/Option[@option = 'PPPoE']"/>

<xsl:template name="numip">
  <xsl:param name="bits"/>
  <xsl:param name="total" select="1"/>

  <xsl:choose>
    <xsl:when test="$bits != '32'">
      <xsl:call-template name="numip">
        <xsl:with-param name="bits" select="$bits+1"/>
        <xsl:with-param name="total" select="$total * 2"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$total"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="pppoeserv">
  <xsl:if test="($pppip != '') or ($extcon = 'ADSL')">
    <xsl:text>PPPoESERVPID=`/bin/pidof </xsl:text>
    <xsl:choose>
      <xsl:when test="$pppip != ''">
        <xsl:text>pppoe-server</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>pppoe-relay</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text>`&#xa;&#xa;</xsl:text>
    <xsl:text>if [ "$PPPoESERVPID" ];then&#xa;</xsl:text>
    <xsl:text>  kill $PPPoESERVPID&#xa;</xsl:text>
    <xsl:text>  sleep 5&#xa;</xsl:text>
    <xsl:text>fi;&#xa;&#xa;</xsl:text>
  </xsl:if>

  <xsl:choose>
    <xsl:when test="/config/Radius/Config/Option[@option = 'PPPoE'] != ''">
      <xsl:text>#Start PPPoE Server&#xa;</xsl:text>
      <xsl:text>/usr/sbin/pppoe-server -sru -o 20 -I </xsl:text>
      <xsl:choose>
        <xsl:when test="$pppint != ''">
           <xsl:value-of select="$pppint"/>
        </xsl:when>
        <xsl:otherwise>
           <xsl:value-of select="$intint"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:value-of select="concat(' -L ',$intip,' -R ',substring-before($pppip,'/'),' -N ')"/>
      <xsl:call-template name="numip">
        <xsl:with-param name="bits" select="substring-after($pppip,'/')"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -S ',/config/DNS/Config/Option[@option = 'Domain'],$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test="($extcon = 'ADSL')">
        <xsl:text>#Start PPPoE Relay&#xa;</xsl:text>
        <xsl:text>/usr/sbin/pppoe-relay -C </xsl:text>
        <xsl:choose>
          <xsl:when test="$pppint != ''">
             <xsl:value-of select="$pppint"/>
          </xsl:when>
          <xsl:otherwise>
             <xsl:value-of select="$intint"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:value-of select="concat(' -S ',$extint,$nl)"/>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash&#xa;&#xa;</xsl:text>
  <xsl:if test="($extcon = 'ADSL') or ($extint = 'Dialup')">
    <xsl:text>#Using Dynamic Addressing&#xa;echo 1 > /proc/sys/net/ipv4/ip_dynaddr&#xa;&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="((($pppint != '') and ($pppint != $intint) and ($pppint != $extint)) or ($extint != $intint))">
    <xsl:call-template name="pppoeserv"/>
  </xsl:if>

</xsl:template>
</xsl:stylesheet>
