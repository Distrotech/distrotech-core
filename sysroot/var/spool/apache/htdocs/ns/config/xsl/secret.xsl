<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="is_modem" select="count(/config/IP/ADSL/Links/Link) or
                                      /config/IP/Sysonf/Option[@option = 'External'] = 'Dialup' or
                                      /config/IP/Dialup/Option[@option = 'Connection'] = 'ADSL'"/>

<!--
http://www.dpawson.co.uk/xsl/sect2/padding.html
-->

<xsl:template name="append-pad">
  <xsl:param name="padChar"/>
  <xsl:param name="padVar"/>
  <xsl:param name="length"/>
  <xsl:choose>
    <xsl:when test="string-length($padVar) &lt; $length">
      <xsl:call-template name="append-pad">
        <xsl:with-param name="padChar" select="$padChar"/>
        <xsl:with-param name="padVar" select="concat($padVar,$padChar)"/>
        <xsl:with-param name="length" select="$length"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="substring($padVar,string-length($padVar) - $length +1)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="User">
  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="."/>
    <xsl:with-param name="length" select="'50'"/>
  </xsl:call-template>
  <xsl:value-of select="concat('*     ',@password)"/>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="Link">
  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="@username"/>
    <xsl:with-param name="length" select="'50'"/>
  </xsl:call-template>
  <xsl:value-of select="concat('*     ',@password)"/>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:choose>
    <xsl:when test="$is_modem">
      <xsl:if test="(/config/IP/Dialup/Option[@option = 'Username'] != '') and (/config/IP/Dialup/Option[@option = 'Password'] != '')">
        <xsl:call-template name="append-pad">
          <xsl:with-param name="padChar" select="' '"/>
          <xsl:with-param name="padVar" select="/config/IP/Dialup/Option[@option = 'Username']"/>
          <xsl:with-param name="length" select="'50'"/>
        </xsl:call-template>
        <xsl:value-of select="concat('*     ',/config/IP/Dialup/Option[@option = 'Password'])"/>
        <xsl:text>&#xa;</xsl:text>
      </xsl:if>
      <xsl:apply-templates select="/config/IP/ADSL/Links/Link"/>
      <xsl:apply-templates select="/config/IP/ADSL/Users/User"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>#PPP Not Required On This System&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>
</xsl:stylesheet>
