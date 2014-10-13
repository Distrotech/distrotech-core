<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template name="parseserv">
  <xsl:param name="servers"/>
  <xsl:param name="position" select="'0'"/>
  <xsl:variable name="curserv" select="substring-before($servers,' ')"/>
  <xsl:variable name="nextserv" select="substring-after($servers,' ')"/>
  
  <xsl:if test="$curserv != ''">
    <xsl:value-of select="concat('server ',$curserv,' iburst burst minpoll 4')"/>
    <xsl:if test="$position = '0'">
      <xsl:text> prefer dynamic</xsl:text>
    </xsl:if>
    <xsl:text>&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="$nextserv != ''">
    <xsl:call-template name="parseserv">
      <xsl:with-param name="servers" select="$nextserv"/>
      <xsl:with-param name="position" select="$position+1"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template match="/config">
  <xsl:call-template name="parseserv">
    <xsl:with-param name="servers" select="concat(/config/IP/SysConf/Option[@option = 'NTPServer'],' ')"/>
  </xsl:call-template>
  <xsl:text>driftfile /tmp/ntp.drift
restrict default nopeer noquery nomodify
restrict 127.0.0.1
restrict ::1
</xsl:text>
</xsl:template>
</xsl:stylesheet>
