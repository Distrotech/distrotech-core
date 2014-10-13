<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>

<xsl:template name="header">
  <xsl:text>set no bouncemail;
set postmaster root@</xsl:text><xsl:value-of select="/config/DNS/Config/Option[@option = 'Domain']"/>
  <xsl:text>;&#xa;</xsl:text>
</xsl:template>

<xsl:template match="Collection[@protocol = 'etrn']">
  <xsl:call-template name="header"/>
  <xsl:text>poll </xsl:text>
  <xsl:value-of select="."/>
  <xsl:text> proto </xsl:text>
  <xsl:value-of select="@protocol"/>
  <xsl:text> no dns fetchdomains </xsl:text>
  <xsl:value-of select="@domain"/>
  <xsl:text>:</xsl:text>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="Collection[@protocol = 'odmr']">
  <xsl:call-template name="header"/>
  <xsl:text>poll "</xsl:text>
  <xsl:value-of select="."/>
  <xsl:text>" protocol ODMR :&#xa;</xsl:text>
    <xsl:text>  user "</xsl:text>
    <xsl:value-of select="@username"/>
    <xsl:text>" there with password "</xsl:text>
    <xsl:value-of select="@password"/>
    <xsl:text>" fetchdomains </xsl:text>
    <xsl:value-of select="@domain"/>
    <xsl:text>;&#xa;</xsl:text>
</xsl:template>

<xsl:template name="mdopts">
  <xsl:choose>
    <xsl:when test="@smtp != ''">
      <xsl:value-of select="concat(' smtphost ',@smtp)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat(' smtphost ',$fqdn)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>,</xsl:text>

  <xsl:if test="@usessl = 'true'">
    <xsl:text> options ssl,</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template match="Collection[(@protocol = 'pop3') or (@protocol = 'imap')]">
  <xsl:call-template name="header"/>

  <xsl:text>poll </xsl:text>
  <xsl:value-of select="."/>
  <xsl:text> proto </xsl:text>
  <xsl:value-of select="@protocol"/>
  <xsl:if test="@envelope != '-'">
    <xsl:value-of select="concat(' envelope ',@envelope)"/> 
  </xsl:if>
  <xsl:text> no dns</xsl:text>

  <xsl:choose>
    <xsl:when test="(@multidrop = '') or (@multidrop = 'true')">
      <xsl:text> localdomains </xsl:text>
      <xsl:value-of select="@domain"/>
      <xsl:text>:&#xa;</xsl:text>
        <xsl:text>  user "</xsl:text>
      <xsl:value-of select="@username"/>
      <xsl:text>", with pass "</xsl:text>
      <xsl:value-of select="@password"/>
      <xsl:text>", is *</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>:&#xa;</xsl:text>
        <xsl:text>  user "</xsl:text>
      <xsl:value-of select="@username"/>
      <xsl:text>", with pass "</xsl:text>
      <xsl:value-of select="@password"/>
      <xsl:text>", is "</xsl:text>
      <xsl:value-of select="concat(@domain,'&quot;')"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text> here,</xsl:text>
  <xsl:call-template name="mdopts"/>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="/config/Email/Collections/Collection"/>
</xsl:template>
</xsl:stylesheet>
